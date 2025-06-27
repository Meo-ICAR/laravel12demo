<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class InvoiceStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $today = now();
        $firstOfCurrentMonth = $today->copy()->startOfMonth();
        $firstOfLastMonth = $today->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $today->copy()->subMonth()->endOfMonth();

        // Current month stats
        $currentMonthCount = Invoice::whereDate('invoice_date', '>=', $firstOfCurrentMonth)
            ->whereDate('invoice_date', '<=', $today)
            ->count();

        $currentMonthTotal = Invoice::whereDate('invoice_date', '>=', $firstOfCurrentMonth)
            ->whereDate('invoice_date', '<=', $today)
            ->sum('total_amount');

        // Last month stats
        $lastMonthCount = Invoice::whereDate('invoice_date', '>=', $firstOfLastMonth)
            ->whereDate('invoice_date', '<=', $endOfLastMonth)
            ->count();

        $lastMonthTotal = Invoice::whereDate('invoice_date', '>=', $firstOfLastMonth)
            ->whereDate('invoice_date', '<=', $endOfLastMonth)
            ->sum('total_amount');

        // Overall stats
        $totalInvoices = Invoice::count();
        $totalAmount = Invoice::sum('total_amount');
        $reconciledCount = Invoice::where('isreconiled', true)->count();
        $unreconciledCount = Invoice::where('isreconiled', false)->orWhereNull('isreconiled')->count();
        $paidCount = Invoice::where('status', 'paid')->count();
        $pendingCount = Invoice::where('status', 'pending')->count();

        // Calculate percentage changes
        $countChange = $lastMonthCount > 0 ? (($currentMonthCount - $lastMonthCount) / $lastMonthCount) * 100 : 0;
        $amountChange = $lastMonthTotal > 0 ? (($currentMonthTotal - $lastMonthTotal) / $lastMonthTotal) * 100 : 0;

        return [
            Stat::make('Total Invoices', number_format($totalInvoices))
                ->description('All time invoices')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),

            Stat::make('Total Amount', '€' . number_format($totalAmount, 2))
                ->description('All time total')
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('success'),

            Stat::make('This Month', '€' . number_format($currentMonthTotal, 2))
                ->description(number_format($currentMonthCount) . ' invoices')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color($amountChange >= 0 ? 'success' : 'danger')
                ->chart([7, 2, 10, 3, 15, 4, 17]),

            Stat::make('Reconciled', number_format($reconciledCount))
                ->description(number_format(($reconciledCount / max($totalInvoices, 1)) * 100, 1) . '% of total')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Unreconciled', number_format($unreconciledCount))
                ->description('Pending reconciliation')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Paid Invoices', number_format($paidCount))
                ->description(number_format(($paidCount / max($totalInvoices, 1)) * 100, 1) . '% of total')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Pending Invoices', number_format($pendingCount))
                ->description('Awaiting payment')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
        ];
    }
}
