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
        'nome',
        'natoil',
        'indirizzo',
        'comune',
        'cap',
        'prov',
        'tel',
        'piva',
        'email',
        'anticipo',
        'contributo',
        'contributo_description',
        'anticipo_description',
        'issubfornitore',
        'operatore',
        'iscollaboratore',
        'isdipendente',
        'regione',
        'citta',
        'coordinatore',
        'company_id',
    ];

    public static function importFromInvoices()
    {
        $uniqueFornitori = \App\Models\Invoice::query()
            ->select('fornitore', 'fornitore_piva')
            ->whereNotNull('fornitore')
            ->where('fornitore', '!=', '')
            ->distinct()
            ->get();

        foreach ($uniqueFornitori as $item) {
            if (!self::where('name', $item->fornitore)->where('piva', $item->fornitore_piva)->exists()) {
                self::create([
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'name' => $item->fornitore,
                    'piva' => $item->fornitore_piva,
                ]);
            }
        }
    }

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class, 'company_id');
    }
}
