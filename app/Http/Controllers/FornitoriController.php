<?php

namespace App\Http\Controllers;

use App\Models\Fornitori;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FornitoriController extends Controller
{
    public function index()
    {
        $fornitoris = Fornitori::paginate(10);
        return view('fornitoris.index', compact('fornitoris'));
    }

    public function create()
    {
        return view('fornitoris.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'codice' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'piva' => 'nullable|string|max:16',
            'email' => 'nullable|string|max:255',
            'operatore' => 'nullable|string|max:255',
            'iscollaboratore' => 'nullable|string|max:255',
            'isdipendente' => 'nullable|string|max:255',
            'regione' => 'nullable|string|max:255',
            'citta' => 'nullable|string|max:255',
            'company_id' => 'required|string|max:36',
        ]);
        $data['id'] = (string) Str::uuid();
        Fornitori::create($data);
        return redirect()->route('fornitoris.index')->with('success', 'Fornitore created successfully.');
    }

    public function show(Fornitori $fornitori)
    {
        return view('fornitoris.show', compact('fornitori'));
    }

    public function edit(Fornitori $fornitori)
    {
        return view('fornitoris.edit', compact('fornitori'));
    }

    public function update(Request $request, Fornitori $fornitori)
    {
        $data = $request->validate([
            'codice' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'piva' => 'nullable|string|max:16',
            'email' => 'nullable|string|max:255',
            'operatore' => 'nullable|string|max:255',
            'iscollaboratore' => 'nullable|string|max:255',
            'isdipendente' => 'nullable|string|max:255',
            'regione' => 'nullable|string|max:255',
            'citta' => 'nullable|string|max:255',
            'company_id' => 'required|string|max:36',
        ]);
        $fornitori->update($data);
        return redirect()->route('fornitoris.index')->with('success', 'Fornitore updated successfully.');
    }

    public function destroy(Fornitori $fornitori)
    {
        $fornitori->delete();
        return redirect()->route('fornitoris.index')->with('success', 'Fornitore deleted successfully.');
    }
}
