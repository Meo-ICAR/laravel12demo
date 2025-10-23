<?php

namespace App\Http\Controllers;

use App\Models\Enasarco;
use Illuminate\Http\Request;

class EnasarcoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $enasarco = Enasarco::latest()->paginate(10);
        return view('enasarco.index', compact('enasarco'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $enasarcoTypes = ['monomandatario', 'plurimandatario'];
        $currentYear = date('Y');
        return view('enasarco.create', compact('enasarcoTypes', 'currentYear'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'enasarco' => 'required|in:monomandatario,plurimandatario',
            'competenza' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'minimo' => 'required|numeric|min:0',
            'massimo' => 'required|numeric|min:0|gt:minimo',
            'minimale' => 'required|numeric|min:0',
            'massimale' => 'required|numeric|min:0|gt:minimale',
        ]);

        Enasarco::create($validated);

        return redirect()->route('enasarco.index')
            ->with('success', 'Record ENASARCO creato con successo.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Enasarco $enasarco)
    {
        $enasarcoTypes = ['monomandatario', 'plurimandatario'];
        $currentYear = date('Y');
        return view('enasarco.edit', compact('enasarco', 'enasarcoTypes', 'currentYear'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Enasarco $enasarco)
    {
        $validated = $request->validate([
            'enasarco' => 'required|in:monomandatario,plurimandatario',
            'competenza' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'minimo' => 'required|numeric|min:0',
            'massimo' => 'required|numeric|min:0|gt:minimo',
            'minimale' => 'required|numeric|min:0',
            'massimale' => 'required|numeric|min:0|gt:minimale',
        ]);

        $enasarco->update($validated);

        return redirect()->route('enasarco.index')
            ->with('success', 'Record ENASARCO aggiornato con successo.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Enasarco $enasarco)
    {
        $enasarco->delete();

        return redirect()->route('enasarco.index')
            ->with('success', 'Record ENASARCO eliminato con successo.');
    }
}
