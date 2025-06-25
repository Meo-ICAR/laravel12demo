<?php

namespace App\Http\Controllers;

use App\Models\Clienti;
use App\Models\Customertype;
use Illuminate\Http\Request;

class ClientiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clientis = Clienti::with('customertype')->get();

        // Add invoice count for each clienti
        foreach ($clientis as $clienti) {
            if ($clienti->coge) {
                $clienti->invoice_count = \App\Models\Invoice::where('coge', $clienti->coge)->count();
            } else {
                $clienti->invoice_count = 0;
            }
        }

        return view('clientis.index', compact('clientis'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customertypes = Customertype::all();
        return view('clientis.create', compact('customertypes'));
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
            'cf' => 'nullable|string',
            'coge' => 'nullable|string',
            'email' => 'nullable|email',
            'regione' => 'nullable|string',
            'citta' => 'nullable|string',
            'company_id' => 'nullable|string',
            'customertype_id' => 'nullable|exists:customertypes,id',
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
        $customertypes = Customertype::all();
        return view('clientis.edit', compact('clienti', 'customertypes'));
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
            'cf' => 'nullable|string',
            'coge' => 'nullable|string',
            'email' => 'nullable|email',
            'regione' => 'nullable|string',
            'citta' => 'nullable|string',
            'company_id' => 'nullable|string',
            'customertype_id' => 'nullable|exists:customertypes,id',
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
