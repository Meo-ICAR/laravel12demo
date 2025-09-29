<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ImportEsitiFromSidial extends Command
{
    protected $signature = 'sidial:import-esiti {--from=} {--to=} {--table=calls} {--dry-run}';

    protected $description = 'Importa gli esiti delle chiamate outbound da SIDIAL e popola la tabella esiti';

    public function handle(): int
    {
        $table = (string)$this->option('table');
        $baseUrl = Config::get('sidial.base_url');
        $token = Config::get('sidial.api_token');
        if (empty($baseUrl) || empty($token)) {
            $this->error('Config mancante: impostare SIDIAL_BASE_URL e SIDIAL_API_TOKEN');
            return self::FAILURE;
        }

        // Resolve date range
        $to = $this->option('to');
        $from = $this->option('from');

        // toDay defaults to yesterday
        if (!$to) {
            $toCarbon = Carbon::yesterday();
        } else {
            $toCarbon = Carbon::createFromFormat('d/m/Y', $to);
        }

        // fromDay defaults to last imported date (date part), else configured activation
        if (!$from) {
            $last = DB::table($table)->max(DB::raw('DATE(data_inizio)'));
            if ($last) {
                $fromCarbon = Carbon::parse($last);
            } else {
                $fallback = Config::get('sidial.last_activation');
                if (!$fallback) {
                    $this->warn('Nessun dato precedente trovato e nessuna SIDIAL_LAST_ACTIVATION configurata. Userò 01/01/2024 come fallback.');
                    $fromCarbon = Carbon::create(2024, 1, 1);
                } else {
                    $fromCarbon = Carbon::createFromFormat('d/m/Y', $fallback);
                }
            }
        } else {
            $fromCarbon = Carbon::createFromFormat('d/m/Y', $from);
        }

        if ($fromCarbon->gt($toCarbon)) {
            $this->info('Intervallo vuoto: fromDay è successivo a toDay, nessuna importazione.');
            return self::SUCCESS;
        }

        $fromDay = $fromCarbon->format('d/m/Y');
        $toDay = $toCarbon->format('d/m/Y');

        $this->info("Importo esiti da $fromDay a $toDay nella tabella '$table'...");

        // Build query params per API requirement
        $query = [
            'apiToken' => $token,
            'a' => 'getOutboundCallsExport',
            'campaign' => null,
            'list' => null,
            'allLists' => null,
            'agent' => null,
            'fromDay' => $fromDay,
            'toDay' => $toDay,
        ];

        // Http client call
        try {
            $response = Http::withHeaders([
                'Accept' => 'text/plain, text/csv, */*'
            ])->withOptions(['http_errors' => false])
              ->retry(3, 500)
              ->get($baseUrl, $query);
        } catch (\Throwable $e) {
            // Some environments may still throw on 404; handle gracefully
            if ($e instanceof \Illuminate\Http\Client\RequestException) {
                $resp = $e->response;
                if ($resp && $resp->status() === 404 && str_contains((string)$resp->body(), 'file not found')) {
                    $this->info("Nessun dato disponibile per l'intervallo $fromDay - $toDay (404 file not found). Proseguo.");
                    return self::SUCCESS;
                }
            }
            $this->error('Errore chiamando SIDIAL (exception): ' . $e->getMessage());
            return self::FAILURE;
        }

        if (!$response->ok()) {
            if ($response->status() === 404 && str_contains((string)$response->body(), 'file not found')) {
                $this->info("Nessun dato disponibile per l'intervallo $fromDay - $toDay (404 file not found). Proseguo.");
                return self::SUCCESS;
            }
            $this->error('Errore chiamando SIDIAL: ' . $response->status() . ' ' . $response->body());
            return self::FAILURE;
        }

        $csv = $response->body();
        if (!str_contains($csv, "Numero chiamato;")) {
            $this->warn('La risposta non sembra un CSV previsto. Stampo le prime 200 battute:');
            $this->line(substr($csv, 0, 200));
            return self::FAILURE;
        }

        $rows = $this->parseSemicolonCsv($csv);
        $this->info('Righe CSV (incl. header): ' . count($rows));
        if (count($rows) <= 1) {
            $this->info('Nessun dato da importare.');
            return self::SUCCESS;
        }

        $header = array_map('trim', array_shift($rows));
        // Expected headers
        $expected = ['Numero chiamato', 'Data inizio', 'Durata', 'Stato Chiamata', 'Esito', 'Utente'];
        foreach ($expected as $i => $name) {
            if (!isset($header[$i]) || $header[$i] !== $name) {
                $this->warn('Header CSV inatteso. Trovato: ' . implode(' | ', $header));
                break;
            }
        }

        $toUpsert = [];
        $imported = 0;
        foreach ($rows as $i => $cols) {
            if (count($cols) < 6) {
                continue; // skip malformed line
            }

            $numero = trim($cols[0]);
            $dataInizioStr = trim($cols[1]); // es. 01/09/2025 09:07:56
            $durataRaw = trim($cols[2]);     // es. 01:24 o 00:00
            $stato = trim($cols[3]);
            $esito = trim($cols[4]);
            $utente = trim($cols[5]);

            // Parse datetime and duration
            try {
                $dataInizio = Carbon::createFromFormat('d/m/Y H:i:s', $dataInizioStr);
            } catch (\Throwable $e) {
                $this->warn("Riga ".$i." datetime non valido: $dataInizioStr");
                continue;
            }

            $durataSecondi = $this->parseDurationToSeconds($durataRaw);

            $toUpsert[] = [
                'numero_chiamato' => $numero,
                'data_inizio' => $dataInizio->toDateTimeString(),
                'durata_raw' => $durataRaw !== '' ? $durataRaw : null,
                // For compatibility with existing 'calls' schema, also provide 'durata'
                'durata' => $durataRaw !== '' ? $durataRaw : null,
                'durata_secondi' => $durataSecondi,
                'stato_chiamata' => $stato !== '' ? $stato : null,
                'esito' => $esito !== '' ? $esito : null,
                'utente' => $utente !== '' ? $utente : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // batch upsert every 1000 to limit memory
            if (count($toUpsert) >= 1000) {
                $imported += $this->flushUpsert($toUpsert, (bool)$this->option('dry-run'), $table);
                $toUpsert = [];
            }
        }

        $imported += $this->flushUpsert($toUpsert, (bool)$this->option('dry-run'), $table);

        $this->info("Importazione completata. Righe inserite/aggiornate: $imported");
        return self::SUCCESS;
    }

    private function parseSemicolonCsv(string $csv): array
    {
        $lines = preg_split("/(\r\n|\n|\r)/", trim($csv));
        $data = [];
        foreach ($lines as $line) {
            // split by ; without quotes handling as dataset seems simple
            $data[] = array_map(static function ($v) { return trim($v); }, explode(';', $line));
        }
        return $data;
    }

    private function parseDurationToSeconds(?string $dur): ?int
    {
        if (!$dur) return null;
        // formats like HH:MM:SS or MM:SS or 00:00
        $parts = explode(':', $dur);
        if (count($parts) === 3) {
            [$h,$m,$s] = $parts;
            return ((int)$h)*3600 + ((int)$m)*60 + (int)$s;
        }
        if (count($parts) === 2) {
            [$m,$s] = $parts;
            return ((int)$m)*60 + (int)$s;
        }
        return null;
    }

    private function flushUpsert(array $batch, bool $dryRun, string $table): int
    {
        if (empty($batch)) return 0;
        if ($dryRun) {
            $this->line('[DRY-RUN] Upsert di ' . count($batch) . ' record saltato.');
            return 0;
        }
        // If target table is 'calls', adapt columns and use updateOrInsert to avoid requiring a unique index
        if ($table === 'calls') {
            $affected = 0;
            foreach ($batch as $row) {
                $keys = [
                    'numero_chiamato' => $row['numero_chiamato'],
                    'data_inizio' => $row['data_inizio'],
                ];
                $values = [
                    // map durata_raw -> durata in 'calls' schema
                    'durata' => $row['durata'],
                    'stato_chiamata' => $row['stato_chiamata'] ?? null,
                    'esito' => $row['esito'] ?? null,
                    'utente' => $row['utente'] ?? null,
                    'updated_at' => $row['updated_at'],
                ];
                // include created_at only on insert; updateOrInsert will handle timestamps appropriately
                $values['created_at'] = $row['created_at'];

                DB::table($table)->updateOrInsert($keys, $values);
                $affected++;
            }
            return $affected;
        }

        // Default behavior: Use DB upsert on composite unique for other tables
        DB::table($table)->upsert(
            $batch,
            ['numero_chiamato', 'data_inizio'],
            ['durata_raw', 'durata_secondi', 'stato_chiamata', 'esito', 'utente', 'updated_at']
        );
        return count($batch);
    }
}
