<?php

namespace Database\Seeders;

use App\Models\PraticaCompenso;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PraticaCompensoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $compensos = [
            ['id' => 'COMPENSO', 'iscoordinamento' => 0],
            ['id' => 'Compenso  ()', 'iscoordinamento' => 0],
            ['id' => 'Compenso  (0)', 'iscoordinamento' => 1],
            ['id' => 'Compenso  (10)', 'iscoordinamento' => 1],
            ['id' => 'Compenso  (10) I tranche', 'iscoordinamento' => 1],
            ['id' => 'Compenso  (30)', 'iscoordinamento' => 1],
            ['id' => 'Compenso  (40)', 'iscoordinamento' => 1],
            ['id' => 'Compenso  (5)', 'iscoordinamento' => 1],
            ['id' => 'Compenso  (50)', 'iscoordinamento' => 1],
            ['id' => 'Compenso  (55)', 'iscoordinamento' => 1],
            ['id' => 'Compenso  (60)', 'iscoordinamento' => 1],
            ['id' => 'Compenso  (60) I tranche', 'iscoordinamento' => 1],
            ['id' => 'Compenso  (65)', 'iscoordinamento' => 1],
            ['id' => 'Compenso  (70)', 'iscoordinamento' => 1],
            ['id' => 'Compenso  (75)', 'iscoordinamento' => 1],
            ['id' => 'Compenso  (80)', 'iscoordinamento' => 1],
            ['id' => 'Compenso  (90)', 'iscoordinamento' => 0],
            ['id' => 'Compenso  60', 'iscoordinamento' => 1],
            ['id' => 'Compenso (50)', 'iscoordinamento' => 1],
            ['id' => 'Compenso (70) II tranche', 'iscoordinamento' => 1],
            ['id' => 'compenso (70) III tranche', 'iscoordinamento' => 1],
            ['id' => 'Compenso BROKERAGGIO', 'iscoordinamento' => 1],
            ['id' => 'COMPENSO DA BROKERAGGIO (75)', 'iscoordinamento' => 1],
            ['id' => 'Compenso da Cliente', 'iscoordinamento' => 0],
            ['id' => 'Compenso da cliente  (30)', 'iscoordinamento' => 1],
            ['id' => 'Compenso da cliente  (5)', 'iscoordinamento' => 1],
            ['id' => 'Compenso da cliente  (58)', 'iscoordinamento' => 1],
            ['id' => 'Compenso da cliente  (60)', 'iscoordinamento' => 1],
            ['id' => 'Compenso da cliente  (65)', 'iscoordinamento' => 1],
            ['id' => 'Compenso da cliente  (70)', 'iscoordinamento' => 1],
            ['id' => 'Compenso da cliente  (75)', 'iscoordinamento' => 1],
            ['id' => 'Compenso da cliente  rinuncia parziale collaboratore', 'iscoordinamento' => 1],
            ['id' => 'COMPENSO DA CLIENTE (65)', 'iscoordinamento' => 1],
            ['id' => 'Compenso da Cliente 67', 'iscoordinamento' => 1],
            ['id' => 'Compenso da Istituto', 'iscoordinamento' => 0],
            ['id' => 'Compenso da Istituto 70', 'iscoordinamento' => 1],
            ['id' => 'Compenso da Istituto I tranche', 'iscoordinamento' => 1],
            ['id' => 'Compenso da istituto II Tranche', 'iscoordinamento' => 1],
            ['id' => 'Compenso da istituto III tranche', 'iscoordinamento' => 1],
            ['id' => 'COMPENSO DA PREMIO POLIZZA (65)', 'iscoordinamento' => 1],
            ['id' => 'compenso fisso 30', 'iscoordinamento' => 1],
            ['id' => 'compenso gas', 'iscoordinamento' => 1],
            ['id' => 'compenso II tranche', 'iscoordinamento' => 1],
            ['id' => 'compenso II tranche (75)', 'iscoordinamento' => 1],
            ['id' => 'compenso internet', 'iscoordinamento' => 1],
            ['id' => 'compenso luce', 'iscoordinamento' => 1],
            ['id' => 'Compenso over (10)', 'iscoordinamento' => 1],
            ['id' => 'Compenso over (5%)', 'iscoordinamento' => 1],
            ['id' => 'COMPENSO PREMIO', 'iscoordinamento' => 1],
            ['id' => 'Compenso Premio (1) da Istituto', 'iscoordinamento' => 1],
            ['id' => 'Compenso Premio (2,5) da Istituto', 'iscoordinamento' => 0],
            ['id' => 'Compenso Premio (3) da Istituto', 'iscoordinamento' => 0],
            ['id' => 'importo provvigioni assicurative', 'iscoordinamento' => 1],
            ['id' => 'POLIZZA BANCA', 'iscoordinamento' => 1],
            ['id' => 'POLIZZA BANCA 75%', 'iscoordinamento' => 1],
        ];

        foreach ($compensos as $compenso) {
            PraticaCompenso::updateOrCreate(
                ['id' => $compenso['id']],
                $compenso
            );
        }
    }
}
