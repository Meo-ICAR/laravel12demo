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
    public function index(Request $request)
    {
        $query = Clienti::with('customertype');

        // Filtering
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        if ($request->filled('piva')) {
            $query->where('piva', 'like', '%' . $request->piva . '%');
        }
        if ($request->filled('coge')) {
            $query->where('coge', 'like', '%' . $request->coge . '%');
        }
        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }
        if ($request->filled('regione')) {
            $query->where('regione', 'like', '%' . $request->regione . '%');
        }
        if ($request->filled('citta')) {
            $query->where('citta', 'like', '%' . $request->citta . '%');
        }
        if ($request->filled('codice')) {
            $query->where('codice', 'like', '%' . $request->codice . '%');
        }
        if ($request->filled('customertype_id')) {
            $query->where('customertype_id', $request->customertype_id);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortDirection = $request->get('sort_direction', 'asc');
        $allowedSortBy = ['name', 'piva', 'coge', 'email', 'regione', 'citta', 'codice'];
        $allowedDirections = ['asc', 'desc'];
        if (!in_array($sortBy, $allowedSortBy)) {
            $sortBy = 'name';
        }
        if (!in_array($sortDirection, $allowedDirections)) {
            $sortDirection = 'asc';
        }
        $query->orderBy($sortBy, $sortDirection);

        $clientis = $query->paginate(20)->appends($request->query());

        // Add invoice count for each clienti
        foreach ($clientis as $clienti) {
            if ($clienti->coge) {
                $clienti->invoice_count = \App\Models\Invoice::where('coge', $clienti->coge)->count();
            } else {
                $clienti->invoice_count = 0;
            }
        }

        $customertypes = \App\Models\Customertype::all();
        return view('clientis.index', compact('clientis', 'customertypes', 'sortBy', 'sortDirection'));
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

    public function importInvoiceinsToInvoicesByClienti()
    {
        try {
            $service = new \App\Services\InvoiceService();
            $result = $service->transferInvoiceinsToInvoicesByClienti();

            $message = "Transfer (by clientis) completed! ";
            $message .= "New invoices created: {$result['imported']}, ";
            $message .= "Skipped (already exist): {$result['skipped']}.";
            if (!empty($result['errors'])) {
                $message .= " Errors: " . implode('; ', $result['errors']);
            }
            return redirect()->route('clientis.index')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('clientis.index')->with('error', 'Error during transfer (by clientis): ' . $e->getMessage());
        }
    }
}
