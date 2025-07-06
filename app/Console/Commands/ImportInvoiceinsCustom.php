<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoicein;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportInvoiceinsCustom extends Command
{
    protected $signature = 'invoiceins:import-custom {file}';
    protected $description = 'Import invoiceins from a custom CSV file (user format)';

    public function handle()
    {
        $file = $this->argument('file');
        if (!file_exists($file)) {
            $this->error("File not found: $file");
            return 1;
        }

        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $importedCount = 0;
        $skippedCount = 0;
        $errors = [];
        $rowNumber = 1;
        if (in_array($extension, ['xls', 'xlsx'])) {
            // Excel import using PhpSpreadsheet
            try {
                $spreadsheet = IOFactory::load($file);
                $sheet = $spreadsheet->getActiveSheet();
                $rows = $sheet->toArray(null, true, true, true);
                $header = null;
                $headerMap = null;
                foreach ($rows as $rowIdx => $row) {
                    if (!$header) {
                        $header = array_values($row);
                        $headerMap = $this->getHeaderMap($header);
                        $rowNumber++;
                        continue;
                    }
                    $data = array_combine($header, array_values($row));
                    try {
                        $mapped = $this->mapRow($data, $headerMap);
                        if (empty($mapped['nr_documento']) || \App\Models\Invoicein::where('nr_documento', $mapped['nr_documento'])->exists()) {
                            $skippedCount++;
                            $rowNumber++;
                            continue;
                        }
                        $this->importRow($mapped);
                        $importedCount++;
                    } catch (\Throwable $e) {
                        $errors[] = "Row $rowNumber: " . $e->getMessage();
                    }
                    $rowNumber++;
                }
            } catch (\Throwable $e) {
                $this->error('Error reading Excel file: ' . $e->getMessage());
                return 1;
            }
        } else {
            // CSV import
            $delimiter = ';';
            if (($handle = fopen($file, 'r')) !== false) {
                $header = null;
                $headerMap = null;
                while (($row = fgetcsv($handle, 10000, $delimiter)) !== false) {
                    if (!$header) {
                        $header = $row;
                        $headerMap = $this->getHeaderMap($header);
                        $rowNumber++;
                        continue;
                    }
                    $data = array_combine($header, $row);
                    try {
                        $mapped = $this->mapRow($data, $headerMap);
                        if (empty($mapped['nr_documento']) || \App\Models\Invoicein::where('nr_documento', $mapped['nr_documento'])->exists()) {
                            $skippedCount++;
                            $rowNumber++;
                            continue;
                        }
                        $this->importRow($mapped);
                        $importedCount++;
                    } catch (\Throwable $e) {
                        $errors[] = "Row $rowNumber: " . $e->getMessage();
                    }
                    $rowNumber++;
                }
                fclose($handle);
            }
        }

        $this->info("Imported $importedCount invoiceins.");
        if ($skippedCount > 0) {
            $this->info("Skipped $skippedCount records (nr_documento already exists or empty).");
        }
        if (!empty($errors)) {
            $this->warn("Some rows failed to import:");
            foreach ($errors as $error) {
                $this->warn($error);
            }
        }
        return 0;
    }

    protected function getHeaderMap($headers)
    {
        // Map CSV headers to model fields
        $map = [];
        $fieldMap = [
            'Nr.' => 'nr_documento',
            'Nr. cliente' => 'nr_cliente_fornitore',
            'Ragione Sociale' => 'nome_fornitore',
            'Partita IVA' => 'partita_iva',
            'Data di registrazione' => 'data_ricezione_fatt',
            'Importo' => 'importo_totale_fornitore',
            'Importo IVA inclusa' => 'importo_iva',
            'Importo residuo' => 'importo_totale_collegato',
            'Cdc Codice' => 'cdc_codice',
            'Cod. colleg. dimen. 2' => 'cod_colleg_dimen_2',
            'Tipo di documento Fattura' => 'tipo_di_documento',
            // Add more mappings as needed
        ];
        foreach ($headers as $header) {
            $key = trim($header);
            if (isset($fieldMap[$key])) {
                $map[$header] = $fieldMap[$key];
            }
        }
        return $map;
    }

    protected function mapRow($row, $headerMap)
    {
        $mapped = [];
        foreach ($headerMap as $csvField => $modelField) {
            $mapped[$modelField] = $row[$csvField] ?? null;
        }
        return $mapped;
    }

    protected function importRow($data)
    {
        $convertDate = function ($value) {
            if (!$value) return null;
            $value = trim($value);
            if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $value, $m)) {
                return "$m[3]-$m[2]-$m[1]";
            }
            return $value;
        };
        $parseDecimal = function ($value) {
            if ($value === null || $value === '') return null;
            $value = str_replace('.', '', $value); // Remove thousands
            $value = str_replace(',', '.', $value); // Convert decimal
            return is_numeric($value) ? (float)$value : null;
        };
        $invoicein = new Invoicein([
            'nr_documento' => $data['nr_documento'] ?? null,
            'nr_cliente_fornitore' => $data['nr_cliente_fornitore'] ?? null,
            'nome_fornitore' => $data['nome_fornitore'] ?? null,
            'partita_iva' => $data['partita_iva'] ?? null,
            'data_ricezione_fatt' => $convertDate($data['data_ricezione_fatt'] ?? null),
            'importo_totale_fornitore' => $parseDecimal($data['importo_totale_fornitore'] ?? null),
            'importo_iva' => $parseDecimal($data['importo_iva'] ?? null),
            'importo_totale_collegato' => $parseDecimal($data['importo_totale_collegato'] ?? null),
            'cdc_codice' => $data['cdc_codice'] ?? null,
            'cod_colleg_dimen_2' => $data['cod_colleg_dimen_2'] ?? null,
            'tipo_di_documento' => $data['tipo_di_documento'] ?? null,
            // Add more fields as needed
        ]);
        $invoicein->save();
    }
}
