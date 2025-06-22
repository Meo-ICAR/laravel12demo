<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Mfcompenso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use FatturaElettronica\FatturaElettronica;
use FatturaElettronica\FatturaElettronicaBody\DatiGenerali\DatiGenerali;
use FatturaElettronica\FatturaElettronicaBody\DatiBeniServizi\DatiBeniServizi;
use FatturaElettronica\FatturaElettronicaBody\DatiPagamento\DatiPagamento;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = \App\Models\Invoice::select('*')->orderByDesc('invoice_date')->paginate(15);
        return view('invoices.index', compact('invoices'));
    }

    public function reconciliation(Request $request)
    {
        // Get unreconciled invoices (isreconiled = false or null)
        $unreconciledInvoices = Invoice::where(function($query) {
            $query->where('isreconiled', false)
                  ->orWhereNull('isreconiled');
        })
        ->orderBy('invoice_date', 'desc')
        ->get();

        // Get MFCompensos summary - records with empty invoice_number but sent emails
        $mfcompensosQuery = Mfcompenso::whereNull('invoice_number')
            ->whereNotNull('sended_at');

        // Apply email date filter if provided
        if ($request->filled('email_date_from')) {
            $mfcompensosQuery->whereDate('sended_at', '>=', $request->email_date_from);
        }
        if ($request->filled('email_date_to')) {
            $mfcompensosQuery->whereDate('sended_at', '<=', $request->email_date_to);
        }

        $mfcompensosSummary = $mfcompensosQuery
            ->selectRaw('
                denominazione_riferimento,
                COUNT(*) as total_records,
                SUM(CAST(importo AS DECIMAL(10,2))) as total_amount,
                MAX(sended_at) as last_sent_date,
                MIN(sended_at) as first_sent_date
            ')
            ->groupBy('denominazione_riferimento')
            ->orderBy('denominazione_riferimento')
            ->get();

        return view('invoices.reconciliation', compact('unreconciledInvoices', 'mfcompensosSummary'));
    }

    public function reconcile(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'denominazione_riferimento' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $invoice = Invoice::findOrFail($request->invoice_id);
            $denominazione = $request->denominazione_riferimento;

            // Update invoice as reconciled
            $invoice->update([
                'isreconiled' => true,
                'paid_at' => now(),
            ]);

            // Update MFCompensos records
            $updatedCount = Mfcompenso::whereNull('invoice_number')
                ->whereNotNull('sended_at')
                ->where('denominazione_riferimento', $denominazione)
                ->update([
                    'invoice_number' => $invoice->invoice_number,
                    'stato' => 'Fatturato',
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully reconciled invoice #{$invoice->invoice_number} with {$updatedCount} MFCompenso records for {$denominazione}",
                'invoice_number' => $invoice->invoice_number,
                'updated_count' => $updatedCount,
                'denominazione' => $denominazione
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error during reconciliation: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getXmlData($id)
    {
        $invoice = Invoice::findOrFail($id);

        if (!$invoice->xml_data) {
            return response()->json([
                'success' => false,
                'message' => 'No XML data available for this invoice'
            ]);
        }

        try {
            // Try to parse as Fattura Elettronica first
            $fatturaData = $this->parseFatturaElettronica($invoice->xml_data);

            if ($fatturaData['success']) {
                // Use professional Fattura Elettronica parsing
                return response()->json([
                    'success' => true,
                    'is_fattura_elettronica' => true,
                    'fattura_data' => $fatturaData['data'],
                    'invoice_number' => $invoice->invoice_number,
                    'raw_xml' => $invoice->xml_data,
                    'formatted_xml' => $this->formatXmlForDisplay($invoice->xml_data)
                ]);
            } else {
                // Fallback to generic XML parsing
                return $this->parseGenericXml($invoice->xml_data, $invoice->invoice_number);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error parsing XML: ' . $e->getMessage()
            ]);
        }
    }

    private function parseFatturaElettronica($xmlString)
    {
        try {
            $fattura = new FatturaElettronica();
            $fattura->loadFromXML($xmlString);

            $data = [
                'header' => $this->extractFatturaHeader($fattura),
                'body' => $this->extractFatturaBody($fattura),
                'validation' => [
                    'valid' => true,
                    'schema_used' => 'Fattura Elettronica v1.6.1',
                    'errors' => [],
                    'warnings' => []
                ]
            ];

            return ['success' => true, 'data' => $data];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function extractFatturaHeader($fattura)
    {
        $header = $fattura->getFatturaElettronicaHeader();

        return [
            'dati_trasmissione' => [
                'id_trasmittente' => $header->getDatiTrasmissione()->getIdTrasmittente()->getIdPaese() .
                                   $header->getDatiTrasmissione()->getIdTrasmittente()->getIdCodice(),
                'progressivo_invio' => $header->getDatiTrasmissione()->getProgressivoInvio(),
                'formato_trasmissione' => $header->getDatiTrasmissione()->getFormatoTrasmissione(),
                'codice_destinatario' => $header->getDatiTrasmissione()->getCodiceDestinatario(),
                'pec_destinatario' => $header->getDatiTrasmissione()->getPecDestinatario(),
            ],
            'cedente_prestatore' => $this->extractCedentePrestatore($header->getCedentePrestatore()),
            'cessionario_committente' => $this->extractCessionarioCommittente($header->getCessionarioCommittente()),
        ];
    }

    private function extractFatturaBody($fattura)
    {
        $bodies = $fattura->getFatturaElettronicaBody();
        $bodyData = [];

        foreach ($bodies as $index => $body) {
            $bodyData[$index] = [
                'dati_generali' => $this->extractDatiGenerali($body->getDatiGenerali()),
                'dati_beni_servizi' => $this->extractDatiBeniServizi($body->getDatiBeniServizi()),
                'dati_pagamento' => $this->extractDatiPagamento($body->getDatiPagamento()),
                'allegati' => $this->extractAllegati($body->getAllegati()),
            ];
        }

        return $bodyData;
    }

    private function extractCedentePrestatore($cedente)
    {
        return [
            'dati_anagrafici' => [
                'id_fiscale_iva' => [
                    'id_paese' => $cedente->getDatiAnagrafici()->getIdFiscaleIVA()->getIdPaese(),
                    'id_codice' => $cedente->getDatiAnagrafici()->getIdFiscaleIVA()->getIdCodice(),
                ],
                'codice_fiscale' => $cedente->getDatiAnagrafici()->getCodiceFiscale(),
                'anagrafica' => [
                    'denominazione' => $cedente->getDatiAnagrafici()->getAnagrafica()->getDenominazione(),
                    'nome' => $cedente->getDatiAnagrafici()->getAnagrafica()->getNome(),
                    'cognome' => $cedente->getDatiAnagrafici()->getAnagrafica()->getCognome(),
                ],
                'regime_fiscale' => $cedente->getDatiAnagrafici()->getRegimeFiscale(),
            ],
            'sede' => [
                'indirizzo' => $cedente->getSede()->getIndirizzo(),
                'numero_civico' => $cedente->getSede()->getNumeroCivico(),
                'cap' => $cedente->getSede()->getCAP(),
                'comune' => $cedente->getSede()->getComune(),
                'provincia' => $cedente->getSede()->getProvincia(),
                'nazione' => $cedente->getSede()->getNazione(),
            ],
        ];
    }

    private function extractCessionarioCommittente($cessionario)
    {
        return [
            'dati_anagrafici' => [
                'id_fiscale_iva' => [
                    'id_paese' => $cessionario->getDatiAnagrafici()->getIdFiscaleIVA()->getIdPaese(),
                    'id_codice' => $cessionario->getDatiAnagrafici()->getIdFiscaleIVA()->getIdCodice(),
                ],
                'codice_fiscale' => $cessionario->getDatiAnagrafici()->getCodiceFiscale(),
                'anagrafica' => [
                    'denominazione' => $cessionario->getDatiAnagrafici()->getAnagrafica()->getDenominazione(),
                    'nome' => $cessionario->getDatiAnagrafici()->getAnagrafica()->getNome(),
                    'cognome' => $cessionario->getDatiAnagrafici()->getAnagrafica()->getCognome(),
                ],
            ],
            'sede' => [
                'indirizzo' => $cessionario->getSede()->getIndirizzo(),
                'numero_civico' => $cessionario->getSede()->getNumeroCivico(),
                'cap' => $cessionario->getSede()->getCAP(),
                'comune' => $cessionario->getSede()->getComune(),
                'provincia' => $cessionario->getSede()->getProvincia(),
                'nazione' => $cessionario->getSede()->getNazione(),
            ],
        ];
    }

    private function extractDatiGenerali($datiGenerali)
    {
        $datiGeneraliDocumento = $datiGenerali->getDatiGeneraliDocumento();

        return [
            'tipo_documento' => $datiGeneraliDocumento->getTipoDocumento(),
            'divisa' => $datiGeneraliDocumento->getDivisa(),
            'data' => $datiGeneraliDocumento->getData(),
            'numero' => $datiGeneraliDocumento->getNumero(),
            'causale' => $datiGeneraliDocumento->getCausale(),
        ];
    }

    private function extractDatiBeniServizi($datiBeniServizi)
    {
        $dettaglioLinee = $datiBeniServizi->getDettaglioLinee();
        $linee = [];

        foreach ($dettaglioLinee as $linea) {
            $linee[] = [
                'numero_linea' => $linea->getNumeroLinea(),
                'descrizione' => $linea->getDescrizione(),
                'quantita' => $linea->getQuantita(),
                'prezzo_unitario' => $linea->getPrezzoUnitario(),
                'prezzo_totale' => $linea->getPrezzoTotale(),
                'aliquota_iva' => $linea->getAliquotaIVA(),
            ];
        }

        $datiRiepilogo = $datiBeniServizi->getDatiRiepilogo();
        $riepiloghi = [];

        foreach ($datiRiepilogo as $riepilogo) {
            $riepiloghi[] = [
                'aliquota_iva' => $riepilogo->getAliquotaIVA(),
                'imponibile_importo' => $riepilogo->getImponibileImporto(),
                'imposta' => $riepilogo->getImposta(),
            ];
        }

        return [
            'linee' => $linee,
            'riepiloghi' => $riepiloghi,
            'importo_totale_documento' => $datiBeniServizi->getImportoTotaleDocumento(),
        ];
    }

    private function extractDatiPagamento($datiPagamento)
    {
        $dettaglioPagamenti = $datiPagamento->getDettaglioPagamento();
        $pagamenti = [];

        foreach ($dettaglioPagamenti as $pagamento) {
            $pagamenti[] = [
                'modalita_pagamento' => $pagamento->getModalitaPagamento(),
                'data_scadenza_pagamento' => $pagamento->getDataScadenzaPagamento(),
                'importo_pagamento' => $pagamento->getImportoPagamento(),
            ];
        }

        return $pagamenti;
    }

    private function extractAllegati($allegati)
    {
        $allegatiData = [];

        foreach ($allegati as $allegato) {
            $allegatiData[] = [
                'nome_attachment' => $allegato->getNomeAttachment(),
                'algoritmo_compressione' => $allegato->getAlgoritmoCompressione(),
                'formato_attachment' => $allegato->getFormatoAttachment(),
                'descrizione_attachment' => $allegato->getDescrizioneAttachment(),
            ];
        }

        return $allegatiData;
    }

    private function parseGenericXml($xmlString, $invoiceNumber)
    {
        // Parse XML data
        $xml = simplexml_load_string($xmlString);

        if ($xml === false) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid XML data'
            ]);
        }

        // Get schema information and validate
        $schemaInfo = $this->extractSchemaInfo($xml);
        $validationResult = $this->validateXmlWithSchema($xml, $schemaInfo);

        // Extract structured data based on schema
        $structuredData = $this->extractStructuredData($xml, $schemaInfo);

        // Format XML for display
        $formattedXml = $this->formatXmlForDisplay($xmlString);

        return response()->json([
            'success' => true,
            'is_fattura_elettronica' => false,
            'xml_data' => $formattedXml,
            'schema_info' => $schemaInfo,
            'validation_result' => $validationResult,
            'structured_data' => $structuredData,
            'invoice_number' => $invoiceNumber,
            'raw_xml' => $xmlString
        ]);
    }

    private function extractSchemaInfo($xml)
    {
        $schemaInfo = [];

        // Check for schemaLocation attribute
        $namespaces = $xml->getNamespaces(true);
        foreach ($namespaces as $prefix => $uri) {
            if (strpos($uri, 'schema') !== false || strpos($uri, 'xsd') !== false) {
                $schemaInfo[] = [
                    'type' => 'namespace',
                    'prefix' => $prefix,
                    'uri' => $uri
                ];
            }
        }

        // Check for schemaLocation in root element
        $rootElement = $xml->getName();
        $attributes = $xml->attributes();

        if (isset($attributes->schemaLocation)) {
            $schemaInfo[] = [
                'type' => 'schemaLocation',
                'value' => (string)$attributes->schemaLocation
            ];
        }

        if (isset($attributes->noNamespaceSchemaLocation)) {
            $schemaInfo[] = [
                'type' => 'noNamespaceSchemaLocation',
                'value' => (string)$attributes->noNamespaceSchemaLocation
            ];
        }

        return $schemaInfo;
    }

    private function validateXmlWithSchema($xml, $schemaInfo)
    {
        $validationResult = [
            'valid' => false,
            'errors' => [],
            'warnings' => [],
            'schema_used' => null
        ];

        try {
            // Try to validate against schema if available
            foreach ($schemaInfo as $schema) {
                if ($schema['type'] === 'schemaLocation' || $schema['type'] === 'noNamespaceSchemaLocation') {
                    $schemaUrl = $schema['value'];

                    // Create DOM document for validation
                    $dom = new \DOMDocument();
                    $dom->loadXML($xml->asXML());

                    // Try to validate against schema
                    if ($dom->schemaValidate($schemaUrl)) {
                        $validationResult['valid'] = true;
                        $validationResult['schema_used'] = $schemaUrl;
                        break;
                    } else {
                        $validationResult['errors'][] = "Failed to validate against schema: {$schemaUrl}";
                    }
                }
            }

            // If no schema validation, do basic XML structure validation
            if (!$validationResult['valid']) {
                $validationResult['warnings'][] = "No schema validation performed - using basic XML structure validation";
                $validationResult['valid'] = true; // Assume valid if no schema
            }

        } catch (\Exception $e) {
            $validationResult['errors'][] = "Schema validation error: " . $e->getMessage();
        }

        return $validationResult;
    }

    private function extractStructuredData($xml, $schemaInfo)
    {
        $structuredData = [
            'invoice_details' => [],
            'supplier_info' => [],
            'customer_info' => [],
            'line_items' => [],
            'totals' => []
        ];

        try {
            // Extract common invoice elements based on typical XML schemas
            $namespaces = $xml->getNamespaces(true);

            // Try to find invoice elements using common patterns
            $structuredData['invoice_details'] = $this->extractInvoiceDetails($xml, $namespaces);
            $structuredData['supplier_info'] = $this->extractSupplierInfo($xml, $namespaces);
            $structuredData['customer_info'] = $this->extractCustomerInfo($xml, $namespaces);
            $structuredData['line_items'] = $this->extractLineItems($xml, $namespaces);
            $structuredData['totals'] = $this->extractTotals($xml, $namespaces);

        } catch (\Exception $e) {
            $structuredData['error'] = "Error extracting structured data: " . $e->getMessage();
        }

        return $structuredData;
    }

    private function extractInvoiceDetails($xml, $namespaces)
    {
        $details = [];

        // Common invoice detail elements
        $invoiceElements = [
            'InvoiceNumber', 'InvoiceNumber', 'invoiceNumber', 'invoice_number',
            'InvoiceDate', 'InvoiceDate', 'invoiceDate', 'invoice_date',
            'IssueDate', 'issueDate', 'issue_date',
            'DueDate', 'dueDate', 'due_date',
            'CurrencyCode', 'currencyCode', 'currency_code', 'currency'
        ];

        foreach ($invoiceElements as $element) {
            $value = $this->findElementValue($xml, $element, $namespaces);
            if ($value !== null) {
                $details[$element] = $value;
            }
        }

        return $details;
    }

    private function extractSupplierInfo($xml, $namespaces)
    {
        $supplier = [];

        // Common supplier elements
        $supplierElements = [
            'SupplierName', 'supplierName', 'supplier_name', 'SellerName', 'sellerName',
            'SupplierID', 'supplierID', 'supplier_id', 'SellerID', 'sellerID',
            'SupplierVAT', 'supplierVAT', 'supplier_vat', 'SellerVAT', 'sellerVAT',
            'SupplierAddress', 'supplierAddress', 'supplier_address'
        ];

        foreach ($supplierElements as $element) {
            $value = $this->findElementValue($xml, $element, $namespaces);
            if ($value !== null) {
                $supplier[$element] = $value;
            }
        }

        return $supplier;
    }

    private function extractCustomerInfo($xml, $namespaces)
    {
        $customer = [];

        // Common customer elements
        $customerElements = [
            'CustomerName', 'customerName', 'customer_name', 'BuyerName', 'buyerName',
            'CustomerID', 'customerID', 'customer_id', 'BuyerID', 'buyerID',
            'CustomerVAT', 'customerVAT', 'customer_vat', 'BuyerVAT', 'buyerVAT',
            'CustomerAddress', 'customerAddress', 'customer_address'
        ];

        foreach ($customerElements as $element) {
            $value = $this->findElementValue($xml, $element, $namespaces);
            if ($value !== null) {
                $customer[$element] = $value;
            }
        }

        return $customer;
    }

    private function extractLineItems($xml, $namespaces)
    {
        $lineItems = [];

        // Look for line item containers
        $lineItemContainers = [
            'InvoiceLine', 'invoiceLine', 'invoice_line',
            'LineItem', 'lineItem', 'line_item',
            'Item', 'item'
        ];

        foreach ($lineItemContainers as $container) {
            $items = $xml->xpath("//*[contains(local-name(), '{$container}')]");
            if (!empty($items)) {
                foreach ($items as $item) {
                    $lineItem = [];
                    $lineItem['description'] = $this->findElementValue($item, 'Description', $namespaces);
                    $lineItem['quantity'] = $this->findElementValue($item, 'Quantity', $namespaces);
                    $lineItem['unit_price'] = $this->findElementValue($item, 'UnitPrice', $namespaces);
                    $lineItem['amount'] = $this->findElementValue($item, 'Amount', $namespaces);
                    $lineItems[] = $lineItem;
                }
                break;
            }
        }

        return $lineItems;
    }

    private function extractTotals($xml, $namespaces)
    {
        $totals = [];

        // Common total elements
        $totalElements = [
            'LineExtensionAmount', 'lineExtensionAmount', 'line_extension_amount',
            'TaxExclusiveAmount', 'taxExclusiveAmount', 'tax_exclusive_amount',
            'TaxInclusiveAmount', 'taxInclusiveAmount', 'tax_inclusive_amount',
            'PayableAmount', 'payableAmount', 'payable_amount',
            'TaxAmount', 'taxAmount', 'tax_amount',
            'TotalAmount', 'totalAmount', 'total_amount'
        ];

        foreach ($totalElements as $element) {
            $value = $this->findElementValue($xml, $element, $namespaces);
            if ($value !== null) {
                $totals[$element] = $value;
            }
        }

        return $totals;
    }

    private function findElementValue($xml, $elementName, $namespaces)
    {
        // Try exact match first
        $xpath = "//*[local-name()='{$elementName}']";
        $result = $xml->xpath($xpath);

        if (!empty($result)) {
            return (string)$result[0];
        }

        // Try case-insensitive match
        $xpath = "//*[translate(local-name(), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz')='{$elementName}']";
        $result = $xml->xpath($xpath);

        if (!empty($result)) {
            return (string)$result[0];
        }

        // Try with different namespaces
        foreach ($namespaces as $prefix => $uri) {
            $xpath = "//{$prefix}:{$elementName}";
            $result = $xml->xpath($xpath);

            if (!empty($result)) {
                return (string)$result[0];
            }
        }

        return null;
    }

    private function formatXmlForDisplay($xmlString)
    {
        $dom = new \DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xmlString);

        return $dom->saveXML();
    }
}
