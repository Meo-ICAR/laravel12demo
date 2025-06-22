<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'fornitore_piva',
        'fornitore',
        'cliente_piva',
        'cliente',
        'invoice_number',
        'invoice_date',
        'total_amount',
        'tax_amount',
        'currency',
        'payment_method',
        'status',
        'paid_at',
        'isreconiled',
        'xml_data'
    ];

    protected $casts = [
        'invoice_date' => 'datetime',
        'paid_at' => 'date',
        'total_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'invoice_number' => 'string',
        'isreconiled' => 'boolean'
    ];

    public function getFormattedTotalAmountAttribute(): string
    {
        return number_format($this->total_amount, 2) . ' ' . $this->currency;
    }

    public function getFormattedTaxAmountAttribute(): string
    {
        return number_format($this->tax_amount, 2) . ' ' . $this->currency;
    }
}
