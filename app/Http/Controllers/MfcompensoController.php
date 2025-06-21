<?php

namespace App\Http\Controllers;

use App\Models\Mfcompenso;
use Illuminate\Http\Request;
use App\Imports\MfcompensosImport;
use Maatwebsite\Excel\Facades\Excel;

class MfcompensoController extends Controller
{
    public function index(Request $request)
    {
        $query = Mfcompenso::query();

        // Filter by stato if provided
        if ($request->has('stato') && $request->stato !== '') {
            $query->where('stato', $request->stato);
        }

        // Filter by denominazione_riferimento if provided
        if ($request->has('denominazione_riferimento') && $request->denominazione_riferimento !== '') {
            $query->where('denominazione_riferimento', 'like', '%' . $request->denominazione_riferimento . '%');
        }

        // Filter by istituto_finanziario if provided
        if ($request->has('istituto_finanziario') && $request->istituto_finanziario !== '') {
            $query->where('istituto_finanziario', 'like', '%' . $request->istituto_finanziario . '%');
        }

        // Filter by cognome if provided
        if ($request->has('cognome') && $request->cognome !== '') {
            $query->where('cognome', 'like', '%' . $request->cognome . '%');
        }

        $mfcompensos = $query->paginate(15);
        $statoOptions = ['Inserito', 'Proforma', 'Fatturato', 'Pagato', 'Stornato','Sospeso'];

        return view('mfcompensos.index', compact('mfcompensos', 'statoOptions'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv',
        ]);
        Excel::import(new MfcompensosImport, $request->file('file'));
        return redirect()->route('mfcompensos.index')->with('success', 'Import completed!');
    }

    public function edit($id)
    {
        $mfcompenso = Mfcompenso::findOrFail($id);
        $statoOptions = ['Inserito', 'Proforma', 'Fatturato', 'Pagato', 'Stornato','Sospeso'];
        return view('mfcompensos.edit', compact('mfcompenso', 'statoOptions'));
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'stato' => 'required|in:Inserito,Proforma,Fatturato,Pagato,Stornato,Sospeso',
                'invoice_number' => 'nullable|string|max:255',
            ]);

            $mfcompenso = Mfcompenso::findOrFail($id);
            $result = $mfcompenso->update($request->only(['stato', 'invoice_number']));

            // Return JSON response for AJAX requests
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Stato updated successfully!',
                    'stato' => $request->stato
                ]);
            }

            // Return redirect for regular form submissions
            return redirect()->route('mfcompensos.index')->with('success', 'Stato updated successfully!');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating stato: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('mfcompensos.index')->with('error', 'Error updating stato: ' . $e->getMessage());
        }
    }

    public function bulkUpdateToProforma(Request $request)
    {
        $query = Mfcompenso::query();

        // Apply the same filters as in index
        if ($request->has('stato') && $request->stato !== '') {
            $query->where('stato', $request->stato);
        }
        if ($request->has('denominazione_riferimento') && $request->denominazione_riferimento !== '') {
            $query->where('denominazione_riferimento', 'like', '%' . $request->denominazione_riferimento . '%');
        }
        if ($request->has('istituto_finanziario') && $request->istituto_finanziario !== '') {
            $query->where('istituto_finanziario', 'like', '%' . $request->istituto_finanziario . '%');
        }
        if ($request->has('cognome') && $request->cognome !== '') {
            $query->where('cognome', 'like', '%' . $request->cognome . '%');
        }

        // Only update records with stato = Inserito
        $updated = $query->where('stato', 'Inserito')->update(['stato' => 'Proforma']);

        return redirect()->route('mfcompensos.index', $request->except('_token'))
            ->with('success', "$updated record(s) updated from Inserito to Proforma.");
    }

    public function proformaSummary(Request $request)
    {
        $orderBy = $request->get('order_by', 'denominazione_riferimento');
        $orderDirection = $request->get('order_direction', 'asc');

        // Validate order parameters
        $allowedOrderBy = ['denominazione_riferimento', 'totale', 'n'];
        $allowedDirections = ['asc', 'desc'];

        if (!in_array($orderBy, $allowedOrderBy)) {
            $orderBy = 'denominazione_riferimento';
        }
        if (!in_array($orderDirection, $allowedDirections)) {
            $orderDirection = 'asc';
        }

        $query = Mfcompenso::where('stato', 'Proforma')
            ->leftJoin('fornitoris', 'mfcompensos.denominazione_riferimento', '=', 'fornitoris.name')
            ->selectRaw('
                mfcompensos.denominazione_riferimento,
                fornitoris.email,
                COUNT(*) as n,
                SUM(CAST(mfcompensos.importo AS DECIMAL(10,2))) as totale,
                MAX(mfcompensos.sended_at) as sended_at,
                MAX(mfcompensos.received_at) as received_at,
                MAX(mfcompensos.paided_at) as paided_at
            ')
            ->groupBy('mfcompensos.denominazione_riferimento', 'fornitoris.email');

        // Apply ordering
        if ($orderBy === 'totale') {
            $query->orderBy('totale', $orderDirection);
        } elseif ($orderBy === 'n') {
            $query->orderBy('n', $orderDirection);
        } else {
            $query->orderBy('mfcompensos.denominazione_riferimento', $orderDirection);
        }

        $proformaSummary = $query->get();

        return view('mfcompensos.proforma-summary', compact('proformaSummary', 'orderBy', 'orderDirection'));
    }

    public function show($id)
    {
        $mfcompenso = Mfcompenso::findOrFail($id);
        return view('mfcompensos.show', compact('mfcompenso'));
    }

    public function create()
    {
        $statoOptions = ['Inserito', 'Proforma', 'Fatturato', 'Pagato', 'Stornato','Sospeso'];
        return view('mfcompensos.create', compact('statoOptions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'stato' => 'required|in:Inserito,Proforma,Fatturato,Pagato,Stornato,Sospeso',
            'descrizione' => 'required|string|max:255',
            'tipo' => 'required|string|max:255',
            'importo' => 'required|numeric',
            'invoice_number' => 'nullable|string|max:255',
        ]);

        Mfcompenso::create($request->all());

        return redirect()->route('mfcompensos.index')->with('success', 'MFCompenso created successfully!');
    }

    public function destroy($id)
    {
        $mfcompenso = Mfcompenso::findOrFail($id);
        $mfcompenso->delete();

        return redirect()->route('mfcompensos.index')->with('success', 'MFCompenso deleted successfully!');
    }

    public function sendProformaEmail(Request $request)
    {
        $denominazione = $request->input('denominazione_riferimento');

        // Get all Proforma records for this denominazione
        $records = Mfcompenso::where('stato', 'Proforma')
            ->where('denominazione_riferimento', $denominazione)
            ->select('cognome', 'nome', 'importo', 'prodotto')
            ->orderBy('cognome')
            ->orderBy('nome')
            ->get();

        $totalImporto = $records->sum('importo');

        // Generate email content
        $emailContent = $this->generateProformaEmailContent($denominazione, $records, $totalImporto);

        // Get email from fornitori table
        $fornitore = \App\Models\Fornitori::where('name', $denominazione)->first();
        $recipientEmail = $fornitore ? $fornitore->email : null;

        if (!$recipientEmail) {
            return response()->json([
                'success' => false,
                'message' => 'No email address found for ' . $denominazione
            ]);
        }

        try {
            // Send the email
            \Mail::send([], [], function ($message) use ($denominazione, $emailContent, $recipientEmail) {
                $message->to($recipientEmail)
                        ->subject('Dettagli Compensi Proforma - ' . $denominazione)
                        ->html($emailContent);
            });

            // Update sended_at timestamp for all records in this denominazione
            Mfcompenso::where('stato', 'Proforma')
                ->where('denominazione_riferimento', $denominazione)
                ->update(['sended_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'Email sent successfully to ' . $recipientEmail,
                'denominazione' => $denominazione,
                'recipient_email' => $recipientEmail,
                'record_count' => $records->count(),
                'total_importo' => $totalImporto,
                'email_content' => $emailContent
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage(),
                'email_content' => $emailContent
            ]);
        }
    }

    public function sendAllProformaEmails(Request $request)
    {
        // Get all unique denominazioni with Proforma records
        $denominazioni = Mfcompenso::where('stato', 'Proforma')
            ->select('denominazione_riferimento')
            ->distinct()
            ->pluck('denominazione_riferimento');

        $allEmailsContent = '';
        $totalRecords = 0;
        $grandTotal = 0;
        $sentEmails = [];
        $failedEmails = [];

        foreach ($denominazioni as $denominazione) {
            if (!$denominazione) continue;

            // Get records for this denominazione
            $records = Mfcompenso::where('stato', 'Proforma')
                ->where('denominazione_riferimento', $denominazione)
                ->select('cognome', 'nome', 'importo', 'prodotto')
                ->orderBy('cognome')
                ->orderBy('nome')
                ->get();

            $totalImporto = $records->sum('importo');
            $totalRecords += $records->count();
            $grandTotal += $totalImporto;

            // Generate email content for this denominazione
            $emailContent = $this->generateProformaEmailContent($denominazione, $records, $totalImporto);

            // Get email from fornitori table
            $fornitore = \App\Models\Fornitori::where('name', $denominazione)->first();
            $recipientEmail = $fornitore ? $fornitore->email : null;

            if ($recipientEmail) {
                try {
                    // Send the email
                    \Mail::send([], [], function ($message) use ($denominazione, $emailContent, $recipientEmail) {
                        $message->to($recipientEmail)
                                ->subject('Dettagli Compensi Proforma - ' . $denominazione)
                                ->html($emailContent);
                    });

                    // Update sended_at timestamp for all records in this denominazione
                    Mfcompenso::where('stato', 'Proforma')
                        ->where('denominazione_riferimento', $denominazione)
                        ->update(['sended_at' => now()]);

                    $sentEmails[] = [
                        'denominazione' => $denominazione,
                        'email' => $recipientEmail,
                        'records' => $records->count(),
                        'total' => $totalImporto
                    ];
                } catch (\Exception $e) {
                    $failedEmails[] = [
                        'denominazione' => $denominazione,
                        'email' => $recipientEmail,
                        'error' => $e->getMessage()
                    ];
                }
            } else {
                $failedEmails[] = [
                    'denominazione' => $denominazione,
                    'email' => null,
                    'error' => 'No email address found'
                ];
            }

            // Add separator between emails
            $allEmailsContent .= $emailContent;
            $allEmailsContent .= '<hr style="border: 2px solid #007bff; margin: 40px 0;">';
        }

        // Add grand summary at the end
        $allEmailsContent .= '<div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin-top: 30px;">';
        $allEmailsContent .= '<h3 style="color: #007bff; text-align: center;">GRAND SUMMARY</h3>';
        $allEmailsContent .= '<p style="text-align: center; font-size: 18px;">';
        $allEmailsContent .= '<strong>Total Records:</strong> ' . $totalRecords . ' | ';
        $allEmailsContent .= '<strong>Grand Total Amount:</strong> € ' . number_format($grandTotal, 2, ',', '.');
        $allEmailsContent .= '</p>';
        $allEmailsContent .= '</div>';

        return response()->json([
            'success' => true,
            'total_denominazioni' => $denominazioni->count(),
            'total_records' => $totalRecords,
            'grand_total' => $grandTotal,
            'sent_emails' => $sentEmails,
            'failed_emails' => $failedEmails,
            'all_emails_content' => $allEmailsContent
        ]);
    }

    private function generateProformaEmailContent($denominazione, $records, $totalImporto)
    {
        $content = "<html><body>";
        $content .= "<p>Gentile <strong>{$denominazione}</strong>,</p>";
        $content .= "<p>Di seguito i dettagli dei compensi in stato Proforma:</p>";

        $content .= "<table style='width: 100%; border-collapse: collapse; margin: 20px 0; font-family: Arial, sans-serif;'>";
        $content .= "<thead>";
        $content .= "<tr style='background-color: #f8f9fa;'>";
        $content .= "<th style='border: 1px solid #dee2e6; padding: 12px; text-align: left; font-weight: bold;'>Cognome</th>";
        $content .= "<th style='border: 1px solid #dee2e6; padding: 12px; text-align: left; font-weight: bold;'>Nome</th>";
        $content .= "<th style='border: 1px solid #dee2e6; padding: 12px; text-align: left; font-weight: bold;'>Prodotto</th>";
        $content .= "<th style='border: 1px solid #dee2e6; padding: 12px; text-align: right; font-weight: bold;'>Importo</th>";
        $content .= "</tr>";
        $content .= "</thead>";
        $content .= "<tbody>";

        foreach ($records as $record) {
            $content .= "<tr>";
            $content .= "<td style='border: 1px solid #dee2e6; padding: 12px;'>" . ($record->cognome ?: 'N/A') . "</td>";
            $content .= "<td style='border: 1px solid #dee2e6; padding: 12px;'>" . ($record->nome ?: 'N/A') . "</td>";
            $content .= "<td style='border: 1px solid #dee2e6; padding: 12px;'>" . ($record->prodotto ?: 'N/A') . "</td>";
            $content .= "<td style='border: 1px solid #dee2e6; padding: 12px; text-align: right; font-weight: bold; color: #28a745;'>€ " . number_format($record->importo, 2, ',', '.') . "</td>";
            $content .= "</tr>";
        }

        $content .= "</tbody>";
        $content .= "<tfoot>";
        $content .= "<tr style='background-color: #343a40; color: white;'>";
        $content .= "<td colspan='3' style='border: 1px solid #dee2e6; padding: 12px; font-weight: bold;'>TOTALE:</td>";
        $content .= "<td style='border: 1px solid #dee2e6; padding: 12px; text-align: right; font-weight: bold;'>€ " . number_format($totalImporto, 2, ',', '.') . "</td>";
        $content .= "</tr>";
        $content .= "</tfoot>";
        $content .= "</table>";

        $content .= "<p>Cordiali saluti,<br>";
        $content .= "Il team di gestione compensi</p>";
        $content .= "</body></html>";

        return $content;
    }

    public function checkSentEmails()
    {
        // This method is for testing purposes to see what emails were sent
        $emails = \Mail::raw('', function ($message) {
            // This is just to get the mailer instance
        });

        return response()->json([
            'message' => 'Email functionality is working. Check your mail configuration for actual sending.',
            'mailer' => config('mail.default'),
            'from_address' => config('mail.from.address'),
            'from_name' => config('mail.from.name')
        ]);
    }

    public function markAsReceived(Request $request)
    {
        $request->validate([
            'denominazione_riferimento' => 'required|string'
        ]);

        $denominazione = $request->input('denominazione_riferimento');

        $updated = Mfcompenso::where('stato', 'Proforma')
            ->where('denominazione_riferimento', $denominazione)
            ->whereNull('received_at')
            ->update(['received_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => "Marked $updated record(s) as received for $denominazione",
            'updated_count' => $updated
        ]);
    }

    public function markAsPaid(Request $request)
    {
        $request->validate([
            'denominazione_riferimento' => 'required|string'
        ]);

        $denominazione = $request->input('denominazione_riferimento');

        $updated = Mfcompenso::where('stato', 'Proforma')
            ->where('denominazione_riferimento', $denominazione)
            ->whereNull('paided_at')
            ->update(['paided_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => "Marked $updated record(s) as paid for $denominazione",
            'updated_count' => $updated
        ]);
    }

    public function syncDenominazioniToFornitori()
    {
        try {
            $result = Mfcompenso::syncDenominazioniToFornitori();

            return response()->json([
                'success' => true,
                'message' => "Sync completed successfully!",
                'data' => $result,
                'details' => "Added: {$result['added']}, Skipped: {$result['skipped']}, Total processed: {$result['total_processed']}"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error syncing denominazioni: ' . $e->getMessage()
            ], 500);
        }
    }

    // ... other CRUD methods ...
}
