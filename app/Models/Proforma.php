<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Proforma extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'fornitori_id',
        'stato',
        'anticipo',
        'anticipo_descrizione',
        'compenso_descrizione',
        'contributo',
        'contributo_descrizione',
        'annotation',
        'sended_at',
        'paid_at',
        'emailsubject',
        'emailbody',
        'emailto',
        'emailfrom',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'sended_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function fornitore()
    {
        return $this->belongsTo(Fornitori::class, 'fornitori_id');
    }

    public function provvigioni()
    {
        return $this->belongsToMany(Provvigione::class, 'proforma_provvigione', 'proforma_id', 'provvigione_id');
    }

    public function getCompensoAttribute()
    {
        return $this->provvigioni->sum('importo');
    }
}
