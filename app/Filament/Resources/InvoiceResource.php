<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Resources\InvoiceResource\RelationManagers;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Invoice Details')
                    ->schema([
                        TextInput::make('invoice_number')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        DatePicker::make('invoice_date')
                            ->required()
                            ->default(now()),
                        TextInput::make('total_amount')
                            ->required()
                            ->numeric()
                            ->prefix('€'),
                        TextInput::make('tax_amount')
                            ->required()
                            ->numeric()
                            ->prefix('€'),
                        Select::make('currency')
                            ->options([
                                'EUR' => 'EUR',
                                'USD' => 'USD',
                                'GBP' => 'GBP',
                            ])
                            ->default('EUR')
                            ->required(),
                        Select::make('status')
                            ->options([
                                'imported' => 'Imported',
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'reconciled' => 'Reconciled',
                                'cancelled' => 'Cancelled',
                                'overdue' => 'Overdue',
                            ])
                            ->default('imported')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Supplier Information')
                    ->schema([
                        TextInput::make('fornitore')
                            ->maxLength(255)
                            ->label('Supplier Name'),
                        TextInput::make('fornitore_piva')
                            ->maxLength(255)
                            ->label('Supplier VAT'),
                        TextInput::make('coge')
                            ->maxLength(255)
                            ->label('COGE Code'),
                    ])->columns(3),

                Forms\Components\Section::make('Customer Information')
                    ->schema([
                        TextInput::make('cliente')
                            ->maxLength(255)
                            ->label('Customer Name'),
                        TextInput::make('cliente_piva')
                            ->maxLength(255)
                            ->label('Customer VAT'),
                    ])->columns(2),

                Forms\Components\Section::make('Payment & Reconciliation')
                    ->schema([
                        DatePicker::make('paid_at')
                            ->label('Paid Date'),
                        TextInput::make('payment_method')
                            ->maxLength(255),
                        Toggle::make('isreconiled')
                            ->label('Reconciled')
                            ->default(false),
                        TextInput::make('delta')
                            ->numeric()
                            ->label('Delta Amount')
                            ->prefix('€'),
                    ])->columns(2),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Textarea::make('xml_data')
                            ->label('XML Data')
                            ->columnSpanFull()
                            ->rows(10),
                    ])->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->searchable()
                    ->sortable()
                    ->label('Invoice #'),

                TextColumn::make('fornitore')
                    ->searchable()
                    ->sortable()
                    ->label('Supplier')
                    ->limit(30),

                TextColumn::make('invoice_date')
                    ->date()
                    ->sortable()
                    ->label('Date'),

                TextColumn::make('total_amount')
                    ->money('EUR')
                    ->sortable()
                    ->label('Total'),

                BadgeColumn::make('status')
                    ->colors([
                        'danger' => 'cancelled',
                        'warning' => 'overdue',
                        'success' => 'paid',
                        'primary' => 'reconciled',
                        'secondary' => 'imported',
                    ])
                    ->label('Status'),

                IconColumn::make('isreconiled')
                    ->boolean()
                    ->label('Reconciled')
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),

                TextColumn::make('paid_at')
                    ->date()
                    ->sortable()
                    ->label('Paid Date')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'imported' => 'Imported',
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'reconciled' => 'Reconciled',
                        'cancelled' => 'Cancelled',
                        'overdue' => 'Overdue',
                    ]),

                SelectFilter::make('isreconiled')
                    ->options([
                        '1' => 'Reconciled',
                        '0' => 'Not Reconciled',
                    ])
                    ->label('Reconciliation Status'),

                Filter::make('amount_range')
                    ->form([
                        TextInput::make('min_amount')
                            ->numeric()
                            ->label('Min Amount'),
                        TextInput::make('max_amount')
                            ->numeric()
                            ->label('Max Amount'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_amount'],
                                fn (Builder $query, $amount): Builder => $query->where('total_amount', '>=', $amount),
                            )
                            ->when(
                                $data['max_amount'],
                                fn (Builder $query, $amount): Builder => $query->where('total_amount', '<=', $amount),
                            );
                    })
                    ->label('Amount Range'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Action::make('reconcile')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Invoice $record) {
                        $record->update([
                            'isreconiled' => true,
                            'status' => 'reconciled',
                        ]);
                    })
                    ->visible(fn (Invoice $record) => !$record->isreconiled)
                    ->label('Mark Reconciled'),

                Action::make('mark_paid')
                    ->icon('heroicon-o-currency-euro')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Invoice $record) {
                        $record->update([
                            'status' => 'paid',
                            'paid_at' => now(),
                        ]);
                    })
                    ->visible(fn (Invoice $record) => $record->status !== 'paid')
                    ->label('Mark Paid'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    BulkAction::make('mark_reconciled')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update([
                                    'isreconiled' => true,
                                    'status' => 'reconciled',
                                ]);
                            });
                        })
                        ->label('Mark Reconciled'),

                    BulkAction::make('mark_paid')
                        ->icon('heroicon-o-currency-euro')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update([
                                    'status' => 'paid',
                                    'paid_at' => now(),
                                ]);
                            });
                        })
                        ->label('Mark Paid'),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('invoice_date', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
            'view' => Pages\ViewInvoice::route('/{record}'),
        ];
    }
}
