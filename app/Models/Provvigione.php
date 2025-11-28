<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Models\Fornitori;
use App\Models\Clienti;
use App\Models\Proforma;
use App\Models\ProvvigioniStato;
use App\Models\PraticheStato;
use App\Models\Coges;

class Provvigione extends Model
{
    use SoftDeletes;
    
    protected $table = 'provvigioni';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

      


    protected $fillable = [
        'id',
        'legacy_id',
        'data_inserimento_compenso',
        'descrizione',
        'tipo',
        'importo',
        'importo_effettivo',
        'quota',
        'status_pratica',
        'data_status_pratica',
         'status_compenso',
        'denominazione_riferimento',
        'deleted_at',
        'fornitori_id',
        'entrata_uscita',
         'id_pratica',
        'cognome',
        'nome',
        'segnalatore',
        'istituto_finanziario',
        'fonte',
          'data_pagamento',
        'id_pratica',
        'tipo_pratica',
        'data_inserimento_pratica',
        'data_stipula',

        'clienti_id',
        'prodotto',
        'macrostatus',
   'stato', // This is the foreign key to provvigioni_statos
  'status_pagamento',
        'montante',
        'importo_erogato',
        'sended_at',
        'received_at',
        'paided_at',
        'invoice_number',
        'data_pagamento',
        'n_fattura',
        'data_fattura',
        'data_status',
        'piva',
        'cf',
    ];

    /**
     * Get the status associated with the provvigione.
     */
    public function statoRel()
    {
        return $this->belongsTo(ProvvigioniStato::class, 'stato', 'stato');
    }

    /**
     * Get the pratica status associated with the provvigione.
     */
    public function praticaStato()
    {
        return $this->belongsTo(PraticheStato::class, 'status_pratica', 'stato_pratica');
    }

    protected $casts = [
        'sended_at' => 'datetime',
        'received_at' => 'datetime',
        'paided_at' => 'datetime',
        'data_inserimento_compenso' => 'datetime',
        'data_inserimento_pratica' => 'datetime',
        'data_stipula' => 'datetime',
        'data_status_pratica' => 'datetime',
        'data_pagamento' => 'datetime',
        'data_fattura' => 'date',
        'data_status' => 'date',
        'importo' => 'decimal:2',
        'importo_effettivo' => 'decimal:2',
        'quota' => 'decimal:2',
        'montante' => 'decimal:2',
        'importo_erogato' => 'decimal:2',
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

    /**
     * Add all distinct denominazione_riferimento from provvigioni to fornitoris (name field).
     */
    public static function add_fornitori()
    {
        $names = self::query()
            ->whereNotNull('denominazione_riferimento')
            ->distinct()
            ->pluck('denominazione_riferimento');

        foreach ($names as $name) {
            if (!$name) continue;
            $exists = Fornitori::where('nome', $name)->exists();
            if (!$exists) {
                Fornitori::create([
                    'id' => (string) Str::uuid(),
                    'name' => $name,
                    // other fields left as null
                ]);
            }
        }
    }

    /**
     * Sync missing denominazione_riferimento from provvigioni to fornitoris table.
     * This method finds all unique denominazione_riferimento values in provvigioni
     * that don't exist in fornitoris.name and adds them.
     *
     * @return array Array with counts of added, skipped, and total processed records
     */
    public static function syncDenominazioniToFornitori()
    {
        // Get all unique denominazione_riferimento from provvigioni
        $denominazioni = self::query()
            ->whereNotNull('denominazione_riferimento')
            ->where('denominazione_riferimento', '!=', '')
            ->distinct()
            ->pluck('denominazione_riferimento');

        $added = 0;
        $skipped = 0;

        foreach ($denominazioni as $denominazione) {
            if (!$denominazione) continue;

            // Check if this denominazione already exists in fornitoris
            $exists = Fornitori::where('name', $denominazione)->exists();

            if (!$exists) {
                try {
                    Fornitori::create([
                        'id' => (string) Str::uuid(),
                        'name' => $denominazione,
                        'codice' => null,
                        'piva' => null,

                        'email' => null,
                        'operatore' => null,
                        'iscollaboratore' => false,
                        'isdipendente' => false,
                        'regione' => null,
                        'citta' => null,
                        'company_id' => null,
                    ]);
                    $added++;
                } catch (\Exception $e) {
                    \Log::error('Failed to add denominazione to fornitori', [
                        'denominazione' => $denominazione,
                        'error' => $e->getMessage()
                    ]);
                }
            } else {
                $skipped++;
            }
        }

        return [
            'added' => $added,
            'skipped' => $skipped,
            'total_processed' => $denominazioni->count()
        ];
    }

    /**
     * Add all distinct istituto_finanziario from provvigioni to clientis (name field).
     */
    public static function add_clientis()
    {
        $names = self::query()
            ->whereNotNull('istituto_finanziario')
            ->distinct()
            ->pluck('istituto_finanziario');

        foreach ($names as $name) {
            if (!$name) continue;
            $exists = Clienti::where('name', $name)->exists();
            if (!$exists) {
                Clienti::create([
                    'id' => (string) Str::uuid(),
                    'name' => $name,
                    // other fields left as null
                ]);
            }
        }
    }

    public function proformas()
    {
        return $this->belongsToMany(Proforma::class, 'proforma_provvigione', 'provvigione_id', 'proforma_id');
    }

    /**
     * Get the fornitore that owns the provvigione.
     */
    public function fornitore()
    {
        return $this->belongsTo(Fornitori::class, 'fornitori_id');
    }

    /**
     * Get the cliente that owns the provvigione.
     */
    public function cliente()
    {
        return $this->belongsTo(Clienti::class, 'clienti_id');
    }

    /**
     * The coges that belong to the provvigione.
     */
    public function coges()
    {
        return $this->belongsToMany(Coges::class, 'provvigioni_coges')
            ->withPivot('data_invio', 'data_storno')
            ->withTimestamps();
    }
}
