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

        // Always join with fornitori to get the fornitore name
        $query->leftJoin('fornitoris', 'provvigioni.denominazione_riferimento', '=', 'fornitoris.coge')
              ->select('provvigioni.*', 'fornitoris.name as fornitore_name');

        // Filter by stato_include if provided
        if ($request->has('stato_include') && $request->stato_include !== '') {
            $stati = array_map('trim', explode(',', $request->stato_include));
            $query->whereIn('provvigioni.stato', $stati);
        } else if ($request->has('stato') && $request->stato !== '') {
            $query->where('provvigioni.stato', $request->stato);
        }

        // Filter by denominazione_riferimento if provided (now filters by fornitore name or original denominazione_riferimento)
        // The parameter name is kept as 'denominazione_riferimento' for backward compatibility
        // but it now filters by joining with fornitori table on COGE and matching fornitore name
        // Also includes fallback to original denominazione_riferimento field for records without matching fornitori
        if ($request->has('denominazione_riferimento') && $request->denominazione_riferimento !== '') {
            $searchTerm = $request->denominazione_riferimento;
            $query->where(function($q) use ($searchTerm) {
                $q->where('fornitoris.name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('provvigioni.denominazione_riferimento', 'like', '%' . $searchTerm . '%');
            });
        }

        // Filter by istituto_finanziario if provided
        if ($request->has('istituto_finanziario') && $request->istituto_finanziario !== '') {
            $query->where('provvigioni.istituto_finanziario', 'like', '%' . $request->istituto_finanziario . '%');
        }

        // Filter by cognome if provided
        if ($request->has('cognome') && $request->cognome !== '') {
            $query->where('provvigioni.cognome', 'like', '%' . $request->cognome . '%');
        }

        // Filter by fonte if provided
        if ($request->has('fonte') && $request->fonte !== '') {
            $query->where('provvigioni.fonte', 'like', '%' . $request->fonte . '%');
        }

        // Filter by data_status_pratica if provided
        if ($request->has('data_status_pratica') && $request->data_status_pratica !== '') {
            $query->where('provvigioni.data_status_pratica', 'like', '%' . $request->data_status_pratica . '%');
        }

        // Filter by data_status_pratica_from if provided
        if ($request->has('data_status_pratica_from') && $request->data_status_pratica_from !== '') {
            $query->whereDate('provvigioni.data_status_pratica', '>=', $request->data_status_pratica_from);
        }

        // Filter by data_status_pratica_to if provided
        if ($request->has('data_status_pratica_to') && $request->data_status_pratica_to !== '') {
            $query->whereDate('provvigioni.data_status_pratica', '<=', $request->data_status_pratica_to);
        }

        // Filter by sended_at if provided
        if ($request->has('sended_at') && $request->sended_at !== '') {
            $query->whereDate('provvigioni.sended_at', $request->sended_at);
        }

        // Filter by entrata_uscita if provided
        if ($request->has('entrata_uscita') && in_array($request->entrata_uscita, ['Entrata', 'Uscita'])) {
            $query->where('provvigioni.entrata_uscita', $request->entrata_uscita);
        }

        // Filter by status_pratica if provided, default to 'PERFEZIONATA'
        if ($request->has('status_pratica')) {
            if ($request->status_pratica !== '') {
                $query->where('provvigioni.status_pratica', $request->status_pratica);
            }
        } else {
            $query->where('provvigioni.status_pratica', 'PERFEZIONATA');
        }

        // Get total count and total importo before pagination
        $totalCount = $query->count();
        $totalImporto = $query->sum('provvigioni.importo');

        // Income (Entrata) - use fresh query
        $incomeCount = Provvigione::where('entrata_uscita', 'Entrata')->count();
        $incomeImporto = Provvigione::where('entrata_uscita', 'Entrata')->sum('importo');

        // Costs (Uscita or others) - use fresh query
        $costCount = Provvigione::where(function($q) {
            $q->where('entrata_uscita', '!=', 'Entrata')->orWhereNull('entrata_uscita');
        })->count();
        $costImporto = Provvigione::where(function($q) {
            $q->where('entrata_uscita', '!=', 'Entrata')->orWhereNull('entrata_uscita');
        })->sum('importo');

        // Get total unfiltered values for comparison
        $totalUnfilteredCount = Provvigione::count();
        $totalUnfilteredImporto = Provvigione::sum('importo');

        // Apply sorting
        $sortField = $request->get('sort', 'id');
        $sortOrder = $request->get('order', 'desc');

        // Validate sort field to prevent SQL injection
        $allowedSortFields = ['id', 'denominazione_riferimento', 'importo', 'stato', 'cognome', 'nome', 'tipo', 'istituto_finanziario', 'data_status_pratica', 'sended_at', 'created_at', 'updated_at'];
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'id';
        }

        // Validate sort order
        $sortOrder = strtolower($sortOrder) === 'asc' ? 'asc' : 'desc';

        // Add table prefix for sorting to avoid ambiguity
        if ($sortField !== 'id') {
            $sortField = 'provvigioni.' . $sortField;
        }

        $query->orderBy($sortField, $sortOrder);

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
            'lastMonthTotal',
            'incomeCount',
            'incomeImporto',
            'costCount',
            'costImporto'
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
            ->where('status_pratica', 'PERFEZIONATA')
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

        $entrataUscita = $request->get('entrata_uscita');
        if (in_array($entrataUscita, ['Entrata', 'Uscita'])) {
            $query->where('provvigioni.entrata_uscita', $entrataUscita);
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

        return view('provvigioni.proforma-summary', compact('proformaSummary', 'orderBy', 'orderDirection', 'entrataUscita'));
    }

    public function createProformaFromSummary(Request $request)
    {
        if ($request->has('denominazioni') && is_array($request->denominazioni)) {
            $results = [];
            foreach ($request->denominazioni as $denominazione) {
                $result = $this->createSingleProformaFromSummary($denominazione);
                $results[] = $result;
            }
            return redirect()->route('proformas.index')->with('success', 'Proforma created for selected denominazioni.');
        }
        // Single case fallback
        $request->validate([
            'denominazione_riferimento' => 'required|string',
        ]);
        $denominazione = $request->input('denominazione_riferimento');
        $this->createSingleProformaFromSummary($denominazione);
        return redirect()->route('proformas.index')->with('success', 'Proforma created for "' . $denominazione . '" and provvigioni linked.');
    }

    private function createSingleProformaFromSummary($denominazione)
    {
        $fornitore = \App\Models\Fornitori::where('name', $denominazione)->first();
        if (!$fornitore) {
            return false;
        }
        $company = $fornitore->company;
        if (!$company) {
            return false;
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
            'compenso_descrizione' => $company->compenso_descrizione,
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
        return true;
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
        // Basic counts
        $totalCount = Provvigione::count();
        $totalImporto = Provvigione::sum('importo');

        // Income (Entrata)
        $incomeCount = Provvigione::where('entrata_uscita', 'Entrata')->count();
        $incomeImporto = Provvigione::where('entrata_uscita', 'Entrata')->sum('importo');

        // Costs (Uscita or others)
        $costCount = Provvigione::where('entrata_uscita', '!=', 'Entrata')->orWhereNull('entrata_uscita')->count();
        $costImporto = Provvigione::where('entrata_uscita', '!=', 'Entrata')->orWhereNull('entrata_uscita')->sum('importo');

        // Counts by stato
        $statoCounts = Provvigione::selectRaw('stato, count(*) as count')
            ->groupBy('stato')
            ->orderBy('count', 'desc')
            ->get();

        // Total importo by stato
        $statoImporto = Provvigione::selectRaw('stato, sum(importo) as total_importo')
            ->groupBy('stato')
            ->orderBy('total_importo', 'desc')
            ->get();

        // Monthly statistics (current year)
        $currentYear = now()->year;
        $monthlyStats = Provvigione::selectRaw('
                MONTH(sended_at) as month,
                COUNT(*) as count,
                SUM(importo) as total_importo
            ')
            ->whereYear('sended_at', $currentYear)
            ->whereNotNull('sended_at')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Top 10 denominazioni by importo
        $topDenominazioni = Provvigione::selectRaw('
                denominazione_riferimento,
                COUNT(*) as count,
                SUM(importo) as total_importo
            ')
            ->whereNotNull('denominazione_riferimento')
            ->groupBy('denominazione_riferimento')
            ->orderBy('total_importo', 'desc')
            ->limit(10)
            ->get();

        // Top 10 istituti finanziari by importo
        $topIstituti = Provvigione::selectRaw('
                istituto_finanziario,
                COUNT(*) as count,
                SUM(importo) as total_importo
            ')
            ->whereNotNull('istituto_finanziario')
            ->groupBy('istituto_finanziario')
            ->orderBy('total_importo', 'desc')
            ->limit(10)
            ->get();

        // Recent activity (last 30 days)
        $recentActivity = Provvigione::where('created_at', '>=', now()->subDays(30))
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Pending proforma records
        $pendingProforma = Provvigione::where('stato', 'Inserito')
            ->count();

        // Sent proforma records
        $sentProforma = Provvigione::where('stato', 'Proforma')
            ->whereNotNull('sended_at')
            ->count();

        // Paid records
        $paidRecords = Provvigione::where('stato', 'Pagato')
            ->count();

        // Average importo
        $averageImporto = Provvigione::whereNotNull('importo')
            ->avg('importo');

        // This month vs last month comparison
        $thisMonth = Provvigione::whereMonth('sended_at', now()->month)
            ->whereYear('sended_at', now()->year)
            ->whereNotNull('sended_at');

        $lastMonth = Provvigione::whereMonth('sended_at', now()->subMonth()->month)
            ->whereYear('sended_at', now()->subMonth()->year)
            ->whereNotNull('sended_at');

        $thisMonthCount = $thisMonth->count();
        $thisMonthImporto = $thisMonth->sum('importo');
        $lastMonthCount = $lastMonth->count();
        $lastMonthImporto = $lastMonth->sum('importo');

        // Calculate percentage changes
        $countChange = $lastMonthCount > 0 ? (($thisMonthCount - $lastMonthCount) / $lastMonthCount) * 100 : 0;
        $importoChange = $lastMonthImporto > 0 ? (($thisMonthImporto - $lastMonthImporto) / $lastMonthImporto) * 100 : 0;

        // Pie chart data: number of provvigioni for each status_pratica
        $statoPraticaCounts = Provvigione::selectRaw('status_pratica, count(*) as count')
            ->groupBy('status_pratica')
            ->pluck('count', 'status_pratica');

        return view('provvigioni.dashboard', compact(
            'totalCount',
            'totalImporto',
            'statoCounts',
            'statoImporto',
            'monthlyStats',
            'topDenominazioni',
            'topIstituti',
            'recentActivity',
            'pendingProforma',
            'sentProforma',
            'paidRecords',
            'averageImporto',
            'thisMonthCount',
            'thisMonthImporto',
            'lastMonthCount',
            'lastMonthImporto',
            'countChange',
            'importoChange',
            'incomeCount',
            'incomeImporto',
            'costCount',
            'costImporto',
            'statoPraticaCounts',
        ));
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
