<?php

namespace App\Http\Controllers;

use App\Models\Fornitori;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FornitoriController extends Controller
{
    public function index(Request $request)
    {
        $query = Fornitori::query();

        // Filter by name if provided
        if ($request->has('name') && $request->name !== '') {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        // Handle sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortDirection = $request->get('sort_direction', 'asc');

        // Validate sort parameters
        $allowedSortBy = ['name', 'codice', 'piva', 'email', 'regione', 'citta', 'created_at'];
        $allowedDirections = ['asc', 'desc'];

        if (!in_array($sortBy, $allowedSortBy)) {
            $sortBy = 'name';
        }
        if (!in_array($sortDirection, $allowedDirections)) {
            $sortDirection = 'asc';
        }

        $query->orderBy($sortBy, $sortDirection);

        $fornitoris = $query->paginate(10)->withQueryString();
        return view('fornitoris.index', compact('fornitoris', 'sortBy', 'sortDirection'));
    }

    public function create()
    {
        $companies = \App\Models\Company::orderBy('name')->get();
        return view('fornitoris.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'codice' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'piva' => 'nullable|string|max:16',
            'email' => 'nullable|string|max:255',
            'operatore' => 'nullable|string|max:255',
            'iscollaboratore' => 'nullable|boolean',
            'isdipendente' => 'nullable|boolean',
            'regione' => 'nullable|string|max:255',
            'citta' => 'nullable|string|max:255',
            'company_id' => 'nullable|string|max:36|exists:companies,id',
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
        $companies = \App\Models\Company::orderBy('name')->get();
        return view('fornitoris.edit', compact('fornitori', 'companies'));
    }

    public function update(Request $request, Fornitori $fornitori)
    {
        $data = $request->validate([
            'codice' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'piva' => 'nullable|string|max:16',
            'email' => 'nullable|string|max:255',
            'operatore' => 'nullable|string|max:255',
            'iscollaboratore' => 'nullable|boolean',
            'isdipendente' => 'nullable|boolean',
            'regione' => 'nullable|string|max:255',
            'citta' => 'nullable|string|max:255',
            'company_id' => 'nullable|string|max:36|exists:companies,id',
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
