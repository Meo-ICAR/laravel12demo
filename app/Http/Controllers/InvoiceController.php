<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Provvigione;
use App\Services\InvoiceFilterService;
use App\Services\XmlParserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    protected $filterService;
    protected $xmlParserService;

    public function __construct(InvoiceFilterService $filterService, XmlParserService $xmlParserService)
    {
        $this->filterService = $filterService;
        $this->xmlParserService = $xmlParserService;
    }

    /**
     * Display a listing of invoices with filters and statistics
     */
    public function index(Request $request)
    {
        $data = $this->filterService->getFilteredInvoices($request);

        return view('invoices.index', $data);
    }

    /**
     * Display reconciliation view
     */
    public function reconciliation(Request $request)
    {
        $data = $this->filterService->getReconciliationData($request);

        return view('invoices.reconciliation', $data);
    }

    /**
     * Reconcile invoice with provvigioni
     */
    public function reconcile(Request $request)
    {
        Log::info('Reconciliation request received', $request->all());

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

            Log::info('Starting reconciliation', [
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

            Log::info('Invoice updated', [
                'invoice_id' => $invoice->id,
                'status' => 'reconciled'
            ]);

            // Update provvigioni records
            $provvigioniQuery = Provvigione::where('stato', 'Proforma')
                ->where('denominazione_riferimento', $denominazione)
                ->whereNotNull('sended_at')
                ->whereNull('invoice_number');

            if ($sentDate) {
                $provvigioniQuery->whereDate('sended_at', $sentDate);
            }

            $provvigioni = $provvigioniQuery->get();

            Log::info('Found provvigioni records', [
                'count' => $provvigioni->count(),
                'denominazione' => $denominazione,
                'sent_date' => $sentDate
            ]);

            foreach ($provvigioni as $provvigione) {
                $provvigione->update([
                    'invoice_number' => $invoice->invoice_number,
                    'stato' => 'Fatturato',
                ]);

                Log::info('Updated provvigione', [
                    'provvigione_id' => $provvigione->id,
                    'invoice_number' => $invoice->invoice_number,
                    'stato' => 'Fatturato'
                ]);
            }

            DB::commit();

            Log::info('Reconciliation completed successfully', [
                'invoice_id' => $invoice->id,
                'provvigioni_updated' => $provvigioni->count()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Reconciliation completed successfully',
                'invoice_id' => $invoice->id,
                'provvigioni_updated' => $provvigioni->count()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Reconciliation failed', [
                'error' => $e->getMessage(),
                'invoice_id' => $request->invoice_id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Reconciliation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get XML data for an invoice
     */
    public function getXmlData($id)
    {
        $invoice = Invoice::findOrFail($id);

        if (empty($invoice->xml_data)) {
            return response()->json([
                'success' => false,
                'message' => 'No XML data available for this invoice'
            ], 404);
        }

        try {
            // Try to parse as Fattura Elettronica first
            $result = $this->xmlParserService->parseFatturaElettronica($invoice->xml_data);

            if (!$result['success']) {
                // Try generic XML parsing
                $result = $this->xmlParserService->parseGenericXml($invoice->xml_data, $invoice->invoice_number);
            }

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => $result['data'],
                    'formatted_xml' => $this->xmlParserService->formatXmlForDisplay($invoice->xml_data)
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to parse XML: ' . $result['error'],
                    'formatted_xml' => $this->xmlParserService->formatXmlForDisplay($invoice->xml_data)
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error parsing XML data', [
                'invoice_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error parsing XML data: ' . $e->getMessage(),
                'formatted_xml' => $this->xmlParserService->formatXmlForDisplay($invoice->xml_data)
            ]);
        }
    }

    /**
     * Test reconciliation functionality
     */
    public function testReconciliation()
    {
        $unreconciledInvoices = Invoice::where(function($query) {
            $query->where('isreconiled', false)
                  ->orWhereNull('isreconiled');
        })->count();

        $unreconciledProvvigioni = Provvigione::where('stato', 'Proforma')
            ->whereNotNull('sended_at')
            ->whereNull('invoice_number')
            ->count();

        return response()->json([
            'unreconciled_invoices' => $unreconciledInvoices,
            'unreconciled_provvigioni' => $unreconciledProvvigioni,
            'message' => 'Test reconciliation data retrieved successfully'
        ]);
    }

    /**
     * Show the form for editing the specified invoice
     */
    public function edit($id)
    {
        $invoice = Invoice::findOrFail($id);
        $statusOptions = [
            'Unreconciled' => 'Da riconciliare',
            'Reconciled' => 'Riconciliato',
            'Sospeso' => 'Sospeso',
        ];

        return view('invoices.edit', compact('invoice', 'statusOptions'));
    }

    /**
     * Check invoice for reconciliation
     */
    public function check($id)
    {
        $invoice = Invoice::findOrFail($id);

        // Find the Fornitori by COGE
        $fornitore = \App\Models\Fornitori::where('coge', $invoice->coge)->first();
        $provvigioni = collect();
        if ($fornitore) {
            // Select provvigioni where denominazione_riferimento = fornitori->name, paided_at is empty, and data_status_pratica < invoice date
            $provvigioni = \App\Models\Provvigione::where('denominazione_riferimento', $fornitore->name)
                ->whereNull('paided_at')
                ->whereDate('data_status_pratica', '<', $invoice->invoice_date)
                ->get();
        }

        return view('invoices.check', [
            'invoice' => $invoice,
            'provvigioni' => $provvigioni,
        ]);
    }

    /**
     * Reconcile checked invoice
     */
    public function reconcileChecked(Request $request, $id)
    {
        $request->validate([
            'provvigioni' => 'required|array',
            'provvigioni.*' => 'exists:provvigioni,id',
        ]);

        $invoice = \App\Models\Invoice::findOrFail($id);
        $provvigioniIds = $request->input('provvigioni', []);
        $invoiceDate = $invoice->invoice_date;

        // Update invoice status
        $invoice->update(['status' => 'reconciled']);

        // Update selected provvigioni
        $provvigioni = \App\Models\Provvigione::whereIn('id', $provvigioniIds)->get();
        foreach ($provvigioni as $provvigione) {
            $updateData = [
                'stato' => 'Fatturato',
                'received_at' => $invoiceDate,
                'invoice_number' => $invoice->invoice_number,
            ];
            if (empty($provvigione->sended_at)) {
                $updateData['sended_at'] = $invoiceDate;
            }
            $provvigione->update($updateData);
        }

        return redirect()->route('invoices.check', $invoice->id)
            ->with('success', 'Invoice and selected provvigioni reconciled successfully.');
    }

    /**
     * Update the specified invoice
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'fornitore' => 'required|string|max:255',
            'invoice_number' => 'required|string|max:255',
            'invoice_date' => 'required|date',
            'total_amount' => 'required|numeric|min:0',
            'tax_amount' => 'required|numeric|min:0',
            'status' => 'required|string|max:50',
            'currency' => 'nullable|string|max:3',
            'payment_method' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        try {
            $invoice = Invoice::findOrFail($id);

            $invoice->update($request->all());

            return redirect()->route('invoices.index')
                ->with('success', 'Invoice updated successfully');

        } catch (\Exception $e) {
            Log::error('Error updating invoice', [
                'invoice_id' => $id,
                'error' => $e->getMessage()
            ]);

            return back()->withInput()
                ->with('error', 'Failed to update invoice: ' . $e->getMessage());
        }
    }

    /**
     * Display invoice dashboard
     */
    public function dashboard(Request $request)
    {
        $data = $this->filterService->getFilteredInvoices($request);

        // Add dateFrom and dateTo for the view
        $data['dateFrom'] = $request->input('date_from', '');
        $data['dateTo'] = $request->input('date_to', '');

        // Add additional dashboard-specific data
        $data['total_invoices'] = Invoice::count();
        $data['total_amount'] = Invoice::sum('total_amount');
        $data['reconciled_count'] = Invoice::where('isreconiled', true)->count();
        $data['unreconciled_count'] = Invoice::where('isreconiled', false)->orWhereNull('isreconiled')->count();

        return view('invoices.dashboard', $data);
    }

    /**
     * Remove the specified invoice
     */
    public function destroy($id)
    {
        try {
            $invoice = Invoice::findOrFail($id);
            $invoice->delete();

            return redirect()->route('invoices.index')
                ->with('success', 'Invoice deleted successfully');

        } catch (\Exception $e) {
            Log::error('Error deleting invoice', [
                'invoice_id' => $id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to delete invoice: ' . $e->getMessage());
        }
    }
}
