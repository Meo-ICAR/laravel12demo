@extends('layouts.admin')

@section('title', 'Invoices')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Invoices</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item active">Invoices</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="table-responsive">
        <table class="table table-bordered table-striped mb-0">
            <thead>
                <tr>
                    <th>Fornitore</th>
                    <th>Fornitore PIVA</th>
                    <th>Cliente</th>
                    <th>Cliente PIVA</th>
                    <th>Invoice Number</th>
                    <th>Date</th>
                    <th>Total Amount</th>
                    <th>Tax Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                    <tr>
                        <td>{{ $invoice->fornitore }}</td>
                        <td>{{ $invoice->fornitore_piva }}</td>
                        <td>{{ $invoice->cliente }}</td>
                        <td>{{ $invoice->cliente_piva }}</td>
                        <td>{{ $invoice->invoice_number }}</td>
                        <td>
                            @if($invoice->invoice_date && ($invoice->invoice_date instanceof \Illuminate\Support\Carbon || strtotime($invoice->invoice_date)))
                                {{ $invoice->invoice_date->format('d/m/Y') }}
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $invoice->total_amount }}</td>
                        <td>{{ $invoice->tax_amount }}</td>
                        <td>
                            <span class="badge badge-{{ $invoice->status === 'imported' ? 'success' : 'warning' }}">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">No invoices found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">
        {{ $invoices->links() }}
    </div>
</div>
@endsection

@section('js')
@endsection
