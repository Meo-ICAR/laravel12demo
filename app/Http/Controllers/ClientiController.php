<?php

namespace App\Http\Controllers;

use App\Models\Clienti;
use Illuminate\Http\Request;

class ClientiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clientis = Clienti::all();
        return view('clientis.index', compact('clientis'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('clientis.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'codice' => 'nullable|string',
            'name' => 'nullable|string',
            'piva' => 'nullable|string',
            'email' => 'nullable|email',
            'iscollaboratore' => 'nullable|string',
            'isdipendente' => 'nullable|string',
            'regione' => 'nullable|string',
            'citta' => 'nullable|string',
            'company_id' => 'nullable|string',
        ]);
        Clienti::create($data);
        return redirect()->route('clientis.index')->with('success', 'Cliente created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Clienti $clienti)
    {
        return view('clientis.show', compact('clienti'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Clienti $clienti)
    {
        return view('clientis.edit', compact('clienti'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Clienti $clienti)
    {
        $data = $request->validate([
            'codice' => 'nullable|string',
            'name' => 'nullable|string',
            'piva' => 'nullable|string',
            'email' => 'nullable|email',
            'iscollaboratore' => 'nullable|string',
            'isdipendente' => 'nullable|string',
            'regione' => 'nullable|string',
            'citta' => 'nullable|string',
            'company_id' => 'nullable|string',
        ]);
        $clienti->update($data);
        return redirect()->route('clientis.index')->with('success', 'Cliente updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Clienti $clienti)
    {
        $clienti->delete();
        return redirect()->route('clientis.index')->with('success', 'Cliente deleted successfully.');
    }
}
