<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\Grid;
use Filament\Actions\Action;
use App\Services\XmlParserService;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('edit')
                ->url(fn () => $this->getRecord()->editUrl)
                ->icon('heroicon-o-pencil')
                ->label('Edit Invoice'),

            Action::make('reconcile')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->action(function () {
                    $this->getRecord()->update([
                        'isreconiled' => true,
                        'status' => 'reconciled',
                    ]);
                    $this->notify('success', 'Invoice marked as reconciled');
                })
                ->visible(fn () => !$this->getRecord()->isreconiled)
                ->label('Mark Reconciled'),

            Action::make('mark_paid')
                ->icon('heroicon-o-currency-euro')
                ->color('success')
                ->requiresConfirmation()
                ->action(function () {
                    $this->getRecord()->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                    ]);
                    $this->notify('success', 'Invoice marked as paid');
                })
                ->visible(fn () => $this->getRecord()->status !== 'paid')
                ->label('Mark Paid'),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Invoice Details')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('invoice_number')
                                    ->label('Invoice Number')
                                    ->size(TextEntry\TextEntrySize::Large)
                                    ->weight('bold'),
                                TextEntry::make('invoice_date')
                                    ->label('Invoice Date')
                                    ->date(),
                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'paid' => 'success',
                                        'reconciled' => 'primary',
                                        'cancelled' => 'danger',
                                        'overdue' => 'warning',
                                        default => 'secondary',
                                    }),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('total_amount')
                                    ->label('Total Amount')
                                    ->money('EUR')
                                    ->size(TextEntry\TextEntrySize::Large)
                                    ->weight('bold'),
                                TextEntry::make('tax_amount')
                                    ->label('Tax Amount')
                                    ->money('EUR'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('currency')
                                    ->label('Currency'),
                                TextEntry::make('payment_method')
                                    ->label('Payment Method'),
                            ]),
                    ]),

                Section::make('Supplier Information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('fornitore')
                                    ->label('Supplier Name'),
                                TextEntry::make('fornitore_piva')
                                    ->label('Supplier VAT'),
                                TextEntry::make('coge')
                                    ->label('COGE Code'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Customer Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('cliente')
                                    ->label('Customer Name'),
                                TextEntry::make('cliente_piva')
                                    ->label('Customer VAT'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Payment & Reconciliation')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('paid_at')
                                    ->label('Paid Date')
                                    ->date(),
                                TextEntry::make('isreconiled')
                                    ->label('Reconciled')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->falseIcon('heroicon-o-x-circle'),
                                TextEntry::make('delta')
                                    ->label('Delta Amount')
                                    ->money('EUR'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('XML Data')
                    ->schema([
                        TextEntry::make('xml_data')
                            ->label('Raw XML')
                            ->markdown()
                            ->columnSpanFull()
                            ->visible(fn () => !empty($this->getRecord()->xml_data)),

                        KeyValueEntry::make('parsed_xml')
                            ->label('Parsed XML Data')
                            ->keyLabel('Field')
                            ->valueLabel('Value')
                            ->columnSpanFull()
                            ->visible(fn () => !empty($this->getRecord()->xml_data))
                            ->getStateUsing(function () {
                                if (empty($this->getRecord()->xml_data)) {
                                    return [];
                                }

                                $xmlParser = new XmlParserService();
                                $result = $xmlParser->parseFatturaElettronica($this->getRecord()->xml_data);

                                if (!$result['success']) {
                                    $result = $xmlParser->parseGenericXml($this->getRecord()->xml_data, $this->getRecord()->invoice_number);
                                }

                                if ($result['success']) {
                                    return $this->flattenArray($result['data']);
                                }

                                return ['error' => 'Failed to parse XML data'];
                            }),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Timestamps')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Created At')
                                    ->dateTime(),
                                TextEntry::make('updated_at')
                                    ->label('Updated At')
                                    ->dateTime(),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    private function flattenArray(array $array, string $prefix = ''): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $newKey = $prefix ? $prefix . '.' . $key : $key;

            if (is_array($value)) {
                $result = array_merge($result, $this->flattenArray($value, $newKey));
            } else {
                $result[$newKey] = $value;
            }
        }

        return $result;
    }
}
