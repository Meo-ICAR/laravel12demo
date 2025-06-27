<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Widgets\ChartWidget;

class InvoiceStatusChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Invoice Status Distribution';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $statuses = Invoice::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        $colors = [
            'imported' => '#6b7280',
            'pending' => '#f59e0b',
            'paid' => '#10b981',
            'reconciled' => '#3b82f6',
            'cancelled' => '#ef4444',
            'overdue' => '#dc2626',
        ];

        $labels = $statuses->pluck('status')->toArray();
        $data = $statuses->pluck('count')->toArray();
        $backgroundColor = $statuses->map(function ($status) use ($colors) {
            return $colors[$status->status] ?? '#6b7280';
        })->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Invoices by Status',
                    'data' => $data,
                    'backgroundColor' => $backgroundColor,
                    'borderWidth' => 2,
                    'borderColor' => '#ffffff',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) {
                            const label = context.label || "";
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return label + ": " + value + " (" + percentage + "%)";
                        }',
                    ],
                ],
            ],
        ];
    }
}
