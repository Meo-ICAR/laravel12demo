<?php

namespace App\Services;

use App\Models\Invoice;
use App\Traits\Filterable;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class InvoiceFilterService
{
    use Filterable;

    /**
     * Get filtered invoices with statistics
     */
    public function getFilteredInvoices(Request $request): array
    {
        $query = Invoice::query();

        // Apply filters
        $filterConfig = [
            'date_field' => 'invoice_date',
            'status_field' => 'status', // Database column name is 'status'
            'custom_filters' => [
                'fornitore' => [
                    'field' => 'fornitore',
                    'operator' => 'like'
                ]
            ]
        ];
        
        // Manually apply status filter to handle the 'stato' parameter
        if ($request->filled('stato')) {
            $query->where('status', $request->stato);
        }

        $this->applyCommonFilters($query, $request, $filterConfig);

        // Apply sorting
        $allowedSortFields = ['fornitore', 'total_amount', 'invoice_date'];
        $this->applySorting($query, $request, $allowedSortFields, 'invoice_date', 'desc');

        // Calculate totals before pagination
        $filteredTotalAmount = (clone $query)->sum('total_amount');
        $filteredTotalCount = (clone $query)->count();

        // Get paginated results
        $invoices = $this->getPaginatedResults($query, 15);

        // Get filter options
        $statuses = Invoice::distinct()->pluck('status')->filter()->values();
        $fornitori = Invoice::distinct()->pluck('fornitore')->filter()->sort()->values();

        // Calculate statistics
        $stats = $this->calculateDateRangeStats($query, 'invoice_date');

        return [
            'invoices' => $invoices,
            'statuses' => $statuses,
            'fornitori' => $fornitori,
            'current_month_count' => $stats['current_month_count'],
            'current_month_total' => $stats['current_month_total'],
            'last_month_count' => $stats['last_month_count'],
            'last_month_total' => $stats['last_month_total'],
            'filtered_total_amount' => $filteredTotalAmount,
            'filtered_total_count' => $filteredTotalCount,
        ];
    }

    /**
     * Get reconciliation data
     */
    public function getReconciliationData(Request $request): array
    {
        // Get unreconciled invoices
        $unreconciledInvoicesQuery = Invoice::where(function($query) {
            $query->where('isreconiled', false)
                  ->orWhereNull('isreconiled');
        });

        if ($request->filled('denominazione_riferimento')) {
            $unreconciledInvoicesQuery->where('fornitore', 'like', '%' . $request->denominazione_riferimento . '%');
        }
        
        // Filter by fornitore ID if provided
        if ($request->filled('fornitore_id')) {
            $unreconciledInvoicesQuery->where('fornitori_id', $request->fornitore_id);
        }

        $unreconciledInvoices = $unreconciledInvoicesQuery
            ->orderBy('invoice_date', 'desc')
            ->get();

        // Get Provvigioni summary
        $provvigioniQuery = \App\Models\Provvigione::where('stato', 'Proforma')
          //  ->whereNotNull('sended_at')
           // ->whereNull('invoice_number')
           ;

        if ($request->filled('denominazione_riferimento')) {
            $provvigioniQuery->where('denominazione_riferimento', 'like', '%' . $request->denominazione_riferimento . '%');
        }
        
        // Filter by fornitore ID if provided
        if ($request->filled('fornitore')) {
            $provvigioniQuery->where('fornitori_id', $request->fornitore);
        }

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

        // Get total unfiltered amounts
        $totalUnfilteredInvoices = Invoice::where(function($query) {
            $query->where('isreconiled', false)
                  ->orWhereNull('isreconiled');
        })->sum('total_amount');

        $totalUnfilteredProvvigioni = \App\Models\Provvigione::where('stato', 'Proforma')
            ->whereNotNull('sended_at')
            ->whereNull('invoice_number')
            ->sum('importo');

        // Get total provvigioni count
        $totalProvvigioni = \App\Models\Provvigione::where('stato', 'Proforma')
            ->whereNull('invoice_number')
            ->count();
            
        return [
            'unreconciled_invoices' => $unreconciledInvoices,
            'provvigioni_summary' => $provvigioniSummary,
            'total_unfiltered_invoices' => $totalUnfilteredInvoices,
            'total_unfiltered_provvigioni' => $totalUnfilteredProvvigioni,
            'total_provvigioni' => $totalProvvigioni,
            'unreconciled_count' => $unreconciledInvoices->count(),
            'total_invoices' => Invoice::count(),
        ];
    }
}
