<?php

namespace App\Console\Commands;

use App\Models\Lead;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

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
            $fallback = Config::get('sidial.last_activation', '01/01/2024');
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
            'fromCreatedWhen' => $fromCreated,
            'toCreatedWhen' => $toCreated,
            'fromRecallWhen' => '',
            'toRecallWhen' => '',
            'totAttemptsFrom' => '',
            'totAttemptsTo' => '',
        ];

        // Append advancedCampaign as array parameter (Laravel serializes as advancedCampaign[0]=...)
        if (!empty($campaigns)) {
            $query['advancedCampaign'] = array_values($campaigns);
        }

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, */*'
            ])->withOptions(['http_errors' => false])
              ->retry(3, 500)
              ->get($baseUrl, $query);
        } catch (\Throwable $e) {
            $this->error('Errore chiamando SIDIAL: '.$e->getMessage());
            return self::FAILURE;
        }

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

        // Parse spreadsheet
        try {
            $spreadsheet = IOFactory::load($tmpFile);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true); // indexed by column letters
        } catch (\Throwable $e) {
            $this->error('Errore parsing XLS: '.$e->getMessage());
            @unlink($tmpFile);
            return self::FAILURE;
        }

        @unlink($tmpFile);

        if (count($rows) <= 1) {
            $this->info('Nessuna riga da importare.');
            return self::SUCCESS;
        }

        // First row is header
        $headerRow = array_shift($rows);
        $headers = [];
        foreach ($headerRow as $colIdx => $value) {
            $label = trim((string)$value);
            $headers[$colIdx] = $this->normalizeHeader($label);
        }

        $batch = [];
        $imported = 0;
        foreach ($rows as $rIndex => $row) {
            $assoc = [];
            foreach ($row as $colIdx => $val) {
                if (!isset($headers[$colIdx]) || $headers[$colIdx] === '') continue;
                $assoc[$headers[$colIdx]] = is_string($val) ? trim($val) : $val;
            }

            $mapped = $this->mapLeadRow($assoc);
            if (!$mapped) continue;

            $mapped['updated_at'] = now();
            $mapped['created_at'] = $mapped['created_at'] ?? now();

            $batch[] = $mapped;
            if (count($batch) >= 1000) {
                $imported += $this->flushLeads($batch, (bool)$this->option('dry-run'));
                $batch = [];
            }
        }

        $imported += $this->flushLeads($batch, (bool)$this->option('dry-run'));

        $this->info("Importazione completata. Righe inserite/aggiornate: $imported");
        return self::SUCCESS;
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

    private function mapLeadRow(array $row): ?array
    {
        // Map incoming associative row to Lead columns
        $data = [
            'legacy_id' => $row['id'] ?? null,
            'campagna' => $row['campagna'] ?? null,
            'lista' => $row['lista'] ?? null,
            'ragione_sociale' => $row['ragione_sociale'] ?? null,
            'cognome' => $row['cognome'] ?? null,
            'nome' => $row['nome'] ?? null,
            'telefono' => $row['telefono'] ?? null,
            'ultimo_operatore' => $row['ultimo_operatore'] ?? null,
            'esito' => $row['esito'] ?? null,
            'data_richiamo' => $this->parseDate($row['data_richiamo'] ?? null),
            'operatore_richiamo' => $row['operatore_richiamo'] ?? null,
            'scadenza_anagrafica' => $this->parseDate($row['scadenza_anagrafica'] ?? null),
            'indirizzo1' => $row['indirizzo1'] ?? null,
            'indirizzo2' => $row['indirizzo2'] ?? null,
            'indirizzo3' => $row['indirizzo3'] ?? null,
            'comune' => $row['comune'] ?? null,
            'provincia' => $row['provincia'] ?? null,
            'cap' => $row['cap'] ?? null,
            'regione' => $row['regione'] ?? null,
            'paese' => $row['paese'] ?? null,
            'email' => $row['email'] ?? null,
            'p_iva' => $row['p_iva'] ?? null,
            'codice_fiscale' => $row['codice_fiscale'] ?? null,
            'telefono2' => $row['telefono2'] ?? null,
            'telefono3' => $row['telefono3'] ?? null,
            'telefono4' => $row['telefono4'] ?? null,
            'sesso' => $row['sesso'] ?? null,
            'nota' => $row['nota'] ?? null,
            'attivo' => $this->parseBoolean($row['attivo'] ?? null),
            'altro1' => $row['altro1'] ?? null,
            'altro2' => $row['altro2'] ?? null,
            'altro3' => $row['altro3'] ?? null,
            'altro4' => $row['altro4'] ?? null,
            'altro5' => $row['altro5'] ?? null,
            'altro6' => $row['altro6'] ?? null,
            'altro7' => $row['altro7'] ?? null,
            'altro8' => $row['altro8'] ?? null,
            'altro9' => $row['altro9'] ?? null,
            'altro10' => $row['altro10'] ?? null,
            'chiamate' => $this->parseInt($row['chiamate'] ?? 0),
            'ultima_chiamata' => $this->parseDate($row['ultima_chiamata'] ?? null),
            'creato_da' => $row['creato_da'] ?? null,
            'durata_ultima_chiamata' => $row['durata_ultima_chiamata'] ?? null,
            'totale_durata_chiamate' => $row['totale_durata_chiamate'] ?? null,
            'chiamate_giornaliere' => $this->parseInt($row['chiamate_giornaliere'] ?? 0),
            'chiamate_mensili' => $this->parseInt($row['chiamate_mensili'] ?? 0),
            'data_creazione' => $this->parseDate($row['data_creazione'] ?? null),
            // company_id intentionally left null in console context
        ];

        // Basic sanity: require at least telefono or ragione_sociale or nome/cognome
        if (empty($data['telefono']) && empty($data['ragione_sociale']) && empty($data['nome']) && empty($data['cognome'])) {
            return null;
        }

        return $data;
    }

    private function flushLeads(array $batch, bool $dryRun): int
    {
        if (empty($batch)) return 0;
        if ($dryRun) {
            $this->line('[DRY-RUN] Upsert di '.count($batch).' lead.');
            return 0;
        }

        // Determine unique keys: prefer legacy_id when present, otherwise telefono+campagna
        $withId = array_filter($batch, fn($r) => !empty($r['legacy_id']));
        $withoutId = array_filter($batch, fn($r) => empty($r['legacy_id']));
        $affected = 0;

        if (!empty($withId)) {
            DB::table('leads')->upsert(
                array_values($withId),
                ['legacy_id'],
                array_diff(array_keys($withId[array_key_first($withId)]), ['legacy_id', 'created_at'])
            );
            $affected += count($withId);
        }

        foreach ($withoutId as $row) {
            $keys = [
                'telefono' => $row['telefono'] ?? null,
                'campagna' => $row['campagna'] ?? null,
            ];
            // if both keys null, fallback to ragione_sociale + nome + cognome + email
            if (empty($keys['telefono']) && empty($keys['campagna'])) {
                $keys = [
                    'ragione_sociale' => $row['ragione_sociale'] ?? null,
                    'nome' => $row['nome'] ?? null,
                    'cognome' => $row['cognome'] ?? null,
                    'email' => $row['email'] ?? null,
                ];
            }
            $values = $row;
            unset($values['created_at']);
            DB::table('leads')->updateOrInsert($keys, $values);
            $affected++;
        }

        return $affected;
    }
}
