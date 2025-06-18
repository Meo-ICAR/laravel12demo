<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Invoice;
use SimpleXMLElement;
use DOMDocument;
use Exception;

class InvoiceImportController extends Controller
{
    public function index()
    {
        return view('invoices.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'xml_file' => 'required|file|mimes:xml|max:10240', // 10MB max
        ]);

        try {
            // Load and validate XML
            $xmlContent = file_get_contents($request->file('xml_file')->getRealPath());

            // Check if the file is empty
            if (empty($xmlContent)) {
                return back()->with('error', 'The XML file is empty.');
            }

            // Basic XML syntax validation
            libxml_use_internal_errors(true);
            $dom = new DOMDocument();
            $dom->loadXML($xmlContent, LIBXML_NOWARNING | LIBXML_NOERROR);
            $errors = libxml_get_errors();
            libxml_clear_errors();

            if (!empty($errors)) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $this->formatXMLError($error);
                }
                return back()->with('error', 'XML syntax error: ' . implode(', ', $errorMessages));
            }

            // Parse XML
            $xml = new SimpleXMLElement($xmlContent);

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
                return back()->with('error', $errorMessage);
            }

            // Register the FatturaPA namespace
            // If it's a default namespace (empty prefix), use 'p' as our working prefix
            $workingPrefix = $namespacePrefix ?: 'p';
            $xml->registerXPathNamespace($workingPrefix, $namespaces[$namespacePrefix]);

            // Process each invoice in the XML
            $invoices = $xml->xpath('//' . $workingPrefix . ':FatturaElettronica');

            if (empty($invoices)) {
                return back()->with('error', 'No valid FatturaPA invoices found in the XML file.');
            }

            $importedCount = 0;
            $errors = [];

            foreach ($invoices as $invoiceXml) {
                try {
                    $this->validateInvoiceData($invoiceXml);
                    $this->processInvoice($invoiceXml);
                    $importedCount++;
                } catch (Exception $e) {
                    Log::error('Error processing invoice: ' . $e->getMessage());
                    $errors[] = 'Error processing invoice: ' . $e->getMessage();
                }
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

        // Calculate total tax amount from DatiRiepilogo
        $taxAmount = 0;
        foreach ($body->DatiBeniServizi->DatiRiepilogo as $riepilogo) {
            $taxAmount += (float)$riepilogo->Imposta;
        }

        // Create invoice record
        $invoice = new Invoice();
        $invoice->fornitore_piva = (string)$header->CedentePrestatore->DatiAnagrafici->IdFiscaleIVA->IdCodice;
        $invoice->fornitore = (string)$header->CedentePrestatore->DatiAnagrafici->Anagrafica->Denominazione;
        $invoice->cliente_piva = (string)$header->CessionarioCommittente->DatiAnagrafici->IdFiscaleIVA->IdCodice;
        $invoice->cliente = (string)$header->CessionarioCommittente->DatiAnagrafici->Anagrafica->Denominazione;
        $invoice->invoice_number = (string)$body->DatiGenerali->DatiGeneraliDocumento->Numero;
        $invoice->invoice_date = date('Y-m-d H:i:s', strtotime((string)$body->DatiGenerali->DatiGeneraliDocumento->Data));
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
}
