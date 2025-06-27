<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class InvoiceChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Invoice Trends';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $months = collect();
        $currentMonth = now()->startOfMonth();

        // Generate last 12 months
        for ($i = 11; $i >= 0; $i--) {
            $month = $currentMonth->copy()->subMonths($i);
            $months->push($month);
        }

        $data = $months->map(function ($month) {
            $startOfMonth = $month->copy()->startOfMonth();
            $endOfMonth = $month->copy()->endOfMonth();

            $amount = \App\Models\Invoice::whereDate('invoice_date', '>=', $startOfMonth)
                ->whereDate('invoice_date', '<=', $endOfMonth)
                ->sum('total_amount');

            return [
                'month' => $month->format('M Y'),
                'month_param' => $month->format('Y-m'),
                'amount' => $amount,
            ];
        });

        return [
            'datasets' => [
                [
                    'label' => 'Total Amount (€)',
                    'data' => $data->pluck('amount')->toArray(),
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $data->pluck('month')->toArray(),
            'months_params' => $data->pluck('month_param')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        $invoiceinsUrl = url('invoiceins');
        return [
            'responsive' => true,
            'onClick' => "function(event, elements, chart) {
                if (elements.length > 0) {
                    var monthParam = chart.data.months_params[elements[0].index];
                    window.open('{$invoiceinsUrl}?month=' + monthParam, '_blank');
                }
            }",
            'scales' => [
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'title' => [
                        'display' => true,
                        'text' => 'Total Amount (€)',
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
        ];
    }
}
