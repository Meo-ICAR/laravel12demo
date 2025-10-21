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

        // Convert relative paths to absolute if needed
        if (!file_exists($file) && !is_file($file)) {
            // Try with storage path if only filename is provided
            $file = storage_path('app/imports/' . basename($file));

            if (!file_exists($file) || !is_file($file)) {
                $this->error("File not found: " . $this->argument('file'));
                $this->error("Tried path: $file");
                return 1;
            }
        }

        $this->info("Processing file: " . $file);
        $this->info("Current database count: " . \App\Models\Invoicein::count());

        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $importedCount = 0;
        $skippedCount = 0;
        $errors = [];
        $rowNumber = 1;

        if (!in_array($extension, ['xls', 'xlsx', 'csv'])) {
            $this->error('Unsupported file format. Please provide an Excel (xls, xlsx) or CSV file.');
            return 1;
        }

        try {
            // Excel import using PhpSpreadsheet
            $spreadsheet = IOFactory::load($file);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            $header = null;
            $headerMap = null;

            foreach ($rows as $rowIdx => $row) {
                if ($rowNumber === 1) {
                    // First row is header
                    $header = array_values($row);
                    $headerMap = $this->getHeaderMap($header);
                    $rowNumber++;
                    continue;
                }

                // Skip empty rows
                if (empty(array_filter($row))) {
                    $rowNumber++;
                    continue;
                }

                try {
                    // Map the row data
                    $data = [];
                    foreach ($header as $index => $headerName) {
                        $data[$headerName] = $row[chr(65 + $index)] ?? ''; // Convert index to column letter (A, B, C, ...)
                    }

                    $mapped = $this->mapRow($data, $headerMap);

                    // Check required fields
                    if (empty($mapped['nr_documento'])) {
                        $this->warn("Skipping row $rowNumber: Empty nr_documento");
                        $skippedCount++;
                        $rowNumber++;
                        continue;
                    }

                    // Check for duplicates
                    $exists = \App\Models\Invoicein::where('nr_documento', $mapped['nr_documento'])->exists();
                    if ($exists) {
                        $this->warn(sprintf(
                            'Skipping existing invoice: %s (Row: %d, Exists in DB: %s)',
                            $mapped['nr_documento'],
                            $rowNumber,
                            'Yes'
                        ));
                        $skippedCount++;
                        $rowNumber++;
                        continue;
                    }

                    // Import the row
                    $result = $this->importRow($mapped);

                    if ($result === false) {
                        $skippedCount++;
                    } else {
                        $importedCount++;
                    }

                } catch (\Throwable $e) {
                    $errors[] = "Row $rowNumber: " . $e->getMessage();
                }

                $rowNumber++;
            }

            // Show import results
            $this->info("\nImport completed.");
            $this->info("Imported: {$importedCount} records");
            $this->info("Skipped: {$skippedCount} records");

            if (!empty($errors)) {
                $this->warn("\nSome rows failed to import:");
                foreach ($errors as $error) {
                    $this->warn($error);
                }
            }

            return 0;

        } catch (\Throwable $e) {
            $this->error('Error processing file: ' . $e->getMessage());
            return 1;
        }
    }

    protected function getHeaderMap($headers)
    {
        // Map Excel column headers to model fields
        $headerMap = [];

        // Create a map of header names to model fields
        $fieldMap = [
            'Tipo di documento' => 'tipo_di_documento',
            'Nr. documento' => 'nr_documento',
            'Nr. Fatt. Acq. Registrata' => 'nr_fatt_acq_registrata',
            'Nr. Nota Cr. Acq. Registrata' => 'nr_nota_cr_acq_registrata',
            'Data Ricezione Fatt.' => 'data_ricezione_fatt',
            'Codice TD' => 'codice_td',
            'Nr. cliente/fornitore' => 'nr_cliente_fornitore',
            'Nome fornitore' => 'nome_fornitore',
            'Partita IVA' => 'partita_iva',
            'Nr. Documento Fornitore' => 'nr_documento_fornitore',
            'Allegato' => 'allegato',
            'Data Documento Fornitore' => 'data_documento_fornitore',
            'Data Primo Pagamento Prev.' => 'data_primo_pagamento_prev',
            'Imponibile IVA' => 'imponibile_iva',
            'Importo IVA' => 'importo_iva',
            'Importo Totale Fornitore' => 'importo_totale_fornitore',
            'Importo Totale Collegato' => 'importo_totale_collegato',
            'Data ora Invio/Ricezione' => 'data_ora_invio_ricezione',
            'Stato' => 'stato',
            'ID documento' => 'id_documento',
            'Id SDI' => 'id_sdi',
            'Nr. Lotto Documento' => 'nr_lotto_documento',
            'Nome File Doc. Elettronico' => 'nome_file_doc_elettronico',
            'Cdc Codice' => 'cdc_codice',
            'Cod. colleg. dimen. 2' => 'cod_colleg_dimen_2',
            'Allegato in File XML' => 'allegato_in_file_xml',
            'Note 1' => 'note1',
            'Note 2' => 'note2'
        ];

        // Create a map of header names to their column indices
        foreach ($headers as $index => $header) {
            $header = trim($header);
            if (isset($fieldMap[$header])) {
                $headerMap[$header] = $fieldMap[$header];
            } else {
                // Log unmapped headers for debugging
                $this->warn("Unmapped header: " . $header);
            }
        }

        return $headerMap;
    }

    /**
     * Format date from Excel serial date or string to Y-m-d format
     *
     * @param mixed $dateValue
     * @return string|null
     */
    protected function formatDate($dateValue)
    {
        if (empty($dateValue)) {
            return null;
        }

        // Handle Excel serial dates (both date and datetime)
        if (is_numeric($dateValue)) {
            try {
                $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateValue);
                return $date->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                $this->warn("Error converting Excel date: " . $e->getMessage());
                return null;
            }
        }

        // Handle string dates in various formats
        $formats = [
            'd/m/Y H:i:s',
            'd/m/Y',
            'Y-m-d H:i:s',
            'Y-m-d',
            'Y/m/d H:i:s',
            'Y/m/d'
        ];

        foreach ($formats as $format) {
            if (($date = \DateTime::createFromFormat($format, $dateValue)) !== false) {
                return $date->format('Y-m-d H:i:s');
            }
        }

        $this->warn("Could not parse date: " . $dateValue);
        return null;
    }

    protected function mapRow($row, $headerMap)
    {
        $mapped = [];

        // First pass: map all fields and clean values
        foreach ($headerMap as $headerName => $modelField) {
            $value = $row[$headerName] ?? '';

            // Handle Excel boolean formulas
            if (is_string($value) && strpos($value, '=TRUE()') !== false) {
                $value = true;
            } elseif (is_string($value) && strpos($value, '=FALSE()') !== false) {
                $value = false;
            }

            $mapped[$modelField] = is_string($value) ? trim($value) : $value;
        }

        // Debug: Log the mapping for the first few rows
        static $rowCount = 0;
        if ($rowCount < 5) {
            $this->info("Raw row data: " . json_encode($row, JSON_PRETTY_PRINT));
            $this->info("Mapped row data: " . json_encode($mapped, JSON_PRETTY_PRINT));
            $rowCount++;
        }

        // Set default values for required fields
        $emptyFields = [
            'nr_fatt_acq_registrata' => '',
            'nr_nota_cr_acq_registrata' => '',
            'codice_td' => $mapped['codice_td'] ?? '',  // From column 'C: Codice TD'
            'partita_iva' => '',  // Not directly in Excel, only have partita_iva_presente
            'nr_documento_fornitore' => $mapped['nr_documento'] ?? '',  // Use nr_documento as fallback
            'allegato' => '',  // Not in Excel
            'data_documento_fornitore' => $mapped['data_ricezione_fatt'] ?? null,  // Use data_ricezione_fatt as fallback
            'data_primo_pagamento_prev' => null,  // Not in Excel
            'imponibile_iva' => 0,  // Not in Excel
            'importo_iva' => 0,     // Not in Excel
            'importo_totale_fornitore' => 0,  // Not in Excel
            'importo_totale_collegato' => 0,  // Not in Excel
            'data_ora_invio_ricezione' => $mapped['data_ora_invio'] ?? null,  // From column 'I: Data ora Invio/Ricezione'
            'id_sdi' => $mapped['id_sdi'] ?? '',  // From column 'R: Id SDI'
            'nr_lotto_documento' => ''  // Not in Excel
        ];

        // Merge with existing mapped data, preserving any existing values
        $mapped = array_merge($emptyFields, $mapped);

        // Clean and format specific fields
        $mapped['codice_td'] = trim($mapped['codice_td'] ?? '');
        $mapped['nr_documento_fornitore'] = trim($mapped['nr_documento_fornitore'] ?? '');
        $mapped['id_sdi'] = trim($mapped['id_sdi'] ?? '');

        // Format numeric fields with proper decimal separators
        $numericFields = [
            'imponibile_iva' => 2,
            'importo_iva' => 2,
            'importo_totale_fornitore' => 2,
            'importo_totale_collegato' => 2,
            'importo_bollo' => 2,
            'nr_lotto_documento' => 0
        ];

        foreach ($numericFields as $field => $decimals) {
            if (isset($mapped[$field]) && $mapped[$field] !== '') {
                // Convert to string if it's a float to handle decimal separators
                $value = (string)$mapped[$field];
                // Remove any thousands separators and convert decimal comma to dot
                $value = str_replace(['.', ','], ['', '.'], $value);
                // Format to specified number of decimal places
                $mapped[$field] = is_numeric($value) ?
                    number_format((float)$value, $decimals, '.', '') :
                    number_format(0, $decimals, '.', '');
            } else {
                $mapped[$field] = number_format(0, $decimals, '.', '');
            }
        }

        // Format date fields
        $dateFields = [
            'data_documento_fornitore',
            'data_primo_pagamento_prev',
            'data_ora_invio_ricezione'
        ];

        foreach ($dateFields as $field) {
            if (!empty($mapped[$field]) || $mapped[$field] === 0 || $mapped[$field] === '0') {
                $mapped[$field] = $this->formatDate($mapped[$field]);
            } else {
                $mapped[$field] = null;
            }
        }

        // Handle boolean fields
        $booleanFields = [
            'allegato',
            'allegato_in_file_xml',
            'pec_presente',
            'partita_iva_presente',
            'cod_fiscale_presente',
            'bollo_virtuale'
        ];

        foreach ($booleanFields as $field) {
            if (isset($mapped[$field])) {
                $value = $mapped[$field];
                if (is_bool($value)) {
                    continue; // Already boolean
                } elseif (is_numeric($value)) {
                    $mapped[$field] = (bool)$value;
                } else {
                    $mapped[$field] = in_array(strtoupper($value), ['TRUE', '1', 'YES', 'Y', 'SI']);
                }
            } else {
                $mapped[$field] = false;
            }
        }

        // Convert date format if needed
        if (!empty($mapped['data_ricezione_fatt'])) {
            $date = \DateTime::createFromFormat('j/n/Y', $mapped['data_ricezione_fatt']);
            if ($date) {
                $mapped['data_ricezione_fatt'] = $date->format('Y-m-d');
            }
        }

        return $mapped;
    }

    protected function importRow($data)
    {
        // Skip if required fields are missing
        if (empty($data['nr_documento']) || empty($data['nr_cliente_fornitore'])) {
            $this->warn('Skipping row: Missing required fields (nr_documento or nr_cliente_fornitore)');
            return false;
        }

        // Check if invoice already exists
        if (Invoicein::where('nr_documento', $data['nr_documento'])->exists()) {
            $this->info("Skipping existing invoice: {$data['nr_documento']}");
            return false;
        }

        try {
            $invoicein = new Invoicein([
                // Required fields
                'nr_documento' => $data['nr_documento'],
                'nr_cliente_fornitore' => $data['nr_cliente_fornitore'],
                'nome_fornitore' => $data['nome_fornitore'] ?? '',
                'data_ricezione_fatt' => $data['data_ricezione_fatt'] ?? null,

                // Financial fields (with defaults)
                'importo_totale_fornitore' => $data['importo_totale_fornitore'] ?? 0,
                'importo_iva' => $data['importo_iva'] ?? 0,
                'importo_totale_collegato' => $data['importo_totale_collegato'] ?? 0,
                'importo_bollo' => $data['importo_bollo'] ?? 0,

                // Additional fields from Excel
                'tipo_di_documento' => $data['tipo_di_documento'] ?? 'Fattura',
                'tipo_fattura' => $data['tipo_fattura'] ?? '',
                'nome_file_doc_elettronico' => $data['nome_file_doc_elettronico'] ?? '',
                'stato' => $data['stato'] ?? '',
                'stato_portale_sdi' => $data['stato_portale_sdi'] ?? '',
                'cod_destinatario' => $data['cod_destinatario'] ?? '',
                'id_documento' => $data['id_documento'] ?? null,
                'cdc_codice' => $data['cdc_codice'] ?? null,
                'cod_colleg_dimen_2' => $data['cod_colleg_dimen_2'] ?? null,
                'note' => trim(($data['note1'] ?? '') . ' ' . ($data['note2'] ?? '')),

                // Boolean flags
                'pec_presente' => $data['pec_presente'] ?? false,
                'partita_iva_presente' => $data['partita_iva_presente'] ?? false,
                'cod_fiscale_presente' => $data['cod_fiscale_presente'] ?? false,
                'bollo_virtuale' => $data['bollo_virtuale'] ?? false,

                // Timestamps
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $invoicein->save();
            $this->info("Imported invoice: {$data['nr_documento']}");
            return true;

        } catch (\Exception $e) {
            $this->error("Error importing invoice {$data['nr_documento']}: " . $e->getMessage());
            return false;
        }
    }
}
