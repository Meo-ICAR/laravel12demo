<?php

namespace App\Http\Controllers;

use App\Models\Call;
use Illuminate\Http\Request;
use App\Imports\CallsImport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class CallController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Call::query();

        // Filter by company_id if provided
        if ($request->has('company_id') && $request->company_id !== '') {
            $query->where('company_id', $request->company_id);
        }

        // Filter by numero_chiamato if provided
        if ($request->has('numero_chiamato') && $request->numero_chiamato !== '') {
            $query->where('numero_chiamato', 'like', '%' . $request->numero_chiamato . '%');
        }

        // Filter by stato_chiamata if provided
        if ($request->has('stato_chiamata') && $request->stato_chiamata !== '') {
            $query->where('stato_chiamata', $request->stato_chiamata);
        }

        // Filter by esito if provided
        if ($request->has('esito') && $request->esito !== '') {
            $query->where('esito', 'like', '%' . $request->esito . '%');
        }

        // Filter by utente if provided
        if ($request->has('utente') && $request->utente !== '') {
            $query->where('utente', 'like', '%' . $request->utente . '%');
        }

        // Filter by date range if provided
        if ($request->has('data_from') && $request->data_from !== '') {
            try {
                $dataFrom = Carbon::createFromFormat('Y-m-d', $request->data_from)->startOfDay();
                $query->where('data_inizio', '>=', $dataFrom);
            } catch (\Exception $e) {
                // Invalid date format, ignore filter
            }
        }

        if ($request->has('data_to') && $request->data_to !== '') {
            try {
                $dataTo = Carbon::createFromFormat('Y-m-d', $request->data_to)->endOfDay();
                $query->where('data_inizio', '<=', $dataTo);
            } catch (\Exception $e) {
                // Invalid date format, ignore filter
            }
        }

        // Handle sorting
        $sortBy = $request->get('sort_by', 'data_inizio');
        $sortDirection = $request->get('sort_direction', 'desc');

        // Validate sort parameters
        $allowedSortBy = ['numero_chiamato', 'data_inizio', 'durata', 'stato_chiamata', 'esito', 'utente', 'created_at'];
        $allowedDirections = ['asc', 'desc'];

        if (!in_array($sortBy, $allowedSortBy)) {
            $sortBy = 'data_inizio';
        }
        if (!in_array($sortDirection, $allowedDirections)) {
            $sortDirection = 'desc';
        }

        $query->orderBy($sortBy, $sortDirection);

        $calls = $query->paginate(15)->withQueryString();

        // Get unique values for filter dropdowns
        $companies = \App\Models\Company::orderBy('name')->get();
        $statoChiamataOptions = Call::distinct()->pluck('stato_chiamata')->filter()->sort()->values();
        $esitoOptions = Call::distinct()->pluck('esito')->filter()->sort()->values();
        $utenteOptions = Call::distinct()->pluck('utente')->filter()->sort()->values();

        return view('calls.index', compact('calls', 'companies', 'statoChiamataOptions', 'esitoOptions', 'utenteOptions', 'sortBy', 'sortDirection'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv',
        ]);

        try {
            Excel::import(new CallsImport, $request->file('file'));
            return redirect()->route('calls.index')->with('success', 'Calls imported successfully!');
        } catch (\Exception $e) {
            return redirect()->route('calls.index')->with('error', 'Error importing calls: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companies = \App\Models\Company::orderBy('name')->get();
        return view('calls.create', compact('companies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'numero_chiamato' => 'nullable|string|max:20',
            'data_inizio' => 'nullable|date',
            'durata' => 'nullable|string|max:10',
            'stato_chiamata' => 'nullable|string|max:50',
            'esito' => 'nullable|string|max:100',
            'utente' => 'nullable|string|max:255',
            'company_id' => 'nullable|string|max:36|exists:companies,id',
        ]);

        Call::create($data);
        return redirect()->route('calls.index')->with('success', 'Call created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Call $call)
    {
        return view('calls.show', compact('call'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Call $call)
    {
        $companies = \App\Models\Company::orderBy('name')->get();
        return view('calls.edit', compact('call', 'companies'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Call $call)
    {
        $data = $request->validate([
            'numero_chiamato' => 'nullable|string|max:20',
            'data_inizio' => 'nullable|date',
            'durata' => 'nullable|string|max:10',
            'stato_chiamata' => 'nullable|string|max:50',
            'esito' => 'nullable|string|max:100',
            'utente' => 'nullable|string|max:255',
            'company_id' => 'nullable|string|max:36|exists:companies,id',
        ]);

        $call->update($data);
        return redirect()->route('calls.index')->with('success', 'Call updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Call $call)
    {
        $call->delete();
        return redirect()->route('calls.index')->with('success', 'Call deleted successfully.');
    }
}
