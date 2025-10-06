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

        // Generate and update email body
        $this->updateEmailBody($proforma);

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

        $provvigioni = $data['provvigioni'] ?? null;
        unset($data['provvigioni']);

        // Update the proforma
        $proforma->update($data);
        if ($provvigioni !== null) {
            $proforma->provvigioni()->sync($provvigioni);
        }

        // If sended_at is being updated, update all associated provvigioni
        if (array_key_exists('sended_at', $data) && $data['sended_at']) {
            $proforma->provvigioni()->update(['sended_at' => $data['sended_at']]);
        }

        // Generate and update email body
        $this->updateEmailBody($proforma);

        return redirect()->route('proformas.index')->with('success', 'Proforma updated successfully. Email body regenerated with updated values.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Proforma $proforma)
    {
        try {
            // Start a database transaction
            \DB::beginTransaction();

            // Get associated provvigioni IDs before deletion
            $provvigioniIds = $proforma->provvigioni->pluck('id')->toArray();

            // First, detach all provvigioni to ensure pivot table is cleaned up
            $proforma->provvigioni()->detach();

            // Restore provvigioni status to 'Inserito'
            if (!empty($provvigioniIds)) {
                \App\Models\Provvigione::whereIn('id', $provvigioniIds)
                    ->where('stato', 'Proforma')
                    ->update(['stato' => 'Inserito']);
            }

            // Force delete the proforma (bypass soft delete)
            $proforma->forceDelete();

            // Commit the transaction
            \DB::commit();

            return redirect()->route('proformas.index')
                ->with('success', 'Proforma deleted successfully. Associated provvigioni restored to \'Inserito\' status.');

        } catch (\Exception $e) {
            // Rollback the transaction on error
            \DB::rollBack();
            \Log::error('Error deleting proforma: ' . $e->getMessage());
            return redirect()->route('proformas.index')
                ->with('error', 'Error deleting proforma: ' . $e->getMessage());
        }
    }

    public function sendEmail(Request $request, Proforma $proforma)
    {
        try {
            // DEBUG: Use fixed email for testing
            $recipientEmail = 'hassistosrl@gmail.com';

            // Prepare email content
            $subject = ($proforma->emailsubject . ($proforma->fornitore->name ?? 'Unknown')) ;
            $body = $proforma->emailbody ?: $this->generateDefaultEmailContent($proforma);
            $from = $proforma->emailfrom ?: config('mail.from.address');

            // DEBUG MODE: Log email details instead of sending
            \Log::info('DEBUG MODE: Original email method would be sent', [
                'proforma_id' => $proforma->id,
                'from' => $from,
                'to' => $recipientEmail,
                'subject' => $subject,
                'body_length' => strlen($body),
                'timestamp' => now()->toDateTimeString()
            ]);

            // In debug mode, we skip the actual email sending
            // \Mail::send([], [], function ($message) use ($proforma, $subject, $body, $from, $recipientEmail) {
            //     $message->from($from)
            //             ->to($recipientEmail)
            //             ->subject($subject)
            //             ->html($body);
            // });

            // Update sended_at timestamp and set status to 'inviato' (even in debug mode to simulate success)
            $proforma->update([
                'sended_at' => now(),
                'stato' => 'Spedito'
            ]);

            $message = 'DEBUG MODE: Email simulation successful to ' . $recipientEmail . ' (not actually sent)';

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message
                ]);
            }

            return redirect()->route('proformas.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            $errorMessage = 'Failed to process email: ' . $e->getMessage();
            \Log::error('Email sending error: ' . $errorMessage);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ]);
            }

            return redirect()->route('proformas.index')
                ->with('error', $errorMessage);
        }
    }

    /**
     * Send email using proforma fields directly
     */
    public function sendProformaEmail(Request $request, Proforma $proforma)
    {
        try {
            // Validate required fields
            if (empty($proforma->emailto)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email recipient (emailto) is required'
                ]);
            }

            if (empty($proforma->emailsubject)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email subject (emailsubject) is required'
                ]);
            }

            if (empty($proforma->emailbody)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email body (emailbody) is required'
                ]);
            }

            // Use proforma fields for email
            $from = $proforma->emailfrom ?: config('mail.from.address');
            $to = $proforma->emailto;
            $subject = $proforma->emailsubject;
            $body = $proforma->emailbody;

            // DEBUG MODE: Log email details instead of sending
            \Log::info('DEBUG MODE: Email would be sent', [
                'proforma_id' => $proforma->id,
                'from' => $from,
                'to' => $to,
                'subject' => $subject,
                'body_length' => strlen($body),
                'timestamp' => now()->toDateTimeString()
            ]);

            // In debug mode, we skip the actual email sending
            // \Mail::send([], [], function ($message) use ($from, $to, $subject, $body) {
            //     $message->from($from)
            //             ->to($to)
            //             ->subject($subject)
            //             ->html($body);
            // });

            // Update sended_at timestamp and set status to 'inviato' (even in debug mode to simulate success)
            $proforma->update([
                'sended_at' => now(),
                 'stato' => 'Spedito'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'DEBUG MODE: Email simulation successful to ' . $to . ' (not actually sent)'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process email: ' . $e->getMessage()
            ]);
        }
         return redirect()->route('proformas.index')
                ->with('success', $message);
    }

    /**
     * Send emails to multiple proformas
     */
    public function sendBulkEmails(Request $request)
    {
        try {
            $proformaIds = $request->input('proforma_ids', []);

            if (empty($proformaIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No proformas selected'
                ]);
            }

            $results = [];
            $successCount = 0;
            $errorCount = 0;

            foreach ($proformaIds as $proformaId) {
                $proforma = Proforma::find($proformaId);

                if (!$proforma) {
                    $results[] = [
                        'proforma_id' => $proformaId,
                        'success' => false,
                        'message' => 'Proforma not found'
                    ];
                    $errorCount++;
                    continue;
                }

                // Check if email was already sent
                if ($proforma->sended_at) {
                    $results[] = [
                        'proforma_id' => $proformaId,
                        'success' => false,
                        'message' => 'Email already sent on ' . $proforma->sended_at->format('d/m/Y H:i')
                    ];
                    $errorCount++;
                    continue;
                }

                // Validate required fields
                if (empty($proforma->emailto)) {
                    $results[] = [
                        'proforma_id' => $proformaId,
                        'success' => false,
                        'message' => 'Email recipient (emailto) is required'
                    ];
                    $errorCount++;
                    continue;
                }

                if (empty($proforma->emailsubject)) {
                    $results[] = [
                        'proforma_id' => $proformaId,
                        'success' => false,
                        'message' => 'Email subject (emailsubject) is required'
                    ];
                    $errorCount++;
                    continue;
                }

                if (empty($proforma->emailbody)) {
                    $results[] = [
                        'proforma_id' => $proformaId,
                        'success' => false,
                        'message' => 'Email body (emailbody) is required'
                    ];
                    $errorCount++;
                    continue;
                }

                try {
                    // Send email using proforma fields
                    $from = $proforma->emailfrom ?: config('mail.from.address');
                    $to = $proforma->emailto;
                    $subject = $proforma->emailsubject;
                    $body = $proforma->emailbody;

                    // DEBUG MODE: Log email details instead of sending
                    \Log::info('DEBUG MODE: Bulk email would be sent', [
                        'proforma_id' => $proformaId,
                        'from' => $from,
                        'to' => $to,
                        'subject' => $subject,
                        'body_length' => strlen($body),
                        'timestamp' => now()->toDateTimeString()
                    ]);

                    // In debug mode, we skip the actual email sending
                    // \Mail::send([], [], function ($message) use ($from, $to, $subject, $body) {
                    //     $message->from($from)
                    //             ->to($to)
                    //             ->subject($subject)
                    //             ->html($body);
                    // });

                    // Update sended_at timestamp (even in debug mode to simulate success)
                       // Update sended_at timestamp and set status to 'inviato' (even in debug mode to simulate success)
                    $proforma->update([
                        'sended_at' => now(),
                        'stato' => 'Spedito'
                    ]);

                    $results[] = [
                        'proforma_id' => $proformaId,
                        'success' => true,
                        'message' => 'DEBUG MODE: Email simulation successful to ' . $to . ' (not actually sent)'
                    ];
                    $successCount++;

                } catch (\Exception $e) {
                    $results[] = [
                        'proforma_id' => $proformaId,
                        'success' => false,
                        'message' => 'Failed to process email: ' . $e->getMessage()
                    ];
                    $errorCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Bulk email operation completed. Success: {$successCount}, Errors: {$errorCount}",
                'results' => $results,
                'summary' => [
                    'total' => count($proformaIds),
                    'success' => $successCount,
                    'errors' => $errorCount
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process bulk emails: ' . $e->getMessage()
            ]);
        }
         return redirect()->route('proformas.index')
                ->with('success', $message);
    }

    /**
     * Update email body for a proforma
     */
    private function updateEmailBody(Proforma $proforma)
    {
        // Reload the proforma with relationships to get updated data
        $proforma->load(['fornitore', 'provvigioni']);

        // Update emailsubject to include proforma number if not already present
        $currentSubject = $proforma->emailsubject ?? '';
        if (!str_contains($currentSubject, '# ' . $proforma->id)) {
            $newSubject = trim($currentSubject . ' # ' . $proforma->id);
            $proforma->update(['emailsubject' => $newSubject]);
        }

        // Generate email body with updated values
        $emailBody = $this->generateDefaultEmailContent($proforma);

        // Update the proforma with the generated email body
        $proforma->update(['emailbody' => $emailBody]);
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
