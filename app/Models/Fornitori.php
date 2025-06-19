<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fornitori extends Model
{
    use SoftDeletes;

    protected $table = 'fornitoris';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'codice',
        'name',
        'piva',
        'email',
        'operatore',
        'iscollaboratore',
        'isdipendente',
        'regione',
        'citta',
        'company_id',
    ];
}
