<?php

namespace App\Imports;

use App\Models\Call;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Carbon\Carbon;

class CallsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, WithCustomCsvSettings
{
    use SkipsErrors;

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ';',
            'enclosure' => '"',
            'escape_character' => '\\',
            'contiguous' => false,
            'input_encoding' => 'UTF-8',
        ];
    }

    public function model(array $row)
    {
        // Parse the date from Italian format (dd/mm/yyyy HH:mm:ss)
        $dataInizio = null;
        $dateValue = $row['Data inizio'] ?? $row['data_inizio'] ?? null;
        if (!empty($dateValue)) {
            try {
                $dataInizio = Carbon::createFromFormat('d/m/Y H:i:s', trim($dateValue));
            } catch (\Exception $e) {
                // Try alternative format if the first one fails
                try {
                    $dataInizio = Carbon::parse(trim($dateValue));
                } catch (\Exception $e2) {
                    // Date parsing failed, keep as null
                }
            }
        }

        // Clean and validate the data
        $numeroChiamato = $row['Numero chiamato'] ?? $row['numero_chiamato'] ?? null;
        $durata = $row['Durata'] ?? $row['durata'] ?? null;
        $statoChiamata = $row['Stato Chiamata'] ?? $row['stato_chiamata'] ?? null;
        $esito = $row['Esito'] ?? $row['esito'] ?? null;
        $utente = $row['Utente'] ?? $row['utente'] ?? null;

        // Ensure all values are strings or null
        $callData = [
            'numero_chiamato' => $numeroChiamato ? (string) trim($numeroChiamato) : null,
            'data_inizio' => $dataInizio,
            'durata' => $durata ? (string) trim($durata) : null,
            'stato_chiamata' => $statoChiamata ? (string) trim($statoChiamata) : null,
            'esito' => $esito ? (string) trim($esito) : null,
            'utente' => $utente ? (string) trim($utente) : null,
            'company_id' => auth()->user()->company_id ?? null,
        ];

        return new Call($callData);
    }

    public function rules(): array
    {
        return [
            'Numero chiamato' => 'nullable',
            'Data inizio' => 'nullable',
            'Durata' => 'nullable',
            'Stato Chiamata' => 'nullable',
            'Esito' => 'nullable',
            'Utente' => 'nullable',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'Numero chiamato.required' => 'Numero chiamato is required.',
            'Data inizio.required' => 'Data inizio is required.',
            'Durata.required' => 'Durata is required.',
            'Stato Chiamata.required' => 'Stato Chiamata is required.',
            'Esito.required' => 'Esito is required.',
            'Utente.required' => 'Utente is required.',
        ];
    }
}
