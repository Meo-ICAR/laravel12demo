<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


class Pratiche extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id';



     /**
     * Get the column name for the "created at" timestamp.
     * This tells Laravel to use 'data_inserimento_pratica' for default ordering
     */
    public function getCreatedAtColumn()
    {
        return 'data_inserimento_pratica';
    }

    protected $fillable = [
        'id',
        'codice_pratica',
        'nome_cliente',
        'cognome_cliente',
        'codice_fiscale',
        'denominazione_agente',
        'partita_iva_agente',
        'denominazione_banca',
        'tipo_prodotto',
        'descrizione_prodotto',
        'data_inserimento_pratica',
        'stato_pratica',
    ];

    protected $dates = [
        'data_inserimento_pratica',
        'created_at',
        'updated_at',
    ];



}
