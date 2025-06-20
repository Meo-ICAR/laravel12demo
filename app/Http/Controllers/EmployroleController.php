<?php

namespace App\Http\Controllers;

use App\Models\Employrole;
use Illuminate\Http\Request;

class EmployroleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $employroles = Employrole::all();
        return view('employroles.index', compact('employroles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('employroles.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'company_id' => 'nullable|string|max:36',
        ]);
        Employrole::create($data);
        return redirect()->route('employroles.index')->with('success', 'Employrole created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Employrole $employrole)
    {
        return view('employroles.show', compact('employrole'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employrole $employrole)
    {
        return view('employroles.edit', compact('employrole'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employrole $employrole)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'company_id' => 'nullable|string|max:36',
        ]);
        $employrole->update($data);
        return redirect()->route('employroles.index')->with('success', 'Employrole updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employrole $employrole)
    {
        $employrole->delete();
        return redirect()->route('employroles.index')->with('success', 'Employrole deleted successfully.');
    }
}
