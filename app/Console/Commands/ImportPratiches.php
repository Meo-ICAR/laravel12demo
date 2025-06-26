<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pratiche;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ImportPratiches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pratiches:import {file : Path to the CSV or Excel file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import pratiches from CSV or Excel file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("File not found: $filePath");
            return 1;
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $this->info("Starting import from: $filePath");

        $imported = 0;
        $skipped = 0;
        $errors = 0;
        $errorRows = [];
        $data = [];
        $headers = [];

        try {
            if (in_array($extension, ['xlsx', 'xls'])) {
                // Excel import
                $excelData = Excel::toArray(new class implements ToArray, WithHeadingRow {
                    public function array(array $array) { return $array; }
                }, $filePath);
                if (!empty($excelData[0])) {
                    $data = $excelData[0];
                    $headers = array_keys($data[0] ?? []);
                    $this->info("Excel Headers: " . implode(', ', $headers));
                } else {
                    $this->error('No data found in Excel file.');
                    return 1;
                }
            } else {
                // Tab-separated CSV import
                $data = array_map(function($line) {
                    return str_getcsv($line, "\t");
                }, file($filePath));
                $headers = array_shift($data); // Remove header row
                $this->info("CSV Headers: " . implode(', ', $headers));
            }

            $progressBar = $this->output->createProgressBar(count($data));
            $progressBar->start();

            foreach ($data as $rowIndex => $row) {
                try {
                    if (in_array($extension, ['xlsx', 'xls'])) {
                        // $row is already an associative array
                        $rowData = $row;
                    } else {
                        if (count($row) < count($headers)) {
                            $this->warn("Row " . ($rowIndex + 2) . ": Incomplete row, skipping");
                            $skipped++;
                            continue;
                        }
                        $rowData = array_combine($headers, $row);
                    }

                    // Check if record already exists
                    $idValue = $rowData['ID'] ?? $rowData['id'] ?? null;
                    if (!$idValue) {
                        $this->warn("Row " . ($rowIndex + 2) . ": Missing ID, skipping");
                        $skipped++;
                        continue;
                    }
                    if (Pratiche::where('pratica_id', $idValue)->exists()) {
                        $skipped++;
                        continue;
                    }

                    // Convert date format from dd/mm/yyyy or Excel serial
                    $dataInserimento = $rowData['Data_inserimento'] ?? $rowData['data_inserimento'] ?? null;
                    if ($dataInserimento) {
                        if (is_numeric($dataInserimento)) {
                            // Excel date serial
                            $excelDate = (float)$dataInserimento;
                            if ($excelDate > 1) {
                                $unixTimestamp = ($excelDate - 25569) * 86400;
                                $dataInserimento = date('Y-m-d', $unixTimestamp);
                            } else {
                                $dataInserimento = null;
                            }
                        } else {
                            $date = \DateTime::createFromFormat('d/m/Y', $dataInserimento);
                            $dataInserimento = $date ? $date->format('Y-m-d') : null;
                        }
                    }

                    $cleanData = [
                        'pratica_id' => trim($idValue),
                        'Data_inserimento' => $dataInserimento,
                        'Descrizione' => trim($rowData['Descrizione'] ?? $rowData['descrizione'] ?? ''),
                        'Cliente' => trim($rowData['Cliente'] ?? $rowData['cliente'] ?? ''),
                        'Agente' => trim($rowData['Agente'] ?? $rowData['agente'] ?? ''),
                        'Segnalatore' => trim($rowData['Segnalatore'] ?? $rowData['segnalatore'] ?? ''),
                        'Fonte' => trim($rowData['Fonte'] ?? $rowData['fonte'] ?? ''),
                        'Tipo' => trim($rowData['Tipo'] ?? $rowData['tipo'] ?? ''),
                        'Istituto_finanziario' => trim($rowData['Istituto finanziario'] ?? $rowData['istituto_finanziario'] ?? $rowData['istituto finanziario'] ?? ''),
                    ];

                    Pratiche::create($cleanData);
                    $imported++;
                } catch (\Exception $e) {
                    $this->error("Row " . ($rowIndex + 2) . ": " . $e->getMessage());
                    $errors++;
                    $errorRows[] = $rowIndex + 2;
                }
                $progressBar->advance();
            }
            $progressBar->finish();
            $this->newLine();
            $this->info("Import completed!");
            $this->info("Imported: $imported records");
            $this->info("Skipped: $skipped records");
            $this->info("Errors: $errors records");
            if ($errors > 0) {
                $this->info("Error rows: " . implode(', ', $errorRows));
            }
            return 0;
        } catch (\Exception $e) {
            $this->error("Import failed: " . $e->getMessage());
            Log::error('Pratiches import error: ' . $e->getMessage());
            return 1;
        }
    }
}
