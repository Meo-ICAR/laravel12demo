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
    <div class="row mb-2">
        <div class="col-sm-12">
            <a href="{{ route('invoices.reconciliation') }}" class="btn btn-warning">
                <i class="fas fa-balance-scale mr-1"></i> Reconciliation Dashboard
            </a>
            <a href="{{ route('invoiceins.import') }}" class="btn btn-primary">
                <i class="fas fa-upload mr-1"></i> Import from CSV/Excel
            </a>
            <a href="{{ route('invoices.dashboard') }}" class="btn btn-info">
                <i class="fas fa-chart-line mr-1"></i> Dashboard
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <!-- Filters Section -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-filter mr-2"></i>Filters & Sorting
                        <button class="btn btn-sm btn-outline-secondary float-right" type="button" data-toggle="collapse" data-target="#filterCollapse">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    </h5>
                </div>
                <div class="collapse" id="filterCollapse">
                    <div class="card-body">
                        <form method="GET" action="{{ route('invoices.index') }}" id="filterForm">
                            <div class="row">
                                <!-- Status Filter -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="stato">Status</label>
                                        <select name="stato" id="stato" class="form-control">
                                            <option value="">All Statuses</option>
                                            @foreach($statuses as $status)
                                                <option value="{{ $status }}" {{ request('stato') == $status ? 'selected' : '' }}>
                                                    {{ ucfirst($status) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- Fornitore Filter -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="fornitore">Fornitore</label>
                                        <select name="fornitore" id="fornitore" class="form-control">
                                            <option value="">All Fornitori</option>
                                            @foreach($fornitori as $fornitore)
                                                <option value="{{ $fornitore }}" {{ request('fornitore') == $fornitore ? 'selected' : '' }}>
                                                    {{ $fornitore }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- Date Range Filters -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="date_from">Date From</label>
                                        <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="date_to">Date To</label>
                                        <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                                    </div>
                                </div>
                            </div>

                            <!-- Sorting Controls -->
                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="sort_by">Sort By</label>
                                        <select name="sort_by" id="sort_by" class="form-control">
                                            <option value="invoice_date" {{ request('sort_by', 'invoice_date') == 'invoice_date' ? 'selected' : '' }}>Date</option>
                                            <option value="fornitore" {{ request('sort_by') == 'fornitore' ? 'selected' : '' }}>Fornitore</option>
                                            <option value="total_amount" {{ request('sort_by') == 'total_amount' ? 'selected' : '' }}>Total Amount</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="sort_direction">Sort Direction</label>
                                        <select name="sort_direction" id="sort_direction" class="form-control">
                                            <option value="desc" {{ request('sort_direction', 'desc') == 'desc' ? 'selected' : '' }}>Descending</option>
                                            <option value="asc" {{ request('sort_direction') == 'asc' ? 'selected' : '' }}>Ascending</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <div>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-search mr-1"></i> Apply Filters
                                            </button>
                                            <a href="{{ route('invoices.index') }}" class="btn btn-secondary">
                                                <i class="fas fa-times mr-1"></i> Clear
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Statistics Cards -->
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="card border-primary">
                <div class="card-body">
                    <h5 class="card-title text-primary">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        This Month ({{ now()->startOfMonth()->format('d/m/Y') }} - {{ now()->format('d/m/Y') }})
                    </h5>
                    <p class="card-text mb-1">
                        <strong>Records:</strong> {{ number_format($currentMonthCount) }}
                    </p>
                    <p class="card-text">
                        <strong>Total Amount:</strong> € {{ number_format($currentMonthTotal, 2, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-info">
                <div class="card-body">
                    <h5 class="card-title text-info">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        Last Month ({{ now()->subMonth()->startOfMonth()->format('d/m/Y') }} - {{ now()->subMonth()->endOfMonth()->format('d/m/Y') }})
                    </h5>
                    <p class="card-text mb-1">
                        <strong>Records:</strong> {{ number_format($lastMonthCount) }}
                    </p>
                    <p class="card-text">
                        <strong>Total Amount:</strong> € {{ number_format($lastMonthTotal, 2, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Invoices</h3>
                        <div class="d-flex align-items-center">
                            <div class="mr-3">
                                <small class="text-muted">
                                    <strong>Total Amount:</strong> <span class="text-success font-weight-bold">€ {{ number_format($filteredTotalAmount, 2, ',', '.') }}</span>
                                </small>
                            </div>
                            @if(request()->hasAny(['stato', 'fornitore', 'date_from', 'date_to']))
                                <small class="text-muted">
                                    Showing {{ $invoices->total() }} results
                                    @if(request('stato'))
                                        | Status: {{ ucfirst(request('stato')) }}
                                    @endif
                                    @if(request('fornitore'))
                                        | Fornitore: {{ request('fornitore') }}
                                    @endif
                                    @if(request('date_from') || request('date_to'))
                                        | Date: {{ request('date_from', 'Any') }} to {{ request('date_to', 'Any') }}
                                    @endif
                                </small>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Fornitore</th>
                                    <th>COGE</th>
                                    {{-- <th>Cliente</th> --}}
                                    {{-- <th>Cliente PIVA</th> --}}
                                    <th>Invoice Number</th>
                                    <th>Date</th>
                                    <th class="text-right">Total Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoices as $invoice)
                                <tr>
                                    <td>
                                        @if($invoice->coge)
                                            <a href="{{ route('provvigioni.index', [
                                                'denominazione_riferimento' => $invoice->fornitore,
                                                'data_status_pratica_from' => $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->subMonths(2)->format('Y-m-d') : '',
                                                'data_status_pratica_to' => $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->format('Y-m-d') : '',
                                                'sort' => 'data_status_pratica',
                                                'order' => 'desc'
                                            ]) }}" class="text-primary" style="text-decoration: underline;">
                                                {{ $invoice->fornitore }}
                                                <i class="fas fa-external-link-alt ml-1"></i>
                                            </a>
                                        @else
                                            {{ $invoice->fornitore }}
                                        @endif
                                    </td>
                                    <td>{{ $invoice->coge }}</td>
                                    {{-- <td>{{ $invoice->cliente }}</td> --}}
                                    {{-- <td>{{ $invoice->cliente_piva }}</td> --}}
                                    <td>
                                        @if($invoice->xml_data)
                                            <a href="#" class="text-primary xml-link"
                                               data-invoice-id="{{ $invoice->id }}"
                                               data-invoice-number="{{ $invoice->invoice_number }}"
                                               style="text-decoration: underline; cursor: pointer;">
                                                {{ $invoice->invoice_number }}
                                                <i class="fas fa-code ml-1"></i>
                                            </a>
                                        @else
                                            {{ $invoice->invoice_number }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($invoice->invoice_date && ($invoice->invoice_date instanceof \Illuminate\Support\Carbon || strtotime($invoice->invoice_date)))
                                            {{ $invoice->invoice_date->format('d/m/Y') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-right">{{ number_format($invoice->total_amount, 2, ',', '.') }}</td>
                                    <td>
                                        <span class="badge badge-{{ $invoice->status === 'imported' ? 'success' : 'warning' }}">
                                            {{ ucfirst($invoice->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('invoices.edit', $invoice->id) }}" class="btn btn-sm btn-warning mr-1">
                                            <i class="fas fa-edit mr-1"></i> Edit
                                        </a>
                                        {{-- <a href="{{ route('invoices.reconciliation', ['denominazione_riferimento' => $invoice->fornitore]) }}" class="btn btn-sm btn-primary mr-1">
                                            <i class="fas fa-balance-scale mr-1"></i> Reconcile
                                        </a> --}}
                                        <a href="{{ route('invoices.check', $invoice->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-search mr-1"></i> Check
                                        </a>
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
                </div>
            </div>
        </div>
    </div>
</div>

<!-- XML Data Modal -->
<div class="modal fade" id="xmlModal" tabindex="-1" role="dialog" aria-labelledby="xmlModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="xmlModalLabel">
                    <i class="fas fa-code mr-2"></i>
                    XML Data - Invoice #<span id="modal-invoice-number"></span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="xml-loading" class="text-center">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p>Loading XML data...</p>
                </div>

                <div id="xml-error" class="alert alert-danger" style="display: none;">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <span id="xml-error-message"></span>
                </div>

                <div id="xml-content" style="display: none;">
                    <!-- Validation Results -->
                    <div class="card mb-3" style="display: none;">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-check-circle mr-2"></i>
                                Validation Results
                            </h6>
                        </div>
                        <div class="card-body">
                            <div id="validation-results">
                                <!-- Validation results will be populated here -->
                            </div>
                        </div>
                    </div>

                    <!-- Schema Information -->
                    <div class="card mb-3" style="display: none;">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-info-circle mr-2"></i>
                                Schema Information
                            </h6>
                        </div>
                        <div class="card-body">
                            <div id="schema-info">
                                <!-- Schema info will be populated here -->
                            </div>
                        </div>
                    </div>

                    <!-- Structured Data -->
                    <div class="card mb-3" style="display: none;">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-table mr-2"></i>
                                Structured Data (Extracted using Schema)
                            </h6>
                        </div>
                        <div class="card-body">
                            <div id="structured-data">
                                <!-- Structured data will be populated here -->
                            </div>
                        </div>
                    </div>

                    <!-- XML Content -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-file-code mr-2"></i>
                                XML Structure View
                            </h6>
                            <div class="float-right">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="toggle-xml-view">
                                    <i class="fas fa-eye mr-1"></i> Toggle View
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="copy-xml">
                                    <i class="fas fa-copy mr-1"></i> Copy XML
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <!-- Pretty XML Tree View -->
                            <div id="xml-tree-view" class="p-3" style="max-height: 500px; overflow-y: auto;">
                                <!-- XML tree will be populated here -->
                            </div>

                            <!-- Raw XML View (hidden by default) -->
                            <pre id="xml-display" class="bg-light p-3 m-0" style="max-height: 500px; overflow-y: auto; font-size: 12px; display: none;"></pre>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('css')
<style>
    .xml-link:hover {
        color: #0056b3 !important;
    }
    pre {
        white-space: pre-wrap;
        word-wrap: break-word;
    }
    .schema-item {
        margin-bottom: 0.5rem;
        padding: 0.5rem;
        background-color: #f8f9fa;
        border-radius: 0.25rem;
    }
    .schema-type {
        font-weight: bold;
        color: #495057;
    }
    .schema-value {
        font-family: monospace;
        color: #007bff;
    }

    /* Summary Section Styling */
    .summary-section {
        background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 1rem;
        margin-bottom: 1rem;
    }
    .summary-section .alert {
        border: none;
        background: transparent;
        padding: 0;
        margin: 0;
    }
    .summary-section .alert-info {
        color: #0c5460;
        background-color: transparent;
    }
    .summary-amount {
        font-size: 1.1em;
        font-weight: 600;
        color: #28a745;
    }
    .summary-count {
        font-weight: 600;
        color: #007bff;
    }

    /* Pretty XML Tree Styles */
    .xml-tree {
        font-family: 'Courier New', monospace;
        font-size: 13px;
        line-height: 1.4;
    }
    .xml-element {
        margin: 2px 0;
        padding: 2px 0;
    }
    .xml-tag {
        color: #d63384;
        font-weight: bold;
    }
    .xml-attribute {
        color: #fd7e14;
        font-weight: bold;
    }
    .xml-value {
        color: #198754;
        font-weight: normal;
    }
    .xml-comment {
        color: #6c757d;
        font-style: italic;
    }
    .xml-cdata {
        color: #0dcaf0;
        font-weight: normal;
    }
    .xml-indent {
        margin-left: 20px;
        border-left: 1px solid #dee2e6;
        padding-left: 10px;
    }
    .xml-collapsible {
        cursor: pointer;
    }
    .xml-collapsible:hover {
        background-color: #f8f9fa;
        border-radius: 3px;
    }
    .xml-collapsed .xml-children {
        display: none;
    }

    /* Pagination Styling */
    .pagination {
        justify-content: center;
        margin-bottom: 0;
    }
    .pagination .page-link {
        color: #007bff;
        border: 1px solid #dee2e6;
    }
    .pagination .page-item.active .page-link {
        background-color: #007bff;
        border-color: #007bff;
    }
    .pagination .page-link:hover {
        color: #0056b3;
        background-color: #e9ecef;
        border-color: #dee2e6;
    }
    .pagination-info {
        text-align: center;
        margin-bottom: 1rem;
        color: #6c757d;
        font-size: 0.875rem;
    }

    /* Layout Fixes */
    .card-body {
        overflow: hidden;
    }
    .table-responsive {
        margin-bottom: 0;
    }
    .summary-section {
        margin-top: 1rem;
    }
</style>
@endsection

@section('js')
<script>
console.log('Invoices index JS loaded');

$(document).ready(function() {
    console.log('Document ready, setting up handlers');

    // Filter functionality
    $('#filterForm select, #filterForm input[type="date"]').on('change', function() {
        // Auto-submit form when filters change
        $('#filterForm').submit();
    });

    // Clear filters functionality
    $('.btn-secondary').click(function(e) {
        if ($(this).text().includes('Clear')) {
            e.preventDefault();
            window.location.href = '{{ route("invoices.index") }}';
        }
    });

    // Collapse/expand filter section
    $('#filterCollapse').on('show.bs.collapse', function() {
        $(this).find('.fa-chevron-down').removeClass('fa-chevron-down').addClass('fa-chevron-up');
    });

    $('#filterCollapse').on('hide.bs.collapse', function() {
        $(this).find('.fa-chevron-up').removeClass('fa-chevron-up').addClass('fa-chevron-down');
    });

    // XML link click handler
    $('.xml-link').click(function(e) {
        console.log('XML link clicked');
        e.preventDefault();

        var invoiceId = $(this).data('invoice-id');
        var invoiceNumber = $(this).data('invoice-number');

        console.log('Invoice ID:', invoiceId, 'Invoice Number:', invoiceNumber);

        // Set modal title
        $('#modal-invoice-number').text(invoiceNumber);

        // Show loading state
        $('#xml-loading').show();
        $('#xml-error').hide();
        $('#xml-content').hide();

        // Show modal
        $('#xmlModal').modal('show');

        // Fetch XML data
        $.ajax({
            url: '/invoices/' + invoiceId + '/xml-data',
            method: 'GET',
            success: function(response) {
                console.log('XML data received:', response);
                $('#xml-loading').hide();

                if (response.success) {
                    // Display validation results
                    var validationHtml = '';
                    if (response.validation_result && response.validation_result.valid) {
                        validationHtml += '<div class="alert alert-success">';
                        validationHtml += '<i class="fas fa-check-circle mr-2"></i>';
                        validationHtml += '<strong>Valid XML</strong>';
                        if (response.validation_result.schema_used) {
                            validationHtml += ' - Validated against schema: ' + response.validation_result.schema_used;
                        }
                        validationHtml += '</div>';
                    } else {
                        validationHtml += '<div class="alert alert-danger">';
                        validationHtml += '<i class="fas fa-times-circle mr-2"></i>';
                        validationHtml += '<strong>Invalid XML</strong>';
                        validationHtml += '</div>';
                    }

                    if (response.validation_result && response.validation_result.errors && response.validation_result.errors.length > 0) {
                        validationHtml += '<div class="alert alert-danger">';
                        validationHtml += '<strong>Validation Errors:</strong><ul>';
                        response.validation_result.errors.forEach(function(error) {
                            validationHtml += '<li>' + error + '</li>';
                        });
                        validationHtml += '</ul></div>';
                    }

                    if (response.validation_result && response.validation_result.warnings && response.validation_result.warnings.length > 0) {
                        validationHtml += '<div class="alert alert-warning">';
                        validationHtml += '<strong>Warnings:</strong><ul>';
                        response.validation_result.warnings.forEach(function(warning) {
                            validationHtml += '<li>' + warning + '</li>';
                        });
                        validationHtml += '</ul></div>';
                    }

                    $('#validation-results').html(validationHtml);

                    // Display schema information
                    var schemaHtml = '';
                    if (response.schema_info && response.schema_info.length > 0) {
                        response.schema_info.forEach(function(schema) {
                            schemaHtml += '<div class="schema-item">';
                            schemaHtml += '<div class="schema-type">' + schema.type + ':</div>';
                            if (schema.uri) {
                                schemaHtml += '<div class="schema-value">' + schema.prefix + ': ' + schema.uri + '</div>';
                            } else if (schema.value) {
                                schemaHtml += '<div class="schema-value">' + schema.value + '</div>';
                            }
                            schemaHtml += '</div>';
                        });
                    } else {
                        schemaHtml = '<p class="text-muted">No schema information found in XML</p>';
                    }
                    $('#schema-info').html(schemaHtml);

                    // Display structured data
                    var structuredHtml = '';
                    if (response.structured_data) {
                        var data = response.structured_data;

                        // Invoice Details
                        if (data.invoice_details && Object.keys(data.invoice_details).length > 0) {
                            structuredHtml += '<div class="mb-3">';
                            structuredHtml += '<h6 class="text-primary">Invoice Details</h6>';
                            structuredHtml += '<div class="table-responsive">';
                            structuredHtml += '<table class="table table-sm table-bordered">';
                            structuredHtml += '<tbody>';
                            Object.keys(data.invoice_details).forEach(function(key) {
                                structuredHtml += '<tr><td><strong>' + key + '</strong></td><td>' + data.invoice_details[key] + '</td></tr>';
                            });
                            structuredHtml += '</tbody></table></div></div>';
                        }

                        // Supplier Information
                        if (data.supplier_info && Object.keys(data.supplier_info).length > 0) {
                            structuredHtml += '<div class="mb-3">';
                            structuredHtml += '<h6 class="text-success">Supplier Information</h6>';
                            structuredHtml += '<div class="table-responsive">';
                            structuredHtml += '<table class="table table-sm table-bordered">';
                            structuredHtml += '<tbody>';
                            Object.keys(data.supplier_info).forEach(function(key) {
                                structuredHtml += '<tr><td><strong>' + key + '</strong></td><td>' + data.supplier_info[key] + '</td></tr>';
                            });
                            structuredHtml += '</tbody></table></div></div>';
                        }

                        // Customer Information
                        if (data.customer_info && Object.keys(data.customer_info).length > 0) {
                            structuredHtml += '<div class="mb-3">';
                            structuredHtml += '<h6 class="text-info">Customer Information</h6>';
                            structuredHtml += '<div class="table-responsive">';
                            structuredHtml += '<table class="table table-sm table-bordered">';
                            structuredHtml += '<tbody>';
                            Object.keys(data.customer_info).forEach(function(key) {
                                structuredHtml += '<tr><td><strong>' + key + '</strong></td><td>' + data.customer_info[key] + '</td></tr>';
                            });
                            structuredHtml += '</tbody></table></div></div>';
                        }

                        // Line Items
                        if (data.line_items && data.line_items.length > 0) {
                            structuredHtml += '<div class="mb-3">';
                            structuredHtml += '<h6 class="text-warning">Line Items</h6>';
                            structuredHtml += '<div class="table-responsive">';
                            structuredHtml += '<table class="table table-sm table-bordered">';
                            structuredHtml += '<thead><tr><th>Description</th><th>Quantity</th><th>Unit Price</th><th>Amount</th></tr></thead>';
                            structuredHtml += '<tbody>';
                            data.line_items.forEach(function(item) {
                                structuredHtml += '<tr>';
                                structuredHtml += '<td>' + (item.description || 'N/A') + '</td>';
                                structuredHtml += '<td>' + (item.quantity || 'N/A') + '</td>';
                                structuredHtml += '<td>' + (item.unit_price || 'N/A') + '</td>';
                                structuredHtml += '<td>' + (item.amount || 'N/A') + '</td>';
                                structuredHtml += '</tr>';
                            });
                            structuredHtml += '</tbody></table></div></div>';
                        }

                        // Totals
                        if (data.totals && Object.keys(data.totals).length > 0) {
                            structuredHtml += '<div class="mb-3">';
                            structuredHtml += '<h6 class="text-danger">Totals</h6>';
                            structuredHtml += '<div class="table-responsive">';
                            structuredHtml += '<table class="table table-sm table-bordered">';
                            structuredHtml += '<tbody>';
                            Object.keys(data.totals).forEach(function(key) {
                                structuredHtml += '<tr><td><strong>' + key + '</strong></td><td>' + data.totals[key] + '</td></tr>';
                            });
                            structuredHtml += '</tbody></table></div></div>';
                        }

                        if (data.error) {
                            structuredHtml += '<div class="alert alert-warning">';
                            structuredHtml += '<i class="fas fa-exclamation-triangle mr-2"></i>';
                            structuredHtml += data.error;
                            structuredHtml += '</div>';
                        }
                    }

                    if (structuredHtml === '') {
                        structuredHtml = '<p class="text-muted">No structured data could be extracted from this XML</p>';
                    }

                    $('#structured-data').html(structuredHtml);

                    // Display formatted XML
                    $('#xml-display').text(response.xml_data || response.formatted_xml || response.raw_xml);

                    // Create pretty XML tree view
                    var treeHtml = createPrettyXmlTree(response.raw_xml);
                    $('#xml-tree-view').html(treeHtml);

                    // Store raw XML for copy functionality
                    $('#xml-display').data('raw-xml', response.raw_xml);

                    $('#xml-content').show();
                } else {
                    $('#xml-error-message').text(response.message);
                    $('#xml-error').show();
                }
            },
            error: function(xhr) {
                console.log('XML data error:', xhr);
                $('#xml-loading').hide();
                var response = xhr.responseJSON;
                var errorMessage = response ? response.message : 'An error occurred while loading XML data';
                $('#xml-error-message').text(errorMessage);
                $('#xml-error').show();
            }
        });
    });

    // Copy XML functionality
    $('#copy-xml').click(function() {
        var rawXml = $('#xml-display').data('raw-xml');
        if (rawXml) {
            navigator.clipboard.writeText(rawXml).then(function() {
                // Show success feedback
                var originalText = $(this).html();
                $(this).html('<i class="fas fa-check mr-1"></i> Copied!');
                $(this).removeClass('btn-outline-secondary').addClass('btn-success');

                setTimeout(function() {
                    $('#copy-xml').html(originalText);
                    $('#copy-xml').removeClass('btn-success').addClass('btn-outline-secondary');
                }, 2000);
            }.bind(this)).catch(function(err) {
                console.error('Failed to copy XML: ', err);
                alert('Failed to copy XML to clipboard');
            });
        }
    });

    // Toggle XML view functionality
    $('#toggle-xml-view').click(function() {
        var treeView = $('#xml-tree-view');
        var rawView = $('#xml-display');

        if (treeView.is(':visible')) {
            treeView.hide();
            rawView.show();
            $(this).html('<i class="fas fa-tree mr-1"></i> Tree View');
        } else {
            treeView.show();
            rawView.hide();
            $(this).html('<i class="fas fa-eye mr-1"></i> Raw View');
        }
    });

    // XML tree creation function
    function createPrettyXmlTree(xmlString) {
        try {
            var parser = new DOMParser();
            var xmlDoc = parser.parseFromString(xmlString, "text/xml");

            if (xmlDoc.getElementsByTagName("parsererror").length > 0) {
                return '<div class="alert alert-danger">Invalid XML format</div>';
            }

            return processXmlNode(xmlDoc.documentElement, 0);
        } catch (e) {
            return '<div class="alert alert-danger">Error parsing XML: ' + e.message + '</div>';
        }
    }

    function processXmlNode(node, level) {
        var html = '';
        var indent = '  '.repeat(level);
        var hasChildren = node.childNodes.length > 1 ||
                         (node.childNodes.length === 1 && node.childNodes[0].nodeType !== Node.TEXT_NODE);

        // Create element start
        html += '<div class="xml-element">';

        if (hasChildren) {
            html += '<span class="xml-toggle" onclick="toggleXmlNode(this)">-</span>';
            html += '<span class="xml-collapsible" onclick="toggleXmlNode(this.previousElementSibling)">';
        } else {
            html += '<span class="xml-leaf"></span>';
        }

        // Element tag
        html += '<span class="xml-tag">&lt;' + node.nodeName + '</span>';

        // Attributes
        if (node.attributes && node.attributes.length > 0) {
            for (var i = 0; i < node.attributes.length; i++) {
                var attr = node.attributes[i];
                html += ' <span class="xml-attribute">' + attr.name + '</span>';
                html += '="<span class="xml-value">' + escapeHtml(attr.value) + '</span>"';
            }
        }

        // Check if it's a self-closing tag
        var hasTextContent = false;
        var textContent = '';

        for (var i = 0; i < node.childNodes.length; i++) {
            var child = node.childNodes[i];
            if (child.nodeType === Node.TEXT_NODE && child.textContent.trim()) {
                hasTextContent = true;
                textContent = child.textContent.trim();
                break;
            }
        }

        if (!hasChildren && !hasTextContent) {
            html += '<span class="xml-tag"> /&gt;</span>';
        } else {
            html += '<span class="xml-tag">&gt;</span>';

            // Text content
            if (hasTextContent) {
                html += '<span class="xml-value">' + escapeHtml(textContent) + '</span>';
            }

            // Closing tag
            if (hasChildren) {
                html += '</span>';
                html += '<div class="xml-children">';

                // Process child nodes
                for (var i = 0; i < node.childNodes.length; i++) {
                    var child = node.childNodes[i];
                    if (child.nodeType === Node.ELEMENT_NODE) {
                        html += processXmlNode(child, level + 1);
                    } else if (child.nodeType === Node.TEXT_NODE && child.textContent.trim()) {
                        html += '<div class="xml-element xml-leaf">';
                        html += '<span class="xml-value">' + escapeHtml(child.textContent.trim()) + '</span>';
                        html += '</div>';
                    } else if (child.nodeType === Node.COMMENT_NODE) {
                        html += '<div class="xml-element xml-leaf">';
                        html += '<span class="xml-comment">&lt;!-- ' + escapeHtml(child.textContent) + ' --&gt;</span>';
                        html += '</div>';
                    } else if (child.nodeType === Node.CDATA_SECTION_NODE) {
                        html += '<div class="xml-element xml-leaf">';
                        html += '<span class="xml-cdata">&lt;![CDATA[' + escapeHtml(child.textContent) + ']]&gt;</span>';
                        html += '</div>';
                    }
                }

                html += '</div>';
            }

            html += '<span class="xml-tag">&lt;/' + node.nodeName + '&gt;</span>';
        }

        html += '</div>';
        return html;
    }

    function toggleXmlNode(toggleElement) {
        var element = toggleElement.parentElement;
        var children = element.querySelector('.xml-children');
        var toggle = element.querySelector('.xml-toggle');

        if (element.classList.contains('xml-collapsed')) {
            element.classList.remove('xml-collapsed');
            toggle.textContent = '-';
        } else {
            element.classList.add('xml-collapsed');
            toggle.textContent = '+';
        }
    }

    function escapeHtml(text) {
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    // Clear modal content when closed
    $('#xmlModal').on('hidden.bs.modal', function() {
        $('#xml-loading').hide();
        $('#xml-error').hide();
        $('#xml-content').hide();
        $('#validation-results').empty();
        $('#schema-info').empty();
        $('#structured-data').empty();
        $('#xml-tree-view').empty();
        $('#xml-display').text('').removeData('raw-xml');
    });

    console.log('XML link handlers setup complete');
});
</script>
@endsection
