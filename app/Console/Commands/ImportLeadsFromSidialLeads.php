<?php

namespace App\Console\Commands;

use App\Models\Lead;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Dotenv\Dotenv;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

// Simple chunk read filter to limit rows loaded in memory
class ChunkReadFilter implements IReadFilter
{
    private int $startRow = 1;
    private int $endRow = 1;

    public function setRows(int $startRow, int $chunkSize): void
    {
        $this->startRow = $startRow;
        $this->endRow = $startRow + $chunkSize - 1;
    }

    public function readCell($column, $row, $worksheetName = ''): bool
    {
        // Always read header row 1 to keep headers available for each chunk
        if ($row === 1) return true;
        return $row >= $this->startRow && $row <= $this->endRow;
    }
}

class ImportLeadsFromSidialLeads extends Command
{
    protected $signature = 'sidial:import-leads '
        .' {--advancedCampaign=* : ID campagna ripetibile, es. --advancedCampaign=17 --advancedCampaign=22}'
        .' {--fromDay= : Data inizio (dd/mm/YYYY); sarà inviata come fromCreatedWhen}'
        .' {--today= : Data fine (dd/mm/YYYY); sarà inviata come toCreatedWhen; default oggi}'
        .' {--dry-run : Non scrivere sul DB, mostra solo conteggi}';

    protected $description = 'Importa LEADS da SIDIAL (getLeadsExport) e inserisce/upserta nella tabella leads';

    public function handle(): int
    {
        // Increase memory limit to handle large spreadsheets safely
        @ini_set('memory_limit', '1024M');
        @set_time_limit(0);
        try { \DB::connection()->disableQueryLog(); } catch (\Throwable $e) {}
        $baseUrl = Config::get('sidial.base_url');
        $token = Config::get('sidial.api_token');
        if (empty($baseUrl) || empty($token)) {
            $this->error('Config mancante: impostare SIDIAL_BASE_URL e SIDIAL_API_TOKEN');
            return self::FAILURE;
        }

        // Validate/resolve dates
        $fromOpt = $this->option('fromDay');
        $toOpt = $this->option('today');

        $toCarbon = $toOpt ? Carbon::createFromFormat('d/m/Y', $toOpt) : Carbon::today();

        if ($fromOpt) {
            $fromCarbon = Carbon::createFromFormat('d/m/Y', $fromOpt);
        } else {
            // fallback: use configured last_activation or 01/01/2024
            $fallback = env('SIDIAL_LEADS_LAST_ACTIVATION', null);
            $fromCarbon = Carbon::createFromFormat('d/m/Y', $fallback);
        }

        if ($fromCarbon->gt($toCarbon)) {
            $this->info('Intervallo vuoto: from è successivo a today.');
            return self::SUCCESS;
        }

        $fromCreated = $fromCarbon->format('d/m/Y');
        $toCreated = $toCarbon->format('d/m/Y');

        $campaigns = (array) $this->option('advancedCampaign');
        if (empty($campaigns)) {
            $this->warn('Nessun --advancedCampaign specificato: la richiesta userà nessun filtro campagna.');
        }

        $this->info("Scarico leads da $fromCreated a $toCreated...");

        // Build query params according to example URL
        $query = [
            'a' => 'getLeadsExport',
            'apiToken' => $token,
            'fromCreatedWhen' => $fromCreated,
            'toCreatedWhen' => $toCreated,
            /*
            'searchPhone' => '',
            'searchRagSoc' => '',
            'searchSurname' => '',
            'searchName' => '',
            'searchMunicipality' => '',
            'searchProvince' => '',
            'searchNotes' => '',
            'searchEmail' => '',
            'searchId' => '',
            'searchTaxCode' => '',
            'fromDay' => '', // left empty as per sample; use createdWhen instead
            'toDay' => '',
            'fromExpireWhen' => '',
            'toExpireWhen' => '',

            'fromRecallWhen' => '',
            'toRecallWhen' => '',
            'totAttemptsFrom' => '',
            'totAttemptsTo' => '',
            */
        ];

        // Append advancedCampaign as array parameter (Laravel serializes as advancedCampaign[0]=...)
        if (!empty($campaigns)) {
            $query['advancedCampaign'] = array_values($campaigns);
        }
            // Log the request and response for debugging
            \Log::info('SIDIAL LEADS API url', [
                'url' => $baseUrl,
                'params' => $query,

            ]);

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, */*'
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
            ->get($baseUrl, $query);

        } catch (\Illuminate\Http\Client\RequestException $e) {
            $this->error('Errore nella richiesta a SIDIAL: ' . $e->getMessage());
            \Log::error('SIDIAL API Request Error', [
                'url' => $baseUrl,
                'query' => $query,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'response' => $e->response ? [
                    'status' => $e->response->status(),
                    'body' => substr((string)$e->response->body(), 0, 1000)
                ] : null
            ]);
            return self::FAILURE;
        } catch (\Throwable $e) {
            $this->error('Errore imprevisto: ' . $e->getMessage());
            \Log::error('Unexpected Error in SIDIAL Import', [
                'url' => $baseUrl,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return self::FAILURE;
        }

        // Log the response details for debugging
        /*
        \Log::info('SIDIAL API LEADS Response', [
            'url' => $baseUrl,
            'status' => $response->status(),
            'headers' => $response->headers(),
            'body' => substr((string)$response->body(), 0, 500) // First 500 chars of response
        ]);
        */
        if (!$response->ok()) {
            $this->error('Errore SIDIAL: '.$response->status().' '.substr((string)$response->body(), 0, 300));
            return self::FAILURE;
        }

        $body = $response->body();
        if (stripos($response->header('Content-Type', ''), 'text/html') !== false) {
            $this->warn('La risposta sembra HTML, non un file Excel. Prime 200 battute:');
            $this->line(substr($body, 0, 200));
            return self::FAILURE;
        }

        // Save to a temporary file
        $tmpDir = storage_path('app');
        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0775, true);
        }
        $tmpFile = $tmpDir.'/sidial_leads_'.now()->format('Ymd_His').'.xls';
        file_put_contents($tmpFile, $body);

        // Parse spreadsheet using chunked reading to avoid high memory usage
        try {
            $reader = IOFactory::createReaderForFile($tmpFile);
            if (method_exists($reader, 'setReadDataOnly')) {
                $reader->setReadDataOnly(true);
            }

            $chunkSize = 200; // rows per chunk to keep memory low
            $filter = new ChunkReadFilter();
            if (method_exists($reader, 'setReadFilter')) {
                $reader->setReadFilter($filter);
            }

            // Load header row (1) only to build headers map
            $filter->setRows(1, 1);
            $spreadsheet = $reader->load($tmpFile);
            $sheet = $spreadsheet->getActiveSheet();
            $highestColumn = $sheet->getHighestColumn();
            $headerRow = $sheet->rangeToArray('A1:'.$highestColumn.'1', null, true, true, true)[1] ?? [];
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

            if (empty($headerRow)) {
                @unlink($tmpFile);
                $this->info('Nessuna riga da importare.');
                return self::SUCCESS;
            }

            $headers = [];
            foreach ($headerRow as $colIdx => $value) {
                $label = trim((string)$value);
                $headers[$colIdx] = $this->normalizeHeader($label);
            }

            $batch = [];
            $imported = 0;
            $startRow = 2; // data starts after header

            while (true) {
                $filter->setRows($startRow, $chunkSize);
                $spreadsheet = $reader->load($tmpFile);
                $sheet = $spreadsheet->getActiveSheet();
                $highestRow = $sheet->getHighestRow();
                if ($highestRow < $startRow) {
                    // No more data available
                    $spreadsheet->disconnectWorksheets();
                    unset($sheet, $spreadsheet);
                    break;
                }
                $endRow = min($startRow + $chunkSize - 1, $highestRow);
                $rowIterator = $sheet->getRowIterator($startRow, $endRow);
                $processedAny = false;
                foreach ($rowIterator as $row) {
                    $rowIndex = $row->getRowIndex();
                    if ($rowIndex === 1) continue; // skip header
                    $processedAny = true;
                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(true);
                    $assoc = [];
                    foreach ($cellIterator as $cell) {
                        $colIdx = $cell->getColumn(); // e.g., 'A','B',...
                        if (!isset($headers[$colIdx]) || $headers[$colIdx] === '') continue;
                        $val = $cell->getValue();
                        $assoc[$headers[$colIdx]] = is_string($val) ? trim($val) : $val;
                    }

                    if (empty($assoc)) continue;

                    $mapped = $this->mapLeadRow($assoc);
                    if (!$mapped) continue;

                    $mapped['updated_at'] = now();
                    $mapped['created_at'] = $mapped['created_at'] ?? now();

                    $batch[] = $mapped;
                    if (count($batch) >= 300) {
                        $imported += $this->flushLeads($batch, (bool)$this->option('dry-run'));
                        $batch = [];
                    }
                }

                $spreadsheet->disconnectWorksheets();
                unset($rowIterator, $sheet, $spreadsheet);

                if (!$processedAny) {
                    break; // no more rows
                }

                $startRow += $chunkSize; // next chunk
            }

            // flush any remaining
            $imported += $this->flushLeads($batch, (bool)$this->option('dry-run'));

            @unlink($tmpFile);

            if ($imported > 0) {
                // Update the last activation date in the environment
                $dotenv = new \Dotenv\Dotenv(base_path());
                $dotenv->load();
                $dotenv->populate([
                    'SIDIAL_LEADS_LAST_ACTIVATION' => $toCreated
                ], true);
                
                // Write the .env file
                $envPath = base_path('.env');
                $envContent = file_get_contents($envPath);
                $envContent = preg_replace(
                    '/^SIDIAL_LEADS_LAST_ACTIVATION=.*/m',
                    "SIDIAL_LEADS_LAST_ACTIVATION=$toCreated",
                    $envContent,
                    1,
                    $count
                );
                
                if ($count === 0) {
                    $envContent .= "\nSIDIAL_LEADS_LAST_ACTIVATION=$toCreated\n";
                }
                
                file_put_contents($envPath, $envContent);
                $this->info("Aggiornato SIDIAL_LEADS_LAST_ACTIVATION a $toCreated");
            }

            $this->info("Importazione completata. Righe inserite/aggiornate: $imported");
            return self::SUCCESS;
        } catch (\Throwable $e) {
            @unlink($tmpFile);
            $this->error('Errore parsing XLS: '.$e->getMessage());
            return self::FAILURE;
        }
    }

    private function normalizeHeader(string $label): string
    {
        // Map Italian labels to snake_case keys used by Lead model, fallback to snake
        $map = [
            'ID' => 'id',
            'Campagna' => 'campagna',
            'Lista' => 'lista',
            'Ragione Sociale' => 'ragione_sociale',
            'Cognome' => 'cognome',
            'Nome' => 'nome',
            'Telefono' => 'telefono',
            'Ultimo Operatore' => 'ultimo_operatore',
            'Esito' => 'esito',
            'Data Richiamo' => 'data_richiamo',
            'Operatore Richiamo' => 'operatore_richiamo',
            'Scadenza Anagrafica' => 'scadenza_anagrafica',
            'Indirizzo1' => 'indirizzo1',
            'Indirizzo2' => 'indirizzo2',
            'Indirizzo3' => 'indirizzo3',
            'Comune' => 'comune',
            'Provincia' => 'provincia',
            'CAP' => 'cap',
            'Regione' => 'regione',
            'Paese' => 'paese',
            'Email' => 'email',
            'P.IVA' => 'p_iva',
            'Codice Fiscale' => 'codice_fiscale',
            'Telefono2' => 'telefono2',
            'Telefono3' => 'telefono3',
            'Telefono4' => 'telefono4',
            'Sesso' => 'sesso',
            'Nota' => 'nota',
            'Attivo' => 'attivo',
            'Altro1' => 'altro1',
            'Altro2' => 'altro2',
            'Altro3' => 'altro3',
            'Altro4' => 'altro4',
            'Altro5' => 'altro5',
            'Altro6' => 'altro6',
            'Altro7' => 'altro7',
            'Altro8' => 'altro8',
            'Altro9' => 'altro9',
            'Altro10' => 'altro10',
            'Chiamate' => 'chiamate',
            'Ultima Chiamata' => 'ultima_chiamata',
            'Creato Da' => 'creato_da',
            'Durata Ultima Chiamata' => 'durata_ultima_chiamata',
            'Totale Durata Chiamate' => 'totale_durata_chiamate',
            'Chiamate Giornaliere' => 'chiamate_giornaliere',
            'Chiamate Mensili' => 'chiamate_mensili',
            'Data Creazione' => 'data_creazione',
        ];

        if (isset($map[$label])) return $map[$label];
        // Fallback: slugify
        return Str::snake($label);
    }

    /**
     * Normalize and validate a phone number
     */
    private function normalizePhone(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        // Remove all non-digit characters except leading +
        $normalized = preg_replace('/[^\d+]/', '', (string)$phone);

        // Handle international numbers (start with + or 00)
        if (strpos($normalized, '+') === 0) {
            // Already in international format
            return $normalized;
        }

        // Handle 00 prefix (international format without +)
        if (strpos($normalized, '00') === 0) {
            return '+' . substr($normalized, 2);
        }

        // Handle Italian numbers (assume if not international)
        if (strlen($normalized) >= 6) { // Basic validation for Italian numbers
            // Remove leading 0 if present and add +39
            if (strpos($normalized, '0') === 0) {
                $normalized = '39' . substr($normalized, 1);
            }
            return '+' . $normalized;
        }

        return $normalized; // Return as is if doesn't match any pattern
    }

    private function parseDate($value)
    {
        if (empty($value)) return null;
        try {
            $formats = ['d/m/Y H:i:s','d/m/Y H:i','d/m/Y','Y-m-d H:i:s','Y-m-d'];
            foreach ($formats as $f) {
                try { return Carbon::createFromFormat($f, (string)$value); } catch (\Throwable $e) {}
            }
            return Carbon::parse((string)$value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function parseBoolean($value): bool
    {
        if ($value === null || $value === '') return true; // default active
        $v = strtolower(trim((string)$value));
        return in_array($v, ['y','yes','true','1','si','sì'], true);
    }

    private function parseInt($value): int
    {
        if ($value === null || $value === '') return 0;
        return (int)$value;
    }

    /**
     * Map and validate a single lead row from the import
     */
    private function mapLeadRow(array $row): ?array
    {
        // Skip if essential fields are missing
        if (empty($row['telefono']) && empty($row['ragione_sociale']) && empty($row['nome']) && empty($row['cognome'])) {
            return null;
        }

        // Helper function to trim strings to specified length
        $truncate = function($value, $maxLength) {
            if ($value === null) return null;
            return mb_substr(trim($value), 0, $maxLength);
        };

        // Normalize and validate phone numbers
        $phone1 = $this->normalizePhone($row['telefono'] ?? null);
        $phone2 = $this->normalizePhone($row['telefono2'] ?? null);
        $phone3 = $this->normalizePhone($row['telefono3'] ?? null);
        $phone4 = $this->normalizePhone($row['telefono4'] ?? null);

        // If primary phone is empty but we have alternative numbers, use the first available
        if (empty($phone1)) {
            if (!empty($phone2)) {
                $phone1 = $phone2;
                $phone2 = null;
            } elseif (!empty($phone3)) {
                $phone1 = $phone3;
                $phone3 = null;
            } elseif (!empty($phone4)) {
                $phone1 = $phone4;
                $phone4 = null;
            }
        }

        // Parse dates with error handling
        $parseDate = function($date) {
            if (empty($date)) return null;
            try {
                return $this->parseDate($date);
            } catch (\Exception $e) {
                return null;
            }
        };

        // Map the data to match the database schema with length constraints
        $data = [
            'legacy_id' => $truncate($row['id'] ?? null, 20),
            'campagna' => $truncate($row['campagna'] ?? null, 100),
            'lista' => $truncate($row['lista'] ?? null, 100),
            'ragione_sociale' => $truncate($row['ragione_sociale'] ?? null, 255),
            'cognome' => $truncate($row['cognome'] ?? null, 100),
            'nome' => $truncate($row['nome'] ?? null, 100),
            'telefono' => $truncate($phone1, 20), // Max 20 chars
            'ultimo_operatore' => $truncate($row['ultimo_operatore'] ?? null, 255),
            'esito' => $truncate($row['esito'] ?? null, 100),
            'data_richiamo' => $parseDate($row['data_richiamo'] ?? null),
            'operatore_richiamo' => $truncate($row['operatore_richiamo'] ?? null, 255),
            'scadenza_anagrafica' => $parseDate($row['scadenza_anagrafica'] ?? null),
            'indirizzo1' => $truncate($row['indirizzo1'] ?? null, 255),
            'indirizzo2' => $truncate($row['indirizzo2'] ?? null, 255),
            'indirizzo3' => $truncate($row['indirizzo3'] ?? null, 255),
            'comune' => $truncate($row['comune'] ?? null, 100),
            'provincia' => $truncate($row['provincia'] ?? null, 10),
            'cap' => $truncate($row['cap'] ?? null, 10),
            'regione' => $truncate($row['regione'] ?? null, 100),
            'paese' => $truncate($row['paese'] ?? 'Italia', 100),
            'email' => filter_var($row['email'] ?? null, FILTER_VALIDATE_EMAIL) ? $truncate($row['email'], 255) : null,
            'p_iva' => $truncate($row['p_iva'] ?? null, 50),
            'codice_fiscale' => $truncate($row['codice_fiscale'] ?? null, 20),
            'telefono2' => $truncate($phone2, 20), // Max 20 chars
            'telefono3' => $truncate($phone3, 20), // Max 20 chars
            'telefono4' => $truncate($phone4, 20), // Max 20 chars
            'sesso' => $truncate($row['sesso'] ?? null, 10),
            'nota' => $row['nota'] ?? null, // Text field, no length limit
            'attivo' => $this->parseBoolean($row['attivo'] ?? null),
            'altro1' => $truncate($row['altro1'] ?? null, 255),
            'altro2' => $truncate($row['altro2'] ?? null, 255),
            'altro3' => $truncate($row['altro3'] ?? null, 255),
            'altro4' => $truncate($row['altro4'] ?? null, 255),
            'altro5' => $truncate($row['altro5'] ?? null, 255),
            'altro6' => $truncate($row['altro6'] ?? null, 255),
            'altro7' => $truncate($row['altro7'] ?? null, 255),
            'altro8' => $truncate($row['altro8'] ?? null, 255),
            'altro9' => $truncate($row['altro9'] ?? null, 255),
            'altro10' => $truncate($row['altro10'] ?? null, 255),
            'chiamate' => max(0, $this->parseInt($row['chiamate'] ?? 0)),
            'ultima_chiamata' => $parseDate($row['ultima_chiamata'] ?? null),
            'creato_da' => $truncate($row['creato_da'] ?? 'Sidial Import', 255),
            'durata_ultima_chiamata' => $truncate($row['durata_ultima_chiamata'] ?? null, 20),
            'totale_durata_chiamate' => $truncate($row['totale_durata_chiamate'] ?? null, 20),
            'chiamate_giornaliere' => max(0, $this->parseInt($row['chiamate_giornaliere'] ?? 0)),
            'chiamate_mensili' => max(0, $this->parseInt($row['chiamate_mensili'] ?? 0)),
            'data_creazione' => $parseDate($row['data_creazione'] ?? null) ?? now(),
            'company_id' => $this->companyId ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Additional validation
        if (empty($data['ragione_sociale']) && empty($data['cognome']) && empty($data['nome'])) {
            return null; // At least one name field is required
        }

        return $data;
    }

    /**
     * Process and save a batch of leads to the database
     *
     * @param array $batch The batch of leads to process
     * @param bool $dryRun Whether to simulate the operation without saving
     * @return int Number of affected rows
     */
    private function flushLeads(array $batch, bool $dryRun): int
    {
        if (empty($batch)) return 0;

        if ($dryRun) {
            $this->line('[DRY-RUN] Upsert di '.count($batch).' lead.');
            return 0;
        }

        $affected = 0;
        $chunkSize = 500; // Process in smaller chunks to avoid timeouts

        // Process in chunks to avoid memory issues
        foreach (array_chunk($batch, $chunkSize) as $chunk) {
            // Split chunk into records with and without legacy_id
            $withId = [];
            $withoutId = [];

            foreach ($chunk as $row) {
                if (!empty($row['legacy_id'])) {
                    $withId[] = $row;
                } else {
                    $withoutId[] = $row;
                }
            }

            // Process records with legacy_id (bulk upsert)
            if (!empty($withId)) {
                try {
                    $columnsToUpdate = array_diff(
                        array_keys($withId[0]),
                        ['legacy_id', 'created_at', 'updated_at']
                    );

                    DB::table('leads')->upsert(
                        $withId,
                        ['legacy_id'],
                        $columnsToUpdate
                    );
                    $affected += count($withId);
                } catch (\Exception $e) {
                    // Fall back to individual updates if bulk fails
                    $this->warn('Bulk upsert failed, falling back to individual updates: ' . $e->getMessage());
                    $affected += $this->processIndividualLeads($withId);
                }
            }

            // Process records without legacy_id (individual updates)
            if (!empty($withoutId)) {
                $affected += $this->processIndividualLeads($withoutId);
            }
        }

        return $affected;
    }

    /**
     * Process leads one by one to handle any unique constraint issues
     *
     * @param array $leads Array of leads to process
     * @return int Number of successfully processed leads
     */
    private function processIndividualLeads(array $leads): int
    {
        $affected = 0;

        foreach ($leads as $row) {
            try {
                $keys = [];

                // If we have a legacy_id, use it as the unique key
                if (!empty($row['legacy_id'])) {
                    $keys = ['legacy_id' => $row['legacy_id']];
                }
                // Otherwise try to create a unique key from available fields
                else {
                    $keys = [
                        'telefono' => $row['telefono'] ?? null,
                        'campagna' => $row['campagna'] ?? null,
                    ];

                    // If phone and campaign are empty, try alternative keys
                    if (empty($keys['telefono']) && empty($keys['campagna'])) {
                        $keys = array_filter([
                            'ragione_sociale' => $row['ragione_sociale'] ?? null,
                            'nome' => $row['nome'] ?? null,
                            'cognome' => $row['cognome'] ?? null,
                            'email' => $row['email'] ?? null,
                        ]);

                        // Skip if we don't have enough data to uniquely identify the lead
                        if (empty($keys)) {
                            $this->warn('Skipping lead - insufficient data for unique identification');
                            continue;
                        }
                    }
                }

                // Prepare values for update/insert
                $values = $row;
                unset($values['created_at']); // Don't update created_at on upsert

                // Use updateOrInsert to handle both new and existing records
                DB::table('leads')->updateOrInsert($keys, $values);
                $affected++;

            } catch (\Exception $e) {
                $this->error('Error processing lead: ' . $e->getMessage());
                // Log the problematic row for debugging
                $this->line('Problematic row: ' . json_encode($row, JSON_PRETTY_PRINT));
            }
        }

        return $affected;
    }
}
