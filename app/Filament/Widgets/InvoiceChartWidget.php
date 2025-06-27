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

            $count = Invoice::whereDate('invoice_date', '>=', $startOfMonth)
                ->whereDate('invoice_date', '<=', $endOfMonth)
                ->count();

            $amount = Invoice::whereDate('invoice_date', '>=', $startOfMonth)
                ->whereDate('invoice_date', '<=', $endOfMonth)
                ->sum('total_amount');

            return [
                'month' => $month->format('M Y'),
                'count' => $count,
                'amount' => $amount,
            ];
        });

        return [
            'datasets' => [
                [
                    'label' => 'Invoice Count',
                    'data' => $data->pluck('count')->toArray(),
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Total Amount (€)',
                    'data' => $data->pluck('amount')->toArray(),
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $data->pluck('month')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'title' => [
                        'display' => true,
                        'text' => 'Invoice Count',
                    ],
                ],
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
