<?php

namespace App\Http\Controllers;

use App\Models\Proforma;
use App\Models\Company;
use App\Models\Fornitori;
use App\Models\Provvigione;
use Illuminate\Http\Request;

class ProformaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $proformas = Proforma::with(['company', 'fornitori', 'provvigioni'])->paginate(15);
        return view('proformas.index', compact('proformas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companies = Company::all();
        $fornitoris = Fornitori::all();
        $provvigioni = Provvigione::all();
        return view('proformas.create', compact('companies', 'fornitoris', 'provvigioni'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'company_id' => 'nullable|string|exists:companies,id',
            'fornitori_id' => 'nullable|string|exists:fornitoris,id',
            'stato' => 'nullable|string|max:255',
            'anticipo' => 'nullable|numeric',
            'anticipo_descrizione' => 'nullable|string|max:255',
            'compenso_descrizione' => 'nullable|string',
            'contributo' => 'nullable|numeric',
            'contributo_descrizione' => 'nullable|string|max:255',
            'annotation' => 'nullable|string',
            'sended_at' => 'nullable|date',
            'paid_at' => 'nullable|date',
            'emailsubject' => 'nullable|string|max:255',
            'emailbody' => 'nullable|string',
            'emailto' => 'nullable|string|max:255',
            'emailfrom' => 'nullable|string|max:255',
            'provvigioni' => 'nullable|array',
            'provvigioni.*' => 'uuid|exists:provvigioni,id',
        ]);
        $provvigioni = $data['provvigioni'] ?? [];
        unset($data['provvigioni']);
        $proforma = Proforma::create($data);
        $proforma->provvigioni()->sync($provvigioni);
        return redirect()->route('proformas.index')->with('success', 'Proforma created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Proforma $proforma)
    {
        $proforma->load(['company', 'fornitori', 'provvigioni']);
        return view('proformas.show', compact('proforma'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Proforma $proforma)
    {
        $companies = Company::all();
        $fornitoris = Fornitori::all();
        $provvigioni = Provvigione::all();
        $proforma->load('provvigioni');
        return view('proformas.edit', compact('proforma', 'companies', 'fornitoris', 'provvigioni'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Proforma $proforma)
    {
        $data = $request->validate([
            'company_id' => 'nullable|string|exists:companies,id',
            'fornitori_id' => 'nullable|string|exists:fornitoris,id',
            'stato' => 'nullable|string|max:255',
            'anticipo' => 'nullable|numeric',
            'anticipo_descrizione' => 'nullable|string|max:255',
            'compenso_descrizione' => 'nullable|string',
            'contributo' => 'nullable|numeric',
            'contributo_descrizione' => 'nullable|string|max:255',
            'annotation' => 'nullable|string',
            'sended_at' => 'nullable|date',
            'paid_at' => 'nullable|date',
            'emailsubject' => 'nullable|string|max:255',
            'emailbody' => 'nullable|string',
            'emailto' => 'nullable|string|max:255',
            'emailfrom' => 'nullable|string|max:255',
            'provvigioni' => 'nullable|array',
            'provvigioni.*' => 'uuid|exists:provvigioni,id',
        ]);
        $provvigioni = $data['provvigioni'] ?? [];
        unset($data['provvigioni']);
        $proforma->update($data);
        $proforma->provvigioni()->sync($provvigioni);
        return redirect()->route('proformas.index')->with('success', 'Proforma updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Proforma $proforma)
    {
        $proforma->delete();
        return redirect()->route('proformas.index')->with('success', 'Proforma deleted successfully.');
    }
}
