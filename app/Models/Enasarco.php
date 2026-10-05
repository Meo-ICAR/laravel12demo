<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enasarco extends Model
{
    protected $fillable = [
        'enasarco',
        'competenza',
        'minimo',
        'massimo',
        'minimale',
        'massimale'
    ];

    protected $casts = [
        'competenza' => 'integer',
        'minimo' => 'decimal:2',
        'massimo' => 'decimal:2',
        'minimale' => 'decimal:2',
        'massimale' => 'decimal:2',
    ];
}
