<?php

namespace App\Imports;

use App\Models\Call;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Carbon\Carbon;

class CallsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
{
    use SkipsErrors;

    public function model(array $row)
    {
        // Parse the date from Italian format (dd/mm/yyyy HH:mm:ss)
        $dataInizio = null;
        if (!empty($row['data_inizio'])) {
            try {
                $dataInizio = Carbon::createFromFormat('d/m/Y H:i:s', $row['data_inizio']);
            } catch (\Exception $e) {
                // Try alternative format if the first one fails
                try {
                    $dataInizio = Carbon::parse($row['data_inizio']);
                } catch (\Exception $e2) {
                    \Log::warning('Could not parse date: ' . $row['data_inizio']);
                }
            }
        }

        return new Call([
            'numero_chiamato' => $row['numero_chiamato'] ?? null,
            'data_inizio' => $dataInizio,
            'durata' => $row['durata'] ?? null,
            'stato_chiamata' => $row['stato_chiamata'] ?? null,
            'esito' => $row['esito'] ?? null,
            'utente' => $row['utente'] ?? null,
            'company_id' => auth()->user()->company_id ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'numero_chiamato' => 'nullable|string|max:20',
            'data_inizio' => 'nullable|string',
            'durata' => 'nullable|string|max:10',
            'stato_chiamata' => 'nullable|string|max:50',
            'esito' => 'nullable|string|max:100',
            'utente' => 'nullable|string|max:255',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'numero_chiamato.max' => 'Numero chiamato must not exceed 20 characters.',
            'durata.max' => 'Durata must not exceed 10 characters.',
            'stato_chiamata.max' => 'Stato chiamata must not exceed 50 characters.',
            'esito.max' => 'Esito must not exceed 100 characters.',
            'utente.max' => 'Utente must not exceed 255 characters.',
        ];
    }
}
