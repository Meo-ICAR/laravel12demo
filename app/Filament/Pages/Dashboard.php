<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Widgets\InvoiceStatsWidget;
use App\Filament\Widgets\InvoiceChartWidget;
use App\Filament\Widgets\InvoiceStatusChartWidget;
use App\Filament\Widgets\RecentInvoicesWidget;
use App\Filament\Widgets\ReconciliationWidget;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.pages.dashboard';

    protected static ?int $navigationSort = -2;

    protected function getHeaderWidgets(): array
    {
        return [
            InvoiceStatsWidget::class,
            ReconciliationWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            InvoiceChartWidget::class,
            InvoiceStatusChartWidget::class,
            RecentInvoicesWidget::class,
        ];
    }
}
