<?php

namespace App\Http\Controllers;

use App\Models\Clienti;
use App\Models\Invoice;
use Illuminate\Http\Request;

class ClientiInvoiceController extends Controller
{
    public function show($id, Request $request)
    {
        $clienti = Clienti::findOrFail($id);
        $coge = $clienti->coge;

        if (!$coge) {
            return redirect()->route('clientis.index')->with('error', 'No COGE value found for this clienti.');
        }

        $query = Invoice::where('coge', $coge);

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('invoice_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('invoice_date', '<=', $request->date_to);
        }

        // Filter by total_amount range
        if ($request->filled('amount_from')) {
            $query->where('total_amount', '>=', $request->amount_from);
        }
        if ($request->filled('amount_to')) {
            $query->where('total_amount', '<=', $request->amount_to);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by fornitore
        if ($request->filled('fornitore')) {
            $query->where('fornitore', 'like', '%' . $request->fornitore . '%');
        }

        // Sort by column and direction
        $sortColumn = $request->get('sort', 'invoice_date');
        $sortDirection = $request->get('direction', 'desc');

        if (in_array($sortColumn, ['invoice_date', 'total_amount', 'tax_amount', 'invoice_number', 'status', 'fornitore'])) {
            $query->orderBy($sortColumn, $sortDirection);
        } else {
            $query->orderBy('invoice_date', 'desc');
        }

        $invoices = $query->paginate(20)->withQueryString();

        // Get unique statuses for filter dropdown
        $statuses = Invoice::where('coge', $coge)->distinct()->pluck('status')->filter()->sort()->values();

        // Get unique fornitori for filter dropdown
        $fornitori = Invoice::where('coge', $coge)->distinct()->pluck('fornitore')->filter()->sort()->values();

        return view('clientis.invoices_show', compact('clienti', 'invoices', 'coge', 'statuses', 'fornitori'));
    }
}
