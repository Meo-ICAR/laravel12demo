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
        Log::info('Reconciliation request received', [
            'request_data' => $request->all(),
            'headers' => $request->headers->all(),
            'user' => auth()->user() ? auth()->user()->id : 'guest'
        ]);

        // Validate request
        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'denominazione_riferimento' => 'required|string',
            'sent_date' => 'nullable|date',
        ]);

        Log::debug('Validation passed', ['validated' => $validated]);

        try {
            DB::beginTransaction();

            $invoice = Invoice::findOrFail($request->invoice_id);
            $denominazione = $request->denominazione_riferimento;
            $sentDate = $request->sent_date;

            Log::info('Starting reconciliation', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'denominazione' => $denominazione,
              //  'sent_date' => $sentDate
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
                ->whereNull('invoice_number');

         //   if ($sentDate) {
         //       $provvigioniQuery->whereDate('sended_at', $sentDate);
         //   }

            $provvigioni = $provvigioniQuery->get();

            Log::info('Found provvigioni records', [
                'count' => $provvigioni->count(),
                'denominazione' => $denominazione,
                'sent_date' => $invoice->invoice_date
            ]);

            $updatedCount = 0;

            foreach ($provvigioni as $provvigione) {
                $updateData = [
                    'invoice_number' => $invoice->invoice_number,
                    'stato' => 'Fatturato',
                    'received_at' => $invoice->invoice_date,
                ];

                Log::debug('Attempting to update provvigione', [
                    'provvigione_id' => $provvigione->id,
                    'update_data' => $updateData,
                    'current_values' => [
                        'invoice_number' => $provvigione->invoice_number,
                        'stato' => $provvigione->stato,
                        'received_at' => $provvigione->received_at
                    ]
                ]);

                $result = $provvigione->update($updateData);

                // Refresh the model to get updated values
                $provvigione->refresh();

                Log::info('Update result', [
                    'provvigione_id' => $provvigione->id,
                    'update_result' => $result,
                    'updated_values' => [
                        'invoice_number' => $provvigione->invoice_number,
                        'stato' => $provvigione->stato,
                        'received_at' => $provvigione->received_at
                    ]
                ]);

                if ($result) $updatedCount++;
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
        $data['fornitore'] = $request->input('fornitore', '');

        // Add fornitoriList for the dropdown (alias of fornitori)
        $data['fornitoriList'] = $data['fornitori'];

        // Add additional dashboard-specific data
        $data['totalInvoices'] = Invoice::count();
        $data['totalAmount'] = Invoice::sum('total_amount');
        $data['reconciledCount'] = Invoice::where('isreconiled', true)->count();
        $data['unreconciledCount'] = Invoice::where('isreconiled', false)->orWhereNull('isreconiled')->count();
        $data['paidInvoices'] = Invoice::where('status', 'paid')->count();
        $data['unpaidInvoices'] = Invoice::where('status', '!=', 'paid')->orWhereNull('status')->count();
        $data['averageAmount'] = Invoice::avg('total_amount') ?: 0;

        // Monthly statistics
        $thisMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();
        $endLastMonth = now()->subMonth()->endOfMonth();

        $data['thisMonthCount'] = Invoice::whereDate('invoice_date', '>=', $thisMonth)->count();
        $data['thisMonthAmount'] = Invoice::whereDate('invoice_date', '>=', $thisMonth)->sum('total_amount');
        $data['lastMonthCount'] = Invoice::whereDate('invoice_date', '>=', $lastMonth)
            ->whereDate('invoice_date', '<=', $endLastMonth)->count();
        $data['lastMonthAmount'] = Invoice::whereDate('invoice_date', '>=', $lastMonth)
            ->whereDate('invoice_date', '<=', $endLastMonth)->sum('total_amount');

        // Calculate percentage change
        $data['countChange'] = $data['lastMonthCount'] > 0
            ? (($data['thisMonthCount'] - $data['lastMonthCount']) / $data['lastMonthCount']) * 100
            : 0;

        // Top performers
        $data['topFornitori'] = Invoice::selectRaw('fornitore, count(*) as count, sum(total_amount) as total_amount')
            ->groupBy('fornitore')
            ->orderBy('total_amount', 'desc')
            ->limit(10)
            ->get();

        $data['topClienti'] = Invoice::selectRaw('cliente, count(*) as count, sum(total_amount) as total_amount')
            ->groupBy('cliente')
            ->orderBy('total_amount', 'desc')
            ->limit(10)
            ->get();

        // Recent invoices
        $data['recentInvoices'] = Invoice::orderBy('invoice_date', 'desc')
            ->limit(20)
            ->get();

        // Status breakdown
        $data['totalByStatus'] = Invoice::selectRaw('status, count(*) as count, sum(total_amount) as total_amount')
            ->groupBy('status')
            ->get();

        // Monthly stats for chart
        $data['monthlyStats'] = Invoice::selectRaw('
                MONTH(invoice_date) as month,
                COUNT(*) as count,
                SUM(total_amount) as total_amount,
                SUM(tax_amount) as total_tax
            ')
            ->whereYear('invoice_date', now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

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
