<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PraticheStato extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pratiches_statos';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'stato_pratica';

    /**
     * The data type of the primary key.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'stato_pratica',
        'isrejected',
        'isworking',
        'isestingued'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'isrejected' => 'boolean',
        'isworking' => 'boolean',
        'isestingued' => 'boolean',
    ];

    /**
     * Get the display name of the status.
     *
     * @return string
     */
    public function getDisplayNameAttribute()
    {
        return $this->stato_pratica ?: 'Nessuno';
    }
}
