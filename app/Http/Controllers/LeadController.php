<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\Request;
use App\Imports\LeadsImport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class LeadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Lead::query();

        // Filter by company_id if provided
        if ($request->has('company_id') && $request->company_id !== '') {
            $query->where('company_id', $request->company_id);
        }

        // Filter by legacy_id if provided
        if ($request->has('legacy_id') && $request->legacy_id !== '') {
            $query->where('legacy_id', 'like', '%' . $request->legacy_id . '%');
        }

        // Filter by campagna if provided
        if ($request->has('campagna') && $request->campagna !== '') {
            $query->where('campagna', 'like', '%' . $request->campagna . '%');
        }

        // Filter by lista if provided
        if ($request->has('lista') && $request->lista !== '') {
            $query->where('lista', 'like', '%' . $request->lista . '%');
        }

        // Filter by cognome if provided
        if ($request->has('cognome') && $request->cognome !== '') {
            $query->where('cognome', 'like', '%' . $request->cognome . '%');
        }

        // Filter by nome if provided
        if ($request->has('nome') && $request->nome !== '') {
            $query->where('nome', 'like', '%' . $request->nome . '%');
        }

        // Filter by telefono if provided
        if ($request->has('telefono') && $request->telefono !== '') {
            $query->where('telefono', 'like', '%' . $request->telefono . '%');
        }

        // Filter by ultimo_operatore if provided
        if ($request->has('ultimo_operatore') && $request->ultimo_operatore !== '') {
            $query->where('ultimo_operatore', 'like', '%' . $request->ultimo_operatore . '%');
        }

        // Filter by esito if provided
        if ($request->has('esito') && $request->esito !== '') {
            $query->where('esito', 'like', '%' . $request->esito . '%');
        }

        // Filter by comune if provided
        if ($request->has('comune') && $request->comune !== '') {
            $query->where('comune', 'like', '%' . $request->comune . '%');
        }

        // Filter by provincia if provided
        if ($request->has('provincia') && $request->provincia !== '') {
            $query->where('provincia', 'like', '%' . $request->provincia . '%');
        }

        // Filter by email if provided
        if ($request->has('email') && $request->email !== '') {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        // Filter by attivo status
        if ($request->has('attivo') && $request->attivo !== '') {
            $attivo = $request->attivo === 'true' ? true : false;
            $query->where('attivo', $attivo);
        }

        // Filter by date range for ultima_chiamata
        if ($request->has('ultima_chiamata_from') && $request->ultima_chiamata_from !== '') {
            try {
                $dataFrom = Carbon::createFromFormat('Y-m-d', $request->ultima_chiamata_from)->startOfDay();
                $query->where('ultima_chiamata', '>=', $dataFrom);
            } catch (\Exception $e) {
                // Invalid date format, ignore filter
            }
        }

        if ($request->has('ultima_chiamata_to') && $request->ultima_chiamata_to !== '') {
            try {
                $dataTo = Carbon::createFromFormat('Y-m-d', $request->ultima_chiamata_to)->endOfDay();
                $query->where('ultima_chiamata', '<=', $dataTo);
            } catch (\Exception $e) {
                // Invalid date format, ignore filter
            }
        }

        // Handle sorting
        $sortBy = $request->get('sort_by', 'data_creazione');
        $sortDirection = $request->get('sort_direction', 'desc');

        // Validate sort parameters
        $allowedSortBy = [
            'legacy_id', 'campagna', 'lista', 'cognome', 'nome', 'telefono',
            'ultimo_operatore', 'esito', 'comune', 'provincia', 'email',
            'ultima_chiamata', 'chiamate', 'chiamate_giornaliere', 'chiamate_mensili',
            'data_creazione', 'created_at'
        ];
        $allowedDirections = ['asc', 'desc'];

        if (!in_array($sortBy, $allowedSortBy)) {
            $sortBy = 'data_creazione';
        }
        if (!in_array($sortDirection, $allowedDirections)) {
            $sortDirection = 'desc';
        }

        $query->orderBy($sortBy, $sortDirection);

        $leads = $query->paginate(15)->withQueryString();

        // Get unique values for filter dropdowns
        $companies = \App\Models\Company::orderBy('name')->get();
        $campagnaOptions = Lead::distinct()->pluck('campagna')->filter()->sort()->values();
        $listaOptions = Lead::distinct()->pluck('lista')->filter()->sort()->values();
        $esitoOptions = Lead::distinct()->pluck('esito')->filter()->sort()->values();
        $operatoreOptions = Lead::distinct()->pluck('ultimo_operatore')->filter()->sort()->values();
        $comuneOptions = Lead::distinct()->pluck('comune')->filter()->sort()->values();
        $provinciaOptions = Lead::distinct()->pluck('provincia')->filter()->sort()->values();

        return view('leads.index', compact(
            'leads', 'companies', 'campagnaOptions', 'listaOptions', 'esitoOptions',
            'operatoreOptions', 'comuneOptions', 'provinciaOptions',
            'sortBy', 'sortDirection'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companies = \App\Models\Company::orderBy('name')->get();
        return view('leads.create', compact('companies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'legacy_id' => 'nullable|string|max:20',
            'campagna' => 'nullable|string|max:100',
            'lista' => 'nullable|string|max:100',
            'ragione_sociale' => 'nullable|string|max:255',
            'cognome' => 'nullable|string|max:100',
            'nome' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'ultimo_operatore' => 'nullable|string|max:255',
            'esito' => 'nullable|string|max:100',
            'data_richiamo' => 'nullable|date',
            'operatore_richiamo' => 'nullable|string|max:255',
            'scadenza_anagrafica' => 'nullable|date',
            'indirizzo1' => 'nullable|string|max:255',
            'indirizzo2' => 'nullable|string|max:255',
            'indirizzo3' => 'nullable|string|max:255',
            'comune' => 'nullable|string|max:100',
            'provincia' => 'nullable|string|max:10',
            'cap' => 'nullable|string|max:10',
            'regione' => 'nullable|string|max:100',
            'paese' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'p_iva' => 'nullable|string|max:50',
            'codice_fiscale' => 'nullable|string|max:20',
            'telefono2' => 'nullable|string|max:20',
            'telefono3' => 'nullable|string|max:20',
            'telefono4' => 'nullable|string|max:20',
            'sesso' => 'nullable|string|max:10',
            'nota' => 'nullable|string',
            'attivo' => 'nullable|boolean',
            'altro1' => 'nullable|string|max:255',
            'altro2' => 'nullable|string|max:255',
            'altro3' => 'nullable|string|max:255',
            'altro4' => 'nullable|string|max:255',
            'altro5' => 'nullable|string|max:255',
            'altro6' => 'nullable|string|max:255',
            'altro7' => 'nullable|string|max:255',
            'altro8' => 'nullable|string|max:255',
            'altro9' => 'nullable|string|max:255',
            'altro10' => 'nullable|string|max:255',
            'chiamate' => 'nullable|integer|min:0',
            'ultima_chiamata' => 'nullable|date',
            'creato_da' => 'nullable|string|max:255',
            'durata_ultima_chiamata' => 'nullable|string|max:20',
            'totale_durata_chiamate' => 'nullable|string|max:20',
            'chiamate_giornaliere' => 'nullable|integer|min:0',
            'chiamate_mensili' => 'nullable|integer|min:0',
            'data_creazione' => 'nullable|date',
            'company_id' => 'nullable|string|max:36|exists:companies,id',
        ]);

        Lead::create($data);
        return redirect()->route('leads.index')->with('success', 'Lead created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Lead $lead)
    {
        return view('leads.show', compact('lead'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Lead $lead)
    {
        $companies = \App\Models\Company::orderBy('name')->get();
        return view('leads.edit', compact('lead', 'companies'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lead $lead)
    {
        $data = $request->validate([
            'legacy_id' => 'nullable|string|max:20',
            'campagna' => 'nullable|string|max:100',
            'lista' => 'nullable|string|max:100',
            'ragione_sociale' => 'nullable|string|max:255',
            'cognome' => 'nullable|string|max:100',
            'nome' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'ultimo_operatore' => 'nullable|string|max:255',
            'esito' => 'nullable|string|max:100',
            'data_richiamo' => 'nullable|date',
            'operatore_richiamo' => 'nullable|string|max:255',
            'scadenza_anagrafica' => 'nullable|date',
            'indirizzo1' => 'nullable|string|max:255',
            'indirizzo2' => 'nullable|string|max:255',
            'indirizzo3' => 'nullable|string|max:255',
            'comune' => 'nullable|string|max:100',
            'provincia' => 'nullable|string|max:10',
            'cap' => 'nullable|string|max:10',
            'regione' => 'nullable|string|max:100',
            'paese' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'p_iva' => 'nullable|string|max:50',
            'codice_fiscale' => 'nullable|string|max:20',
            'telefono2' => 'nullable|string|max:20',
            'telefono3' => 'nullable|string|max:20',
            'telefono4' => 'nullable|string|max:20',
            'sesso' => 'nullable|string|max:10',
            'nota' => 'nullable|string',
            'attivo' => 'nullable|boolean',
            'altro1' => 'nullable|string|max:255',
            'altro2' => 'nullable|string|max:255',
            'altro3' => 'nullable|string|max:255',
            'altro4' => 'nullable|string|max:255',
            'altro5' => 'nullable|string|max:255',
            'altro6' => 'nullable|string|max:255',
            'altro7' => 'nullable|string|max:255',
            'altro8' => 'nullable|string|max:255',
            'altro9' => 'nullable|string|max:255',
            'altro10' => 'nullable|string|max:255',
            'chiamate' => 'nullable|integer|min:0',
            'ultima_chiamata' => 'nullable|date',
            'creato_da' => 'nullable|string|max:255',
            'durata_ultima_chiamata' => 'nullable|string|max:20',
            'totale_durata_chiamate' => 'nullable|string|max:20',
            'chiamate_giornaliere' => 'nullable|integer|min:0',
            'chiamate_mensili' => 'nullable|integer|min:0',
            'data_creazione' => 'nullable|date',
            'company_id' => 'nullable|string|max:36|exists:companies,id',
        ]);

        $lead->update($data);
        return redirect()->route('leads.index')->with('success', 'Lead updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lead $lead)
    {
        $lead->delete();
        return redirect()->route('leads.index')->with('success', 'Lead deleted successfully.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv',
        ]);

        try {
            Excel::import(new LeadsImport, $request->file('file'));
            return redirect()->route('leads.index')->with('success', 'Leads imported successfully!');
        } catch (\Exception $e) {
            return redirect()->route('leads.index')->with('error', 'Error importing leads: ' . $e->getMessage());
        }
    }
}
