<?php

namespace App\Http\Controllers;

use App\Models\Coges;
use Illuminate\Http\Request;

class CogesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $coges = Coges::latest()->paginate(15);
        return view('coges.index', compact('coges'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $coge = new Coges();
        return view('coges.create', compact('coge'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fonte' => 'required|string|max:255',
            'conto_dare' => 'required|string|max:255',
            'descrizione_dare' => 'required|string|max:255',
            'conto_avere' => 'required|string|max:255',
            'descrizione_avere' => 'required|string|max:255',
            'annotazioni' => 'nullable|string|max:255',
        ]);

        $coge = Coges::create($validated);

        return redirect()
            ->route('coges.show', $coge)
            ->with('success', 'Coge creato con successo!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Coges $coge)
    {
        return view('coges.show', compact('coge'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Coges $coge)
    {
        return view('coges.edit', compact('coge'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Coges $coge)
    {
        $validated = $request->validate([
            'fonte' => 'required|string|max:255',
            'conto_dare' => 'required|string|max:255',
            'descrizione_dare' => 'required|string|max:255',
            'conto_avere' => 'required|string|max:255',
            'descrizione_avere' => 'required|string|max:255',
            'annotazioni' => 'nullable|string|max:255',
        ]);

        $coge->update($validated);

        return redirect()
            ->route('coges.show', $coge)
            ->with('success', 'Coge aggiornato con successo!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Coges $coge)
    {
        $coge->delete();
        
        return redirect()
            ->route('coges.index')
            ->with('success', 'Coge eliminato con successo!');
    }
}
