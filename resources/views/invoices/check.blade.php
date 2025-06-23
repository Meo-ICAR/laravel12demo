@extends('layouts.admin')

@section('title', 'Invoice Check - ' . $invoice->invoice_number)

<meta name="csrf-token" content="{{ csrf_token() }}">

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Invoice Check</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">Invoices</a></li>
                <li class="breadcrumb-item active">Check</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <!-- Invoice Information -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Invoice Information</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Invoice Number:</strong><br>
                            {{ $invoice->invoice_number }}
                        </div>
                        <div class="col-md-3">
                            <strong>Fornitore:</strong><br>
                            {{ $invoice->fornitore }}
                        </div>
                        <div class="col-md-3">
                            <strong>Date:</strong><br>
                            {{ $invoice->invoice_date ? $invoice->invoice_date->format('d/m/Y') : 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Total Amount:</strong><br>
                            € {{ number_format($invoice->total_amount, 2, ',', '.') }}
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <strong>Checked Amount:</strong><br>
                            <span id="invoice-checked-amount" class="text-success font-weight-bold">
                                € {{ number_format($provvigioniRecords->sum('importo'), 2, ',', '.') }}
                            </span>
                        </div>
                        <div class="col-md-6">
                            <strong>Difference:</strong><br>
                            <span id="invoice-difference" class="font-weight-bold">
                                € {{ number_format($provvigioniRecords->sum('importo') - $invoice->total_amount, 2, ',', '.') }}
                            </span>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="button" class="btn btn-success" id="reconcile-btn">
                                <i class="fas fa-balance-scale mr-1"></i> Reconcile
                            </button>
                            <small class="text-muted ml-2" id="reconcile-status">
                                <!-- Status message will be shown here -->
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Provvigioni Records -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list mr-2"></i>
                        Provvigioni Records ({{ $invoice->fornitore }})
                    </h3>
                    <div class="card-tools">
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="select-all">
                                <i class="fas fa-check-square mr-1"></i> Select All
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="deselect-all">
                                <i class="fas fa-square mr-1"></i> Deselect All
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped mb-0">
                            <thead>
                                <tr>
                                    <th width="50">
                                        <input type="checkbox" id="select-all-checkbox" checked>
                                    </th>
                                    <th>Cognome</th>
                                    <th>Nome</th>
                                    <th>Prodotto</th>
                                    <th>Descrizione</th>
                                    <th class="text-right">Importo</th>
                                    <th>Sent Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($provvigioniRecords as $index => $record)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="record-checkbox"
                                                   data-importo="{{ $record->importo }}"
                                                   data-record-id="{{ $record->id }}"
                                                   checked>
                                        </td>
                                        <td>
                                            <strong>{{ $record->cognome ?: 'N/A' }}</strong>
                                        </td>
                                        <td>{{ $record->nome ?: 'N/A' }}</td>
                                        <td>{{ $record->prodotto ?: 'N/A' }}</td>
                                        <td>{{ $record->descrizione ?: 'N/A' }}</td>
                                        <td class="text-right">
                                            <span class="text-success font-weight-bold">
                                                € {{ number_format($record->importo, 2, ',', '.') }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($record->sended_at)
                                                <span class="badge badge-info">
                                                    {{ \Carbon\Carbon::parse($record->sended_at)->format('d/m/Y') }}
                                                </span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">
                                            <div class="py-4">
                                                <i class="fas fa-info-circle text-muted fa-2x mb-3"></i>
                                                <p class="text-muted">No matching provvigioni records found.</p>
                                                <small class="text-muted">
                                                    Criteria: Proforma status, no invoice number,
                                                    denominazione_riferimento = "{{ $invoice->fornitore }}",
                                                    sended_at <= {{ $invoice->invoice_date ? $invoice->invoice_date->format('d/m/Y') : 'N/A' }}
                                                </small>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    // Initialize variables
    let totalAmount = {{ $provvigioniRecords->sum('importo') }};
    let checkedAmount = totalAmount;
    let checkedCount = {{ $provvigioniRecords->count() }};
    let totalCount = {{ $provvigioniRecords->count() }};

    // Function to update summary
    function updateSummary() {
        // Update invoice information card
        $('#invoice-checked-amount').text('€ ' + checkedAmount.toLocaleString('it-IT', {minimumFractionDigits: 2}));

        // Calculate and update difference
        const invoiceAmount = {{ $invoice->total_amount }};
        const difference = checkedAmount - invoiceAmount;

        $('#invoice-difference').text('€ ' + difference.toLocaleString('it-IT', {minimumFractionDigits: 2}));

        // Color code the difference
        if (difference > 0) {
            $('#invoice-difference').removeClass('text-danger text-success').addClass('text-success');
        } else if (difference < 0) {
            $('#invoice-difference').removeClass('text-danger text-success').addClass('text-danger');
        } else {
            $('#invoice-difference').removeClass('text-danger text-success');
        }

        // Handle reconcile button state
        const reconcileBtn = $('#reconcile-btn');
        const reconcileStatus = $('#reconcile-status');

        if (difference > 0) {
            // Disable button if difference > 0
            reconcileBtn.prop('disabled', true).removeClass('btn-success').addClass('btn-secondary');
            reconcileStatus.text('Cannot reconcile: Checked amount exceeds invoice amount');
        } else if (difference < 0) {
            // Enable button but show warning if difference < 0
            reconcileBtn.prop('disabled', false).removeClass('btn-secondary').addClass('btn-warning');
            reconcileStatus.text('Warning: Checked amount is less than invoice amount');
        } else {
            // Enable button normally if difference = 0
            reconcileBtn.prop('disabled', false).removeClass('btn-secondary btn-warning').addClass('btn-success');
            reconcileStatus.text('Ready to reconcile');
        }
    }

    // Handle individual checkbox changes
    $('.record-checkbox').change(function() {
        const importo = parseFloat($(this).data('importo'));

        if ($(this).is(':checked')) {
            checkedAmount += importo;
            checkedCount++;
        } else {
            checkedAmount -= importo;
            checkedCount--;
        }

        updateSummary();
    });

    // Handle select all checkbox
    $('#select-all-checkbox').change(function() {
        const isChecked = $(this).is(':checked');
        $('.record-checkbox').prop('checked', isChecked);

        if (isChecked) {
            checkedAmount = totalAmount;
            checkedCount = totalCount;
        } else {
            checkedAmount = 0;
            checkedCount = 0;
        }

        updateSummary();
    });

    // Handle select all button
    $('#select-all').click(function() {
        $('#select-all-checkbox').prop('checked', true).trigger('change');
    });

    // Handle deselect all button
    $('#deselect-all').click(function() {
        $('#select-all-checkbox').prop('checked', false).trigger('change');
    });

    // Handle reconcile button click
    $('#reconcile-btn').click(function() {
        const invoiceAmount = {{ $invoice->total_amount }};
        const difference = checkedAmount - invoiceAmount;

        // Get all checked record IDs
        const checkedIds = [];
        $('.record-checkbox:checked').each(function() {
            const recordId = $(this).data('record-id');
            if (recordId) {
                checkedIds.push(recordId);
            }
        });

        if (checkedIds.length === 0) {
            alert('Please select at least one record to reconcile.');
            return;
        }

        let confirmMessage = `Are you sure you want to reconcile ${checkedIds.length} selected records with invoice #{{ $invoice->invoice_number }}?`;

        if (difference < 0) {
            confirmMessage += `\n\nWARNING: The checked amount (€ ${checkedAmount.toLocaleString('it-IT', {minimumFractionDigits: 2})}) is less than the invoice amount (€ ${invoiceAmount.toLocaleString('it-IT', {minimumFractionDigits: 2})}).\n\nDo you want to continue?`;
        }

        if (confirm(confirmMessage)) {
            // Show loading state
            const originalText = $(this).html();
            $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Reconciling...');

            // Send AJAX request
            $.ajax({
                url: '{{ route("invoices.reconcileChecked", $invoice->id) }}',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                data: JSON.stringify({
                    checked_ids: checkedIds
                }),
                success: function(response) {
                    if (response.success) {
                        alert('Success: ' + response.message);
                        // Redirect to invoices index
                        window.location.href = '{{ route("invoices.index") }}';
                    } else {
                        alert('Error: ' + response.message);
                        // Reset button state
                        $('#reconcile-btn').prop('disabled', false).html(originalText);
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    const errorMessage = response ? response.message : 'An error occurred during reconciliation';
                    alert('Error: ' + errorMessage);
                    // Reset button state
                    $('#reconcile-btn').prop('disabled', false).html(originalText);
                }
            });
        }
    });

    // Initialize summary
    updateSummary();
});
</script>
@endsection
