<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Invoicein;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class InvoiceinImportController extends Controller
{
    public function index()
    {
        return view('invoiceins.import');
    }

    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'csv_file' => 'required|file|mimes:csv,txt|max:20480',
        ], [
            'csv_file.mimes' => 'The file must be a CSV file.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $file = $request->file('csv_file');
        $importedCount = 0;
        $errors = [];

        if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
            $header = null;
            while (($row = fgetcsv($handle, 10000, ';')) !== false) {
                if (!$header) {
                    $header = $row;
                    continue;
                }
                $data = array_combine($header, $row);
                try {
                    $this->importRow($data);
                    $importedCount++;
                } catch (\Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }
            fclose($handle);
        }

        if ($importedCount > 0) {
            $message = "Successfully imported {$importedCount} invoiceins.";
            if (!empty($errors)) {
                $message .= " However, there were some errors: " . implode(', ', $errors);
            }
            return redirect()->route('invoiceins.index')->with('success', $message);
        }

        return back()->with('error', 'No valid invoiceins could be imported. Errors: ' . implode(', ', $errors));
    }

    protected function importRow($data)
    {
        // Map CSV columns to model fields
        $invoicein = new Invoicein([
            'tipo_di_documento' => $data['Tipo di documento'] ?? null,
            'nr_documento' => $data['Nr. documento'] ?? null,
            'nr_fatt_acq_registrata' => $data['Nr. Fatt. Acq. Registrata'] ?? null,
            'nr_nota_cr_acq_registrata' => $data['Nr. Nota Cr. Acq. Registrata'] ?? null,
            'data_ricezione_fatt' => $data['Data Ricezione Fatt.'] ?? null,
            'codice_td' => $data['Codice TD'] ?? null,
            'nr_cliente_fornitore' => $data['Nr. cliente/fornitore'] ?? null,
            'nome_fornitore' => $data['Nome fornitore'] ?? null,
            'partita_iva' => $data['Partita IVA'] ?? null,
            'nr_documento_fornitore' => $data['Nr. Documento Fornitore'] ?? null,
            'allegato' => $data['Allegato'] ?? null,
            'data_documento_fornitore' => $data['Data Documento Fornitore'] ?? null,
            'data_primo_pagamento_prev' => $data['Data Primo Pagamento Prev.'] ?? null,
            'imponibile_iva' => $data['Imponibile IVA'] ?? null,
            'importo_iva' => $data['Importo IVA'] ?? null,
            'importo_totale_fornitore' => $data['Importo Totale Fornitore'] ?? null,
            'importo_totale_collegato' => $data['Importo Totale Collegato'] ?? null,
            'data_ora_invio_ricezione' => $data['Data ora Invio/Ricezione'] ?? null,
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
}
