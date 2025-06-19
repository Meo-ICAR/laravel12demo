<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::query();

        // Search by invoice number
        if ($request->filled('search')) {
            $query->where('invoice_number', 'like', '%' . $request->search . '%');
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('invoice_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('invoice_date', '<=', $request->date_to);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by amount range
        if ($request->filled('amount_from')) {
            $query->where('total_amount', '>=', $request->amount_from);
        }
        if ($request->filled('amount_to')) {
            $query->where('total_amount', '<=', $request->amount_to);
        }

        // Calculate statistics
        $stats = [
            'total_count' => $query->count(),
            'total_amount' => $query->sum('total_amount'),
            'total_tax' => $query->sum('tax_amount'),
            'average_amount' => $query->avg('total_amount'),
            'status_counts' => $query->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get(),
            'monthly_totals' => $query->select(
                DB::raw('YEAR(invoice_date) as year'),
                DB::raw('MONTH(invoice_date) as month'),
                DB::raw('SUM(total_amount) as total')
            )
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->limit(6)
                ->get(),
        ];

        // Sorting
        $sortField = $request->get('sort', 'invoice_date');
        $sortDirection = $request->get('direction', 'desc');

        // Validate sort field to prevent SQL injection
        $allowedSortFields = ['invoice_number', 'invoice_date', 'total_amount', 'tax_amount', 'status'];
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'invoice_date';
        }

        $query->orderBy($sortField, $sortDirection);

        // Get unique statuses for filter dropdown
        $statuses = Invoice::select('status')
            ->distinct()
            ->pluck('status');

        $invoices = $query->paginate(10);

        return view('invoices.index', compact('invoices', 'statuses', 'stats'));
    }
}
