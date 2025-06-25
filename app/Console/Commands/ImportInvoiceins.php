<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoicein;

class ImportInvoiceins extends Command
{
    protected $signature = 'invoiceins:import {file=fatturein.csv}';
    protected $description = 'Import invoiceins from a CSV file';

    public function handle()
    {
        $file = $this->argument('file');
        if ($file === 'update-fornitori-piva') {
            return $this->updateFornitoriPiva();
        }
        if ($file === 'import-to-invoices') {
            return $this->importToInvoices();
        }
        if (!file_exists($file)) {
            $this->error("File not found: $file");
            return 1;
        }

        // Auto-detect delimiter: tab for .tsv, semicolon for .csv, fallback to comma
        $delimiter = ',';
        if (preg_match('/\\.tsv$/i', $file)) {
            $delimiter = "\t";
        } elseif (preg_match('/\\.csv$/i', $file)) {
            $delimiter = ';';
        }

        $importedCount = 0;
        $errors = [];
        $rowNumber = 1;
        if (($handle = fopen($file, 'r')) !== false) {
            $header = null;
            while (($row = fgetcsv($handle, 10000, $delimiter)) !== false) {
                if (!$header) {
                    $header = $row;
                    $rowNumber++;
                    continue;
                }
                $data = array_combine($header, $row);
                try {
                    $this->importRow($data);
                    $importedCount++;
                } catch (\Throwable $e) {
                    $errors[] = "Row $rowNumber: " . $e->getMessage();
                }
                $rowNumber++;
            }
            fclose($handle);
        }

        $this->info("Imported $importedCount invoiceins.");
        if (!empty($errors)) {
            $this->warn("Some rows failed to import:");
            foreach ($errors as $error) {
                $this->warn($error);
            }
        }
        return 0;
    }

    protected function importRow($data)
    {
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
            return $value; // fallback
        };

        // Helper to parse decimal fields (replace comma with dot, remove thousands separator)
        $parseDecimal = function ($value) {
            if ($value === null) return null;
            $value = str_replace(['.', ','], ['', '.'], $value); // Remove thousands, convert decimal
            if (is_numeric($value)) {
                return $value;
            }
            return null;
        };

        $imponibile_iva = $parseDecimal($data['Imponibile IVA'] ?? null);
        $importo_iva = $parseDecimal($data['Importo IVA'] ?? null);
        $importo_totale_fornitore = $parseDecimal($data['Importo Totale Fornitore'] ?? null);
        $importo_totale_collegato = $parseDecimal($data['Importo Totale Collegato'] ?? null);

        $invoicein = new Invoicein([
            'tipo_di_documento' => $data['Tipo di documento'] ?? null,
            'nr_documento' => $data['Nr. documento'] ?? null,
            'nr_fatt_acq_registrata' => $data['Nr. Fatt. Acq. Registrata'] ?? null,
            'nr_nota_cr_acq_registrata' => $data['Nr. Nota Cr. Acq. Registrata'] ?? null,
            'data_ricezione_fatt' => $convertDate($data['Data Ricezione Fatt.'] ?? null),
            'codice_td' => $data['Codice TD'] ?? null,
            'nr_cliente_fornitore' => $data['Nr. cliente/fornitore'] ?? null,
            'nome_fornitore' => $data['Nome fornitore'] ?? null,
            'partita_iva' => $data['Partita IVA'] ?? null,
            'nr_documento_fornitore' => $data['Nr. Documento Fornitore'] ?? null,
            'allegato' => $data['Allegato'] ?? null,
            'data_documento_fornitore' => $convertDate($data['Data Documento Fornitore'] ?? null),
            'data_primo_pagamento_prev' => $convertDate($data['Data Primo Pagamento Prev.'] ?? null),
            'imponibile_iva' => $imponibile_iva,
            'importo_iva' => $importo_iva,
            'importo_totale_fornitore' => $importo_totale_fornitore,
            'importo_totale_collegato' => $importo_totale_collegato,
            'data_ora_invio_ricezione' => $convertDate($data['Data ora Invio/Ricezione'] ?? null, true),
            'stato' => $data['Stato'] ?? null,
            'id_documento' => $data['ID documento'] ?? null,
            'id_sdi' => $data['Id SDI'] ?? null,
            'nr_lotto_documento' => $data['Nr. Lotto Documento'] ?? null,
            'nome_file_doc_elettronico' => $data['Nome File Doc. Elettronico'] ?? null,
            'filtro_carichi' => $data['Filtro Carichi'] ?? null,
            'cdc_codice' => $data['Cdc Codice'] ?? null,
            'cod_colleg_dimen_2' => $data['Cod. colleg. dimen. 2'] ?? null,
            'allegato_in_file_xml' => $data['Allegato in File XML'] ?? null,
            'note_1' => $data['Note 1'] ?? null,
            'note_2' => $data['Note 2'] ?? null,
        ]);
        $invoicein->save();
    }

    protected function updateFornitoriPiva()
    {
        $updated = 0;
        $fornitori = \App\Models\Fornitori::all();
        foreach ($fornitori as $fornitore) {
            $match = \App\Models\Invoicein::whereRaw('nome_fornitore COLLATE utf8mb4_unicode_ci = ? COLLATE utf8mb4_unicode_ci', [$fornitore->name])
                ->whereNotNull('partita_iva')
                ->orderByDesc('id')
                ->first();
            if ($match && $fornitore->piva !== $match->partita_iva) {
                $fornitore->piva = $match->partita_iva;
                $fornitore->save();
                $updated++;
            }
        }
        $this->info("Updated $updated fornitori records with piva from invoiceins (case-insensitive match).");
        return 0;
    }

    protected function importToInvoices()
    {
        $imported = 0;
        $skipped = 0;

        // Get invoiceins that have matching fornitoris
        $invoiceins = \App\Models\Invoicein::join('fornitoris', 'fornitoris.coge', '=', 'invoiceins.nr_cliente_fornitore')
            ->select('invoiceins.*')
            ->get();

        foreach ($invoiceins as $invoicein) {
            // Check if invoice with same nr_documento already exists
            if (\App\Models\Invoice::where('invoice_number', $invoicein->nr_documento)->exists()) {
                $skipped++;
                continue;
            }

            // Create new invoice
            $invoice = new \App\Models\Invoice([
                'fornitore_piva' => $invoicein->partita_iva,
                'fornitore' => $invoicein->nome_fornitore,
                'invoice_number' => $invoicein->nr_documento,
                'invoice_date' => $invoicein->data_ricezione_fatt,
                'total_amount' => $invoicein->importo_totale_fornitore,
                'tax_amount' => $invoicein->importo_iva,
                'coge' => $invoicein->nr_cliente_fornitore,
                'status' => 'imported',
                'currency' => 'EUR',
            ]);

            $invoice->save();
            $imported++;
        }

        $this->info("Imported $imported invoices from invoiceins.");
        if ($skipped > 0) {
            $this->warn("Skipped $skipped invoices (duplicate invoice_number).");
        }
        return 0;
    }
}
