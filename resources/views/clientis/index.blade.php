@extends('layouts.admin')

@section('title', 'Clienti')

@section('content_header')
    <h1>Clienti</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card mb-3">
        <div class="card-header" id="filterHeader" style="cursor: pointer;" data-toggle="collapse" data-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
            <h5 class="card-title mb-0">
                <i class="fas fa-filter mr-2"></i> Search, Filter & Sort
                <i class="fas fa-chevron-down float-right" id="filterIcon"></i>
            </h5>
        </div>
        <div id="filterCollapse" class="collapse">
            <div class="card-body">
                <form method="GET" action="{{ route('clientis.index') }}" class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" name="name" id="name" class="form-control" value="{{ request('name') }}" placeholder="Search by name...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="piva">PIVA</label>
                            <input type="text" name="piva" id="piva" class="form-control" value="{{ request('piva') }}" placeholder="Search by PIVA...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="coge">COGE</label>
                            <input type="text" name="coge" id="coge" class="form-control" value="{{ request('coge') }}" placeholder="Search by COGE...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="text" name="email" id="email" class="form-control" value="{{ request('email') }}" placeholder="Search by email...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="regione">Regione</label>
                            <input type="text" name="regione" id="regione" class="form-control" value="{{ request('regione') }}" placeholder="Search by regione...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="citta">Città</label>
                            <input type="text" name="citta" id="citta" class="form-control" value="{{ request('citta') }}" placeholder="Search by città...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="codice">Codice</label>
                            <input type="text" name="codice" id="codice" class="form-control" value="{{ request('codice') }}" placeholder="Search by codice...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="customertype_id">Customer Type</label>
                            <select name="customertype_id" id="customertype_id" class="form-control">
                                <option value="">All Types</option>
                                @foreach($customertypes as $type)
                                    <option value="{{ $type->id }}" {{ request('customertype_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="sort_by">Sort By</label>
                            <select name="sort_by" id="sort_by" class="form-control">
                                <option value="name" {{ request('sort_by', $sortBy ?? 'name') == 'name' ? 'selected' : '' }}>Name</option>
                                <option value="piva" {{ request('sort_by', $sortBy ?? '') == 'piva' ? 'selected' : '' }}>PIVA</option>
                                <option value="coge" {{ request('sort_by', $sortBy ?? '') == 'coge' ? 'selected' : '' }}>COGE</option>
                                <option value="email" {{ request('sort_by', $sortBy ?? '') == 'email' ? 'selected' : '' }}>Email</option>
                                <option value="regione" {{ request('sort_by', $sortBy ?? '') == 'regione' ? 'selected' : '' }}>Regione</option>
                                <option value="citta" {{ request('sort_by', $sortBy ?? '') == 'citta' ? 'selected' : '' }}>Città</option>
                                <option value="codice" {{ request('sort_by', $sortBy ?? '') == 'codice' ? 'selected' : '' }}>Codice</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="sort_direction">Sort Direction</label>
                            <select name="sort_direction" id="sort_direction" class="form-control">
                                <option value="asc" {{ request('sort_direction', $sortDirection ?? 'asc') == 'asc' ? 'selected' : '' }}>Ascending</option>
                                <option value="desc" {{ request('sort_direction', $sortDirection ?? 'asc') == 'desc' ? 'selected' : '' }}>Descending</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12 d-flex align-items-end">
                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary mr-2">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <a href="{{ route('clientis.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Clienti List</h3>
            <div class="card-tools">
                <a href="{{ route('clientis.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add Cliente
                </a>
                <form action="{{ route('clientis.importInvoiceinsToInvoicesByClienti') }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to import all eligible Invoiceins to Invoices (by Clienti)?');">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="fas fa-random"></i> Import Invoiceins to Invoices (by Clienti)
                    </button>
                </form>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Customer Type</th>
                            <th>PIVA</th>
                            <th>CF</th>
                            <th>COGE</th>
                            <th>Email</th>
                            <th>Regione</th>
                            <th>Città</th>
                            <th>Codice</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clientis as $clienti)
                            <tr>
                                <td>{{ $clienti->name }}</td>
                                <td>{{ $clienti->customertype ? $clienti->customertype->name : '' }}</td>
                                <td>{{ $clienti->piva }}</td>
                                <td>{{ $clienti->cf }}</td>
                                <td>
                                    @if($clienti->coge)
                                        <a href="{{ route('clientis.invoices.show', $clienti->id) }}" class="coge-link">
                                            {{ $clienti->coge }}
                                            @if($clienti->invoice_count > 0)
                                                <span class="badge badge-info ml-1">{{ $clienti->invoice_count }}</span>
                                            @endif
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $clienti->email }}</td>
                                <td>{{ $clienti->regione }}</td>
                                <td>{{ $clienti->citta }}</td>
                                <td>{{ $clienti->codice }}</td>
                                <td>
                                    <a href="{{ route('clientis.show', $clienti) }}" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('clientis.edit', $clienti) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                                    <form action="{{ route('clientis.destroy', $clienti) }}" method="POST" style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No clienti found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer clearfix">
            <!-- Pagination removed - using Collection instead of paginated results -->
        </div>
    </div>
</div>
@endsection

<style>
.coge-link {
    color: #007bff;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.2s ease;
}

.coge-link:hover {
    color: #0056b3;
    text-decoration: underline;
    cursor: pointer;
}

.coge-link:active {
    color: #004085;
}
</style>
