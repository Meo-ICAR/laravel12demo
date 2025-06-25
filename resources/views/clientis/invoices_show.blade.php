@extends('layouts.admin')

@section('title', 'Clienti Invoices - COGE: ' . $coge)

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Clienti Invoices</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('clientis.index') }}">Clienti</a></li>
                <li class="breadcrumb-item active">Invoices - COGE: {{ $coge }}</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-file-invoice mr-2"></i>
                        Invoices for COGE: <strong>{{ $coge }}</strong>
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('clientis.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Clienti
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="row">
                            <div class="col-md-3">
                                <strong>Clienti:</strong> {{ $clienti->name }}
                            </div>
                            <div class="col-md-3">
                                <strong>COGE:</strong> {{ $coge }}
                            </div>
                            <div class="col-md-3">
                                <strong>Total Invoices:</strong> {{ $invoices->total() }}
                            </div>
                            <div class="col-md-3">
                                <strong>Total Amount:</strong> € {{ number_format($invoices->sum('total_amount'), 2, ',', '.') }}
                            </div>
                        </div>
                    </div>

                    <!-- Filter Section -->
                    <div class="card card-outline card-primary mb-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-filter mr-2"></i>Filters
                                <button class="btn btn-sm btn-outline-secondary float-right" type="button" data-toggle="collapse" data-target="#filterCollapse">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </h5>
                        </div>
                        <div class="collapse" id="filterCollapse">
                            <div class="card-body">
                                <form method="GET" action="{{ route('clientis.invoices.show', $clienti->id) }}">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="date_from">Date From</label>
                                                <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="date_to">Date To</label>
                                                <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="amount_from">Amount From</label>
                                                <input type="number" name="amount_from" id="amount_from" class="form-control" step="0.01" value="{{ request('amount_from') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="amount_to">Amount To</label>
                                                <input type="number" name="amount_to" id="amount_to" class="form-control" step="0.01" value="{{ request('amount_to') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="status">Status</label>
                                                <select name="status" id="status" class="form-control">
                                                    <option value="">All Statuses</option>
                                                    @foreach($statuses as $status)
                                                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                                            {{ ucfirst($status) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
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
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-search mr-1"></i> Apply Filters
                                            </button>
                                            <a href="{{ route('clientis.invoices.show', $clienti->id) }}" class="btn btn-secondary">
                                                <i class="fas fa-times mr-1"></i> Clear
                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    @if(request()->hasAny(['date_from', 'date_to', 'amount_from', 'amount_to', 'status', 'fornitore']))
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-1"></i>
                            Showing {{ $invoices->total() }} results
                            @if(request('date_from') || request('date_to'))
                                | Date: {{ request('date_from', 'Any') }} to {{ request('date_to', 'Any') }}
                            @endif
                            @if(request('amount_from') || request('amount_to'))
                                | Amount: €{{ request('amount_from', 'Any') }} to €{{ request('amount_to', 'Any') }}
                            @endif
                            @if(request('status'))
                                | Status: {{ ucfirst(request('status')) }}
                            @endif
                            @if(request('fornitore'))
                                | Fornitore: {{ request('fornitore') }}
                            @endif
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'invoice_date', 'direction' => request('sort') == 'invoice_date' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-dark text-decoration-none">
                                            Date
                                            @if(request('sort') == 'invoice_date')
                                                <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                            @else
                                                <i class="fas fa-sort text-muted"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'invoice_number', 'direction' => request('sort') == 'invoice_number' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-dark text-decoration-none">
                                            Invoice Number
                                            @if(request('sort') == 'invoice_number')
                                                <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                            @else
                                                <i class="fas fa-sort text-muted"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'fornitore', 'direction' => request('sort') == 'fornitore' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-dark text-decoration-none">
                                            Fornitore
                                            @if(request('sort') == 'fornitore')
                                                <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                            @else
                                                <i class="fas fa-sort text-muted"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-right">
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'tax_amount', 'direction' => request('sort') == 'tax_amount' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-dark text-decoration-none">
                                            Tax Amount
                                            @if(request('sort') == 'tax_amount')
                                                <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                            @else
                                                <i class="fas fa-sort text-muted"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-right">
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'total_amount', 'direction' => request('sort') == 'total_amount' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-dark text-decoration-none">
                                            Total Amount
                                            @if(request('sort') == 'total_amount')
                                                <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                            @else
                                                <i class="fas fa-sort text-muted"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'status', 'direction' => request('sort') == 'status' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-dark text-decoration-none">
                                            Status
                                            @if(request('sort') == 'status')
                                                <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                            @else
                                                <i class="fas fa-sort text-muted"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoices as $invoice)
                                    <tr>
                                        <td>{{ $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') : '-' }}</td>
                                        <td>{{ $invoice->invoice_number }}</td>
                                        <td>{{ $invoice->fornitore }}</td>
                                        <td class="text-right">€ {{ number_format($invoice->tax_amount, 2, ',', '.') }}</td>
                                        <td class="text-right">€ {{ number_format($invoice->total_amount, 2, ',', '.') }}</td>
                                        <td>
                                            <span class="badge badge-{{ $invoice->status == 'imported' ? 'success' : ($invoice->status == 'reconciled' ? 'info' : 'warning') }}">
                                                {{ ucfirst($invoice->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('invoices.edit', $invoice->id) }}" class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="{{ route('invoices.show', $invoice->id) }}" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No invoices found for this COGE value.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $invoices->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
