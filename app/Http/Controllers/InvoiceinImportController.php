<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Invoicein;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\Artisan;

class InvoiceinImportController extends Controller
{
    public function index()
    {
        return view('invoiceins.import');
    }

    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:20480',
        ], [
            'file.mimes' => 'The file must be a CSV, XLSX, or XLS file.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $file = $request->file('file');
        $importedCount = 0;
        $skippedCount = 0;
        $errors = [];

        $extension = strtolower($file->getClientOriginalExtension());

        if (in_array($extension, ['xlsx', 'xls'])) {
            // Handle Excel files
            try {
                $data = Excel::toArray(new class implements ToArray, WithHeadingRow {
                    public function array(array $array)
                    {
                        return $array;
                    }
                }, $file);

                if (!empty($data[0])) {
                    // Debug: Log the first row to see headers
                    Log::info('Excel headers:', array_keys($data[0][0] ?? []));

                    // Build header mapping for Excel
                    $headerMap = $this->getInvoiceinHeaderMap(array_keys($data[0][0] ?? []));
                    foreach ($data[0] as $index => $row) {
                        // Skip the first row if it matches the headers (header row)
                        if ($index === 0) {
                            continue;
                        }
                        try {
                            $mappedRow = $this->mapInvoiceinRow($row, $headerMap);
                            if (Invoicein::where('nome_file_doc_elettronico', $mappedRow['nome_file_doc_elettronico'] ?? null)->exists()) {
                                $skippedCount++;
                                continue;
                            }
                            $this->importRow($mappedRow);
                            $importedCount++;
                        } catch (\Exception $e) {
                            $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                        }
                    }
                }
            } catch (\Exception $e) {
                return back()->with('error', 'Error processing Excel file: ' . $e->getMessage());
            }
        } else {
            // Handle CSV files
            if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
                $header = null;
                $rowIndex = 0;
                $headerMap = null;
                while (($row = fgetcsv($handle, 10000, ';')) !== false) {
                    if (!$header) {
                        $header = $row;
                        $headerMap = $this->getInvoiceinHeaderMap($header);
                        Log::info('CSV headers:', $header);
                        $rowIndex++;
                        continue;
                    }
                    $data = array_combine($header, $row);
                    try {
                        $mappedRow = $this->mapInvoiceinRow($data, $headerMap);
                        if (Invoicein::where('nr_documento', $mappedRow['nr_documento'] ?? null)->exists()) {
                            $skippedCount++;
                            $rowIndex++;
                            continue;
                        }
                        $this->importRow($mappedRow);
                        $importedCount++;
                    } catch (\Exception $e) {
                        $errors[] = "Row " . ($rowIndex + 1) . ": " . $e->getMessage();
                    }
                    $rowIndex++;
                }
                fclose($handle);
            }
        }

        // Step 2: Import to invoices table
        $invoiceService = new InvoiceService();
        $result = $invoiceService->transferInvoiceinsToInvoices();
        $invoicesImported = $result['imported'];
        $invoicesSkipped = $result['skipped'];
        $errors = array_merge($errors, $result['errors']);

        // Prepare success message
        $message = "Successfully imported {$importedCount} invoiceins.";
        if ($skippedCount > 0) {
            $message .= " Skipped {$skippedCount} duplicate invoiceins (nr_documento already exists).";
        }
        if ($invoicesImported > 0) {
            $message .= " Transferred {$invoicesImported} to invoices table.";
        }
        if ($invoicesSkipped > 0) {
            $message .= " Skipped {$invoicesSkipped} duplicate invoices.";
        }
        if (!empty($errors)) {
            $message .= " However, there were some errors: " . implode(', ', $errors);
        }

        if ($importedCount > 0) {
            return redirect()->route('invoices.index')->with('success', $message);
        }

        return back()->with('error', 'No valid invoiceins could be imported. Errors: ' . implode(', ', $errors));
    }

    public function importCustom(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,xls,xlsx|max:20480',
        ], [
            'file.mimes' => 'The file must be a CSV, XLSX, or XLS file.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $file = $request->file('file');
        $path = $file->storeAs('imports', uniqid('invoiceins_') . '.' . $file->getClientOriginalExtension());
        $fullPath = storage_path('app/' . $path);

        // Call the custom import command
        $output = null;
        $exitCode = Artisan::call('invoiceins:import-custom', [
            'file' => $fullPath
        ]);
        $output = Artisan::output();

        if ($exitCode === 0) {
            return redirect()->route('invoiceins.index')->with('success', 'Import completed.<br><pre>' . e($output) . '</pre>');
        } else {
            return redirect()->route('invoiceins.index')->with('error', 'Import failed.<br><pre>' . e($output) . '</pre>');
        }
    }

    protected function importRow($data)
    {
        // Debug: Log the data being processed
        Log::info('Processing row data:', $data);

        // Check if invoicein with same nr_documento already exists
        if (Invoicein::where('nr_documento', $data['nr_documento'] ?? null)->exists()) {
            Log::info('Skipping duplicate nr_documento: ' . ($data['nr_documento'] ?? 'null'));
            return; // Skip this row
        }

        // Helper to convert Excel date serial numbers to dates
        $convertExcelDate = function ($value) {
            if (!$value || !is_numeric($value)) return null;
            // Excel dates are serial numbers starting from 1900-01-01
            $excelDate = (float)$value;
            if ($excelDate > 1) {
                $unixTimestamp = ($excelDate - 25569) * 86400; // Convert to Unix timestamp
                return date('Y-m-d', $unixTimestamp);
            }
            return null;
        };

        // Helper to convert date formats
        $convertDate = function ($value, $withTime = false) {
            if (!$value) return null;
            // Try to match dd/mm/yyyy or dd/mm/yy
            if (preg_match('/^(\d{2})\/(\d{2})\/(\d{2,4})(?: (\d{2}):(\d{2}):(\d{2}))?$/', trim($value), $matches)) {
                $day = $matches[1];
                $month = $matches[2];
                $year = $matches[3];
                if (strlen($year) == 2) {
                    $year = ($year > 50 ? '19' : '20') . $year;
                }
                if (isset($matches[4])) {
                    // Has time
                    return "$year-$month-$day {$matches[4]}:{$matches[5]}:{$matches[6]}";
                }
                return "$year-$month-$day";
            }
            // Try to match yyyy-mm-dd or yyyy-mm-dd HH:MM:SS
            if (preg_match('/^\d{4}-\d{2}-\d{2}(?: \d{2}:\d{2}:\d{2})?$/', trim($value))) {
                return trim($value);
            }
            return $value;
        };

        // Helper to parse decimal fields (handle European format #.###,##)
        $parseDecimal = function ($value) {
            if ($value === null || $value === '') return null;

            // Convert to string and trim
            $value = trim((string)$value);

            // Handle European format: #.###,## (e.g., 1.139,17)
            // First, check if it's a European format (has comma as decimal separator)
            if (strpos($value, ',') !== false) {
                // Remove thousands separator (dots) and convert comma to dot
                $value = str_replace('.', '', $value); // Remove thousands separator
                $value = str_replace(',', '.', $value); // Convert decimal separator
            } else {
                // Handle standard format or already converted format
                // Remove any existing thousands separators
                $value = str_replace(',', '', $value);
            }

            // Validate it's a number
            if (is_numeric($value)) {
                return (float)$value;
            }

            Log::warning('Failed to parse decimal value:', ['original' => $value]);
            return null;
        };

        // Helper to clean boolean values from Excel
        $cleanBoolean = function ($value) {
            if ($value === '=TRUE()' || $value === true || $value === 'TRUE') return true;
            if ($value === '=FALSE()' || $value === false || $value === 'FALSE') return false;
            return null;
        };

        $imponibile_iva = $parseDecimal($data['imponibile_iva'] ?? null);
        $importo_iva = $parseDecimal($data['importo_iva'] ?? null);
        $importo_totale_fornitore = $parseDecimal($data['importo_totale_fornitore'] ?? null);
        $importo_totale_collegato = $parseDecimal($data['importo_totale_collegato'] ?? null);

        $invoicein = new Invoicein([
            'tipo_di_documento' => $data['tipo_di_documento'] ?? null,
            'nr_documento' => $data['nr_documento'] ?? null,
            'nr_fatt_acq_registrata' => $data['nr_fatt_acq_registrata'] ?? null,
            'nr_nota_cr_acq_registrata' => $data['nr_nota_cr_acq_registrata'] ?? null,
            'data_ricezione_fatt' => $convertExcelDate($data['data_ricezione_fatt'] ?? null),
            'codice_td' => $data['codice_td'] ?? null,
            'nr_cliente_fornitore' => $data['nr_cliente_fornitore'] ?? null,
            'nome_fornitore' => $data['nome_fornitore'] ?? null,
            'partita_iva' => $data['partita_iva'] ?? null,
            'nr_documento_fornitore' => $data['nr_documento_fornitore'] ?? null,
            'allegato' => $cleanBoolean($data['allegato'] ?? null),
            'data_documento_fornitore' => $convertExcelDate($data['data_documento_fornitore'] ?? null),
            'data_primo_pagamento_prev' => $convertExcelDate($data['data_primo_pagamento_prev'] ?? null),
            'imponibile_iva' => $imponibile_iva,
            'importo_iva' => $importo_iva,
            'importo_totale_fornitore' => $importo_totale_fornitore,
            'importo_totale_collegato' => $importo_totale_collegato,
            'data_ora_invio_ricezione' => $convertExcelDate($data['data_ora_invioricezione'] ?? null), // Fixed column name
            'stato' => $data['stato'] ?? null,
            'id_documento' => $data['id_documento'] ?? null,
            'id_sdi' => $data['id_sdi'] ?? null,
            'nr_lotto_documento' => $data['nr_lotto_documento'] ?? null,
            'nome_file_doc_elettronico' => $data['nome_file_doc_elettronico'] ?? null,
            'filtro_carichi' => $data['filtro_carichi'] ?? null,
            'cdc_codice' => $data['cdc_codice'] ?? null,
            'cod_colleg_dimen_2' => $data['cod_colleg_dimen_2'] ?? null,
            'allegato_in_file_xml' => $cleanBoolean($data['allegato_in_file_xml'] ?? null),
            'note_1' => $data['note_1'] ?? null,
            'note_2' => $data['note_2'] ?? null,
        ]);

        Log::info('Saving invoicein:', $invoicein->toArray());
        $invoicein->save();
    }

    /**
     * Returns a mapping from possible CSV headers to model fields.
     */
    protected function getInvoiceinHeaderMap($headers)
    {
        // Lowercase and trim all headers for matching
        $normalized = array_map(fn($h) => strtolower(trim($h)), $headers);
        $map = [];
        $fieldMap = [
            // Standard fields
            'tipo di documento' => 'tipo_di_documento',
            'nr. documento' => 'nr_documento',
            'codice td' => 'codice_td',
            'data ricezione fatt.' => 'data_ricezione_fatt',
            'nr. cliente/fornitore' => 'nr_cliente_fornitore',
            'nome fornitore' => 'nome_fornitore',
            'partita iva' => 'partita_iva',
            'nome file doc. elettronico' => 'nome_file_doc_elettronico',
            'data ora invio/ricezione' => 'data_ora_invio_ricezione',
            'stato' => 'stato',
            'id documento' => 'id_documento',
            'id sdi' => 'id_sdi',
            'cdc codice' => 'cdc_codice',
            'cod. colleg. dimen. 2' => 'cod_colleg_dimen_2',
            'note 1' => 'note_1',
            'note 2' => 'note_2',
            // IFE alternates
            'nome cliente' => 'nome_fornitore',
            'data registrazione documento' => 'data_ricezione_fatt',
            'partita iva presente' => 'partita_iva',
        ];
        foreach ($headers as $i => $header) {
            $key = strtolower(trim($header));
            if (isset($fieldMap[$key])) {
                $map[$header] = $fieldMap[$key];
            }
        }
        return $map;
    }

    /**
     * Maps a row using the header map to the expected model fields.
     */
    protected function mapInvoiceinRow($row, $headerMap)
    {
        $mapped = [];
        foreach ($headerMap as $csvField => $modelField) {
            $mapped[$modelField] = $row[$csvField] ?? null;
        }
        return $mapped;
    }
}
