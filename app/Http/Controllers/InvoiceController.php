<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = \App\Models\Invoice::select('*')->orderByDesc('invoice_date')->paginate(15);
        return view('invoices.index', compact('invoices'));
    }
}
