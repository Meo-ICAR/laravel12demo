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

        // Create the proforma
        $proforma = Proforma::create($data);
        $proforma->provvigioni()->sync($provvigioni);

        // Reload the proforma with relationships to get updated data
        $proforma->load(['fornitore', 'provvigioni']);

        // Generate email body with the same logic as update
        $data['emailbody'] = $this->generateDefaultEmailContent($proforma);
        $proforma->update(['emailbody' => $data['emailbody']]);

        return redirect()->route('proformas.index')->with('success', 'Proforma created successfully. Email body generated.');
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

        // Update the proforma
        $proforma->update($data);
        $proforma->provvigioni()->sync($provvigioni);

        // Reload the proforma with relationships to get updated data
        $proforma->load(['fornitore', 'provvigioni']);

        // Regenerate email body with updated values
        $data['emailbody'] = $this->generateDefaultEmailContent($proforma);
        $proforma->update(['emailbody' => $data['emailbody']]);

        return redirect()->route('proformas.index')->with('success', 'Proforma updated successfully. Email body regenerated with updated values.');
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
        $totale = $totalImporto + ($proforma->contributo ?? 0) - ($proforma->anticipo ?? 0);

        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 800px; margin: 0 auto; padding: 20px; }
                .header { background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
                .section { margin-bottom: 20px; }
                .section h3 { color: #007bff; border-bottom: 2px solid #007bff; padding-bottom: 5px; }
                table { width: 100%; border-collapse: collapse; margin: 10px 0; }
                th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
                th { background-color: #f8f9fa; font-weight: bold; }
                .amount { text-align: right; font-weight: bold; }
                .total { background-color: #e9ecef; font-weight: bold; }
                .positive { color: #28a745; }
                .negative { color: #dc3545; }
                .info-box { background-color: #d1ecf1; border: 1px solid #bee5eb; border-radius: 5px; padding: 15px; margin: 10px 0; }
                .note { background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px; padding: 15px; margin: 10px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>📋 Proforma Details</h2>
                    <p><strong>Proforma ID:</strong> #{$proforma->id}</p>
                    <p><strong>Date:</strong> " . now()->format('d/m/Y H:i') . "</p>
                </div>

                <div class='section'>
                    <h3>🏢 Fornitore Information</h3>
                    <p><strong>Nome:</strong> {$fornitoreName}</p>
                </div>

                <div class='section'>
                    <h3>💰 Financial Summary</h3>
                    <table>
                        <tr>
                            <th>Description</th>
                            <th class='amount'>Amount</th>
                        </tr>
                        <tr>
                            <td>Compenso (from Provvigioni)</td>
                            <td class='amount positive'>€ " . number_format($totalImporto, 2, ',', '.') . "</td>
                        </tr>";

        if ($proforma->contributo) {
            $html .= "
                        <tr>
                            <td>Contributo" . ($proforma->contributo_descrizione ? " - {$proforma->contributo_descrizione}" : "") . "</td>
                            <td class='amount positive'>€ " . number_format($proforma->contributo, 2, ',', '.') . "</td>
                        </tr>";
        }

        if ($proforma->anticipo) {
            $html .= "
                        <tr>
                            <td>Anticipo" . ($proforma->anticipo_descrizione ? " - {$proforma->anticipo_descrizione}" : "") . "</td>
                            <td class='amount negative'>€ " . number_format($proforma->anticipo, 2, ',', '.') . "</td>
                        </tr>";
        }

        $html .= "
                        <tr class='total'>
                            <td><strong>TOTALE</strong></td>
                            <td class='amount " . ($totale >= 0 ? 'positive' : 'negative') . "'><strong>€ " . number_format($totale, 2, ',', '.') . "</strong></td>
                        </tr>
                    </table>
                </div>

                <div class='section'>
                    <h3>📊 Provvigioni Details</h3>
                    <div class='info-box'>
                        <p><strong>Total Provvigioni:</strong> {$provvigioniCount} records</p>
                        <p><strong>Total Amount:</strong> € " . number_format($totalImporto, 2, ',', '.') . "</p>
                    </div>";

        if ($proforma->compenso_descrizione) {
            $html .= "
                    <div class='note'>
                        <p><strong>Compenso Description:</strong></p>
                        <p>" . nl2br(htmlspecialchars($proforma->compenso_descrizione)) . "</p>
                    </div>";
        }

        $html .= "
                </div>

                <div class='section'>
                    <h3>📋 Status Information</h3>
                    <p><strong>Status:</strong> <span style='background-color: #007bff; color: white; padding: 5px 10px; border-radius: 3px;'>{$proforma->stato}</span></p>
                </div>";

        if ($proforma->annotation) {
            $html .= "
                <div class='section'>
                    <h3>📝 Notes</h3>
                    <div class='note'>
                        <p>" . nl2br(htmlspecialchars($proforma->annotation)) . "</p>
                    </div>
                </div>";
        }

        $html .= "
            </div>
        </body>
        </html>";

        return $html;
    }
}
