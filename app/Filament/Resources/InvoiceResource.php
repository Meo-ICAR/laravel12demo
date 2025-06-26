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

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('clienti_id')
                    ->maxLength(36)
                    ->default(null),
                Forms\Components\TextInput::make('fornitore_piva')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('fornitore')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('cliente_piva')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('cliente')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('invoice_number')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('invoice_date')
                    ->required(),
                Forms\Components\TextInput::make('total_amount')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('delta')
                    ->numeric()
                    ->default(null),
                Forms\Components\DateTimePicker::make('sended_at'),
                Forms\Components\DateTimePicker::make('sended2_at'),
                Forms\Components\TextInput::make('tax_amount')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('currency')
                    ->required()
                    ->maxLength(3)
                    ->default('EUR'),
                Forms\Components\TextInput::make('payment_method')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('status')
                    ->required()
                    ->maxLength(255)
                    ->default('imported'),
                Forms\Components\DatePicker::make('paid_at'),
                Forms\Components\Toggle::make('isreconiled')
                    ->required(),
                Forms\Components\Textarea::make('xml_data')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('company_id'),
                Forms\Components\TextInput::make('coge')
                    ->maxLength(255)
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('clienti_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('fornitore_piva')
                    ->searchable(),
                Tables\Columns\TextColumn::make('fornitore')
                    ->searchable(),
                Tables\Columns\TextColumn::make('cliente_piva')
                    ->searchable(),
                Tables\Columns\TextColumn::make('cliente')
                    ->searchable(),
                Tables\Columns\TextColumn::make('invoice_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('invoice_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('delta')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sended_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sended2_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tax_amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('currency')
                    ->searchable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('paid_at')
                    ->date()
                    ->sortable(),
                Tables\Columns\IconColumn::make('isreconiled')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('company_id'),
                Tables\Columns\TextColumn::make('coge')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
        ];
    }
}
