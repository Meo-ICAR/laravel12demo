<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'fornitore_piva',
        'fornitore',
        'fornitori_id',
        'cliente_piva',
        'cliente',
        'clienti_id',
        'invoice_number',
        'invoice_date',
        'total_amount',
        'tax_amount',
        'currency',
        'payment_method',
        'status',
        'paid_at',
        'isreconiled',
        'sended_at',
        'sended2_at',
        'xml_data',
        'delta',
        'coge',
    ];

    protected $casts = [
        'invoice_date' => 'datetime',
        'paid_at' => 'date',
        'sended_at' => 'datetime',
        'sended2_at' => 'datetime',
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

    /**
     * Get the fornitore that owns the invoice.
     */
    public function fornitore()
    {
        return $this->belongsTo(\App\Models\Fornitori::class, 'fornitori_id');
    }

    /**
     * Get the cliente that owns the invoice.
     */
    public function cliente()
    {
        return $this->belongsTo(\App\Models\Clienti::class, 'clienti_id');
    }
}
