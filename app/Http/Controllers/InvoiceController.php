<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Provvigione;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use FatturaElettronicaPhp\FatturaElettronica\DigitalDocument;
use FatturaElettronicaPhp\FatturaElettronica\Parser\DigitalDocumentParser;
use FatturaElettronicaPhp\FatturaElettronica\FatturaElettronica;
use FatturaElettronicaPhp\FatturaElettronica\FatturaElettronicaBody\DatiGenerali\DatiGenerali;
use FatturaElettronicaPhp\FatturaElettronica\FatturaElettronicaBody\DatiBeniServizi\DatiBeniServizi;
use FatturaElettronicaPhp\FatturaElettronica\FatturaElettronicaBody\DatiPagamento\DatiPagamento;
use FatturaElettronicaPhp\FatturaElettronica\Decoder\XMLDecoder;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::query();

        // Apply filters
        if ($request->filled('stato')) {
            $query->where('status', $request->stato);
        }

        if ($request->filled('fornitore')) {
            $query->where('fornitore', 'like', '%' . $request->fornitore . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('invoice_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('invoice_date', '<=', $request->date_to);
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'invoice_date');
        $sortDirection = $request->get('sort_direction', 'desc');

        // Validate sort fields
        $allowedSortFields = ['fornitore', 'total_amount', 'invoice_date'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'invoice_date';
        }

        // Validate sort direction
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }

        $query->orderBy($sortBy, $sortDirection);

        // Calculate total amount for filtered records (before pagination)
        $filteredTotalAmount = (clone $query)->sum('total_amount');
        $filteredTotalCount = (clone $query)->count();

        $invoices = $query->paginate(15)->withQueryString();

        // Get unique statuses for filter dropdown
        $statuses = Invoice::distinct()->pluck('status')->filter()->values();

        // Get unique fornitori for filter dropdown
        $fornitori = Invoice::distinct()->pluck('fornitore')->filter()->sort()->values();

        // Monthly statistics
        $today = now();
        $firstOfCurrentMonth = $today->copy()->startOfMonth();
        $firstOfLastMonth = $today->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $today->copy()->subMonth()->endOfMonth();

        $currentMonthCount = Invoice::whereDate('invoice_date', '>=', $firstOfCurrentMonth)
            ->whereDate('invoice_date', '<=', $today)
            ->count();
        $currentMonthTotal = Invoice::whereDate('invoice_date', '>=', $firstOfCurrentMonth)
            ->whereDate('invoice_date', '<=', $today)
            ->sum('total_amount');

        $lastMonthCount = Invoice::whereDate('invoice_date', '>=', $firstOfLastMonth)
            ->whereDate('invoice_date', '<=', $endOfLastMonth)
            ->count();
        $lastMonthTotal = Invoice::whereDate('invoice_date', '>=', $firstOfLastMonth)
            ->whereDate('invoice_date', '<=', $endOfLastMonth)
            ->sum('total_amount');

        return view('invoices.index', compact(
            'invoices',
            'statuses',
            'fornitori',
            'currentMonthCount',
            'currentMonthTotal',
            'lastMonthCount',
            'lastMonthTotal',
            'filteredTotalAmount',
            'filteredTotalCount'
        ));
    }

    public function reconciliation(Request $request)
    {
        // Get unreconciled invoices (isreconiled = false or null)
        $unreconciledInvoicesQuery = Invoice::where(function($query) {
            $query->where('isreconiled', false)
                  ->orWhereNull('isreconiled');
        });

        // Apply denominazione_riferimento filter to invoices if provided
        if ($request->filled('denominazione_riferimento')) {
            $unreconciledInvoicesQuery->where('fornitore', 'like', '%' . $request->denominazione_riferimento . '%');
        }

        $unreconciledInvoices = $unreconciledInvoicesQuery
            ->orderBy('invoice_date', 'desc')
            ->get();

        // Get Provvigioni summary - only Proforma records with sended_at not null, grouped by denominazione and sended_at date
        $provvigioniQuery = Provvigione::where('stato', 'Proforma')
            ->whereNotNull('sended_at')
            ->whereNull('invoice_number');

        // Apply denominazione_riferimento filter if provided
        if ($request->filled('denominazione_riferimento')) {
            $provvigioniQuery->where('denominazione_riferimento', 'like', '%' . $request->denominazione_riferimento . '%');
        }

        // Apply email date filter if provided
        if ($request->filled('email_date_from')) {
            $provvigioniQuery->whereDate('sended_at', '>=', $request->email_date_from);
        }
        if ($request->filled('email_date_to')) {
            $provvigioniQuery->whereDate('sended_at', '<=', $request->email_date_to);
        }

        $provvigioniSummary = $provvigioniQuery
            ->selectRaw('
                denominazione_riferimento,
                DATE(sended_at) as sent_date,
                COUNT(*) as total_records,
                SUM(CAST(importo AS DECIMAL(10,2))) as total_amount,
                MAX(sended_at) as last_sent_date,
                MIN(sended_at) as first_sent_date
            ')
            ->groupBy('denominazione_riferimento', 'sent_date')
            ->orderBy('denominazione_riferimento')
            ->orderBy('sent_date', 'desc')
            ->get();

        // Get total unfiltered amounts for comparison
        $totalUnfilteredInvoices = Invoice::where(function($query) {
            $query->where('isreconiled', false)
                  ->orWhereNull('isreconiled');
        })->sum('total_amount');

        $totalUnfilteredProvvigioni = Provvigione::where('stato', 'Proforma')
            ->whereNotNull('sended_at')
            ->whereNull('invoice_number')
            ->sum('importo');

        return view('invoices.reconciliation', compact(
            'unreconciledInvoices',
            'provvigioniSummary',
            'totalUnfilteredInvoices',
            'totalUnfilteredProvvigioni'
        ));
    }

    public function reconcile(Request $request)
    {
        \Log::info('Reconciliation request received', $request->all());

        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'denominazione_riferimento' => 'required|string',
            'sent_date' => 'nullable|date',
        ]);

        try {
            DB::beginTransaction();

            $invoice = Invoice::findOrFail($request->invoice_id);
            $denominazione = $request->denominazione_riferimento;
            $sentDate = $request->sent_date;

            \Log::info('Starting reconciliation', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'denominazione' => $denominazione,
                'sent_date' => $sentDate
            ]);

            // Update invoice as reconciled
            $invoice->update([
                'isreconiled' => 1,
                'status' => 'reconciled',
            ]);

            \Log::info('Invoice updated', [
                'invoice_id' => $invoice->id,
                'isreconiled' => $invoice->isreconiled,
                'status' => $invoice->status
            ]);

            // Update Provvigioni records - only Proforma records with sended_at not null
            $provvigioniQuery = Provvigione::where('stato', 'Proforma')
                ->whereNotNull('sended_at')
                ->whereNull('invoice_number')
                ->where('denominazione_riferimento', $denominazione);

            // If sent_date is provided, filter by that specific date
            if ($sentDate) {
                $provvigioniQuery->whereDate('sended_at', $sentDate);
            }

            // Log the query conditions
            \Log::info('Provvigioni query conditions', [
                'stato' => 'Proforma',
                'sended_at_not_null' => true,
                'invoice_number_null' => true,
                'denominazione_riferimento' => $denominazione,
                'sent_date_filter' => $sentDate
            ]);

            $updatedCount = $provvigioniQuery->update([
                'invoice_number' => $invoice->invoice_number,
                'stato' => 'Fatturato',
                'received_at' => now(),
            ]);

            \Log::info('Provvigioni updated', [
                'updated_count' => $updatedCount,
                'invoice_number_set' => $invoice->invoice_number,
                'stato_set' => 'Fatturato',
                'received_at_set' => now()
            ]);

            DB::commit();

            $dateInfo = $sentDate ? " for date {$sentDate}" : "";
            $response = [
                'success' => true,
                'message' => "Successfully reconciled invoice #{$invoice->invoice_number} with {$updatedCount} Provvigione records for {$denominazione}{$dateInfo}",
                'invoice_number' => $invoice->invoice_number,
                'updated_count' => $updatedCount,
                'denominazione' => $denominazione,
                'sent_date' => $sentDate
            ];

            \Log::info('Reconciliation completed successfully', $response);

            return response()->json($response);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Reconciliation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

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
            $xml = simplexml_load_string($xmlString);
            $parser = new DigitalDocumentParser($xml);
            $document = $parser->parse();

            // Extract basic information that we know exists
            $data = [
                'document_type' => 'Fattura Elettronica',
                'supplier' => $this->extractSupplierFromDocument($document),
                'customer' => $this->extractCustomerFromDocument($document),
                'invoice_details' => $this->extractInvoiceDetailsFromDocument($document),
                'line_items' => $this->extractLineItemsFromDocument($document),
                'totals' => $this->extractTotalsFromDocument($document),
                'validation' => [
                    'valid' => true,
                    'schema_used' => 'Fattura Elettronica',
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

    private function extractSupplierFromDocument($document)
    {
        try {
            $supplier = $document->getSupplier();
            if (!$supplier) {
                return ['error' => 'No supplier data found'];
            }

            $address = $supplier->getAddress();
            return [
                'name' => $supplier->getName(),
                'surname' => $supplier->getSurname(),
                'organization' => $supplier->getOrganization(),
                'fiscal_code' => $supplier->getFiscalCode(),
                'vat_number' => $supplier->getVatNumber(),
                'country_code' => $supplier->getCountryCode(),
                'email' => $supplier->getEmail(),
                'phone' => $supplier->getPhone(),
                'address' => $address ? [
                    'street' => $address->getStreet(),
                    'street_number' => $address->getStreetNumber(),
                    'city' => $address->getCity(),
                    'zip' => $address->getZip(),
                    'state' => $address->getState(),
                    'country_code' => $address->getCountryCode(),
                ] : null,
            ];
        } catch (\Exception $e) {
            return ['error' => 'Could not extract supplier data: ' . $e->getMessage()];
        }
    }

    private function extractCustomerFromDocument($document)
    {
        try {
            $customer = $document->getCustomer();
            if (!$customer) {
                return ['error' => 'No customer data found'];
            }

            $address = $customer->getAddress();
            return [
                'name' => $customer->getName(),
                'surname' => $customer->getSurname(),
                'organization' => $customer->getOrganization(),
                'fiscal_code' => $customer->getFiscalCode(),
                'vat_number' => $customer->getVatNumber(),
                'country_code' => $customer->getCountryCode(),
                'address' => $address ? [
                    'street' => $address->getStreet(),
                    'street_number' => $address->getStreetNumber(),
                    'city' => $address->getCity(),
                    'zip' => $address->getZip(),
                    'state' => $address->getState(),
                    'country_code' => $address->getCountryCode(),
                ] : null,
            ];
        } catch (\Exception $e) {
            return ['error' => 'Could not extract customer data: ' . $e->getMessage()];
        }
    }

    private function extractInvoiceDetailsFromDocument($document)
    {
        try {
            $instances = $document->getDocumentInstances();
            if (empty($instances)) {
                return ['error' => 'No document instances found'];
            }

            // Get the first instance for basic details
            $instance = $instances[0];
            return [
                'document_type' => 'Fattura Elettronica',
                'transmission_format' => $document->getTransmissionFormat() ? $document->getTransmissionFormat()->value : null,
                'sender_vat_id' => $document->getSenderVatId(),
                'sending_id' => $document->getSendingId(),
                'customer_sdi_code' => $document->getCustomerSdiCode(),
                'customer_pec' => $document->getCustomerPec(),
                'sender_phone' => $document->getSenderPhone(),
                'sender_email' => $document->getSenderEmail(),
            ];
        } catch (\Exception $e) {
            return ['error' => 'Could not extract invoice details: ' . $e->getMessage()];
        }
    }

    private function extractLineItemsFromDocument($document)
    {
        try {
            $instances = $document->getDocumentInstances();
            if (empty($instances)) {
                return ['error' => 'No document instances found'];
            }

            $lineItems = [];
            foreach ($instances as $instance) {
                $lines = $instance->getLines();
                foreach ($lines as $line) {
                    $lineItems[] = [
                        'number' => $line->getNumber(),
                        'description' => $line->getDescription(),
                        'quantity' => $line->getQuantity(),
                        'unit' => $line->getUnit(),
                        'unit_price' => $line->getUnitPrice(),
                        'total' => $line->getTotal(),
                        'tax_percentage' => $line->getTaxPercentage(),
                        'start_date' => $line->getStartDate() ? $line->getStartDate()->format('Y-m-d') : null,
                        'end_date' => $line->getEndDate() ? $line->getEndDate()->format('Y-m-d') : null,
                    ];
                }
            }

            return $lineItems;
        } catch (\Exception $e) {
            return ['error' => 'Could not extract line items: ' . $e->getMessage()];
        }
    }

    private function extractTotalsFromDocument($document)
    {
        try {
            $instances = $document->getDocumentInstances();
            if (empty($instances)) {
                return ['error' => 'No document instances found'];
            }

            $totals = [];
            foreach ($instances as $instance) {
                $instanceTotals = $instance->getTotals();
                foreach ($instanceTotals as $total) {
                    $totals[] = [
                        'total' => $total->getTotal(),
                        'tax_amount' => $total->getTaxAmount(),
                        'tax_percentage' => $total->getTaxPercentage(),
                        'other_expenses' => $total->getOtherExpenses(),
                        'rounding' => $total->getRounding(),
                        'reference' => $total->getReference(),
                    ];
                }
            }

            return $totals;
        } catch (\Exception $e) {
            return ['error' => 'Could not extract totals: ' . $e->getMessage()];
        }
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

    public function testReconciliation()
    {
        $unreconciledInvoicesCount = Invoice::where(function($query) {
            $query->where('isreconiled', false)
                  ->orWhereNull('isreconiled');
        })->count();

        $proformaProvvigioniCount = Provvigione::where('stato', 'Proforma')
            ->whereNotNull('sended_at')
            ->whereNull('invoice_number')
            ->count();

        return response()->json([
            'unreconciled_invoices_count' => $unreconciledInvoicesCount,
            'proforma_provvigioni_count' => $proformaProvvigioniCount,
            'message' => 'Test completed successfully'
        ]);
    }

    public function edit($id)
    {
        $invoice = Invoice::findOrFail($id);
        $statusOptions = ['pending', 'paid', 'reconciled', 'cancelled', 'overdue'];
        return view('invoices.edit', compact('invoice', 'statusOptions'));
    }

    public function check($id)
    {
        $invoice = Invoice::findOrFail($id);

        // Get individual provvigioni records that match the criteria
        $provvigioniRecords = Provvigione::where('stato', 'Proforma')
            ->whereNull('invoice_number')
            ->where('denominazione_riferimento', $invoice->fornitore)
            ->whereNotNull('sended_at')
            ->where('sended_at', '<=', $invoice->invoice_date)
            ->orderBy('sended_at', 'desc')
            ->orderBy('cognome')
            ->orderBy('nome')
            ->get();

        return view('invoices.check', compact('invoice', 'provvigioniRecords'));
    }

    public function reconcileChecked(Request $request, $id)
    {
        try {
            $request->validate([
                'checked_ids' => 'required|array',
                'checked_ids.*' => 'string|exists:provvigioni,id'
            ]);

            $invoice = Invoice::findOrFail($id);
            $checkedIds = $request->input('checked_ids');

            // Update all checked provvigioni records
            $updatedCount = Provvigione::whereIn('id', $checkedIds)
                ->where('stato', 'Proforma')
                ->whereNull('invoice_number')
                ->where('denominazione_riferimento', $invoice->fornitore)
                ->update([
                    'invoice_number' => $invoice->invoice_number,
                    'received_at' => $invoice->invoice_date,
                    'stato' => 'Fatturato'
                ]);

            // Update invoice as reconciled
            $invoice->update([
                'isreconiled' => 1,
                'status' => 'reconciled',
            ]);

            return response()->json([
                'success' => true,
                'message' => "Successfully reconciled {$updatedCount} provvigioni records with invoice #{$invoice->invoice_number}",
                'updated_count' => $updatedCount,
                'invoice_number' => $invoice->invoice_number
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error during reconciliation: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:pending,paid,reconciled,cancelled,overdue',
                'isreconiled' => 'nullable|boolean',
                'paid_at' => 'nullable|date',
                'sended_at' => 'nullable|date',
                'sended2_at' => 'nullable|date',
            ]);

            $invoice = Invoice::findOrFail($id);

            // Prepare the data for update
            $updateData = [
                'status' => $request->status,
            ];

            // Handle isreconiled field
            if ($request->has('isreconiled')) {
                $updateData['isreconiled'] = $request->boolean('isreconiled');
            }

            // Handle paid_at field
            if ($request->filled('paid_at')) {
                $updateData['paid_at'] = $request->paid_at;
            } else {
                $updateData['paid_at'] = null;
            }

            // Handle sended_at field
            if ($request->filled('sended_at')) {
                $updateData['sended_at'] = $request->sended_at;
            } else {
                $updateData['sended_at'] = null;
            }

            // Handle sended2_at field
            if ($request->filled('sended2_at')) {
                $updateData['sended2_at'] = $request->sended2_at;
            } else {
                $updateData['sended2_at'] = null;
            }

            // Handle delta field
            if ($request->filled('delta')) {
                $updateData['delta'] = $request->delta;
            } else {
                $updateData['delta'] = null;
            }

            $result = $invoice->update($updateData);

            // Return JSON response for AJAX requests
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Invoice updated successfully!',
                    'status' => $request->status
                ]);
            }

            // Return redirect for regular form submissions
            return redirect()->route('invoices.reconciliation')->with('success', 'Invoice updated successfully!');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating invoice: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('invoices.reconciliation')->with('error', 'Error updating invoice: ' . $e->getMessage());
        }
    }

    public function dashboard(Request $request)
    {
        // Get fornitori for dropdown
        $fornitoriList = Invoice::distinct()->pluck('fornitore')->filter()->sort()->values();

        // Filters
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $fornitore = $request->input('fornitore');

        $query = Invoice::query();
        if ($dateFrom) {
            $query->whereDate('invoice_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('invoice_date', '<=', $dateTo);
        }
        if ($fornitore) {
            $query->where('fornitore', 'like', "%$fornitore%");
        }

        // Basic counts
        $totalInvoices = $query->count();
        $totalAmount = $query->sum('total_amount');
        // $totalTaxAmount = $query->sum('tax_amount'); // removed

        // Counts by status
        $totalByStatus = (clone $query)->select('status', \DB::raw('COUNT(*) as count'), \DB::raw('SUM(total_amount) as total_amount'))
            ->groupBy('status')
            ->orderByDesc('count')
            ->get();

        // Reconciliation status
        $reconciledCount = (clone $query)->where('isreconiled', true)->count();
        $unreconciledCount = (clone $query)->where(function($q){ $q->where('isreconiled', false)->orWhereNull('isreconiled'); })->count();

        // Top fornitori by amount
        $topFornitori = (clone $query)->select('fornitore', \DB::raw('COUNT(*) as count'), \DB::raw('SUM(total_amount) as total_amount'))
            ->whereNotNull('fornitore')
            ->groupBy('fornitore')
            ->orderByDesc('total_amount')
            ->limit(10)
            ->get();

        // Top clienti by amount
        $topClienti = (clone $query)->select('cliente', \DB::raw('COUNT(*) as count'), \DB::raw('SUM(total_amount) as total_amount'))
            ->whereNotNull('cliente')
            ->groupBy('cliente')
            ->orderByDesc('total_amount')
            ->limit(10)
            ->get();

        // Monthly statistics (current year)
        $currentYear = now()->year;
        $monthlyStats = (clone $query)->selectRaw('
                MONTH(invoice_date) as month,
                COUNT(*) as count,
                SUM(total_amount) as total_amount,
                SUM(tax_amount) as total_tax
            ')
            ->whereYear('invoice_date', $currentYear)
            ->whereNotNull('invoice_date')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Recent invoices (last 30 days)
        $recentInvoices = (clone $query)->where('created_at', '>=', now()->subDays(30))
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Payment status analysis
        $paidInvoices = (clone $query)->whereNotNull('paid_at')->count();
        $unpaidInvoices = (clone $query)->whereNull('paid_at')->count();

        // Average amounts
        $averageAmount = (clone $query)->whereNotNull('total_amount')->avg('total_amount');
        // $averageTaxAmount = (clone $query)->whereNotNull('tax_amount')->avg('tax_amount'); // removed

        // This month vs last month comparison
        $thisMonth = (clone $query)->whereMonth('invoice_date', now()->month)
            ->whereYear('invoice_date', now()->year)
            ->whereNotNull('invoice_date');

        $lastMonth = (clone $query)->whereMonth('invoice_date', now()->subMonth()->month)
            ->whereYear('invoice_date', now()->subMonth()->year)
            ->whereNotNull('invoice_date');

        $thisMonthCount = $thisMonth->count();
        $thisMonthAmount = $thisMonth->sum('total_amount');
        $lastMonthCount = $lastMonth->count();
        $lastMonthAmount = $lastMonth->sum('total_amount');

        // Calculate percentage changes
        $countChange = $lastMonthCount > 0 ? (($thisMonthCount - $lastMonthCount) / $lastMonthCount) * 100 : 0;
        $amountChange = $lastMonthAmount > 0 ? (($thisMonthAmount - $lastMonthAmount) / $lastMonthAmount) * 100 : 0;

        // Removed currencyStats and paymentMethodStats

        return view('invoices.dashboard', compact(
            'totalInvoices',
            'totalAmount',
            'totalByStatus',
            'reconciledCount',
            'unreconciledCount',
            'topFornitori',
            'topClienti',
            'monthlyStats',
            'recentInvoices',
            'paidInvoices',
            'unpaidInvoices',
            'averageAmount',
            'thisMonthCount',
            'thisMonthAmount',
            'lastMonthCount',
            'lastMonthAmount',
            'countChange',
            'amountChange',
            'fornitoriList',
            'dateFrom',
            'dateTo',
            'fornitore'
        ));
    }

    public function destroy($id)
    {
        $invoice = \App\Models\Invoice::findOrFail($id);
        $invoice->delete();
        return redirect()->route('invoices.reconciliation')->with('success', 'Invoice deleted successfully!');
    }
}
