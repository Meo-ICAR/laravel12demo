<?php

namespace App\Imports;

use App\Models\Fornitori;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Carbon\Carbon;
use Illuminate\Support\Str;

class FornitoriImport implements ToModel, WithValidation, SkipsOnError, WithCustomCsvSettings
{
    use SkipsErrors;

    private $delimiter;
    private static $processedCodici = [];

    public function __construct($delimiter = ',')
    {
        $this->delimiter = $delimiter;
        // Reset processed codici for each new import
        self::$processedCodici = [];
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => $this->delimiter,
            'enclosure' => '"',
            'escape_character' => '\\',
            'contiguous' => false,
            'input_encoding' => 'UTF-8',
        ];
    }

    public function model(array $row)
    {
        // Skip if codice already exists (column 0)
        $codice = $row[0] ?? null;

        if (!empty($codice)) {
            // Check if we've already processed this codice in this import
            if (in_array($codice, self::$processedCodici)) {
                \Log::info("Skipping duplicate codice in import: " . $codice);
                return null; // Skip this record
            }

            // Check if codice exists in database
            if (Fornitori::where('codice', $codice)->exists()) {
                \Log::info("Skipping existing codice in database: " . $codice);
                return null; // Skip this record
            }

            // Add to processed list
            self::$processedCodici[] = $codice;
        }

        // Parse the birth date from Italian format (dd/mm/yyyy) - column 3
        $natoil = null;
        $dateValue = $row[3] ?? null;
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

        // Map columns by position:
        // 0=codice, 1=denominazione, 2=nome, 3=natoil, 4=indirizzo, 5=comune, 6=cap, 7=prov, 8=tel, 9=empty, 10=empty, 11=email, 12=regione, 13=citta, 14=coordinatore
        $fornitoriData = [
            'id' => (string) Str::uuid(),
            'codice' => $row[0] ?? null,
            'name' => trim(($row[1] ?? '') . ' ' . ($row[2] ?? '')),
            'nome' => $row[2] ?? null,
            'natoil' => $natoil,
            'indirizzo' => $row[4] ?? null,
            'comune' => $row[5] ?? null,
            'cap' => $row[6] ?? null,
            'prov' => $row[7] ?? null,
            'tel' => $row[8] ?? null,
            'piva' => null, // Not in the file
            'email' => $row[11] ?? null,
            'anticipo' => null, // Default value
            'contributo' => null, // Default value
            'contributo_description' => 'Contributo spese', // Default value
            'anticipo_description' => 'Anticipo attuale', // Default value
            'issubfornitore' => 0, // Default value
            'operatore' => null,
            'iscollaboratore' => null,
            'isdipendente' => null,
            'regione' => $row[12] ?? null,
            'citta' => $row[13] ?? null,
            'coordinatore' => $row[14] ?? null,
            'company_id' => auth()->user()->company_id ?? null,
        ];

        return new Fornitori($fornitoriData);
    }

    public function rules(): array
    {
        return [
            '0' => 'nullable', // codice
            '1' => 'nullable', // denominazione
            '2' => 'nullable', // nome
            '3' => 'nullable', // natoil
            '4' => 'nullable', // indirizzo
            '5' => 'nullable', // comune
            '6' => 'nullable', // cap
            '7' => 'nullable', // prov
            '8' => 'nullable', // tel
            '11' => 'nullable', // email
            '12' => 'nullable', // regione
            '13' => 'nullable', // citta
            '14' => 'nullable', // coordinatore
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

    public function onError(\Throwable $e)
    {
        \Log::error('Import error: ' . $e->getMessage());
    }

    public function onFailure(\Maatwebsite\Excel\Validators\Failure ...$failures)
    {
        foreach ($failures as $failure) {
            \Log::warning('Import validation failure: ' . $failure->errors()[0] . ' on row ' . $failure->row());
        }
    }
}
