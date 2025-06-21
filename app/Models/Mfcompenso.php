<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Fornitori;
use App\Models\Clienti;

class Mfcompenso extends Model
{
    protected $table = 'mfcompensos';
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
        'stato',
        'denominazione_riferimento',
        'entrata_uscita',
        'cognome',
        'nome',
        'segnalatore',
        'fonte',
        'id_pratica',
        'tipo_pratica',
        'data_inserimento_pratica',
        'data_stipula',
        'istituto_finanziario',
        'prodotto',
        'macrostatus',
        'status_pratica',
        'data_status_pratica',
        'montante',
        'importo_erogato',
        'sended_at',
        'received_at',
        'paided_at',
        'invoice_number',
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
     * Add all distinct denominazione_riferimento from mfcompensos to fornitoris (name field).
     */
    public static function add_fornitori()
    {
        $names = self::query()
            ->whereNotNull('denominazione_riferimento')
            ->distinct()
            ->pluck('denominazione_riferimento');

        foreach ($names as $name) {
            if (!$name) continue;
            $exists = Fornitori::where('name', $name)->exists();
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
     * Sync missing denominazione_riferimento from mfcompensos to fornitoris table.
     * This method finds all unique denominazione_riferimento values in mfcompensos
     * that don't exist in fornitoris.name and adds them.
     *
     * @return array Array with counts of added, skipped, and total processed records
     */
    public static function syncDenominazioniToFornitori()
    {
        // Get all unique denominazione_riferimento from mfcompensos
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
     * Add all distinct istituto_finanziario from mfcompensos to clientis (name field).
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
}
