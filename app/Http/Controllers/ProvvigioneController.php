<?php

namespace App\Http\Controllers;

use App\Models\Provvigione;
use Illuminate\Http\Request;
use App\Imports\ProvvigioniImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Proforma;
use App\Models\Fornitori;
use Illuminate\Support\Str;

class ProvvigioneController extends Controller
{
    public function index(Request $request)
    {
        $query = Provvigione::query();

        // Filter by stato_include if provided
        if ($request->has('stato_include') && $request->stato_include !== '') {
            $stati = array_map('trim', explode(',', $request->stato_include));
            $query->whereIn('stato', $stati);
        } else if ($request->has('stato') && $request->stato !== '') {
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

        // Filter by fonte if provided
        if ($request->has('fonte') && $request->fonte !== '') {
            $query->where('fonte', 'like', '%' . $request->fonte . '%');
        }

        // Filter by data_status_pratica if provided
        if ($request->has('data_status_pratica') && $request->data_status_pratica !== '') {
            $query->where('data_status_pratica', 'like', '%' . $request->data_status_pratica . '%');
        }

        // Filter by sended_at if provided
        if ($request->has('sended_at') && $request->sended_at !== '') {
            $query->whereDate('sended_at', $request->sended_at);
        }

        // Get total count and total importo before pagination
        $totalCount = $query->count();
        $totalImporto = $query->sum('importo');

        // Get total unfiltered values for comparison
        $totalUnfilteredCount = Provvigione::count();
        $totalUnfilteredImporto = Provvigione::sum('importo');

        $provvigioni = $query->paginate(15);
        $statoOptions = ['Inserito', 'Proforma', 'Fatturato', 'Pagato', 'Stornato','Sospeso'];

        // Monthly statistics
        $today = now();
        $firstOfCurrentMonth = $today->copy()->startOfMonth();
        $firstOfLastMonth = $today->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $today->copy()->subMonth()->endOfMonth();

        // Use sended_at as the date field for statistics
        $currentMonthCount = Provvigione::whereDate('sended_at', '>=', $firstOfCurrentMonth)
            ->whereDate('sended_at', '<=', $today)
            ->count();
        $currentMonthTotal = Provvigione::whereDate('sended_at', '>=', $firstOfCurrentMonth)
            ->whereDate('sended_at', '<=', $today)
            ->sum('importo');

        $lastMonthCount = Provvigione::whereDate('sended_at', '>=', $firstOfLastMonth)
            ->whereDate('sended_at', '<=', $endOfLastMonth)
            ->count();
        $lastMonthTotal = Provvigione::whereDate('sended_at', '>=', $firstOfLastMonth)
            ->whereDate('sended_at', '<=', $endOfLastMonth)
            ->sum('importo');

        return view('provvigioni.index', compact(
            'provvigioni',
            'statoOptions',
            'totalCount',
            'totalImporto',
            'totalUnfilteredCount',
            'totalUnfilteredImporto',
            'currentMonthCount',
            'currentMonthTotal',
            'lastMonthCount',
            'lastMonthTotal'
        ));
    }

    public function import(Request $request)
    {
        // If it's a GET request, show the import view
        if ($request->isMethod('get')) {
            return view('provvigioni.import');
        }

        // If it's a POST request, process the import
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv',
        ]);

        try {
        Excel::import(new ProvvigioniImport, $request->file('file'));
            return redirect()->route('provvigioni.index')->with('success', 'Import completed successfully!');
        } catch (\Exception $e) {
            return redirect()->route('provvigioni.import')->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $provvigione = Provvigione::findOrFail($id);
        $statoOptions = ['Inserito', 'Proforma', 'Fatturato', 'Pagato', 'Stornato','Sospeso'];
        return view('provvigioni.edit', compact('provvigione', 'statoOptions'));
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'stato' => 'required|in:Inserito,Proforma,Fatturato,Pagato,Stornato,Sospeso',
                'invoice_number' => 'nullable|string|max:255',
                'sended_at' => 'nullable|date',
                'received_at' => 'nullable|date',
                'paided_at' => 'nullable|date',
                'importo' => 'required|numeric|min:0',
            ]);

            $provvigione = Provvigione::findOrFail($id);

            // Prepare the data for update
            $updateData = [
                'stato' => $request->stato,
                'invoice_number' => $request->invoice_number,
                'importo' => $request->importo,
            ];

            // Handle datetime fields - convert empty strings to null
            if ($request->filled('sended_at')) {
                $updateData['sended_at'] = $request->sended_at;
            } else {
                $updateData['sended_at'] = null;
            }

            if ($request->filled('received_at')) {
                $updateData['received_at'] = $request->received_at;
            } else {
                $updateData['received_at'] = null;
            }

            if ($request->filled('paided_at')) {
                $updateData['paided_at'] = $request->paided_at;
            } else {
                $updateData['paided_at'] = null;
            }

            $result = $provvigione->update($updateData);

            // Return JSON response for AJAX requests
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Provvigione updated successfully!',
                    'stato' => $request->stato
                ]);
            }

            // Return redirect for regular form submissions
            return redirect()->route('provvigioni.index')->with('success', 'Provvigione updated successfully!');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating Provvigione: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('provvigioni.index')->with('error', 'Error updating Provvigione: ' . $e->getMessage());
        }
    }

    public function updateStato(Request $request, $id)
    {
        try {
            $request->validate([
                'stato' => 'required|in:Inserito,Proforma,Fatturato,Pagato,Stornato,Sospeso',
            ]);

            $provvigione = Provvigione::findOrFail($id);
            $provvigione->update(['stato' => $request->stato]);

            return response()->json([
                'success' => true,
                'message' => 'Stato updated successfully!',
                'stato' => $request->stato
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating stato: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bulkUpdateToProforma(Request $request)
    {
        try {
            \Log::info('Bulk update to proforma started', $request->all());

            // Build the query based on current URL parameters (filters)
            $query = Provvigione::query();

            // Apply filters from the current request
            if ($request->filled('stato')) {
                $query->where('stato', $request->stato);
            }
            if ($request->filled('denominazione_riferimento')) {
                $query->where('denominazione_riferimento', 'like', '%' . $request->denominazione_riferimento . '%');
            }
            if ($request->filled('istituto_finanziario')) {
                $query->where('istituto_finanziario', 'like', '%' . $request->istituto_finanziario . '%');
            }
            if ($request->filled('cognome')) {
                $query->where('cognome', 'like', '%' . $request->cognome . '%');
            }
            if ($request->filled('fonte')) {
                $query->where('fonte', 'like', '%' . $request->fonte . '%');
            }
            if ($request->filled('data_status_pratica')) {
                $query->where('data_status_pratica', 'like', '%' . $request->data_status_pratica . '%');
            }
            if ($request->filled('sended_at')) {
                $query->whereDate('sended_at', $request->sended_at);
            }

            // Get count of records that match filters AND have stato 'Inserito'
            $recordsToUpdate = $query->where('stato', 'Inserito')->count();

            \Log::info('Records to update: ' . $recordsToUpdate);

            if ($recordsToUpdate === 0) {
                return redirect()->route('provvigioni.index', $request->except('_token'))
                    ->with('warning', 'No records with stato "Inserito" found in the current filter.');
            }

            // Update all matching records from 'Inserito' to 'Proforma'
            $updated = $query->where('stato', 'Inserito')->update(['stato' => 'Proforma']);

            \Log::info('Bulk update completed', ['updated' => $updated, 'records_to_update' => $recordsToUpdate]);

            // Build redirect parameters - preserve all current filters
            $redirectParams = $request->except(['_token']);

            return redirect()->route('provvigioni.index', $redirectParams)
                ->with('success', "Successfully updated {$updated} record(s) from 'Inserito' to 'Proforma'.");

        } catch (\Exception $e) {
            \Log::error('Bulk update error: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('provvigioni.index', $request->except('_token'))
                ->with('error', 'Error during bulk update: ' . $e->getMessage());
        }
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

        $query = Provvigione::where('stato', 'Inserito')
            ->leftJoin('fornitoris', 'provvigioni.denominazione_riferimento', '=', 'fornitoris.name')
            ->selectRaw('
                provvigioni.denominazione_riferimento,
                fornitoris.email,
                COUNT(*) as n,
                SUM(CAST(provvigioni.importo AS DECIMAL(10,2))) as totale,
                fornitoris.contributo,
                fornitoris.anticipo
            ')
            ->groupBy('provvigioni.denominazione_riferimento', 'fornitoris.email', 'fornitoris.contributo', 'fornitoris.anticipo');

        // Apply ordering
        if ($orderBy === 'totale') {
            $query->orderBy('totale', $orderDirection);
        } elseif ($orderBy === 'n') {
            $query->orderBy('n', $orderDirection);
        } else {
            $query->orderBy('provvigioni.denominazione_riferimento', $orderDirection);
        }

        $proformaSummary = $query->get();

        // Convert raw date strings to Carbon instances
        $proformaSummary->transform(function ($item) {
            if ($item->sended_at) {
                $item->sended_at = \Carbon\Carbon::parse($item->sended_at);
            }
            if ($item->received_at) {
                $item->received_at = \Carbon\Carbon::parse($item->received_at);
            }
            if ($item->paided_at) {
                $item->paided_at = \Carbon\Carbon::parse($item->paided_at);
            }
            return $item;
        });

        return view('provvigioni.proforma-summary', compact('proformaSummary', 'orderBy', 'orderDirection'));
    }

    public function createProformaFromSummary(Request $request)
    {
        // Support both single and multiple denominazioni
        $denominazioni = $request->input('denominazioni', []);
        if (empty($denominazioni)) {
            $request->validate([
                'denominazione_riferimento' => 'required|string',
            ]);
            $denominazioni = [$request->input('denominazione_riferimento')];
        }

        $results = [];
        foreach ($denominazioni as $denominazione) {
            $fornitore = \App\Models\Fornitori::where('name', $denominazione)->first();
            if (!$fornitore) {
                $results[] = "Fornitore not found for '$denominazione'";
                continue;
            }
            $company = $fornitore->company;
            if (!$company) {
                $results[] = "Company not found for fornitore '$denominazione'";
                continue;
            }
            $proforma = \App\Models\Proforma::create([
                'company_id' => $company->id,
                'fornitori_id' => $fornitore->id,
                'stato' => 'Inserito',
                'anticipo' => $fornitore->anticipo,
                'contributo' => $fornitore->contributo,
                'emailto' => $fornitore->email,
                'anticipo_descrizione' => $fornitore->anticipo_description,
                'contributo_descrizione' => $fornitore->contributo_description,
                'emailfrom' => $company->email,
                'emailsubject' => $company->emailsubject ?? 'Proforma compensi provvigionali',
            ]);
            $provvigioni = \App\Models\Provvigione::where('denominazione_riferimento', $denominazione)->where('stato', 'Inserito')->get();
            if ($provvigioni->isNotEmpty()) {
                $pivotData = [];
                foreach ($provvigioni as $provvigione) {
                    $pivotData[$provvigione->id] = [
                        'created_at' => $proforma->created_at,
                        'updated_at' => $proforma->updated_at,
                    ];
                }
                $proforma->provvigioni()->attach($pivotData);
                \App\Models\Provvigione::whereIn('id', $provvigioni->pluck('id')->toArray())
                    ->update(['stato' => 'Proforma']);
            }
            $results[] = "Proforma created for '$denominazione' and provvigioni linked.";
        }
        return redirect()->route('proformas.index')->with('success', implode(' ', $results));
    }

    public function show($id)
    {
        $provvigione = Provvigione::findOrFail($id);
        return view('provvigioni.show', compact('provvigione'));
    }

    public function create()
    {
        $statoOptions = ['Inserito', 'Proforma', 'Fatturato', 'Pagato', 'Stornato','Sospeso'];
        return view('provvigioni.create', compact('statoOptions'));
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

        Provvigione::create($request->all());

        return redirect()->route('provvigioni.index')->with('success', 'Provvigione created successfully!');
    }

    public function destroy($id)
    {
        $provvigione = Provvigione::findOrFail($id);
        $provvigione->delete();

        return redirect()->route('provvigioni.index')->with('success', 'Provvigione deleted successfully!');
    }

    public function sendProformaEmail(Request $request)
    {
        $denominazione = $request->input('denominazione_riferimento');

        // Get all Inserito records for this denominazione
        $records = Provvigione::where('stato', 'Inserito')
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
            Provvigione::where('stato', 'Inserito')
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
        // Get all unique denominazioni with Inserito records
        $denominazioni = Provvigione::where('stato', 'Inserito')
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
            $records = Provvigione::where('stato', 'Inserito')
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
                    Provvigione::where('stato', 'Inserito')
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

        $updated = Provvigione::where('stato', 'Proforma')
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

        $updated = Provvigione::where('stato', 'Proforma')
            ->where('denominazione_riferimento', $denominazione)
            ->whereNull('paided_at')
            ->update(['paided_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => "Marked $updated record(s) as paid for $denominazione",
            'updated_count' => $updated
        ]);
    }

    public function markAsSent(Request $request)
    {
        $request->validate([
            'denominazione_riferimento' => 'required|string'
        ]);

        $denominazione = $request->input('denominazione_riferimento');

        $updated = Provvigione::where('stato', 'Inserito')
            ->where('denominazione_riferimento', $denominazione)
            ->whereNull('sended_at')
            ->update([
                'sended_at' => now(),
                'stato' => 'Proforma'
            ]);

        return response()->json([
            'success' => true,
            'message' => "Marked $updated record(s) as sent and updated to Proforma status for $denominazione",
            'updated_count' => $updated
        ]);
    }

    public function syncDenominazioniToFornitori()
    {
        try {
            $result = Provvigione::syncDenominazioniToFornitori();

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

    public function dashboard()
    {
        $totalProvvigioni = \App\Models\Provvigione::count();
        $totalImporto = \App\Models\Provvigione::sum('importo');
        $totalByStato = \App\Models\Provvigione::select('stato', \DB::raw('COUNT(*) as count'), \DB::raw('SUM(importo) as total_importo'))
            ->groupBy('stato')
            ->orderByDesc('count')
            ->get();
        $topDenominazioni = \App\Models\Provvigione::select('denominazione_riferimento', \DB::raw('SUM(importo) as total_importo'))
            ->whereNotNull('denominazione_riferimento')
            ->groupBy('denominazione_riferimento')
            ->orderByDesc('total_importo')
            ->limit(5)
            ->get();
        return view('provvigioni.dashboard', compact('totalProvvigioni', 'totalImporto', 'totalByStato', 'topDenominazioni'));
    }

    public function toggleStato(Request $request, $id)
    {
        $provvigione = Provvigione::findOrFail($id);
        $current = $provvigione->stato;
        $new = $request->input('stato');
        if (!in_array($current, ['Inserito', 'Sospeso']) || !in_array($new, ['Inserito', 'Sospeso'])) {
            return response()->json(['success' => false, 'message' => 'Invalid stato change.'], 400);
        }
        if ($current === $new) {
            return response()->json(['success' => true, 'message' => 'No change needed.']);
        }
        $provvigione->stato = $new;
        $provvigione->save();
        return response()->json(['success' => true, 'message' => 'Stato updated.', 'stato' => $new]);
    }

    // ... other CRUD methods ...
}
