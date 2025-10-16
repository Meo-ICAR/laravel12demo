<?php

namespace App\Console\Commands;

use App\Models\Provvigione;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ImportProvvigioniFromApi extends Command
{
    protected $signature = 'provvigioni:import-api
                            {--start-date= : Start date (YYYY-MM-DD)}
                            {--end-date= : End date (YYYY-MM-DD)}}';

    protected $description = 'Import provvigioni from external API';

    public function handle()
    {
        $endDate = $this->option('end-date') ? Carbon::parse($this->option('end-date')) : now();
        $startDate = $this->option('start-date')
            ? Carbon::parse($this->option('start-date'))
            : $endDate->copy()->subDays(7);

        $this->info("Importing provvigioni from {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}");

        try {
            $response = Http::get('https://races.mediafacile.it/ws/hassisto.php', [
                'table' => 'provigioni',
                'data_inizio' => $startDate->format('Y-m-d'),
                'data_fine' => $endDate->format('Y-m-d'),
            ]);

            if ($response->successful()) {
                $lines = explode("\n", trim($response->body()));

                if (empty($lines)) {
                    $this->error('No data received from API');
                    return 1;
                }

                // Get headers from first line
                $headers = $this->parseLine($lines[0]);
                $data = [];

                // Process data lines
                for ($i = 1; $i < count($lines); $i++) {
                    $values = $this->parseLine($lines[$i]);
                    if (count($values) === count($headers)) {
                        $data[] = array_combine($headers, $values);
                    }
                }

                if (empty($data)) {
                    $this->info('No records found in the specified date range');
                    return 0;
                }

                $imported = 0;
                $updated = 0;
                $errors = 0;

                foreach ($data as $item) {
                    try {
                        $provvigioneData = $this->mapApiToModel($item);

                        if (empty($provvigioneData['id_pratica'])) {
                            $this->warn('Skipping item without id_pratica: ' . json_encode($item));
                            $errors++;
                            continue;
                        }

                        $existing = Provvigione::where('id_pratica', $provvigioneData['id_pratica'])->first();

                        if ($existing) {
                            $existing->update($provvigioneData);
                            $updated++;
                            $this->info("Updated provvigione for pratica: {$provvigioneData['id_pratica']}");
                        } else {
                            Provvigione::create($provvigioneData);
                            $imported++;
                            $this->info("Imported new provvigione for pratica: {$provvigioneData['id_pratica']}");
                        }
                    } catch (\Exception $e) {
                        $this->error("Error processing item: " . $e->getMessage());
                        $errors++;
                    }
                }

                $this->info("Import completed. Imported: {$imported}, Updated: {$updated}, Errors: {$errors}");
                return 0;
            } else {
                $this->error('API request failed: ' . $response->status());
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }

    protected function parseLine($line)
    {
        return array_map('trim', explode("\t", $line));
    }

    protected function mapApiToModel(array $apiData): array
    {
        return [
            'id' => $apiData['ID'] ?? (string) Str::uuid(),
            'legacy_id' => $apiData['ID'] ?? null,
            'data_inserimento_compenso' => $apiData['Data Inserimento Compenso'] ?? now(),
            'descrizione' => $apiData['Descrizione'] ?? null,
            'tipo' => $apiData['Tipo'] ?? 'provvigione',
            'importo' => $apiData['Importo'] ?? 0,
            'importo_effettivo' => $apiData['Importo Effettivo'] ?? 0,
            'quota' => $apiData['Quota'] ?? 0,
            'stato' => $apiData['Stato'] ?? 'da_pagare',
            'denominazione_riferimento' => $apiData['Denominazione Riferimento'] ?? null,
            'entrata_uscita' => $apiData['Entrata/Uscita'] ?? 'entrata',
            'cognome' => $apiData['Cognome'] ?? null,
            'nome' => $apiData['Nome'] ?? null,
            'segnalatore' => $apiData['Segnalatore'] ?? null,
            'fonte' => $apiData['Fonte'] ?? 'api',
            'id_pratica' => $apiData['ID Pratica'] ?? null,
            'tipo_pratica' => $apiData['Tipo Pratica'] ?? null,
            'data_inserimento_pratica' => $apiData['Data Inserimento Pratica'] ?? now(),
            'data_stipula' => $apiData['Data Stipula'] ?? null,
            'istituto_finanziario' => $apiData['Istituto Finanziario'] ?? null,
            'prodotto' => $apiData['Prodotto'] ?? null,
            'macrostatus' => $apiData['Macrostatus'] ?? null,
            'status_pratica' => $apiData['Status Pratica'] ?? null,
            'data_status_pratica' => $apiData['Data Status Pratica'] ?? null,
            'montante' => $apiData['Montante'] ?? 0,
            'importo_erogato' => $apiData['Importo Erogato'] ?? 0,
            'sended_at' => $apiData['Data Invio'] ?? null,
            'received_at' => $apiData['Data Ricezione'] ?? null,
            'paided_at' => $apiData['Data Pagamento'] ?? null,
        ];
    }
}
