<?php

namespace App\Services;

use App\Models\Provvigione;
use App\Traits\Filterable;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class ProvvigioneFilterService
{
    use Filterable;

    /**
     * Get filtered provvigioni with statistics
     */
    public function getFilteredProvvigioni(Request $request): array
    {
        $query = Provvigione::query();

        // Always join with fornitori to get the fornitore name
        $query->leftJoin('fornitoris', 'provvigioni.denominazione_riferimento', '=', 'fornitoris.coge')
              ->select('provvigioni.*', 'fornitoris.name as fornitore_name');

        // Apply custom filters
        $filterConfig = [
            'date_field' => 'sended_at',
            'status_field' => 'stato',
            'custom_filters' => [
                'stato_include' => [
                    'field' => 'provvigioni.stato',
                    'operator' => 'in',
                    'special' => 'comma_separated'
                ],
                'denominazione_riferimento' => [
                    'field' => 'fornitoris.name',
                    'operator' => 'like',
                    'fallback_field' => 'provvigioni.denominazione_riferimento'
                ],
                'istituto_finanziario' => [
                    'field' => 'provvigioni.istituto_finanziario',
                    'operator' => 'like'
                ],
                'cognome' => [
                    'field' => 'provvigioni.cognome',
                    'operator' => 'like'
                ],
                'fonte' => [
                    'field' => 'provvigioni.fonte',
                    'operator' => 'like'
                ],
                'data_status_pratica' => [
                    'field' => 'provvigioni.data_status_pratica',
                    'operator' => 'like'
                ],
                'data_status_pratica_from' => [
                    'field' => 'provvigioni.data_status_pratica',
                    'operator' => '>=',
                    'type' => 'date'
                ],
                'data_status_pratica_to' => [
                    'field' => 'provvigioni.data_status_pratica',
                    'operator' => '<=',
                    'type' => 'date'
                ],
                'sended_at' => [
                    'field' => 'provvigioni.sended_at',
                    'operator' => '=',
                    'type' => 'date'
                ]
            ]
        ];

        $this->applyCustomFilters($query, $request, $filterConfig);

        // Get total count and total importo before pagination
        $totalCount = (clone $query)->count();
        $totalImporto = (clone $query)->sum('provvigioni.importo');

        // Get total unfiltered values for comparison
        $totalUnfilteredCount = Provvigione::count();
        $totalUnfilteredImporto = Provvigione::sum('importo');

        // Apply sorting
        $allowedSortFields = ['id', 'denominazione_riferimento', 'importo', 'stato', 'cognome', 'nome', 'tipo', 'istituto_finanziario', 'data_status_pratica', 'sended_at', 'created_at', 'updated_at'];
        $this->applySorting($query, $request, $allowedSortFields, 'id', 'desc');

        // Add table prefix for sorting to avoid ambiguity
        $sortField = $request->get('sort', 'id');
        if ($sortField !== 'id') {
            $query->orderBy('provvigioni.' . $sortField, $request->get('order', 'desc'));
        }

        $provvigioni = $this->getPaginatedResults($query, 15);
        $statoOptions = ['Inserito', 'Proforma', 'Fatturato', 'Pagato', 'Stornato', 'Sospeso'];

        // Calculate statistics
        $stats = $this->calculateDateRangeStats($query, 'sended_at');

        return [
            'provvigioni' => $provvigioni,
            'stato_options' => $statoOptions,
            'total_count' => $totalCount,
            'total_importo' => $totalImporto,
            'total_unfiltered_count' => $totalUnfilteredCount,
            'total_unfiltered_importo' => $totalUnfilteredImporto,
            'current_month_count' => $stats['current_month_count'],
            'current_month_total' => $stats['current_month_total'],
            'last_month_count' => $stats['last_month_count'],
            'last_month_total' => $stats['last_month_total'],
        ];
    }

    /**
     * Apply custom filters with special handling
     */
    private function applyCustomFilters(Builder $query, Request $request, array $filterConfig): Builder
    {
        if (isset($filterConfig['custom_filters'])) {
            foreach ($filterConfig['custom_filters'] as $filterName => $config) {
                if ($request->filled($filterName)) {
                    $field = $config['field'];
                    $operator = $config['operator'] ?? '=';
                    $value = $request->$filterName;

                    // Handle special cases
                    if (isset($config['special']) && $config['special'] === 'comma_separated') {
                        $stati = array_map('trim', explode(',', $value));
                        $query->whereIn($field, $stati);
                    } elseif (isset($config['fallback_field'])) {
                        // Handle fallback field (for denominazione_riferimento)
                        $fallbackField = $config['fallback_field'];
                        $query->where(function($q) use ($field, $fallbackField, $value, $operator) {
                            $q->where($field, $operator, '%' . $value . '%')
                              ->orWhere($fallbackField, $operator, '%' . $value . '%');
                        });
                    } elseif (isset($config['type']) && $config['type'] === 'date') {
                        if ($operator === '>=' || $operator === '<=') {
                            $query->whereDate($field, $operator, $value);
                        } else {
                            $query->whereDate($field, $operator, $value);
                        }
                    } else {
                        if ($operator === 'like') {
                            $value = '%' . $value . '%';
                        }
                        $query->where($field, $operator, $value);
                    }
                }
            }
        }

        return $query;
    }

    /**
     * Get proforma summary data
     */
    public function getProformaSummary(Request $request): array
    {
        $query = Provvigione::where('stato', 'Proforma')
            ->whereNotNull('sended_at')
            ->whereNull('invoice_number');

        // Apply denominazione_riferimento filter if provided
        if ($request->filled('denominazione_riferimento')) {
            $searchTerm = $request->denominazione_riferimento;
            $query->where(function($q) use ($searchTerm) {
                $q->where('denominazione_riferimento', 'like', '%' . $searchTerm . '%');
            });
        }

        // Apply date range filters
        if ($request->filled('date_from')) {
            $query->whereDate('sended_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('sended_at', '<=', $request->date_to);
        }

        $summary = $query
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
        $totalUnfilteredAmount = Provvigione::where('stato', 'Proforma')
            ->whereNotNull('sended_at')
            ->whereNull('invoice_number')
            ->sum('importo');

        return [
            'summary' => $summary,
            'total_unfiltered_amount' => $totalUnfilteredAmount,
        ];
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats(): array
    {
        $today = now();
        $firstOfCurrentMonth = $today->copy()->startOfMonth();
        $firstOfLastMonth = $today->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $today->copy()->subMonth()->endOfMonth();

        // Current month stats
        $currentMonthStats = Provvigione::whereDate('sended_at', '>=', $firstOfCurrentMonth)
            ->whereDate('sended_at', '<=', $today)
            ->selectRaw('
                stato,
                COUNT(*) as count,
                SUM(CAST(importo AS DECIMAL(10,2))) as total_amount
            ')
            ->groupBy('stato')
            ->get();

        // Last month stats
        $lastMonthStats = Provvigione::whereDate('sended_at', '>=', $firstOfLastMonth)
            ->whereDate('sended_at', '<=', $endOfLastMonth)
            ->selectRaw('
                stato,
                COUNT(*) as count,
                SUM(CAST(importo AS DECIMAL(10,2))) as total_amount
            ')
            ->groupBy('stato')
            ->get();

        // Top denominazioni by amount
        $topDenominazioni = Provvigione::selectRaw('
                denominazione_riferimento,
                COUNT(*) as count,
                SUM(CAST(importo AS DECIMAL(10,2))) as total_amount
            ')
            ->groupBy('denominazione_riferimento')
            ->orderByDesc('total_amount')
            ->limit(10)
            ->get();

        // Recent provvigioni
        $recentProvvigioni = Provvigione::where('created_at', '>=', now()->subDays(30))
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return [
            'current_month_stats' => $currentMonthStats,
            'last_month_stats' => $lastMonthStats,
            'top_denominazioni' => $topDenominazioni,
            'recent_provvigioni' => $recentProvvigioni,
        ];
    }
}
