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
        try {
            // Check if file was uploaded
            if (!$request->hasFile('file')) {
                return redirect()->route('leads.index')->with('error', 'No file was selected. Please choose a file to import.');
            }

            $file = $request->file('file');

            // Check if file is valid
            if (!$file->isValid()) {
                return redirect()->route('leads.index')->with('error', 'The uploaded file is not valid. Please try again.');
            }

            // Validate file
            $request->validate([
                'file' => 'required|file|max:2048', // 2MB max
            ], [
                'file.required' => 'Please select a file to import.',
                'file.file' => 'The uploaded file is not valid.',
                'file.max' => 'The file size must not exceed 2MB.',
            ]);

            // Custom validation for file type
            $file = $request->file('file');
            $extension = strtolower($file->getClientOriginalExtension());
            $allowedExtensions = ['csv', 'xlsx', 'xls'];

            if (!in_array($extension, $allowedExtensions)) {
                return redirect()->route('leads.index')->with('error', 'The file must be a CSV, XLSX, or XLS file. Detected extension: ' . $extension);
            }

            Excel::import(new LeadsImport, $file);

            return redirect()->route('leads.index')->with('success', 'Leads imported successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('leads.index')->withErrors($e->errors());
        } catch (\Exception $e) {
            return redirect()->route('leads.index')->with('error', 'Error importing leads: ' . $e->getMessage());
        }
    }

    /**
     * Display the leads dashboard with statistics and analytics.
     */
    public function dashboard(Request $request)
    {
        $query = Lead::query();

        // Apply company filter if provided
        if ($request->has('company_id') && $request->company_id !== '') {
            $query->where('company_id', $request->company_id);
        }

        // Apply date range filter if provided
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        if ($dateFrom) {
            $query->where('data_creazione', '>=', Carbon::parse($dateFrom)->startOfDay());
        }
        if ($dateTo) {
            $query->where('data_creazione', '<=', Carbon::parse($dateTo)->endOfDay());
        }

        // Get base query for statistics
        $baseQuery = clone $query;

        // Overall statistics
        $totalLeads = $baseQuery->count();
        $activeLeads = $baseQuery->where('attivo', true)->count();
        $inactiveLeads = $baseQuery->where('attivo', false)->count();
        $leadsWithCalls = $baseQuery->where('chiamate', '>', 0)->count();
        $leadsWithoutCalls = $baseQuery->where('chiamate', 0)->orWhereNull('chiamate')->count();

        // Campaign statistics
        $campaignStats = $baseQuery->selectRaw('campagna, COUNT(*) as total, SUM(CASE WHEN attivo = 1 THEN 1 ELSE 0 END) as active, SUM(CASE WHEN attivo = 0 THEN 1 ELSE 0 END) as inactive, AVG(chiamate) as avg_calls')
            ->whereNotNull('campagna')
            ->groupBy('campagna')
            ->orderBy('total', 'desc')
            ->get();

        // List statistics
        $listStats = $baseQuery->selectRaw('lista, COUNT(*) as total, SUM(CASE WHEN attivo = 1 THEN 1 ELSE 0 END) as active, SUM(CASE WHEN attivo = 0 THEN 1 ELSE 0 END) as inactive')
            ->whereNotNull('lista')
            ->groupBy('lista')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        // Outcome statistics
        $outcomeStats = $baseQuery->selectRaw('esito, COUNT(*) as total')
            ->whereNotNull('esito')
            ->groupBy('esito')
            ->orderBy('total', 'desc')
            ->get();

        // Operator statistics
        $operatorStats = $baseQuery->selectRaw('ultimo_operatore, COUNT(*) as total, AVG(chiamate) as avg_calls')
            ->whereNotNull('ultimo_operatore')
            ->groupBy('ultimo_operatore')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        // Regional statistics
        $regionalStats = $baseQuery->selectRaw('regione, COUNT(*) as total, SUM(CASE WHEN attivo = 1 THEN 1 ELSE 0 END) as active')
            ->whereNotNull('regione')
            ->groupBy('regione')
            ->orderBy('total', 'desc')
            ->get();

        // Recent activity
        $recentLeads = $baseQuery->orderBy('data_creazione', 'desc')->limit(10)->get();
        $recentCalls = $baseQuery->whereNotNull('ultima_chiamata')->orderBy('ultima_chiamata', 'desc')->limit(10)->get();

        // Call statistics
        $totalCalls = $baseQuery->sum('chiamate');
        $avgCallsPerLead = $totalLeads > 0 ? round($totalCalls / $totalLeads, 2) : 0;
        $leadsWithMultipleCalls = $baseQuery->where('chiamate', '>', 1)->count();

        // Monthly trends (last 12 months)
        $monthlyTrends = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();

            $monthlyQuery = clone $baseQuery;
            $monthlyLeads = $monthlyQuery->whereBetween('data_creazione', [$monthStart, $monthEnd])->count();
            $monthlyCalls = $monthlyQuery->whereBetween('ultima_chiamata', [$monthStart, $monthEnd])->count();

            $monthlyTrends[] = [
                'month' => $date->format('M Y'),
                'leads' => $monthlyLeads,
                'calls' => $monthlyCalls
            ];
        }

        // Get filter options
        $companies = \App\Models\Company::orderBy('name')->get();
        $campagnaOptions = Lead::distinct()->pluck('campagna')->filter()->sort()->values();
        $listaOptions = Lead::distinct()->pluck('lista')->filter()->sort()->values();
        $esitoOptions = Lead::distinct()->pluck('esito')->filter()->sort()->values();
        $operatoreOptions = Lead::distinct()->pluck('ultimo_operatore')->filter()->sort()->values();
        $regioneOptions = Lead::distinct()->pluck('regione')->filter()->sort()->values();

        return view('leads.dashboard', compact(
            'totalLeads', 'activeLeads', 'inactiveLeads', 'leadsWithCalls', 'leadsWithoutCalls',
            'campaignStats', 'listStats', 'outcomeStats', 'operatorStats', 'regionalStats',
            'recentLeads', 'recentCalls', 'totalCalls', 'avgCallsPerLead', 'leadsWithMultipleCalls',
            'monthlyTrends', 'companies', 'campagnaOptions', 'listaOptions', 'esitoOptions',
            'operatoreOptions', 'regioneOptions', 'dateFrom', 'dateTo'
        ));
    }

    /**
     * Export leads data for dashboard analytics.
     */
    public function export(Request $request)
    {
        $query = Lead::query();

        // Apply filters similar to dashboard
        if ($request->has('company_id') && $request->company_id !== '') {
            $query->where('company_id', $request->company_id);
        }

        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        if ($dateFrom) {
            $query->where('data_creazione', '>=', Carbon::parse($dateFrom)->startOfDay());
        }
        if ($dateTo) {
            $query->where('data_creazione', '<=', Carbon::parse($dateTo)->endOfDay());
        }

        $leads = $query->get();

        $filename = 'leads_export_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($leads) {
            $file = fopen('php://output', 'w');

            // Add headers
            fputcsv($file, [
                'ID', 'Campagna', 'Lista', 'Cognome', 'Nome', 'Telefono', 'Email',
                'Operatore', 'Esito', 'Comune', 'Provincia', 'Regione',
                'Chiamate', 'Ultima Chiamata', 'Attivo', 'Data Creazione'
            ]);

            // Add data
            foreach ($leads as $lead) {
                fputcsv($file, [
                    $lead->legacy_id,
                    $lead->campagna,
                    $lead->lista,
                    $lead->cognome,
                    $lead->nome,
                    $lead->telefono,
                    $lead->email,
                    $lead->ultimo_operatore,
                    $lead->esito,
                    $lead->comune,
                    $lead->provincia,
                    $lead->regione,
                    $lead->chiamate,
                    $lead->ultima_chiamata ? $lead->ultima_chiamata->format('Y-m-d H:i:s') : '',
                    $lead->attivo ? 'Yes' : 'No',
                    $lead->data_creazione ? $lead->data_creazione->format('Y-m-d H:i:s') : ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get analytics data for AJAX requests.
     */
    public function analytics(Request $request)
    {
        $query = Lead::query();

        // Apply filters
        if ($request->has('company_id') && $request->company_id !== '') {
            $query->where('company_id', $request->company_id);
        }

        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        if ($dateFrom) {
            $query->where('data_creazione', '>=', Carbon::parse($dateFrom)->startOfDay());
        }
        if ($dateTo) {
            $query->where('data_creazione', '<=', Carbon::parse($dateTo)->endOfDay());
        }

        $baseQuery = clone $query;

        // Get various analytics
        $analytics = [
            'total_leads' => $baseQuery->count(),
            'active_leads' => $baseQuery->where('attivo', true)->count(),
            'leads_with_calls' => $baseQuery->where('chiamate', '>', 0)->count(),
            'total_calls' => $baseQuery->sum('chiamate'),
            'avg_calls_per_lead' => $baseQuery->count() > 0 ? round($baseQuery->sum('chiamate') / $baseQuery->count(), 2) : 0,
            'top_campaigns' => $baseQuery->selectRaw('campagna, COUNT(*) as total')
                ->whereNotNull('campagna')
                ->groupBy('campagna')
                ->orderBy('total', 'desc')
                ->limit(5)
                ->get(),
            'top_outcomes' => $baseQuery->selectRaw('esito, COUNT(*) as total')
                ->whereNotNull('esito')
                ->groupBy('esito')
                ->orderBy('total', 'desc')
                ->limit(5)
                ->get(),
            'top_operators' => $baseQuery->selectRaw('ultimo_operatore, COUNT(*) as total, AVG(chiamate) as avg_calls')
                ->whereNotNull('ultimo_operatore')
                ->groupBy('ultimo_operatore')
                ->orderBy('total', 'desc')
                ->limit(5)
                ->get(),
        ];

        return response()->json($analytics);
    }
}
