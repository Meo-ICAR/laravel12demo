<?php

namespace App\Imports;

use App\Models\Provvigione;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProvvigioniImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Skip insert if a record with the same legacy_id and descrizione exists
        $legacyId = $row['id'] ?? null;
        $descrizione = $row['descrizione'] ?? null;
        if ($legacyId && $descrizione) {
            $exists = \App\Models\Provvigione::where('legacy_id', $legacyId)
                ->where('descrizione', $descrizione)
                ->exists();
            if ($exists) {
                return null;
            }
        }
        // Exclude if status_pratica is not 'PERFEZIONATA'
        // if (isset($row['status_pratica']) && $row['status_pratica'] !== 'PERFEZIONATA') {
        //     return null;
        // }
        return new Provvigione([
            'legacy_id' => $legacyId,
            'data_inserimento_compenso' => $this->excelDateToDate($row['data_inserimento_compenso'] ?? null),
            'descrizione' => $descrizione,
            'tipo' => $row['tipo'] ?? null,
            'importo' => $this->parseDecimal($row['importo'] ?? null),
            'importo_effettivo' => $this->parseDecimal($row['importo_effettivo'] ?? null),
            'quota' => $row['quota'] ?? null,
            'stato' => $row['stato'] ?? null,
            'denominazione_riferimento' => $row['denominazione_riferimento'] ?? null,
            'entrata_uscita' => $row['entrata_uscita'] ?? null,
            'cognome' => $row['cognome'] ?? null,
            'nome' => $row['nome'] ?? null,
            'segnalatore' => $row['segnalatore'] ?? null,
            'fonte' => $row['fonte'] ?? null,
            'id_pratica' => $row['id_1'] ?? $row['id_pratica'] ?? null,
            'tipo_pratica' => $row['tipo_1'] ?? $row['tipo_pratica'] ?? null,
            'data_inserimento_pratica' => $this->excelDateToDate($row['data_inserimento'] ?? $row['data_inserimento_pratica'] ?? null),
            'data_stipula' => $this->excelDateToDate($row['data_stipula'] ?? null),
            'istituto_finanziario' => $row['istituto_finanziario'] ?? null,
            'prodotto' => $row['prodotto'] ?? null,
            'macrostatus' => $row['macrostatus'] ?? null,
            'status_pratica' => $row['status_pratica'] ?? null,
            'data_status_pratica' => $this->excelDateToDate($row['data_status_pratica'] ?? null),
            'montante' => $this->parseDecimal($row['montante'] ?? null),
            'importo_erogato' => $this->parseDecimal($row['importo_erogato'] ?? null),
        ]);
    }

    private function excelDateToDate($value)
    {
        if (!$value) return null;
        // Try to parse as d/m/Y
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value)) {
            return \Carbon\Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
        }
        // Try to parse as Y-m-d
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }
        return null;
    }

    private function parseDecimal($value)
    {
        if (!$value) return null;
        // Replace comma with dot for decimals
        $value = str_replace('.', '', $value); // remove thousands separator
        $value = str_replace(',', '.', $value);
        return is_numeric($value) ? $value : null;
    }
}
