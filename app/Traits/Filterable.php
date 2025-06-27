<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait Filterable
{
    /**
     * Apply common filters to a query
     */
    protected function applyCommonFilters(Builder $query, Request $request, array $filterConfig = []): Builder
    {
        // Date range filters
        if ($request->filled('date_from')) {
            $dateField = $filterConfig['date_field'] ?? 'created_at';
            $query->whereDate($dateField, '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $dateField = $filterConfig['date_field'] ?? 'created_at';
            $query->whereDate($dateField, '<=', $request->date_to);
        }

        // Search filters
        if ($request->filled('search')) {
            $searchFields = $filterConfig['search_fields'] ?? ['name'];
            $searchTerm = $request->search;

            $query->where(function ($q) use ($searchFields, $searchTerm) {
                foreach ($searchFields as $field) {
                    $q->orWhere($field, 'like', '%' . $searchTerm . '%');
                }
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $statusField = $filterConfig['status_field'] ?? 'status';
            $query->where($statusField, $request->status);
        }

        // Apply custom filters from config
        if (isset($filterConfig['custom_filters'])) {
            foreach ($filterConfig['custom_filters'] as $filterName => $config) {
                if ($request->filled($filterName)) {
                    $field = $config['field'];
                    $operator = $config['operator'] ?? '=';
                    $value = $request->$filterName;

                    if ($operator === 'like') {
                        $value = '%' . $value . '%';
                    }

                    $query->where($field, $operator, $value);
                }
            }
        }

        return $query;
    }

    /**
     * Apply sorting to a query
     */
    protected function applySorting(Builder $query, Request $request, array $allowedFields = [], string $defaultField = 'id', string $defaultDirection = 'desc'): Builder
    {
        $sortBy = $request->get('sort_by', $defaultField);
        $sortDirection = $request->get('sort_direction', $defaultDirection);

        // Validate sort field
        if (!empty($allowedFields) && !in_array($sortBy, $allowedFields)) {
            $sortBy = $defaultField;
        }

        // Validate sort direction
        if (!in_array(strtolower($sortDirection), ['asc', 'desc'])) {
            $sortDirection = $defaultDirection;
        }

        return $query->orderBy($sortBy, $sortDirection);
    }

    /**
     * Get paginated results with query string
     */
    protected function getPaginatedResults(Builder $query, int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Calculate statistics for a date range
     */
    protected function calculateDateRangeStats(Builder $query, string $dateField = 'created_at'): array
    {
        $today = now();
        $firstOfCurrentMonth = $today->copy()->startOfMonth();
        $firstOfLastMonth = $today->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $today->copy()->subMonth()->endOfMonth();

        $currentMonthCount = (clone $query)
            ->whereDate($dateField, '>=', $firstOfCurrentMonth)
            ->whereDate($dateField, '<=', $today)
            ->count();

        $currentMonthTotal = (clone $query)
            ->whereDate($dateField, '>=', $firstOfCurrentMonth)
            ->whereDate($dateField, '<=', $today)
            ->sum('total_amount');

        $lastMonthCount = (clone $query)
            ->whereDate($dateField, '>=', $firstOfLastMonth)
            ->whereDate($dateField, '<=', $endOfLastMonth)
            ->count();

        $lastMonthTotal = (clone $query)
            ->whereDate($dateField, '>=', $firstOfLastMonth)
            ->whereDate($dateField, '<=', $endOfLastMonth)
            ->sum('total_amount');

        return [
            'current_month_count' => $currentMonthCount,
            'current_month_total' => $currentMonthTotal,
            'last_month_count' => $lastMonthCount,
            'last_month_total' => $lastMonthTotal,
        ];
    }
}
