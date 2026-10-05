<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use App\Models\Provvigione;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ReconciliationWidget extends BaseWidget
{
    protected ?string $heading = 'Reconciliation Overview';

    protected static ?int $sort = 5;

    protected function getStats(): array
    {
        // Unreconciled invoices (excluding those with is_notenasarco = true and not reconciled)
        $unreconciledInvoices = Invoice::where('is_notenasarco', false)
            ->where(function($query) {
                $query->where('status', '!=', 'reconciled')
                      ->orWhereNull('status')
                      ->orWhere('isreconiled', false)
                      ->orWhereNull('isreconiled');
            });

        $unreconciledInvoicesCount = $unreconciledInvoices->count();
        $unreconciledInvoicesAmount = $unreconciledInvoices->sum('total_amount');

        // Unreconciled provvigioni
        $unreconciledProvvigioni = Provvigione::where('stato', 'Proforma')
            ->whereNotNull('sended_at')
            ->whereNull('invoice_number');

        $unreconciledProvvigioniCount = $unreconciledProvvigioni->count();
        $unreconciledProvvigioniAmount = $unreconciledProvvigioni->sum('importo');

        // Calculate difference
        $difference = $unreconciledInvoicesAmount - $unreconciledProvvigioniAmount;
        $differenceColor = abs($difference) < 0.01 ? 'success' : 'warning';

        return [
            Stat::make('Unreconciled Invoices', number_format($unreconciledInvoicesCount))
                ->description('€' . number_format($unreconciledInvoicesAmount, 2))
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning'),

            Stat::make('Unreconciled Provvigioni', number_format($unreconciledProvvigioniCount))
                ->description('€' . number_format($unreconciledProvvigioniAmount, 2))
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('info'),

            Stat::make('Difference', '€' . number_format($difference, 2))
                ->description($difference > 0 ? 'Invoices > Provvigioni' : 'Provvigioni > Invoices')
                ->descriptionIcon('heroicon-m-scale')
                ->color($differenceColor),
        ];
    }
}
