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
            // Your API import logic here
            $response = [
                'success' => true,
                'message' => 'Importazione completata con successo!',
            ];

            return redirect()
                ->route('pratiches.import.api.form')
                ->with('success', $response['message']);

        } catch (\Exception $e) {
            return redirect()
                ->route('pratiches.import.api.form')
                ->with('error', 'Errore durante l\'importazione: ' . $e->getMessage());
        }
    }
}
