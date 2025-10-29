<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProvvigioneResource\Pages;
use App\Models\Provvigione;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;

class ProvvigioneResource extends Resource
{
    protected static ?string $model = Provvigione::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-euro';

    protected static ?string $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Provvigione Details')
                    ->schema([
                        TextInput::make('denominazione_riferimento')
                            ->required()
                            ->maxLength(255)
                            ->label('Denominazione Riferimento'),
                        TextInput::make('importo')
                            ->required()
                            ->numeric()
                            ->prefix('€')
                            ->label('Amount'),
                        Select::make('stato')
                            ->options([
                                'Inserito' => 'Inserito',
                                'Proforma' => 'Proforma',
                                'Fatturato' => 'Fatturato',
                                'Pagato' => 'Pagato',
                                'Stornato' => 'Stornato',
                                'Sospeso' => 'Sospeso',
                            ])
                            ->default('Inserito')
                            ->required()
                            ->label('Status'),
                        TextInput::make('invoice_number')
                            ->maxLength(255)
                            ->label('Invoice Number'),
                        DatePicker::make('data_pagamento')
                            ->label('Payment Date'),
                        TextInput::make('n_fattura')
                            ->maxLength(20)
                            ->label('Invoice Number (n_fattura)'),
                        DatePicker::make('data_fattura')
                            ->label('Invoice Date'),
                        DatePicker::make('data_status')
                            ->label('Status Date'),
                        TextInput::make('piva')
                            ->maxLength(20)
                            ->label('VAT Number'),
                        TextInput::make('cf')
                            ->maxLength(20)
                            ->label('Tax Code'),
                    ])->columns(2),

                Forms\Components\Section::make('Personal Information')
                    ->schema([
                        TextInput::make('cognome')
                            ->maxLength(255)
                            ->label('Surname'),
                        TextInput::make('nome')
                            ->maxLength(255)
                            ->label('Name'),
                        TextInput::make('tipo')
                            ->maxLength(255)
                            ->label('Type'),
                    ])->columns(3),

                Forms\Components\Section::make('Financial Information')
                    ->schema([
                        TextInput::make('istituto_finanziario')
                            ->maxLength(255)
                            ->label('Financial Institution'),
                        TextInput::make('fonte')
                            ->maxLength(255)
                            ->label('Source'),
                        DatePicker::make('data_status_pratica')
                            ->label('Practice Status Date'),
                    ])->columns(3),

                Forms\Components\Section::make('Timestamps')
                    ->schema([
                        DatePicker::make('sended_at')
                            ->label('Sent Date'),
                        DatePicker::make('received_at')
                            ->label('Received Date'),
                        DatePicker::make('paided_at')
                            ->label('Paid Date'),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('denominazione_riferimento')
                    ->searchable()
                    ->sortable()
                    ->label('Denominazione')
                    ->limit(30),

                TextColumn::make('cognome')
                    ->searchable()
                    ->sortable()
                    ->label('Surname'),

                TextColumn::make('nome')
                    ->searchable()
                    ->sortable()
                    ->label('Name'),

                TextColumn::make('importo')
                    ->money('EUR')
                    ->sortable()
                    ->label('Amount'),

                BadgeColumn::make('stato')
                    ->colors([
                        'danger' => 'Stornato',
                        'warning' => 'Sospeso',
                        'success' => 'Pagato',
                        'primary' => 'Fatturato',
                        'secondary' => 'Proforma',
                        'gray' => 'Inserito',
                    ])
                    ->label('Status'),

                TextColumn::make('istituto_finanziario')
                    ->searchable()
                    ->label('Financial Institution')
                    ->toggleable(),

                TextColumn::make('sended_at')
                    ->date()
                    ->sortable()
                    ->label('Sent Date')
                    ->toggleable(),

                TextColumn::make('invoice_number')
                    ->searchable()
                    ->label('Invoice #')
                    ->toggleable(),
                    
                TextColumn::make('n_fattura')
                    ->searchable()
                    ->label('N. Fattura')
                    ->toggleable(),
                    
                TextColumn::make('data_fattura')
                    ->date()
                    ->sortable()
                    ->label('Data Fattura')
                    ->toggleable(),
                    
                TextColumn::make('data_pagamento')
                    ->date()
                    ->sortable()
                    ->label('Data Pagamento')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('stato')
                    ->options([
                        'Inserito' => 'Inserito',
                        'Proforma' => 'Proforma',
                        'Fatturato' => 'Fatturato',
                        'Pagato' => 'Pagato',
                        'Stornato' => 'Stornato',
                        'Sospeso' => 'Sospeso',
                    ]),

                SelectFilter::make('istituto_finanziario')
                    ->options(function () {
                        return Provvigione::distinct()
                            ->pluck('istituto_finanziario', 'istituto_finanziario')
                            ->filter()
                            ->toArray();
                    }),

                Filter::make('has_invoice')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('invoice_number'))
                    ->label('Has Invoice Number'),

                Filter::make('no_invoice')
                    ->query(fn (Builder $query): Builder => $query->whereNull('invoice_number'))
                    ->label('No Invoice Number'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Action::make('mark_sent')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Provvigione $record) {
                        $record->update([
                            'sended_at' => now(),
                        ]);
                    })
                    ->visible(fn (Provvigione $record) => !$record->sended_at)
                    ->label('Mark Sent'),

                Action::make('mark_received')
                    ->icon('heroicon-o-inbox')
                    ->color('info')
                    ->requiresConfirmation()
                    ->action(function (Provvigione $record) {
                        $record->update([
                            'received_at' => now(),
                        ]);
                    })
                    ->visible(fn (Provvigione $record) => !$record->received_at)
                    ->label('Mark Received'),

                Action::make('mark_paid')
                    ->icon('heroicon-o-currency-euro')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Provvigione $record) {
                        $record->update([
                            'stato' => 'Pagato',
                            'paided_at' => now(),
                        ]);
                    })
                    ->visible(fn (Provvigione $record) => $record->stato !== 'Pagato')
                    ->label('Mark Paid'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    BulkAction::make('mark_sent')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update([
                                    'sended_at' => now(),
                                ]);
                            });
                        })
                        ->label('Mark Sent'),

                    BulkAction::make('mark_received')
                        ->icon('heroicon-o-inbox')
                        ->color('info')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update([
                                    'received_at' => now(),
                                ]);
                            });
                        })
                        ->label('Mark Received'),

                    BulkAction::make('mark_paid')
                        ->icon('heroicon-o-currency-euro')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update([
                                    'stato' => 'Pagato',
                                    'paided_at' => now(),
                                ]);
                            });
                        })
                        ->label('Mark Paid'),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListProvvigioni::route('/'),
            'create' => Pages\CreateProvvigione::route('/create'),
            'edit' => Pages\EditProvvigione::route('/{record}/edit'),
            'view' => Pages\ViewProvvigione::route('/{record}'),
        ];
    }
}
