<?php

namespace App\Http\Controllers;

use App\Models\Pratiche;
use Illuminate\Http\Request;
use App\Http\Requests\StorePraticheRequest;
use App\Http\Requests\UpdatePraticheRequest;

class PraticheCrudController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pratiches = Pratiche::orderBy('Data_inserimento', 'desc')->paginate(20);
        return view('pratiches-crud.index', compact('pratiches'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pratiches-crud.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePraticheRequest $request)
    {
        try {
            Pratiche::create($request->validated());
            return redirect()->route('pratiches-crud.index')
                ->with('success', 'Pratica creata con successo.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Errore durante la creazione della pratica: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Pratiche $pratiche)
    {
        return view('pratiches-crud.show', compact('pratiche'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pratiche $pratiche)
    {
        return view('pratiches-crud.edit', compact('pratiche'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePraticheRequest $request, Pratiche $pratiche)
    {
        try {
            $pratiche->update($request->validated());
            return redirect()->route('pratiches-crud.index')
                ->with('success', 'Pratica aggiornata con successo.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Errore durante l\'aggiornamento della pratica: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pratiche $pratiche)
    {
        try {
            $pratiche->delete();
            return redirect()->route('pratiches-crud.index')
                ->with('success', 'Pratica eliminata con successo.');
        } catch (\Exception $e) {
            return back()->with('error', 'Errore durante l\'eliminazione della pratica: ' . $e->getMessage());
        }
    }
}
