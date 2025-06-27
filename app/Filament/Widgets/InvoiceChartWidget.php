<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Filament\Support\RawJs;

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

    protected function getOptions(): RawJs
    {
        $invoicesIndexUrl = route('invoices.index');
        return RawJs::make(<<<JS
            {
                responsive: true,
                onClick: function(event, elements, chart) {
                    if (elements.length > 0) {
                        var monthParam = chart.data.months_params[elements[0].index];
                        var year = monthParam.split('-')[0];
                        var month = monthParam.split('-')[1];
                        var dateFrom = year + '-' + month + '-01';
                        var dateTo = new Date(year, month, 0); // last day of month
                        var dateToStr = dateTo.getFullYear() + '-' + String(dateTo.getMonth() + 1).padStart(2, '0') + '-' + String(dateTo.getDate()).padStart(2, '0');
                        window.open('{$invoicesIndexUrl}?date_from=' + dateFrom + '&date_to=' + dateToStr, '_blank');
                    }
                },
                scales: {
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Total Amount (€)',
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    },
                },
            }
        JS);
    }
}
