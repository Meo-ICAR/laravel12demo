<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoicein extends Model
{
    use HasFactory;

    protected $table = 'invoiceins';

    protected $fillable = [
        'tipo_di_documento',
        'nr_documento',
        'nr_fatt_acq_registrata',
        'nr_nota_cr_acq_registrata',
        'data_ricezione_fatt',
        'codice_td',
        'nr_cliente_fornitore',
        'nome_fornitore',
        'partita_iva',
        'nr_documento_fornitore',
        'allegato',
        'data_documento_fornitore',
        'data_primo_pagamento_prev',
        'imponibile_iva',
        'importo_iva',
        'importo_totale_fornitore',
        'importo_totale_collegato',
        'data_ora_invio_ricezione',
        'stato',
        'id_documento',
        'id_sdi',
        'nr_lotto_documento',
        'nome_file_doc_elettronico',
        'filtro_carichi',
        'cdc_codice',
        'cod_colleg_dimen_2',
        'allegato_in_file_xml',
        'note_1',
        'note_2',
    ];

    public function getImportoAttribute()
    {
        return $this->importo_totale_fornitore;
    }

    public function getDataDocumentoAttribute()
    {
        return $this->data_documento_fornitore;
    }

    /**
     * Get the invoice associated with this invoicein (if imported)
     */
    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'invoice_number', 'nr_documento');
    }

    /**
     * Check if this invoicein has been imported to invoices
     */
    public function getIsImportedAttribute()
    {
        return $this->invoice !== null;
    }
}
