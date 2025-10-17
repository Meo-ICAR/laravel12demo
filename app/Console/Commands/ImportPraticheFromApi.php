<?php

namespace App\Console\Commands;

use App\Models\Pratiche;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ImportPraticheFromApi extends Command
{
    protected $signature = 'pratiche:import-api
                            {--start-date= : Start date (YYYY-MM-DD)}
                            {--end-date= : End date (YYYY-MM-DD)}}';

    protected $description = 'Import pratiche from external API';

    public function handle()
    {
        $endDate = $this->option('end-date') ? Carbon::parse($this->option('end-date')) : now();
        $startDate = $this->option('start-date')
            ? Carbon::parse($this->option('start-date'))
            : $endDate->copy()->subDays(7);

        $this->info("Importing pratiche from {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}");

        try {
            $apiUrl = env('MEDIAFACILE_BASE_URL', 'https://races.mediafacile.it/ws/hassisto.php');
            $response = Http::get($apiUrl , [
                'table' => 'pratiche',
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
                        $praticaData = $this->mapApiToModel($item);

                        if (empty($praticaData['codice_pratica'])) {
                            $this->warn('Skipping item without codice_pratica: ' . json_encode($item));
                            $errors++;
                            continue;
                        }

                        $existing = Pratiche::where('codice_pratica', $praticaData['codice_pratica'])->first();

                        if ($existing) {
                            $existing->update($praticaData);
                            $updated++;
                            $this->info("Updated pratica: {$praticaData['codice_pratica']}");
                        } else {
                            Pratiche::create($praticaData);
                            $imported++;
                            $this->info("Imported new pratica: {$praticaData['codice_pratica']}");
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
            'codice_pratica' => $apiData['Codice Pratica'] ?? null,
            'nome_cliente' => $apiData['Nome Cliente'] ?? null,
            'cognome_cliente' => $apiData['Cognome Cliente'] ?? null,
            'codice_fiscale' => $apiData['Codice Fiscale'] ?? null,
            'denominazione_agente' => $apiData['Denominazione Agente'] ?? null,
            'partita_iva_agente' => $apiData['Partita IVA Agente'] ?? null,
            'denominazione_banca' => $apiData['Denominazione Banca'] ?? null,
            'tipo_prodotto' => $apiData['Tipo Prodotto'] ?? null,
            'denominazione_prodotto' => $apiData['Descrizione Prodotto'] ?? null,
            'data_inserimento_pratica' => $apiData['Data Inserimento Pratica'] ?? now(),
            'stato_pratica' => $apiData['Stato Pratica'] ?? null,
        ];
    }
}
