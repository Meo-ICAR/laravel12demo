<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Invoice;
use SimpleXMLElement;
use DOMDocument;
use Exception;
use Illuminate\Support\Facades\Validator;

class InvoiceImportController extends Controller
{
    public function index()
    {
        return view('invoices.import');
    }

    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'xml_file' => 'required|file|mimes:xml,zip|max:10240',
        ], [
            'xml_file.mimes' => 'The file must be an XML or ZIP file.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $file = $request->file('xml_file');
            $importedCount = 0;
            $errors = [];

            if ($file->getClientOriginalExtension() === 'zip') {
                // Handle ZIP file
                $zip = new \ZipArchive;
                if ($zip->open($file->getRealPath()) === TRUE) {
                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $entry = $zip->getNameIndex($i);
                        if (strtolower(pathinfo($entry, PATHINFO_EXTENSION)) === 'xml') {
                            $xmlContent = $zip->getFromIndex($i);
                            if (empty($xmlContent)) {
                                $errors[] = "File $entry is empty.";
                                continue;
                            }
                            try {
                                $this->importXmlContent($xmlContent, $importedCount, $errors);
                            } catch (Exception $e) {
                                $errors[] = "File $entry: " . $e->getMessage();
                            }
                        }
                    }
                    $zip->close();
                } else {
                    return back()->with('error', 'Could not open ZIP file.');
                }
            } else {
                // Handle single XML file
                $xmlContent = file_get_contents($file->getRealPath());
                if (empty($xmlContent)) {
                    return back()->with('error', 'The XML file is empty.');
                }
                $this->importXmlContent($xmlContent, $importedCount, $errors);
            }

            if ($importedCount > 0) {
                $message = "Successfully imported {$importedCount} invoices.";
                if (!empty($errors)) {
                    $message .= " However, there were some errors: " . implode(', ', $errors);
                }
                return redirect()->route('invoices.index')->with('success', $message);
            }

            return back()->with('error', 'No valid invoices could be imported. Errors: ' . implode(', ', $errors));
        } catch (Exception $e) {
            Log::error('XML Import Error: ' . $e->getMessage());
            return back()->with('error', 'Error processing XML file: ' . $e->getMessage());
        }
    }

    protected function validateInvoiceData($invoiceXml)
    {
        // Validate required fields
        $requiredFields = [
            'FatturaElettronicaHeader/CedentePrestatore/DatiAnagrafici/IdFiscaleIVA/IdCodice',
            'FatturaElettronicaHeader/CedentePrestatore/DatiAnagrafici/Anagrafica/Denominazione',
            'FatturaElettronicaHeader/CessionarioCommittente/DatiAnagrafici/IdFiscaleIVA/IdCodice',
            'FatturaElettronicaHeader/CessionarioCommittente/DatiAnagrafici/Anagrafica/Denominazione',
            'FatturaElettronicaBody/DatiGenerali/DatiGeneraliDocumento/Numero',
            'FatturaElettronicaBody/DatiGenerali/DatiGeneraliDocumento/Data',
            'FatturaElettronicaBody/DatiGenerali/DatiGeneraliDocumento/ImportoTotaleDocumento',
            'FatturaElettronicaBody/DatiBeniServizi/DatiRiepilogo/Imposta',
            'FatturaElettronicaBody/DatiGenerali/DatiGeneraliDocumento/Divisa'
        ];

        foreach ($requiredFields as $field) {
            if (empty($invoiceXml->xpath($field))) {
                throw new Exception("Missing required field: {$field}");
            }
        }

        // Validate date format
        $date = (string)$invoiceXml->FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento->Data;
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            throw new Exception("Invalid date format: {$date}");
        }

        // Validate amounts
        $totalAmount = (float)$invoiceXml->FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento->ImportoTotaleDocumento;

        // Get tax amount from DatiRiepilogo
        $taxAmount = 0;
        foreach ($invoiceXml->FatturaElettronicaBody->DatiBeniServizi->DatiRiepilogo as $riepilogo) {
            $taxAmount += (float)$riepilogo->Imposta;
        }

        if ($totalAmount < 0) {
            throw new Exception("Invalid total amount: {$totalAmount}");
        }
        if ($taxAmount < 0) {
            throw new Exception("Invalid tax amount: {$taxAmount}");
        }

        // Validate VAT numbers format (Italian VAT number)
        $fornitorePiva = (string)$invoiceXml->FatturaElettronicaHeader->CedentePrestatore->DatiAnagrafici->IdFiscaleIVA->IdCodice;
        $clientePiva = (string)$invoiceXml->FatturaElettronicaHeader->CessionarioCommittente->DatiAnagrafici->IdFiscaleIVA->IdCodice;

        if (!preg_match('/^[0-9]{11}$/', $fornitorePiva)) {
            throw new Exception("Invalid supplier VAT number format: {$fornitorePiva}");
        }
        if (!preg_match('/^[0-9]{11}$/', $clientePiva)) {
            throw new Exception("Invalid customer VAT number format: {$clientePiva}");
        }
    }

    protected function processInvoice($invoiceXml)
    {
        // Extract basic invoice information
        $header = $invoiceXml->FatturaElettronicaHeader;
        $body = $invoiceXml->FatturaElettronicaBody;

        $cliente_piva = (string)$header->CessionarioCommittente->DatiAnagrafici->IdFiscaleIVA->IdCodice;
        $fornitore_piva = (string)$header->CedentePrestatore->DatiAnagrafici->IdFiscaleIVA->IdCodice;
        $invoice_number = (string)$body->DatiGenerali->DatiGeneraliDocumento->Numero;
        $invoice_date = date('Y-m-d H:i:s', strtotime((string)$body->DatiGenerali->DatiGeneraliDocumento->Data));

        // Skip if invoice with same invoice_number, invoice_date, cliente_piva, and fornitore_piva exists
        if (\App\Models\Invoice::where('invoice_number', $invoice_number)
            ->where('invoice_date', $invoice_date)
            ->where('cliente_piva', $cliente_piva)
            ->where('fornitore_piva', $fornitore_piva)
            ->exists()) {
            throw new \Exception("Invoice with invoice_number $invoice_number, invoice_date $invoice_date, cliente_piva $cliente_piva, and fornitore_piva $fornitore_piva already exists. Skipped.");
        }

        // Calculate total tax amount from DatiRiepilogo
        $taxAmount = 0;
        foreach ($body->DatiBeniServizi->DatiRiepilogo as $riepilogo) {
            $taxAmount += (float)$riepilogo->Imposta;
        }

        // Create invoice record
        $invoice = new Invoice();
        $invoice->fornitore_piva = $fornitore_piva;
        $invoice->fornitore = (string)$header->CedentePrestatore->DatiAnagrafici->Anagrafica->Denominazione;
        $invoice->cliente_piva = $cliente_piva;
        $invoice->cliente = (string)$header->CessionarioCommittente->DatiAnagrafici->Anagrafica->Denominazione;
        $invoice->invoice_number = $invoice_number;
        $invoice->invoice_date = $invoice_date;
        $invoice->total_amount = (float)$body->DatiGenerali->DatiGeneraliDocumento->ImportoTotaleDocumento;
        $invoice->tax_amount = $taxAmount;
        $invoice->currency = (string)$body->DatiGenerali->DatiGeneraliDocumento->Divisa;
        $invoice->payment_method = (string)$body->DatiPagamento->CondizioniPagamento;
        $invoice->status = 'imported';
        $invoice->xml_data = $invoiceXml->asXML();
        $invoice->save();

        return $invoice;
    }

    protected function formatXMLError($error)
    {
        $message = trim($error->message);
        $line = $error->line;
        $column = $error->column;
        return "Line {$line}, Column {$column}: {$message}";
    }

    // Helper to import XML content (single or from ZIP)
    protected function importXmlContent($xmlContent, &$importedCount, array &$errors)
    {
        // Basic XML syntax validation
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadXML($xmlContent, LIBXML_NOWARNING | LIBXML_NOERROR);
        $xmlErrors = libxml_get_errors();
        libxml_clear_errors();

        if (!empty($xmlErrors)) {
            $errorMessages = [];
            foreach ($xmlErrors as $error) {
                $errorMessages[] = $this->formatXMLError($error);
            }
            throw new \Exception('XML syntax error: ' . implode(', ', $errorMessages));
        }

        // Parse XML
        $xml = new \SimpleXMLElement($xmlContent);

        // Check for FatturaPA namespace
        $namespaces = $xml->getNamespaces(true);
        $foundNamespace = false;
        $namespacePrefix = null;

        $validNamespaces = [
            'http://ivaservizi.agenziaentrate.gov.it/docs/xsd/fatture/v1.2',
            'http://www.fatturapa.gov.it/sdi/fatturapa/v1.1',
            'http://ivaservizi.agenziaentrate.gov.it/docs/xsd/fatture/v1.1',
            'http://www.fatturapa.gov.it/sdi/fatturapa/v1.0',
            'http://ivaservizi.agenziaentrate.gov.it/docs/xsd/fatture/v1.0'
        ];

        // Check both prefixed and default namespaces
        foreach ($namespaces as $prefix => $uri) {
            if (in_array($uri, $validNamespaces)) {
                $foundNamespace = true;
                $namespacePrefix = $prefix;
                break;
            }
        }

        if (!$foundNamespace) {
            $errorMessage = "Invalid XML namespace. Expected one of the following FatturaPA namespaces:\n";
            foreach ($validNamespaces as $namespace) {
                $errorMessage .= "- {$namespace}\n";
            }
            $errorMessage .= "\nFound namespaces:\n";
            foreach ($namespaces as $prefix => $uri) {
                $errorMessage .= "- {$prefix}: {$uri}\n";
            }
            throw new \Exception($errorMessage);
        }

        // Register the FatturaPA namespace
        // If it's a default namespace (empty prefix), use 'p' as our working prefix
        $workingPrefix = $namespacePrefix ?: 'p';
        $xml->registerXPathNamespace($workingPrefix, $namespaces[$namespacePrefix]);

        // Process each invoice in the XML
        $invoices = $xml->xpath('//' . $workingPrefix . ':FatturaElettronica');

        if (empty($invoices)) {
            throw new \Exception('No valid FatturaPA invoices found in the XML file.');
        }

        foreach ($invoices as $invoiceXml) {
            try {
                $this->validateInvoiceData($invoiceXml);
                $this->processInvoice($invoiceXml);
                $importedCount++;
            } catch (\Exception $e) {
                $errors[] = 'Error processing invoice: ' . $e->getMessage();
            }
        }
    }
}
