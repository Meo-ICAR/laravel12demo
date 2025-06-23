<?php

namespace App\Imports;

use App\Models\Fornitori;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Carbon\Carbon;
use Illuminate\Support\Str;

class FornitoriImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, WithCustomCsvSettings
{
    use SkipsErrors;

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ',',
            'enclosure' => '"',
            'escape_character' => '\\',
            'contiguous' => false,
            'input_encoding' => 'UTF-8',
        ];
    }

    public function model(array $row)
    {
        // Parse the birth date from Italian format (dd/mm/yyyy)
        $natoil = null;
        $dateValue = $row['natoil'] ?? null;
        if (!empty($dateValue)) {
            try {
                $natoil = Carbon::createFromFormat('d/m/Y', trim($dateValue));
            } catch (\Exception $e) {
                // Try alternative format if the first one fails
                try {
                    $natoil = Carbon::parse(trim($dateValue));
                } catch (\Exception $e2) {
                    // Date parsing failed, keep as null
                }
            }
        }

        // Clean and validate the data
        $fornitoriData = [
            'id' => (string) Str::uuid(),
            'codice' => $row['codice'] ?? null,
            'name' => $row['denominazione'] ?? null,
            'nome' => $row['nome'] ?? null,
            'natoil' => $natoil,
            'indirizzo' => $row['indirizzo'] ?? null,
            'comune' => $row['comune'] ?? null,
            'cap' => $row['cap'] ?? null,
            'prov' => $row['prov'] ?? null,
            'tel' => $row['tel'] ?? null,
            'piva' => $row['piva'] ?? null,
            'email' => $row['email'] ?? null,
            'anticipo' => null, // Default value
            'issubfornitore' => 0, // Default value
            'operatore' => null,
            'iscollaboratore' => null,
            'isdipendente' => null,
            'regione' => $row['regione'] ?? null,
            'citta' => $row['comune'] ?? null, // Use comune as citta
            'coordinatore' => $row['coordinatore'] ?? null,
            'company_id' => auth()->user()->company_id ?? null,
        ];

        return new Fornitori($fornitoriData);
    }

    public function rules(): array
    {
        return [
            'codice' => 'nullable',
            'denominazione' => 'nullable',
            'nome' => 'nullable',
            'natoil' => 'nullable',
            'indirizzo' => 'nullable',
            'comune' => 'nullable',
            'cap' => 'nullable',
            'prov' => 'nullable',
            'tel' => 'nullable',
            'piva' => 'nullable',
            'email' => 'nullable',
            'regione' => 'nullable',
            'coordinatore' => 'nullable',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'email.email' => 'The email field must be a valid email address.',
            'codice.max' => 'Codice must not exceed 50 characters.',
            'denominazione.max' => 'Denominazione must not exceed 255 characters.',
            'nome.max' => 'Nome must not exceed 255 characters.',
            'indirizzo.max' => 'Indirizzo must not exceed 255 characters.',
            'comune.max' => 'Comune must not exceed 100 characters.',
            'cap.max' => 'CAP must not exceed 10 characters.',
            'prov.max' => 'Provincia must not exceed 10 characters.',
            'tel.max' => 'Telefono must not exceed 20 characters.',
            'piva.max' => 'P.IVA must not exceed 16 characters.',
            'regione.max' => 'Regione must not exceed 100 characters.',
            'coordinatore.max' => 'Coordinatore must not exceed 255 characters.',
        ];
    }
}
