<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Widgets\InvoiceStatsWidget;
use App\Filament\Widgets\InvoiceChartWidget;
use App\Filament\Widgets\InvoiceStatusChartWidget;
use App\Filament\Widgets\ReconciliationWidget;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('heroicon-m-plus')
                ->label('New Invoice'),
        ];
    }

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
        ];
    }
}
