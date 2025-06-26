<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pratiche;
use Illuminate\Support\Facades\Log;

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
            'csv_file' => 'required|file|mimes:csv,txt|max:2048'
        ]);

        try {
            $file = $request->file('csv_file');
            $path = $file->getRealPath();

            $data = array_map('str_getcsv', file($path));
            $headers = array_shift($data); // Remove header row

            Log::info('CSV Headers:', $headers);

            $imported = 0;
            $skipped = 0;

            foreach ($data as $row) {
                if (count($row) < count($headers)) {
                    continue; // Skip incomplete rows
                }

                $rowData = array_combine($headers, $row);

                // Check if record already exists
                if (Pratiche::where('pratica_id', $rowData['ID'])->exists()) {
                    $skipped++;
                    continue;
                }

                // Convert date format from dd/mm/yyyy to yyyy-mm-dd
                if (!empty($rowData['Data_inserimento'])) {
                    $date = \DateTime::createFromFormat('d/m/Y', $rowData['Data_inserimento']);
                    $rowData['Data_inserimento'] = $date ? $date->format('Y-m-d') : null;
                }

                // Clean up the data
                $cleanData = [
                    'pratica_id' => trim($rowData['ID'] ?? ''),
                    'Data_inserimento' => $rowData['Data_inserimento'],
                    'Descrizione' => trim($rowData['Descrizione'] ?? ''),
                    'Cliente' => trim($rowData['Cliente'] ?? ''),
                    'Agente' => trim($rowData['Agente'] ?? ''),
                    'Segnalatore' => trim($rowData['Segnalatore'] ?? ''),
                    'Fonte' => trim($rowData['Fonte'] ?? ''),
                    'Tipo' => trim($rowData['Tipo'] ?? ''),
                    'Istituto_finanziario' => trim($rowData['Istituto finanziario'] ?? '')
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
