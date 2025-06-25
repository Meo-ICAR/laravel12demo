<?php

namespace App\Http\Controllers;

use App\Models\Proforma;
use App\Models\Company;
use App\Models\Fornitori;
use App\Models\Provvigione;
use Illuminate\Http\Request;

class ProformaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $proformas = Proforma::with(['company', 'fornitore', 'provvigioni']);

        // Filtri testo
        if ($request->filled('fornitore')) {
            $proformas->whereHas('fornitore', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->fornitore . '%');
            });
        }
        if ($request->filled('stato')) {
            $proformas->where('stato', $request->stato);
        } else {
            $proformas->where('stato', 'Inserito');
        }
        if ($request->filled('emailsubject')) {
            $proformas->where('emailsubject', 'like', '%' . $request->emailsubject . '%');
        }
        if ($request->filled('sended_at')) {
            $proformas->whereDate('sended_at', $request->sended_at);
        }
        if ($request->filled('paid_at')) {
            $proformas->whereDate('paid_at', $request->paid_at);
        }

        // Range compenso
        if ($request->filled('compenso_min')) {
            $proformas->whereRaw('(SELECT COALESCE(SUM(importo),0) FROM proforma_provvigione pp JOIN provvigioni p ON pp.provvigione_id = p.id WHERE pp.proforma_id = proformas.id) >= ?', [$request->compenso_min]);
        }
        if ($request->filled('compenso_max')) {
            $proformas->whereRaw('(SELECT COALESCE(SUM(importo),0) FROM proforma_provvigione pp JOIN provvigioni p ON pp.provvigione_id = p.id WHERE pp.proforma_id = proformas.id) <= ?', [$request->compenso_max]);
        }
        // Range totale (compenso+contributo-anticipo)
        if ($request->filled('totale_min')) {
            $proformas->whereRaw('((SELECT COALESCE(SUM(importo),0) FROM proforma_provvigione pp JOIN provvigioni p ON pp.provvigione_id = p.id WHERE pp.proforma_id = proformas.id) + COALESCE(contributo,0) - COALESCE(anticipo,0)) >= ?', [$request->totale_min]);
        }
        if ($request->filled('totale_max')) {
            $proformas->whereRaw('((SELECT COALESCE(SUM(importo),0) FROM proforma_provvigione pp JOIN provvigioni p ON pp.provvigione_id = p.id WHERE pp.proforma_id = proformas.id) + COALESCE(contributo,0) - COALESCE(anticipo,0)) <= ?', [$request->totale_max]);
        }

        $proformas = $proformas->paginate(15)->appends($request->query());
        return view('proformas.index', compact('proformas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companies = Company::all();
        $fornitoris = Fornitori::all();
        $provvigioni = Provvigione::all();
        return view('proformas.create', compact('companies', 'fornitoris', 'provvigioni'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'company_id' => 'nullable|string|exists:companies,id',
            'fornitori_id' => 'nullable|string|exists:fornitoris,id',
            'stato' => 'nullable|string|max:255',
            'anticipo' => 'nullable|numeric',
            'anticipo_descrizione' => 'nullable|string|max:255',
            'compenso_descrizione' => 'nullable|string',
            'contributo' => 'nullable|numeric',
            'contributo_descrizione' => 'nullable|string|max:255',
            'annotation' => 'nullable|string',
            'sended_at' => 'nullable|date',
            'paid_at' => 'nullable|date',
            'emailsubject' => 'nullable|string|max:255',
            'emailbody' => 'nullable|string',
            'emailto' => 'nullable|string|max:255',
            'emailfrom' => 'nullable|string|max:255',
            'provvigioni' => 'nullable|array',
            'provvigioni.*' => 'uuid|exists:provvigioni,id',
        ]);
        $provvigioni = $data['provvigioni'] ?? [];
        unset($data['provvigioni']);
        $proforma = Proforma::create($data);
        $proforma->provvigioni()->sync($provvigioni);
        return redirect()->route('proformas.index')->with('success', 'Proforma created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Proforma $proforma)
    {
        $proforma->load(['company', 'fornitore', 'provvigioni']);
        return view('proformas.show', compact('proforma'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Proforma $proforma)
    {
        $companies = Company::all();
        $fornitoris = Fornitori::all();
        $provvigioni = Provvigione::all();
        $proforma->load('provvigioni');
        return view('proformas.edit', compact('proforma', 'companies', 'fornitoris', 'provvigioni'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Proforma $proforma)
    {
        $data = $request->validate([
            'company_id' => 'nullable|string|exists:companies,id',
            'fornitori_id' => 'nullable|string|exists:fornitoris,id',
            'stato' => 'nullable|string|max:255',
            'anticipo' => 'nullable|numeric',
            'anticipo_descrizione' => 'nullable|string|max:255',
            'compenso_descrizione' => 'nullable|string',
            'contributo' => 'nullable|numeric',
            'contributo_descrizione' => 'nullable|string|max:255',
            'annotation' => 'nullable|string',
            'sended_at' => 'nullable|date',
            'paid_at' => 'nullable|date',
            'emailsubject' => 'nullable|string|max:255',
            'emailbody' => 'nullable|string',
            'emailto' => 'nullable|string|max:255',
            'emailfrom' => 'nullable|string|max:255',
            'provvigioni' => 'nullable|array',
            'provvigioni.*' => 'uuid|exists:provvigioni,id',
        ]);
        $provvigioni = $data['provvigioni'] ?? [];
        unset($data['provvigioni']);
        $proforma->update($data);
        $proforma->provvigioni()->sync($provvigioni);
        return redirect()->route('proformas.index')->with('success', 'Proforma updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Proforma $proforma)
    {
        try {
            // Get associated provvigioni IDs before deletion
            $provvigioniIds = $proforma->provvigioni->pluck('id')->toArray();

            // Restore provvigioni status to 'Inserito'
            if (!empty($provvigioniIds)) {
                \App\Models\Provvigione::whereIn('id', $provvigioniIds)
                    ->where('stato', 'Proforma')
                    ->update(['stato' => 'Inserito']);
            }

            // Delete the proforma (this will cascade delete proforma_provvigione records)
            $proforma->delete();

            return redirect()->route('proformas.index')->with('success', 'Proforma deleted successfully. Associated provvigioni restored to \'Inserito\' status.');
        } catch (\Exception $e) {
            return redirect()->route('proformas.index')->with('error', 'Error deleting proforma: ' . $e->getMessage());
        }
    }

    public function sendEmail(Request $request, Proforma $proforma)
    {
        try {
            // DEBUG: Use fixed email for testing
            $recipientEmail = 'hassistosrl@gmail.com';

            // Prepare email content
            $subject = $proforma->emailsubject ?: 'Proforma Details - ' . ($proforma->fornitore->name ?? 'Unknown');
            $body = $proforma->emailbody ?: $this->generateDefaultEmailContent($proforma);
            $from = $proforma->emailfrom ?: config('mail.from.address');

            // Send email
            \Mail::send([], [], function ($message) use ($proforma, $subject, $body, $from, $recipientEmail) {
                $message->from($from)
                        ->to($recipientEmail)
                        ->subject($subject)
                        ->html($body);
            });

            // Update sended_at timestamp
            $proforma->update(['sended_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'Email sent successfully to ' . $recipientEmail
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage()
            ]);
        }
    }

    private function generateDefaultEmailContent(Proforma $proforma)
    {
        $fornitoreName = $proforma->fornitore->name ?? 'Unknown';
        $totalImporto = $proforma->provvigioni->sum('importo');
        $provvigioniCount = $proforma->provvigioni->count();

        return "
        <h2>Proforma Details</h2>
        <p><strong>Fornitore:</strong> {$fornitoreName}</p>
        <p><strong>Compenso:</strong> € " . number_format($proforma->compenso, 2, ',', '.') . "</p>
        <p><strong>Contributo:</strong> € " . number_format($proforma->contributo ?? 0, 2, ',', '.') . "</p>
        <p><strong>Anticipo:</strong> € " . number_format($proforma->anticipo ?? 0, 2, ',', '.') . "</p>
        <p><strong>Totale Provvigioni:</strong> € " . number_format($totalImporto, 2, ',', '.') . " ({$provvigioniCount} records)</p>
        <p><strong>Stato:</strong> {$proforma->stato}</p>
        " . ($proforma->annotation ? "<p><strong>Note:</strong> {$proforma->annotation}</p>" : "");
    }
}
