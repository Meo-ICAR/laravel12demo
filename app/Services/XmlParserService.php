<?php

namespace App\Services;

use FatturaElettronicaPhp\FatturaElettronica\DigitalDocument;
use FatturaElettronicaPhp\FatturaElettronica\Parser\DigitalDocumentParser;
use FatturaElettronicaPhp\FatturaElettronica\FatturaElettronica;
use FatturaElettronicaPhp\FatturaElettronica\FatturaElettronicaBody\DatiGenerali\DatiGenerali;
use FatturaElettronicaPhp\FatturaElettronica\FatturaElettronicaBody\DatiBeniServizi\DatiBeniServizi;
use FatturaElettronicaPhp\FatturaElettronica\FatturaElettronicaBody\DatiPagamento\DatiPagamento;
use FatturaElettronicaPhp\FatturaElettronica\Decoder\XMLDecoder;
use Illuminate\Support\Facades\Log;

class XmlParserService
{
    /**
     * Parse Fattura Elettronica XML
     */
    public function parseFatturaElettronica(string $xmlString): array
    {
        try {
            $parser = new DigitalDocumentParser();
            $document = $parser->parse($xmlString);

            if (!$document instanceof DigitalDocument) {
                throw new \Exception('Invalid document type');
            }

            return [
                'success' => true,
                'data' => [
                    'supplier' => $this->extractSupplierFromDocument($document),
                    'customer' => $this->extractCustomerFromDocument($document),
                    'invoice_details' => $this->extractInvoiceDetailsFromDocument($document),
                    'line_items' => $this->extractLineItemsFromDocument($document),
                    'totals' => $this->extractTotalsFromDocument($document),
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error parsing Fattura Elettronica XML', [
                'error' => $e->getMessage(),
                'xml_length' => strlen($xmlString)
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Parse generic XML
     */
    public function parseGenericXml(string $xmlString, string $invoiceNumber): array
    {
        try {
            $xml = simplexml_load_string($xmlString);
            if (!$xml) {
                throw new \Exception('Invalid XML format');
            }

            $schemaInfo = $this->extractSchemaInfo($xml);

            if (!$this->validateXmlWithSchema($xml, $schemaInfo)) {
                throw new \Exception('XML validation failed');
            }

            $namespaces = $xml->getNamespaces(true);

            return [
                'success' => true,
                'data' => [
                    'supplier' => $this->extractSupplierInfo($xml, $namespaces),
                    'customer' => $this->extractCustomerInfo($xml, $namespaces),
                    'invoice_details' => $this->extractInvoiceDetails($xml, $namespaces),
                    'line_items' => $this->extractLineItems($xml, $namespaces),
                    'totals' => $this->extractTotals($xml, $namespaces),
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error parsing generic XML', [
                'error' => $e->getMessage(),
                'invoice_number' => $invoiceNumber
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Extract supplier information from Fattura Elettronica document
     */
    private function extractSupplierFromDocument(DigitalDocument $document): array
    {
        $supplier = $document->getFatturaElettronicaBody()[0]->getDatiBeniServizi()->getDettaglioLinee()[0]->getDatiRiepilogo()[0]->getAliquotaIVA();

        return [
            'name' => $document->getFatturaElettronicaHeader()->getCedentePrestatore()->getDatiAnagrafici()->getAnagrafica()->getDenominazione(),
            'vat_number' => $document->getFatturaElettronicaHeader()->getCedentePrestatore()->getDatiAnagrafici()->getIdFiscaleIVA()->getIdCodice(),
            'address' => $document->getFatturaElettronicaHeader()->getCedentePrestatore()->getSede()->getIndirizzo(),
            'city' => $document->getFatturaElettronicaHeader()->getCedentePrestatore()->getSede()->getComune(),
            'postal_code' => $document->getFatturaElettronicaHeader()->getCedentePrestatore()->getSede()->getCAP(),
            'country' => $document->getFatturaElettronicaHeader()->getCedentePrestatore()->getSede()->getNazione(),
        ];
    }

    /**
     * Extract customer information from Fattura Elettronica document
     */
    private function extractCustomerFromDocument(DigitalDocument $document): array
    {
        return [
            'name' => $document->getFatturaElettronicaHeader()->getCessionarioCommittente()->getDatiAnagrafici()->getAnagrafica()->getDenominazione(),
            'vat_number' => $document->getFatturaElettronicaHeader()->getCessionarioCommittente()->getDatiAnagrafici()->getIdFiscaleIVA()->getIdCodice(),
            'address' => $document->getFatturaElettronicaHeader()->getCessionarioCommittente()->getSede()->getIndirizzo(),
            'city' => $document->getFatturaElettronicaHeader()->getCessionarioCommittente()->getSede()->getComune(),
            'postal_code' => $document->getFatturaElettronicaHeader()->getCessionarioCommittente()->getSede()->getCAP(),
            'country' => $document->getFatturaElettronicaHeader()->getCessionarioCommittente()->getSede()->getNazione(),
        ];
    }

    /**
     * Extract invoice details from Fattura Elettronica document
     */
    private function extractInvoiceDetailsFromDocument(DigitalDocument $document): array
    {
        $datiGenerali = $document->getFatturaElettronicaBody()[0]->getDatiGenerali();

        return [
            'invoice_number' => $datiGenerali->getDatiGeneraliDocumento()->getNumero(),
            'invoice_date' => $datiGenerali->getDatiGeneraliDocumento()->getData(),
            'currency' => $datiGenerali->getDatiGeneraliDocumento()->getDivisa(),
            'document_type' => $datiGenerali->getDatiGeneraliDocumento()->getTipoDocumento(),
        ];
    }

    /**
     * Extract line items from Fattura Elettronica document
     */
    private function extractLineItemsFromDocument(DigitalDocument $document): array
    {
        $lineItems = [];
        $dettaglioLinee = $document->getFatturaElettronicaBody()[0]->getDatiBeniServizi()->getDettaglioLinee();

        foreach ($dettaglioLinee as $linea) {
            $lineItems[] = [
                'description' => $linea->getDescrizione(),
                'quantity' => $linea->getQuantita(),
                'unit_price' => $linea->getPrezzoUnitario(),
                'total_price' => $linea->getPrezzoTotale(),
                'vat_rate' => $linea->getAliquotaIVA(),
            ];
        }

        return $lineItems;
    }

    /**
     * Extract totals from Fattura Elettronica document
     */
    private function extractTotalsFromDocument(DigitalDocument $document): array
    {
        $datiRiepilogo = $document->getFatturaElettronicaBody()[0]->getDatiBeniServizi()->getDatiRiepilogo();

        $totals = [
            'subtotal' => 0,
            'vat_total' => 0,
            'total' => 0,
        ];

        foreach ($datiRiepilogo as $riepilogo) {
            $totals['subtotal'] += $riepilogo->getImponibileImporto();
            $totals['vat_total'] += $riepilogo->getImposta();
        }

        $totals['total'] = $totals['subtotal'] + $totals['vat_total'];

        return $totals;
    }

    /**
     * Extract schema information from XML
     */
    private function extractSchemaInfo(\SimpleXMLElement $xml): array
    {
        $namespaces = $xml->getNamespaces(true);
        $schemaInfo = [];

        foreach ($namespaces as $prefix => $uri) {
            if (strpos($uri, 'fattura') !== false || strpos($uri, 'invoice') !== false) {
                $schemaInfo['type'] = 'invoice';
                $schemaInfo['namespace'] = $uri;
                $schemaInfo['prefix'] = $prefix;
                break;
            }
        }

        return $schemaInfo;
    }

    /**
     * Validate XML with schema
     */
    private function validateXmlWithSchema(\SimpleXMLElement $xml, array $schemaInfo): bool
    {
        // Basic validation - check if required elements exist
        $requiredElements = ['invoice', 'supplier', 'customer', 'items'];

        foreach ($requiredElements as $element) {
            if (!$xml->xpath("//*[contains(local-name(), '$element')]")) {
                return false;
            }
        }

        return true;
    }

    /**
     * Extract supplier information from generic XML
     */
    private function extractSupplierInfo(\SimpleXMLElement $xml, array $namespaces): array
    {
        return [
            'name' => $this->findElementValue($xml, 'supplier_name', $namespaces),
            'vat_number' => $this->findElementValue($xml, 'supplier_vat', $namespaces),
            'address' => $this->findElementValue($xml, 'supplier_address', $namespaces),
            'city' => $this->findElementValue($xml, 'supplier_city', $namespaces),
            'postal_code' => $this->findElementValue($xml, 'supplier_postal_code', $namespaces),
            'country' => $this->findElementValue($xml, 'supplier_country', $namespaces),
        ];
    }

    /**
     * Extract customer information from generic XML
     */
    private function extractCustomerInfo(\SimpleXMLElement $xml, array $namespaces): array
    {
        return [
            'name' => $this->findElementValue($xml, 'customer_name', $namespaces),
            'vat_number' => $this->findElementValue($xml, 'customer_vat', $namespaces),
            'address' => $this->findElementValue($xml, 'customer_address', $namespaces),
            'city' => $this->findElementValue($xml, 'customer_city', $namespaces),
            'postal_code' => $this->findElementValue($xml, 'customer_postal_code', $namespaces),
            'country' => $this->findElementValue($xml, 'customer_country', $namespaces),
        ];
    }

    /**
     * Extract invoice details from generic XML
     */
    private function extractInvoiceDetails(\SimpleXMLElement $xml, array $namespaces): array
    {
        return [
            'invoice_number' => $this->findElementValue($xml, 'invoice_number', $namespaces),
            'invoice_date' => $this->findElementValue($xml, 'invoice_date', $namespaces),
            'currency' => $this->findElementValue($xml, 'currency', $namespaces),
            'document_type' => $this->findElementValue($xml, 'document_type', $namespaces),
        ];
    }

    /**
     * Extract line items from generic XML
     */
    private function extractLineItems(\SimpleXMLElement $xml, array $namespaces): array
    {
        $lineItems = [];
        $items = $xml->xpath("//*[contains(local-name(), 'item') or contains(local-name(), 'line')]");

        foreach ($items as $item) {
            $lineItems[] = [
                'description' => $this->findElementValue($item, 'description', $namespaces),
                'quantity' => $this->findElementValue($item, 'quantity', $namespaces),
                'unit_price' => $this->findElementValue($item, 'unit_price', $namespaces),
                'total_price' => $this->findElementValue($item, 'total_price', $namespaces),
                'vat_rate' => $this->findElementValue($item, 'vat_rate', $namespaces),
            ];
        }

        return $lineItems;
    }

    /**
     * Extract totals from generic XML
     */
    private function extractTotals(\SimpleXMLElement $xml, array $namespaces): array
    {
        return [
            'subtotal' => $this->findElementValue($xml, 'subtotal', $namespaces),
            'vat_total' => $this->findElementValue($xml, 'vat_total', $namespaces),
            'total' => $this->findElementValue($xml, 'total', $namespaces),
        ];
    }

    /**
     * Find element value in XML with namespace support
     */
    private function findElementValue(\SimpleXMLElement $xml, string $elementName, array $namespaces): string
    {
        $value = '';

        // Try different possible element names
        $possibleNames = [
            $elementName,
            str_replace('_', '', $elementName),
            ucfirst($elementName),
            strtoupper($elementName),
        ];

        foreach ($possibleNames as $name) {
            // Try without namespace
            $element = $xml->xpath("//*[local-name()='$name']");
            if (!empty($element)) {
                $value = (string) $element[0];
                break;
            }

            // Try with namespaces
            foreach ($namespaces as $prefix => $uri) {
                $element = $xml->xpath("//$prefix:$name");
                if (!empty($element)) {
                    $value = (string) $element[0];
                    break 2;
                }
            }
        }

        return $value;
    }

    /**
     * Format XML for display
     */
    public function formatXmlForDisplay(string $xmlString): string
    {
        $dom = new \DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xmlString);

        return $dom->saveXML();
    }
}
