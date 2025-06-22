@extends('layouts.app')

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
                    Filter MFCompensos by Email Date
                </h3>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('invoices.reconciliation') }}" class="row">
                    <div class="col-md-3">
                        <label for="email_date_from">From Date:</label>
                        <input type="date" class="form-control" id="email_date_from" name="email_date_from"
                               value="{{ request('email_date_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="email_date_to">To Date:</label>
                        <input type="date" class="form-control" id="email_date_to" name="email_date_to"
                               value="{{ request('email_date_to') }}">
                    </div>
                    <div class="col-md-3">
                        <label>&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search mr-1"></i> Filter
                            </button>
                            <a href="{{ route('invoices.reconciliation') }}" class="btn btn-secondary">
                                <i class="fas fa-times mr-1"></i> Clear
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Left Column: Unreconciled Invoices -->
    <div class="col-md-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-file-invoice mr-2"></i>
                    Unreconciled Invoices
                    <span class="badge badge-warning ml-2">{{ $unreconciledInvoices->count() }}</span>
                </h3>
            </div>
            <div class="card-body p-0">
                @if($unreconciledInvoices->isEmpty())
                    <div class="p-3 text-center text-muted">
                        <i class="fas fa-check-circle fa-2x mb-2"></i>
                        <p>All invoices are reconciled!</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Fornitore</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($unreconciledInvoices as $invoice)
                                    <tr>
                                        <td>
                                            <strong>{{ $invoice->invoice_number ?: 'N/A' }}</strong>
                                        </td>
                                        <td>{{ $invoice->fornitore ?: 'N/A' }}</td>
                                        <td>{{ $invoice->invoice_date ? $invoice->invoice_date->format('d/m/Y') : 'N/A' }}</td>
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
                                        <td>
                                            <button type="button" class="btn btn-sm btn-success reconcile-btn"
                                                    data-invoice-id="{{ $invoice->id }}"
                                                    data-invoice-number="{{ $invoice->invoice_number }}"
                                                    data-fornitore="{{ $invoice->fornitore }}">
                                                <i class="fas fa-link mr-1"></i> Reconcile
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
                <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-list mr-1"></i> View All Invoices
                </a>
            </div>
        </div>
    </div>

    <!-- Right Column: MFCompensos Summary -->
    <div class="col-md-6">
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-envelope mr-2"></i>
                    Sent MFCompensos (No Invoice)
                    <span class="badge badge-info ml-2">{{ $mfcompensosSummary->count() }}</span>
                </h3>
            </div>
            <div class="card-body p-0">
                @if($mfcompensosSummary->isEmpty())
                    <div class="p-3 text-center text-muted">
                        <i class="fas fa-inbox fa-2x mb-2"></i>
                        <p>No sent MFCompensos without invoice numbers</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>
                                        <input type="checkbox" id="select-all-mfcompensos">
                                    </th>
                                    <th>Denominazione</th>
                                    <th class="text-center">Records</th>
                                    <th class="text-right">Total Amount</th>
                                    <th class="text-center">Email Date Range</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($mfcompensosSummary as $summary)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="mfcompenso-checkbox"
                                                   value="{{ $summary->denominazione_riferimento }}"
                                                   data-denominazione="{{ $summary->denominazione_riferimento }}">
                                        </td>
                                        <td>
                                            <strong>{{ $summary->denominazione_riferimento ?: 'N/A' }}</strong>
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
                                            <small class="text-muted">
                                                @if($summary->first_sent_date && $summary->last_sent_date)
                                                    {{ \Carbon\Carbon::parse($summary->first_sent_date)->format('d/m/Y') }} -
                                                    {{ \Carbon\Carbon::parse($summary->last_sent_date)->format('d/m/Y') }}
                                                @else
                                                    N/A
                                                @endif
                                            </small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
            <div class="card-footer">
                <a href="{{ route('mfcompensos.index') }}" class="btn btn-sm btn-outline-success">
                    <i class="fas fa-list mr-1"></i> View All MFCompensos
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Reconciliation Modal -->
<div class="modal fade" id="reconciliationModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reconcile Invoice</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="reconciliationForm">
                    @csrf
                    <input type="hidden" id="invoice_id" name="invoice_id">
                    <input type="hidden" id="denominazione_riferimento" name="denominazione_riferimento">

                    <div class="form-group">
                        <label>Invoice Number:</label>
                        <input type="text" class="form-control" id="modal_invoice_number" readonly>
                    </div>

                    <div class="form-group">
                        <label>Fornitore:</label>
                        <input type="text" class="form-control" id="modal_fornitore" readonly>
                    </div>

                    <div class="form-group">
                        <label>Select MFCompenso Group:</label>
                        <select class="form-control" id="denominazione_select" required>
                            <option value="">Select a denominazione...</option>
                            @foreach($mfcompensosSummary as $summary)
                                <option value="{{ $summary->denominazione_riferimento }}">
                                    {{ $summary->denominazione_riferimento }}
                                    ({{ $summary->total_records }} records - €{{ number_format($summary->total_amount, 2, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmReconcile">
                    <i class="fas fa-link mr-1"></i> Confirm Reconciliation
                </button>
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
                <span class="info-box-text">Unreconciled Invoices</span>
                <span class="info-box-number">{{ $unreconciledInvoices->count() }}</span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-info">
                <i class="fas fa-envelope"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text">Sent MFCompensos</span>
                <span class="info-box-number">{{ $mfcompensosSummary->count() }}</span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-success">
                <i class="fas fa-euro-sign"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text">Total MFCompensos Amount</span>
                <span class="info-box-number">
                    € {{ number_format($mfcompensosSummary->sum('total_amount'), 2, ',', '.') }}
                </span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-primary">
                <i class="fas fa-users"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text">Total Records</span>
                <span class="info-box-number">{{ $mfcompensosSummary->sum('total_records') }}</span>
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
    // Enable tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // Select all checkbox functionality
    $('#select-all-mfcompensos').change(function() {
        $('.mfcompenso-checkbox').prop('checked', $(this).is(':checked'));
    });

    // Reconcile button click
    $('.reconcile-btn').click(function() {
        var invoiceId = $(this).data('invoice-id');
        var invoiceNumber = $(this).data('invoice-number');
        var fornitore = $(this).data('fornitore');

        $('#invoice_id').val(invoiceId);
        $('#modal_invoice_number').val(invoiceNumber);
        $('#modal_fornitore').val(fornitore);
        $('#denominazione_select').val('');

        $('#reconciliationModal').modal('show');
    });

    // Confirm reconciliation
    $('#confirmReconcile').click(function() {
        var denominazione = $('#denominazione_select').val();
        if (!denominazione) {
            alert('Please select a MFCompenso group to reconcile with.');
            return;
        }

        $('#denominazione_riferimento').val(denominazione);

        $.ajax({
            url: '{{ route("invoices.reconcile") }}',
            method: 'POST',
            data: $('#reconciliationForm').serialize(),
            success: function(response) {
                if (response.success) {
                    // Show success message
                    alert(response.message);

                    // Close modal and reload page
                    $('#reconciliationModal').modal('hide');
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr) {
                var response = xhr.responseJSON;
                alert('Error: ' + (response ? response.message : 'Unknown error occurred'));
            }
        });
    });

    // Auto-refresh every 5 minutes
    setTimeout(function() {
        location.reload();
    }, 300000); // 5 minutes
});
</script>
@stop
