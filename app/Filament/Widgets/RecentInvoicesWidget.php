<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Actions\Action;

class RecentInvoicesWidget extends BaseWidget
{
    protected static ?string $heading = 'Recent Invoices';

    protected static ?int $sort = 4;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Invoice::query()
                    ->latest('invoice_date')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('Invoice #')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('fornitore')
                    ->label('Supplier')
                    ->searchable()
                    ->limit(25),

                TextColumn::make('invoice_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),

                TextColumn::make('total_amount')
                    ->label('Amount')
                    ->money('EUR')
                    ->sortable(),

                BadgeColumn::make('status')
                    ->colors([
                        'danger' => 'cancelled',
                        'warning' => 'overdue',
                        'success' => 'paid',
                        'primary' => 'reconciled',
                        'secondary' => 'imported',
                    ])
                    ->label('Status'),
            ])
            ->actions([
                Action::make('view')
                    ->url(fn (Invoice $record): string => route('filament.admin.resources.invoices.view', $record))
                    ->icon('heroicon-m-eye')
                    ->label('View'),

                Action::make('edit')
                    ->url(fn (Invoice $record): string => route('filament.admin.resources.invoices.edit', $record))
                    ->icon('heroicon-m-pencil')
                    ->label('Edit'),
            ])
            ->paginated(false);
    }
}
