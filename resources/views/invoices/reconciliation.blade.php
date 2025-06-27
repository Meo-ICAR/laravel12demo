@extends('layouts.admin')

@section('title', 'Invoice Reconciliation')

@section('content_header')
    <h1>Invoice Reconciliation</h1>
@stop

@section('content')
<!-- Email Date Filter Form -->
<div class="row mb-3">
    <div class="col-md-12">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-filter mr-2"></i>
                    Filter Provvigioni
                </h3>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('invoices.reconciliation') }}" class="row">
                    <div class="col-md-2">
                        <label for="denominazione_riferimento">Denominazione Riferimento:</label>
                        <input type="text" class="form-control" id="denominazione_riferimento" name="denominazione_riferimento"
                               value="{{ request('denominazione_riferimento') }}" placeholder="Search...">
                    </div>
                    <div class="col-md-2">
                        <label for="email_date_from">From Date:</label>
                        <input type="date" class="form-control" id="email_date_from" name="email_date_from"
                               value="{{ request('email_date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="email_date_to">To Date:</label>
                        <input type="date" class="form-control" id="email_date_to" name="email_date_to"
                               value="{{ request('email_date_to') }}">
                    </div>
                    <div class="col-md-3">
                        <fieldset>
                            <legend class="sr-only">Filter Actions</legend>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search mr-1"></i> Filter
                                </button>
                                <a href="{{ route('invoices.reconciliation') }}" class="btn btn-secondary">
                                    <i class="fas fa-times mr-1"></i> Clear
                                </a>
                            </div>
                        </fieldset>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Left Column: Provvigioni Summary -->
    <div class="col-md-6">
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-envelope mr-2"></i>
                    Sent Provvigioni (Proforma Status)
                    <span class="badge badge-info ml-2">{{ $provvigioni_summary->count() }}</span>
                </h3>
                <small class="text-muted">
                    <i class="fas fa-info-circle mr-1"></i>
                    Click the link button to select an Provvigione, then click reconcile on an invoice to match them.
                </small>
            </div>
            <div class="card-body p-0">
                @if($provvigioni_summary->isEmpty())
                    <div class="p-3 text-center text-muted">
                        <i class="fas fa-inbox fa-2x mb-2"></i>
                        <p>No sent Provvigioni with Proforma status</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Denominazione</th>
                                    <th class="text-center">Sent Date</th>
                                    <th class="text-center">Records</th>
                                    <th class="text-right">Total Amount</th>
                                    <th class="text-center">Select</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($provvigioni_summary as $summary)
                                    <tr>
                                        <td>
                                            <a href="{{ route('provvigioni.index', [
                                                'denominazione_riferimento' => $summary->denominazione_riferimento,
                                                'sended_at' => $summary->sent_date
                                            ]) }}"
                                               class="text-primary font-weight-bold"
                                               title="Click to view Provvigioni for {{ $summary->denominazione_riferimento }} sent on {{ \Carbon\Carbon::parse($summary->sent_date)->format('d/m/Y') }}">
                                                {{ $summary->denominazione_riferimento ?: 'N/A' }}
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-secondary">
                                                {{ \Carbon\Carbon::parse($summary->sent_date)->format('d/m/Y') }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-primary">{{ $summary->total_records }}</span>
                                        </td>
                                        <td class="text-right">
                                            <span class="badge badge-success">
                                                € {{ number_format($summary->total_amount, 2, ',', '.') }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <button type="button"
                                                    class="btn btn-sm btn-primary provvigione-select-btn"
                                                    data-denominazione="{{ $summary->denominazione_riferimento }}"
                                                    data-sent-date="{{ $summary->sent_date }}"
                                                    data-total-records="{{ $summary->total_records }}"
                                                    data-total-amount="{{ $summary->total_amount }}"
                                                    title="Click to select">
                                                <i class="fas fa-link"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
            <div class="card-footer">
                <a href="{{ route('provvigioni.index') }}" class="btn btn-sm btn-outline-success">
                    <i class="fas fa-list mr-1"></i> View All Provvigioni
                </a>
            </div>
        </div>
    </div>

    <!-- Right Column: Unreconciled Invoices -->
    <div class="col-md-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-file-invoice mr-2"></i>
                    Unreconciled Invoices
                    <span class="badge badge-warning ml-2">{{ $unreconciled_invoices->count() }}</span>
                </h3>
                <small class="text-muted">
                    <i class="fas fa-info-circle mr-1"></i>
                    Reconcile buttons are enabled only when an Provvigione is selected.
                </small>
            </div>
            <div class="card-body p-0">
                @if($unreconciled_invoices->isEmpty())
                    <div class="p-3 text-center text-muted">
                        <i class="fas fa-check-circle fa-2x mb-2"></i>
                        <p>All invoices are reconciled!</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Action</th>
                                    <th>Fornitore</th>
                                    <th>Date</th>
                                    <th>Invoice #</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($unreconciled_invoices as $invoice)
                                    <tr>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary reconcile-btn"
                                                    data-invoice-id="{{ $invoice->id }}"
                                                    data-invoice-number="{{ $invoice->invoice_number }}"
                                                    data-fornitore="{{ $invoice->fornitore }}"
                                                    title="Reconcile this invoice">
                                                <i class="fas fa-link"></i>
                                            </button>
                                        </td>
                                        <td>
                                            @if($invoice->fornitore)
                                                <a href="{{ route('invoices.reconciliation', array_merge(request()->query(), ['denominazione_riferimento' => $invoice->fornitore])) }}"
                                                   class="text-primary font-weight-bold"
                                                   title="Click to filter by {{ $invoice->fornitore }}">
                                                    {{ $invoice->fornitore }}
                                                </a>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>{{ $invoice->invoice_date ? $invoice->invoice_date->format('d/m/Y') : 'N/A' }}</td>
                                        <td>
                                            @if($invoice->invoice_number)
                                                <a href="{{ route('invoices.edit', $invoice->id) }}"
                                                   class="text-primary font-weight-bold"
                                                   title="Click to edit invoice {{ $invoice->invoice_number }}">
                                                    <strong>{{ Str::limit($invoice->invoice_number, 10, '...') }}</strong>
                                                </a>
                                            @else
                                                <strong>N/A</strong>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            <span class="badge badge-info">
                                                € {{ number_format($invoice->total_amount, 2, ',', '.') }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($invoice->isreconiled === null)
                                                <span class="badge badge-secondary">Not Set</span>
                                            @else
                                                <span class="badge badge-warning">Unreconciled</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
            <div class="card-footer">
                <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-list mr-1"></i> View All Invoices
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Summary Cards Row -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-warning">
                <i class="fas fa-file-invoice"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text">
                    @if(request('denominazione_riferimento') || request('email_date_from') || request('email_date_to'))
                        Filtered Unreconciled Invoices
                    @else
                        Unreconciled Invoices
                    @endif
                </span>
                <span class="info-box-number">{{ $unreconciled_invoices->count() }}</span>
                <span class="info-box-text">
                    @if(request('denominazione_riferimento') || request('email_date_from') || request('email_date_to'))
                        Filtered: € {{ number_format($unreconciled_invoices->sum('total_amount'), 2, ',', '.') }}
                    @else
                        Total: € {{ number_format($unreconciled_invoices->sum('total_amount'), 2, ',', '.') }}
                    @endif
                </span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-info">
                <i class="fas fa-envelope"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text">
                    @if(request('denominazione_riferimento') || request('email_date_from') || request('email_date_to'))
                        Filtered Provvigioni
                    @else
                        Sent Provvigioni
                    @endif
                </span>
                <span class="info-box-number">{{ $provvigioni_summary->count() }}</span>
                <span class="info-box-text">
                    @if(request('denominazione_riferimento') || request('email_date_from') || request('email_date_to'))
                        Filtered: € {{ number_format($provvigioni_summary->sum('total_amount'), 2, ',', '.') }}
                    @else
                        Total: € {{ number_format($provvigioni_summary->sum('total_amount'), 2, ',', '.') }}
                    @endif
                </span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-success">
                <i class="fas fa-users"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text">Total Records</span>
                <span class="info-box-number">{{ $provvigioni_summary->sum('total_records') }}</span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-primary">
                <i class="fas fa-balance-scale"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text">Difference</span>
                @php
                    $invoiceTotal = $unreconciled_invoices->sum('total_amount');
                    $provvigioneTotal = $provvigioni_summary->sum('total_amount');
                    $difference = $invoiceTotal - $provvigioneTotal;
                @endphp
                <span class="info-box-number">
                    € {{ number_format($difference, 2, ',', '.') }}
                </span>
                <span class="info-box-text">
                    @if($difference == 0)
                        <span class="text-success">Balanced</span>
                    @elseif($difference > 0)
                        <span class="text-warning">Invoices higher</span>
                    @else
                        <span class="text-info">Provvigioni higher</span>
                    @endif
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Provvigione Selection Modal -->
<div class="modal fade" id="provvigioneModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle mr-2"></i>
                    Provvigione Selected
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="provvigione-details">
                    <!-- Details will be populated here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .table th {
        border-top: none;
        font-weight: 600;
    }

    .info-box {
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        border-radius: 0.25rem;
        background-color: #fff;
        display: flex;
        margin-bottom: 1rem;
        min-height: 80px;
        position: relative;
        width: 100%;
    }
    .info-box-icon {
        border-radius: 0.25rem 0 0 0.25rem;
        display: flex;
        align-items: center;
        font-size: 1.875rem;
        font-weight: 300;
        justify-content: center;
        text-align: center;
        width: 70px;
        color: #fff;
    }
    .info-box-content {
        display: flex;
        flex-direction: column;
        justify-content: center;
        line-height: 1.8;
        flex: 1;
        padding: 0 10px;
    }
    .info-box-text {
        display: block;
        font-size: 0.875rem;
        color: #6c757d;
    }
    .info-box-number {
        display: block;
        font-weight: 700;
        font-size: 1.25rem;
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    console.log('Document ready!');

    // Simple Provvigione selection
    $(document).on('click', '.provvigione-select-btn', function() {
        console.log('Provvigione button clicked!');

        var $btn = $(this);
        var denominazione = $btn.data('denominazione');
        var sentDate = $btn.data('sent-date');

        // Simple alert to show selection
        alert('Selected: ' + (denominazione || 'N/A') + '\nSent Date: ' + (sentDate || 'N/A'));

        // Change button color to show selection
        $('.provvigione-select-btn').removeClass('btn-success').addClass('btn-primary');
        $btn.removeClass('btn-primary').addClass('btn-success');
    });

    // Simple reconcile button
    $(document).on('click', '.reconcile-btn', function() {
        console.log('Reconcile button clicked!');

        var invoiceId = $(this).data('invoice-id');
        var invoiceNumber = $(this).data('invoice-number');

        alert('Reconcile invoice: ' + (invoiceNumber || 'N/A') + '\nID: ' + invoiceId);
    });
});
</script>
@stop
