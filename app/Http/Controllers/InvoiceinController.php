<?php

namespace App\Http\Controllers;

use App\Models\Invoicein;
use Illuminate\Http\Request;

class InvoiceinController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoicein::with('invoice');
        if ($request->filled('nome_fornitore')) {
            $nome = $request->input('nome_fornitore');
            $query->where('nome_fornitore', 'LIKE', "%$nome%");
        }
        if ($request->filled('tipo_di_documento')) {
            $tipo_di_documento = $request->input('tipo_di_documento');
            $query->where('tipo_di_documento',  $tipo_di_documento);
        }
        $invoiceins = $query->paginate(20);
        return view('invoiceins.index', compact('invoiceins'));
    }

    public function create()
    {
        return view('invoiceins.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            // Add validation rules for all fields as needed
            'nome_fornitore' => 'nullable|string',
        ]);
        Invoicein::create($request->all());
        return redirect()->route('invoiceins.index')->with('success', 'Invoicein created successfully.');
    }

    public function show(Invoicein $invoicein)
    {
        return view('invoiceins.show', compact('invoicein'));
    }

    public function edit(Invoicein $invoicein)
    {
        return view('invoiceins.edit', compact('invoicein'));
    }

    public function update(Request $request, Invoicein $invoicein)
    {
        $data = $request->validate([
            // Add validation rules for all fields as needed
            'nome_fornitore' => 'nullable|string',
        ]);
        $invoicein->update($request->all());
        return redirect()->route('invoiceins.index')->with('success', 'Invoicein updated successfully.');
    }

    public function destroy(Invoicein $invoicein)
    {
        $invoicein->delete();
        return redirect()->route('invoiceins.index')->with('success', 'Invoicein deleted successfully.');
    }
}
