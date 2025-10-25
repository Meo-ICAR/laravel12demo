<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProvvigioniStato extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'provvigioni_statos';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'stato';

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
    protected $fillable = ['stato'];

    /**
     * Get all provvigioni with this status.
     */
    public function provvigioni()
    {
        return $this->hasMany(Provvigione::class, 'stato', 'stato');
    }
}
