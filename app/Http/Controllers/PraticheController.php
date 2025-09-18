<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pratiche;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PraticheController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pratiches = Pratiche::orderBy('Data_inserimento', 'desc')->paginate(20);
        return view('pratiches.index', compact('pratiches'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pratiches.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Import CSV file
     */
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt,xlsx,xls|max:8192'
        ]);

        try {
            $file = $request->file('csv_file');
            $extension = strtolower($file->getClientOriginalExtension());

            $imported = 0;
            $skipped = 0;
            $rows = [];

            if (in_array($extension, ['xlsx', 'xls'])) {
                // Read Excel with heading row mapping
                $arrays = Excel::toArray(new class implements ToArray, WithHeadingRow {
                    public function array(array $array) { return $array; }
                }, $file);

                if (!empty($arrays[0])) {
                    $rows = $arrays[0]; // Already associative arrays with normalized keys
                    Log::info('Excel headers (normalized):', array_keys($rows[0] ?? []));
                }
            } else {
                // CSV/TXT parsing
                $path = $file->getRealPath();
                $data = array_map('str_getcsv', file($path));
                $headers = array_shift($data); // header row
                Log::info('CSV Headers:', $headers);
                foreach ($data as $row) {
                    if (count($row) < count($headers)) {
                        continue;
                    }
                    $rows[] = array_combine($headers, $row);
                }
            }

            // Helper to parse European decimal values
            $parseDecimal = function ($value) {
                if ($value === null || $value === '') return null;
                $value = trim((string)$value);
                $value = str_replace("\xc2\xa0", ' ', $value);
                $value = str_replace(['€', ' '], '', $value);
                if (strpos($value, ',') !== false) {
                    $value = str_replace('.', '', $value);
                    $value = str_replace(',', '.', $value);
                } else {
                    $value = str_replace(',', '', $value);
                }
                return is_numeric($value) ? (float)$value : null;
            };

            $parseDate = function ($value) {
                if (!$value) return null;
                $value = trim((string)$value);
                // dd/mm/yyyy or dd/mm/yyyy HH:MM
                if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})(?: (\d{2}):(\d{2})(?::(\d{2}))?)?$/', $value, $m)) {
                    if (isset($m[3]) && isset($m[4])) {
                        $sec = $m[5] ?? '00';
                        return "$m[3]-$m[2]-$m[1] {$m[4]}:{$m[5]}:$sec";
                    }
                    return "$m[3]-$m[2]-$m[1]";
                }
                // yyyy-mm-dd or yyyy-mm-dd HH:MM:SS
                if (preg_match('/^\d{4}-\d{2}-\d{2}(?: \d{2}:\d{2}(?::\d{2})?)?$/', $value)) {
                    return $value;
                }
                return null;
            };

            foreach ($rows as $row) {
                // Normalize keys to handle both Excel normalized and CSV original headers
                $id = $row['ID'] ?? $row['id'] ?? $row['Id'] ?? null;
                if (!$id) {
                    continue;
                }

                if (Pratiche::where('pratica_id', $id)->exists()) {
                    $skipped++;
                    continue;
                }

                $dataInserimento = $row['Data_inserimento'] ?? $row['data_inserimento'] ?? null;
                $dataInserimento = $parseDate($dataInserimento);

                $cleanData = [
                    'pratica_id' => trim((string)$id),
                    'Data_inserimento' => $dataInserimento ? substr($dataInserimento, 0, 10) : null,
                    'Descrizione' => trim((string)($row['Descrizione'] ?? $row['descrizione'] ?? '')),
                    'Cliente' => trim((string)($row['Cliente'] ?? $row['cliente'] ?? '')),
                    'Agente' => trim((string)($row['Agente'] ?? $row['agente'] ?? '')),
                    'Segnalatore' => trim((string)($row['Segnalatore'] ?? $row['segnalatore'] ?? '')),
                    'Fonte' => trim((string)($row['Fonte'] ?? $row['fonte'] ?? '')),
                    'Tipo' => trim((string)($row['Tipo'] ?? $row['tipo'] ?? '')),
                    'Istituto_finanziario' => trim((string)($row['Istituto finanziario'] ?? $row['istituto_finanziario'] ?? '')),
                    'Pratica' => trim((string)($row['Pratica'] ?? $row['pratica'] ?? '')),
                    'Status_pratica' => trim((string)($row['Status pratica'] ?? $row['status_pratica'] ?? '')),
                    'Cliente_ID' => trim((string)($row['ID'] ?? $row['cliente_id'] ?? '')),
                    'Codice_fiscale' => trim((string)($row['Codice_fiscale'] ?? $row['codice_fiscale'] ?? '')),
                    'Prodotto' => trim((string)($row['Prodotto'] ?? $row['prodotto'] ?? '')),
                    'Residenza_citta' => trim((string)($row['Residenza_citta'] ?? $row['residenza_citta'] ?? '')),
                    'Residenza_provincia' => trim((string)($row['Residenza_provincia'] ?? $row['residenza_provincia'] ?? '')),
                    'Regione' => trim((string)($row['Regione'] ?? $row['regione'] ?? '')),
                    'Importo_erogato' => $parseDecimal($row['Importo_erogato'] ?? $row['importo_erogato'] ?? null),
                    'Importo' => $parseDecimal($row['Importo'] ?? $row['importo'] ?? null),
                    'Totale_compensi_lordo' => $parseDecimal($row['Totale_compensi_lordo'] ?? $row['totale_compensi_lordo'] ?? null),
                    'Totale_compensi_passivo' => $parseDecimal($row['Totale_compensi_passivo'] ?? $row['totale_compensi_passivo'] ?? null),
                    'Totale_compensi_netto' => $parseDecimal($row['Totale_compensi_netto'] ?? $row['totale_compensi_netto'] ?? null),
                    'Importo_compenso' => $parseDecimal($row['Importo_compenso'] ?? $row['importo_compenso'] ?? null),
                    'Importo_compenso_euro' => $parseDecimal($row['Importo_compenso_euro'] ?? $row['importo_compenso_euro'] ?? null),
                    'Importo_rata' => $parseDecimal($row['Importo_rata'] ?? $row['importo_rata'] ?? null),
                    'Durata' => isset($row['Durata']) ? (int)$row['Durata'] : (isset($row['durata']) ? (int)$row['durata'] : null),
                    'Montante' => $parseDecimal($row['Montante'] ?? $row['montante'] ?? null),
                    'TAN' => $parseDecimal($row['TAN'] ?? $row['tan'] ?? null),
                    'Importo_compenso2' => $parseDecimal($row['Importo_compenso'] ?? $row['importo_compenso'] ?? null),
                    'Data_decorrenza' => $parseDate($row['Data decorrenza'] ?? $row['data_decorrenza'] ?? null),
                    'Inserita_at' => $parseDate($row['Inserita'] ?? $row['inserita'] ?? null),
                    'Invio_in_istruttoria_at' => $parseDate($row['Invio in istruttoria'] ?? $row['invio_in_istruttoria'] ?? null),
                    'Deliberata_at' => $parseDate($row['Deliberata'] ?? $row['deliberata'] ?? null),
                    'Liquidata_at' => $parseDate($row['Liquidata'] ?? $row['liquidata'] ?? null),
                    'Perfezionata_at' => $parseDate($row['Perfezionata'] ?? $row['perfezionata'] ?? null),
                    'Declinata_at' => $parseDate($row['Declinata'] ?? $row['declinata'] ?? null),
                    'Pratica_respinta_at' => $parseDate($row['Pratica Respinta'] ?? $row['pratica_respinta'] ?? null),
                    'Rinuncia_cliente_at' => $parseDate($row['Rinuncia Cliente'] ?? $row['rinuncia_cliente'] ?? null),
                    'Data_firma_at' => $parseDate($row['Data Firma'] ?? $row['data_firma'] ?? null),
                ];

                Pratiche::create($cleanData);
                $imported++;
            }

            return redirect()->route('pratiches.index')
                ->with('success', "Import completato: $imported record importati, $skipped record saltati.");

        } catch (\Exception $e) {
            Log::error('Import error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Errore durante l\'import: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $pratiche = Pratiche::findOrFail($id);
        return view('pratiches.show', compact('pratiche'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $pratiche = Pratiche::findOrFail($id);
        return view('pratiches.edit', compact('pratiche'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
