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
            $apiUrl = env('MEDIAFACILE_BASE_URL', 'https://races.mediafacile.it/ws/hassisto.php');
            $queryParams = [
                'table' => 'compensi',
                'data_inizio' => $startDate->format('Y-m-d'),
                'data_fine' => $endDate->format('Y-m-d'),
            ];
            $response = Http::withHeaders([
                'Accept' => 'application/json, */*',
                'User-Agent' => 'ProForma Import/1.0',
                'X-Api-Key' => 'kzoPW9i3HCs4WJ8ja8xk',
            ])
            ->timeout(60) // 60 seconds timeout
            ->connectTimeout(10) // 10 seconds to establish connection
            ->withOptions([
                'http_errors' => false,
                'verify' => false, // Only if you need to bypass SSL verification
            ])
            ->retry(3, 1000, function ($exception) {
                // Retry on connection timeouts or server errors
                return $exception instanceof \Illuminate\Http\Client\ConnectionException ||
                       ($exception->getCode() >= 500);
            })
            ->get($apiUrl, $queryParams);

            // Log the request and response for debugging
            \Log::info('Provvigioni API Request', [
                'url' => $apiUrl,
                'params' => $queryParams,
                'status' => $response->status(),
                'response_size' => strlen($response->body()),
            ]);

            if (!$response->successful()) {
                \Log::error('Provvigioni API Error', [
                    'status' => $response->status(),
                    'response' => substr($response->body(), 0, 1000),
                ]);
                $this->error('API request failed with status: ' . $response->status());
                return 1;
            }

            $responseBody = trim($response->body());

            if (empty($responseBody)) {
                $this->error('Empty response body from API');
                return 1;
            }

            // Log complete API response
            \Log::info('Complete API response:', [
                'response' => $responseBody,
                'total_bytes' => strlen($responseBody),
                'line_count' => count(explode("\n", $responseBody))
            ]);

            // Process lines
            $lines = explode("\n", $responseBody);
            $lines = array_filter($lines, function($line) {
                return trim($line) !== '';
            });
            $lines = array_values($lines);

            // Remove empty lines (already done above)
            $lines = array_values($lines); // Reindex array

            if (count($lines) < 2) { // Need at least header row + 1 data row
                $this->info('No data rows found in API response');
                return 0;
            }

            // Get headers from first line and clean them
            $headers = $this->parseLine($lines[0]);
            $data = [];
            $headerCount = count($headers);

            // Debug: Log the headers
            \Log::debug('Headers:', ['headers' => $headers, 'count' => $headerCount]);

            // Process data lines
            for ($i = 1; $i < count($lines); $i++) {
                $values = $this->parseLine($lines[$i]);

                // Skip empty lines
                if (empty($values)) {
                    continue;
                }

                // If we have more values than headers, truncate the extra values
                if (count($values) > $headerCount) {
                    $values = array_slice($values, 0, $headerCount);
                }
                // If we have fewer values than headers, pad with nulls
                elseif (count($values) < $headerCount) {
                    $values = array_pad($values, $headerCount, null);
                }

                try {
                    $data[] = array_combine($headers, $values);
                } catch (\Exception $e) {
                    $this->warn(sprintf(
                        'Error combining row %d: %s',
                        $i + 1,
                        $e->getMessage()
                    ));
                    \Log::error('Error combining row data', [
                        'headers' => $headers,
                        'values' => $values,
                        'error' => $e->getMessage()
                    ]);
                }
            }


            if (empty($data)) {
                $this->info('No records found in the specified date range');
                $this->info('API Response Status: ' . $response->status());
                $this->info('API Response Preview: ' . substr($response->body(), 0, 200));
                return 0;
            }

            // Verify response headers match expected format
            if (!empty($data)) {
                $firstItem = $data[0];
                $expectedHeaders = [
                    'ID Compenso',
                    'Data Inserimento',
                    'Descrizione',
                    'Tipo',
                    'Importo',
                    'Importo Effettivo',
                    'Stato',
                    'Denominazione Riferimento',
                    'Entrata Uscita',
                    'ID Pratica',
                    'Agente',
                    'Istituto finanziario'
                ];

                $actualHeaders = array_keys($firstItem);

                // Check if all expected headers exist in the response
                $missingHeaders = array_diff($expectedHeaders, $actualHeaders);
                if (!empty($missingHeaders)) {
                    $this->error('Missing expected headers: ' . implode(', ', $missingHeaders));
                    $this->warn('Actual headers: ' . implode(', ', $actualHeaders));
                    return 1;
                }

                // Check if headers are in the correct order
                $matchingHeaders = array_intersect($expectedHeaders, $actualHeaders);
                if ($matchingHeaders !== $expectedHeaders) {
                    $this->warn('Warning: Headers are not in the expected order.');
                    $this->warn('Expected order: ' . implode('\t', $expectedHeaders));
                    $this->warn('Actual order:   ' . implode('\t', $matchingHeaders));
                }
            }

            $imported = 0;
            $updated = 0;
            $errors = 0;

            foreach ($data as $item) {
                try {
                    $provvigioneData = $this->mapApiToModel($item);

                    // Use 'Codice Pratica' as the identifier since that's what's in the API response
                    if (empty($item['ID Compenso'])) {
                        $this->warn('Skipping item without ID Compenso: ' . json_encode($item));
                        $errors++;
                        continue;
                    }

                    // Ensure we have the ID Compenso in our data
                    $provvigioneData['id'] = $item['ID Compenso'];

                    $existing = Provvigione::where('id', $provvigioneData['id'])->first();

                    if ($existing) {
                        // Check if any of the timestamp fields are already set
                        if (empty($existing->sended_at) && empty($existing->received_at) && empty($existing->paided_at)) {
                            $existing->update($provvigioneData);
                            $updated++;
                            $this->info("Updated provvigione: {$provvigioneData['id']}");
                        } else {
                            $this->info("Skipped update for provvigione {$provvigioneData['id']} - timestamps sended, received paide dalready set");
                            $skipped++;
                        }
                    } else {
                        Provvigione::create($provvigioneData);
                        $imported++;
                        $this->info("Imported new provvigione: {$provvigioneData['id']}");
                    }
                } catch (\Exception $e) {
                    $this->error("Error processing item: " . $e->getMessage());
                    $errors++;
                }
            }

            $this->info("Import completed. Imported: {$imported}, Updated: {$updated}, Errors: {$errors}");
            return 0;
        } catch (\Illuminate\Http\Client\RequestException $e) {
            $this->error('HTTP Request Error: ' . $e->getMessage());
            \Log::error('Provvigioni API Request Exception', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'response' => $e->response ? [
                    'status' => $e->response->status(),
                    'body' => substr((string)$e->response->body(), 0, 1000)
                ] : null
            ]);
            return 1;
        } catch (\Throwable $e) {
            $this->error('Unexpected Error: ' . $e->getMessage());
            \Log::error('Unexpected Error in Provvigioni Import', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    protected function parseLine($line)
    {
        // First try to split by tab
        $parts = explode("\t", trim($line));

        // If we only got one part, try splitting by multiple spaces
        if (count($parts) <= 1) {
            $parts = preg_split('/\s{2,}/', trim($line));
        }

        // Clean up each part
        return array_map(function($part) {
            return trim($part, " \t\n\r\0\x0B\"'`");
        }, $parts);
    }

    protected function mapApiToModel(array $apiData): array
    {
        $dataInserimento = null;
        $dataInserimentoValue = $apiData['Data Inserimento Compenso'] ?? $apiData['Data Inserimento'] ?? null;

        if (!empty($dataInserimentoValue)) {
            try {
                $dateParts = explode('/', $dataInserimentoValue);
                if (count($dateParts) === 3) {
                    $dataInserimento = Carbon::createFromFormat('d/m/Y', $dataInserimentoValue);
                }
            } catch (\Exception $e) {
                // If parsing fails, leave as null
                $this->warn("Failed to parse date: " . $dataInserimentoValue);
            }
        }

        return [
            'id' => $apiData['ID Compenso'] ?? null,
            'data_inserimento_compenso' => $dataInserimento,
            'descrizione' => $apiData['Descrizione'] ?? null,
            'tipo' => $apiData['Tipo'] ?? 'provvigione',
            'importo' => is_numeric($apiData['Importo']) ? $apiData['Importo'] : (is_string($apiData['Importo']) ? (float) str_replace(',', '.', $apiData['Importo']) : 0),
            'importo_effettivo' => is_numeric($apiData['Importo Effettivo'] ?? null) ? $apiData['Importo Effettivo'] : (is_string($apiData['Importo Effettivo'] ?? null) ? (float) str_replace(',', '.', $apiData['Importo Effettivo']) : null),
            'status_pratica' => $apiData['Stato'] ?? '',
            'denominazione_riferimento' => $apiData['Denominazione Riferimento'] ?? null,
            'entrata_uscita' => $apiData['Entrata Uscita'] ?? null,
            'id_pratica' => $apiData['ID Pratica'] ?? null,
            'segnalatore' => $apiData['Agente'] ?? null,
            'istituto_finanziario' => $apiData['Istituto finanziario'] ?? null,
            'fonte' => 'api',
        ];
    }
}
