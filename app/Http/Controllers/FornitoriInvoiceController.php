<?php

namespace App\Http\Controllers;

use App\Models\Fornitori;
use Illuminate\Http\Request;

class FornitoriInvoiceController extends Controller
{
    public function index()
    {
        $fornitoris = Fornitori::paginate(20);
        return view('fornitoris.invoices_index', compact('fornitoris'));
    }

    public function show($id, Request $request)
    {
        $fornitore = Fornitori::findOrFail($id);
        $query = $fornitore->invoices();

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

        // Sort by column and direction
        $sortColumn = $request->get('sort', 'invoice_date');
        $sortDirection = $request->get('direction', 'desc');

        if (in_array($sortColumn, ['invoice_date', 'total_amount', 'tax_amount', 'invoice_number', 'status'])) {
            $query->orderBy($sortColumn, $sortDirection);
        } else {
            $query->orderBy('invoice_date', 'desc');
        }

        $invoices = $query->paginate(20)->withQueryString();
        return view('fornitoris.invoices_show', compact('fornitore', 'invoices'));
    }
}
