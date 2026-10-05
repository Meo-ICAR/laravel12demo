<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Clienti extends Model
{
    use SoftDeletes;

    protected $table = 'clientis';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'codice',
        'name',
        'piva',
        'cf',
        'coge',
        'email',
        'regione',
        'citta',
        'company_id',
        'customertype_id',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function customertype()
    {
        return $this->belongsTo(\App\Models\Customertype::class);
    }

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class, 'company_id');
    }
}
