<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Lead extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'legacy_id',
        'campagna',
        'lista',
        'ragione_sociale',
        'cognome',
        'nome',
        'telefono',
        'ultimo_operatore',
        'esito',
        'data_richiamo',
        'operatore_richiamo',
        'scadenza_anagrafica',
        'indirizzo1',
        'indirizzo2',
        'indirizzo3',
        'comune',
        'provincia',
        'cap',
        'regione',
        'paese',
        'email',
        'p_iva',
        'codice_fiscale',
        'telefono2',
        'telefono3',
        'telefono4',
        'sesso',
        'nota',
        'attivo',
        'altro1',
        'altro2',
        'altro3',
        'altro4',
        'altro5',
        'altro6',
        'altro7',
        'altro8',
        'altro9',
        'altro10',
        'chiamate',
        'ultima_chiamata',
        'creato_da',
        'durata_ultima_chiamata',
        'totale_durata_chiamate',
        'chiamate_giornaliere',
        'chiamate_mensili',
        'data_creazione',
        'company_id',
    ];

    protected $casts = [
        'data_richiamo' => 'datetime',
        'scadenza_anagrafica' => 'datetime',
        'ultima_chiamata' => 'datetime',
        'data_creazione' => 'datetime',
        'attivo' => 'boolean',
        'chiamate' => 'integer',
        'chiamate_giornaliere' => 'integer',
        'chiamate_mensili' => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    // Helper method to get full name
    public function getFullNameAttribute()
    {
        $parts = array_filter([$this->cognome, $this->nome]);
        return implode(' ', $parts) ?: 'N/A';
    }

    // Helper method to get full address
    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->indirizzo1,
            $this->indirizzo2,
            $this->indirizzo3,
            $this->cap,
            $this->comune,
            $this->provincia,
            $this->regione,
            $this->paese
        ]);
        return implode(', ', $parts) ?: 'N/A';
    }

    // Helper method to get primary phone
    public function getPrimaryPhoneAttribute()
    {
        return $this->telefono ?: 'N/A';
    }

    // Helper method to check if lead is active
    public function getIsActiveAttribute()
    {
        return $this->attivo;
    }

    // Helper method to get status badge class
    public function getStatusBadgeClassAttribute()
    {
        if (!$this->attivo) {
            return 'badge-secondary';
        }

        switch (strtolower($this->esito ?? '')) {
            case 'non interessato':
                return 'badge-danger';
            case 'numero errato':
                return 'badge-warning';
            case 'no risposta':
                return 'badge-info';
            case 'richiamo personale':
                return 'badge-primary';
            default:
                return 'badge-success';
        }
    }

    // Scope for active leads
    public function scopeActive($query)
    {
        return $query->where('attivo', true);
    }

    // Scope for inactive leads
    public function scopeInactive($query)
    {
        return $query->where('attivo', false);
    }

    // Scope for leads by campaign
    public function scopeByCampaign($query, $campaign)
    {
        return $query->where('campagna', $campaign);
    }

    // Scope for leads by list
    public function scopeByList($query, $list)
    {
        return $query->where('lista', $list);
    }

    // Scope for leads by operator
    public function scopeByOperator($query, $operator)
    {
        return $query->where('ultimo_operatore', 'like', '%' . $operator . '%');
    }
}
