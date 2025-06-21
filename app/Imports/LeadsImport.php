<?php

namespace App\Imports;

use App\Models\Lead;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Carbon\Carbon;

class LeadsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
{
    use SkipsErrors;

    public function model(array $row)
    {
        // Debug: Log the raw row data and headers
        \Log::info('Processing lead row', [
            'raw_row' => $row,
            'keys' => array_keys($row)
        ]);

        // Parse dates from various formats
        $dataRichiamo = $this->parseDate($row['data_richiamo'] ?? null);
        $scadenzaAnagrafica = $this->parseDate($row['scadenza_anagrafica'] ?? null);
        $ultimaChiamata = $this->parseDate($row['ultima_chiamata'] ?? null);
        $dataCreazione = $this->parseDate($row['data_creazione'] ?? null);

        // Convert 'y'/'n' to boolean for attivo field
        $attivo = $this->parseBoolean($row['attivo'] ?? null);

        // Convert numeric fields
        $chiamate = $this->parseInteger($row['chiamate'] ?? 0);
        $chiamateGiornaliere = $this->parseInteger($row['chiamate_giornaliere'] ?? 0);
        $chiamateMensili = $this->parseInteger($row['chiamate_mensili'] ?? 0);

        $leadData = [
            'legacy_id' => $row['id'] ?? null,
            'campagna' => $row['campagna'] ?? null,
            'lista' => $row['lista'] ?? null,
            'ragione_sociale' => $row['ragione_sociale'] ?? null,
            'cognome' => $row['cognome'] ?? null,
            'nome' => $row['nome'] ?? null,
            'telefono' => $row['telefono'] ?? null,
            'ultimo_operatore' => $row['ultimo_operatore'] ?? null,
            'esito' => $row['esito'] ?? null,
            'data_richiamo' => $dataRichiamo,
            'operatore_richiamo' => $row['operatore_richiamo'] ?? null,
            'scadenza_anagrafica' => $scadenzaAnagrafica,
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
            'attivo' => $attivo,
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
            'chiamate' => $chiamate,
            'ultima_chiamata' => $ultimaChiamata,
            'creato_da' => $row['creato_da'] ?? null,
            'durata_ultima_chiamata' => $row['durata_ultima_chiamata'] ?? null,
            'totale_durata_chiamate' => $row['totale_durata_chiamate'] ?? null,
            'chiamate_giornaliere' => $chiamateGiornaliere,
            'chiamate_mensili' => $chiamateMensili,
            'data_creazione' => $dataCreazione,
            'company_id' => auth()->user()->company_id ?? null,
        ];

        // Debug: Log the processed data
        \Log::info('Creating lead with processed data', $leadData);

        return new Lead($leadData);
    }

    private function parseDate($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            // Try different date formats
            $formats = [
                'd/m/Y H:i:s', // 25/09/2024 11:12:32
                'd/m/Y H:i',   // 25/09/2024 11:12
                'd/m/Y',       // 25/09/2024
                'Y-m-d H:i:s', // 2024-09-24 18:01:05
                'Y-m-d',       // 2024-09-24
            ];

            foreach ($formats as $format) {
                try {
                    return Carbon::createFromFormat($format, $value);
                } catch (\Exception $e) {
                    continue;
                }
            }

            // If no format works, try Carbon's parse
            return Carbon::parse($value);
        } catch (\Exception $e) {
            \Log::warning('Could not parse date: ' . $value);
            return null;
        }
    }

    private function parseBoolean($value)
    {
        if (empty($value)) {
            return true; // Default to active
        }

        $value = strtolower(trim($value));
        return in_array($value, ['y', 'yes', 'true', '1', 'si', 'sì']);
    }

    private function parseInteger($value)
    {
        if (empty($value)) {
            return 0;
        }

        return (int) $value;
    }

    public function rules(): array
    {
        return [
            'id' => 'nullable|string|max:20',
            'campagna' => 'nullable|string|max:100',
            'lista' => 'nullable|string|max:100',
            'ragione_sociale' => 'nullable|string|max:255',
            'cognome' => 'nullable|string|max:100',
            'nome' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'ultimo_operatore' => 'nullable|string|max:255',
            'esito' => 'nullable|string|max:100',
            'data_richiamo' => 'nullable|string',
            'operatore_richiamo' => 'nullable|string|max:255',
            'scadenza_anagrafica' => 'nullable|string',
            'indirizzo1' => 'nullable|string|max:255',
            'indirizzo2' => 'nullable|string|max:255',
            'indirizzo3' => 'nullable|string|max:255',
            'comune' => 'nullable|string|max:100',
            'provincia' => 'nullable|string|max:10',
            'cap' => 'nullable|string|max:10',
            'regione' => 'nullable|string|max:100',
            'paese' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'p_iva' => 'nullable|string|max:50',
            'codice_fiscale' => 'nullable|string|max:20',
            'telefono2' => 'nullable|string|max:20',
            'telefono3' => 'nullable|string|max:20',
            'telefono4' => 'nullable|string|max:20',
            'sesso' => 'nullable|string|max:10',
            'nota' => 'nullable|string',
            'attivo' => 'nullable|string',
            'altro1' => 'nullable|string|max:255',
            'altro2' => 'nullable|string|max:255',
            'altro3' => 'nullable|string|max:255',
            'altro4' => 'nullable|string|max:255',
            'altro5' => 'nullable|string|max:255',
            'altro6' => 'nullable|string|max:255',
            'altro7' => 'nullable|string|max:255',
            'altro8' => 'nullable|string|max:255',
            'altro9' => 'nullable|string|max:255',
            'altro10' => 'nullable|string|max:255',
            'chiamate' => 'nullable|string',
            'ultima_chiamata' => 'nullable|string',
            'creato_da' => 'nullable|string|max:255',
            'durata_ultima_chiamata' => 'nullable|string|max:20',
            'totale_durata_chiamate' => 'nullable|string|max:20',
            'chiamate_giornaliere' => 'nullable|string',
            'chiamate_mensili' => 'nullable|string',
            'data_creazione' => 'nullable|string',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'email.email' => 'The email field must be a valid email address.',
            'cognome.max' => 'Cognome must not exceed 100 characters.',
            'nome.max' => 'Nome must not exceed 100 characters.',
            'telefono.max' => 'Telefono must not exceed 20 characters.',
            'comune.max' => 'Comune must not exceed 100 characters.',
            'provincia.max' => 'Provincia must not exceed 10 characters.',
            'cap.max' => 'CAP must not exceed 10 characters.',
        ];
    }
}
