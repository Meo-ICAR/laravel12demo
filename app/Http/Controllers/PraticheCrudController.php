<?php

namespace App\Http\Controllers;

use App\Models\Pratiche;
use Illuminate\Http\Request;
use App\Http\Requests\StorePraticheRequest;
use App\Http\Requests\UpdatePraticheRequest;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

class PraticheCrudController extends Controller
{
    /**
     * Show the import form
     */
    public function showImportForm()
    {
        return view('pratiches.import');
    }

    /**
     * Handle the import request
     */
    public function import(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        try {
            $startDate = Carbon::parse($request->start_date)->format('Y-m-d');
            $endDate = Carbon::parse($request->end_date)->format('Y-m-d');
            
            // Run the import command
            $exitCode = Artisan::call('pratiche:import-api', [
                '--start-date' => $startDate,
                '--end-date' => $endDate,
            ]);

            if ($exitCode === 0) {
                return response()->json([
                    'message' => 'Importazione completata con successo!',
                ]);
            }

            throw new \Exception('Errore durante l\'esecuzione del comando di importazione');
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Pratiche::query();

        // Search functionality
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('codice_pratica', 'like', "%{$search}%")
                  ->orWhere('nome_cliente', 'like', "%{$search}%")
                  ->orWhere('cognome_cliente', 'like', "%{$search}%")
                  ->orWhere('codice_fiscale', 'like', "%{$search}%")
                  ->orWhere('denominazione_agente', 'like', "%{$search}%")
                  ->orWhere('denominazione_banca', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortField = $request->input('sort', 'data_inserimento_pratica');
        $sortDirection = $request->input('direction', 'desc');
        
        // Validate sort direction
        if (!in_array(strtolower($sortDirection), ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }

        // Add sorting
        $query->orderBy($sortField, $sortDirection);

        $pratiches = $query->paginate(20)->withQueryString();

        return view('pratiches-crud.index', compact('pratiches', 'sortField', 'sortDirection'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pratiches-crud.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePraticheRequest $request)
    {
        try {
            Pratiche::create($request->validated());
            return redirect()->route('pratiches-crud.index')
                ->with('success', 'Pratica creata con successo.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Errore durante la creazione della pratica: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Pratiche $pratiche)
    {
        // Debug: Log the Pratiche model
        \Log::info('Pratiche model in show method:', [
            'id' => $pratiche->id,
            'codice_pratica' => $pratiche->codice_pratica,
            'exists' => $pratiche->exists,
            'wasRecentlyCreated' => $pratiche->wasRecentlyCreated,
        ]);
        
        if (!$pratiche->exists) {
            abort(404, 'Pratica non trovata');
        }
        
        return view('pratiches-crud.show', compact('pratiche'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pratiche $pratiche)
    {
        return view('pratiches-crud.edit', compact('pratiche'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePraticheRequest $request, Pratiche $pratiche)
    {
        try {
            $pratiche->update($request->validated());
            return redirect()->route('pratiches-crud.index')
                ->with('success', 'Pratica aggiornata con successo.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Errore durante l\'aggiornamento della pratica: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pratiche $pratiche)
    {
        try {
            $pratiche->delete();
            return redirect()->route('pratiches-crud.index')
                ->with('success', 'Pratica eliminata con successo.');
        } catch (\Exception $e) {
            return back()->with('error', 'Errore durante l\'eliminazione della pratica: ' . $e->getMessage());
        }
    }
}
