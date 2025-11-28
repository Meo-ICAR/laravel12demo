<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Provvigione;

class Coges extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'fonte',
          'entrata_uscita',
        'conto_dare',
        'descrizione_dare',
        'conto_avere',
        'descrizione_avere',
        'annotazioni',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'coges';

    /**
     * The provvigioni that belong to the coge.
     */
    public function provvigioni()
    {
        return $this->belongsToMany(Provvigione::class, 'provvigioni_coges')
            ->withPivot('data_invio', 'data_storno')
            ->withTimestamps();
    }
}
