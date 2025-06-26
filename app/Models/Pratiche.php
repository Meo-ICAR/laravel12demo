<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pratiche extends Model
{
    protected $fillable = [
        'pratica_id',
        'Data_inserimento',
        'Descrizione',
        'Cliente',
        'Agente',
        'Segnalatore',
        'Fonte',
        'Tipo',
        'Istituto_finanziario'
    ];

    protected $casts = [
        'Data_inserimento' => 'date',
    ];
}
