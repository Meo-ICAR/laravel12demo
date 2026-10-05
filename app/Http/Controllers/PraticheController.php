<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pratiche;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class PraticheController extends PraticheCrudController
{
    /**
     * Show the form for importing Pratiche from API
     */
    public function showImportApiForm()
    {
        return view('pratiches.import-api');
    }

    /**
 * Import Pratiche from API
 */
public function importFromApi(Request $request)
{
    $request->validate([
        'data_inizio' => 'required|date',
        'data_fine' => 'required|date|after_or_equal:data_inizio',
    ]);

    try {
        // Run the command programmatically
        \Artisan::call('pratiche:import-api', [
            '--start-date' => $request->data_inizio,
            '--end-date' => $request->data_fine,
        ]);

        // Get the command output
        $output = \Artisan::output();

        return redirect()
            ->route('pratiches.import.api.form')
            ->with('success', 'Importazione completata con successo!')
            ->with('output', $output);

    } catch (\Exception $e) {
        \Log::error('Import error: ' . $e->getMessage());
        return redirect()
            ->route('pratiches.import.api.form')
            ->with('error', 'Errore durante l\'importazione: ' . $e->getMessage());
    }

}    }
