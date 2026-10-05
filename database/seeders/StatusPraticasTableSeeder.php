<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StatusPratica;

class StatusPraticasTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $statuses = [
            'IN AMMORTAMENTO',
            'Fatturato',
            'RINUNCIA CLIENTE',
            'Inserita',
            'Inserito',
            'DECLINATA',
            '',
            'PERFEZIONATA',
            'PRATICA RESPINTA',
            'SOSPESA',
            'NOTIFICA',
            'RIENTRO BENESTARE',
            'RIENTRO POLIZZA',
            'CARICATA BANCA',
            'Deliberata',
            'RICHIESTA POLIZZA'
        ];

        foreach ($statuses as $status) {
            StatusPratica::firstOrCreate(
                ['id' => $status],
                ['id' => $status]
            );
        }
    }
}
