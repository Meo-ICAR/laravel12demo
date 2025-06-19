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
    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="icon fas fa-check"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    {{-- Filters --}}
    <div class="card card-primary card-outline mb-4">
        <div class="card-header">
            <h3 class="card-title">Filter Invoices</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <form method="GET" action="{{ route('invoices.index') }}">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Invoice Number" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-control">
                            <option value="">All Statuses</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-3">
                        <input type="number" step="0.01" name="amount_from" class="form-control" placeholder="Amount From" value="{{ request('amount_from') }}">
                    </div>
                    <div class="col-md-3">
                        <input type="number" step="0.01" name="amount_to" class="form-control" placeholder="Amount To" value="{{ request('amount_to') }}">
                    </div>
                    <div class="col-md-6 text-right">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('invoices.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Reset
                        </a>
                        <a href="{{ route('invoices.import') }}" class="btn btn-success">
                            <i class="fas fa-upload"></i> Import Invoices
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Invoices Table --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Invoices List</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Invoice Number</th>
                            <th>Date</th>
                            <th>Cliente</th>
                            <th>Total Amount</th>
                            <th>Tax Amount</th>
                            <th>Status</th>
                            <th style="width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                            <tr>
                                <td>{{ $invoice->invoice_number }}</td>
                                <td>
                                    @if($invoice->invoice_date && ($invoice->invoice_date instanceof \Illuminate\Support\Carbon || strtotime($invoice->invoice_date)))
                                        {{ $invoice->invoice_date->format('d/m/Y') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $invoice->cliente }}</td>
                                <td>{{ $invoice->formatted_total_amount }}</td>
                                <td>{{ $invoice->formatted_tax_amount }}</td>
                                <td>
                                    <span class="badge badge-{{ $invoice->status === 'imported' ? 'success' : 'warning' }}">
                                        {{ ucfirst($invoice->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="#" class="btn btn-info btn-sm" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    {{-- Add more actions as needed --}}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No invoices found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer clearfix">
            {{ $invoices->links() }}
        </div>
    </div>

    {{-- Optional: Summary Table --}}
    <div class="card mt-4">
        <div class="card-header">
            <h3 class="card-title">Summary (Current Page)</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Invoice Number</th>
                            <th>Invoice Date</th>
                            <th>Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $invoice)
                            <tr>
                                <td>{{ $invoice->cliente }}</td>
                                <td>{{ $invoice->invoice_number }}</td>
                                <td>
                                    @if($invoice->invoice_date && ($invoice->invoice_date instanceof \Illuminate\Support\Carbon || strtotime($invoice->invoice_date)))
                                        {{ $invoice->invoice_date->format('d/m/Y') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $invoice->total_amount }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Auto-submit form when select elements change
    $('#status').change(function() {
        $('#filterForm').submit();
    });

    // Add debounce to search input
    let searchTimeout;
    $('#search').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            $('#filterForm').submit();
        }, 500);
    });

    // Status Distribution Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($stats['status_counts']->pluck('status')) !!},
            datasets: [{
                data: {!! json_encode($stats['status_counts']->pluck('count')) !!},
                backgroundColor: [
                    '#28a745',
                    '#ffc107',
                    '#17a2b8',
                    '#dc3545'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Monthly Trends Chart
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($stats['monthly_totals']->map(function($item) {
                return date('M Y', mktime(0, 0, 0, $item->month, 1, $item->year));
            })) !!},
            datasets: [{
                label: 'Total Amount',
                data: {!! json_encode($stats['monthly_totals']->pluck('total')) !!},
                borderColor: '#007bff',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString('it-IT', {
                                style: 'currency',
                                currency: 'EUR'
                            });
                        }
                    }
                }
            }
        }
    });

    $('input[type="file"]').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName);
    });
});
</script>
@stop
