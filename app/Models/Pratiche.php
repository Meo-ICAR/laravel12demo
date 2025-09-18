<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pratiche extends Model
{
    protected $fillable = [
        'pratica_id',
        'Data_inserimento',
        'Descrizione',
        'Cliente',
        'Agente',
        'Segnalatore',
        'Fonte',
        'Tipo',
        'Istituto_finanziario',
        'Pratica',
        'Status_pratica',
        'Cliente_ID',
        'Codice_fiscale',
        'Prodotto',
        'Residenza_citta',
        'Residenza_provincia',
        'Regione',
        'Importo_erogato',
        'Importo',
        'Totale_compensi_lordo',
        'Totale_compensi_passivo',
        'Totale_compensi_netto',
        'Importo_compenso',
        'Importo_compenso_euro',
        'Importo_rata',
        'Durata',
        'Montante',
        'TAN',
        'Importo_compenso2',
        'Data_decorrenza',
        'Inserita_at',
        'Invio_in_istruttoria_at',
        'Deliberata_at',
        'Liquidata_at',
        'Perfezionata_at',
        'Declinata_at',
        'Pratica_respinta_at',
        'Rinuncia_cliente_at',
        'Data_firma_at'
    ];

    protected $casts = [
        'Data_inserimento' => 'date',
        'Data_decorrenza' => 'date',
        'Inserita_at' => 'datetime',
        'Invio_in_istruttoria_at' => 'datetime',
        'Deliberata_at' => 'datetime',
        'Liquidata_at' => 'datetime',
        'Perfezionata_at' => 'datetime',
        'Declinata_at' => 'datetime',
        'Pratica_respinta_at' => 'datetime',
        'Rinuncia_cliente_at' => 'datetime',
        'Data_firma_at' => 'datetime',
    ];
}
