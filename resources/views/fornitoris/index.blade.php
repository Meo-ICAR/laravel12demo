@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <!-- Filter Form -->
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-filter"></i> Filter Fornitori
                <button class="btn btn-link float-right p-0" type="button" data-toggle="collapse" data-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </h5>
        </div>
        <div id="filterCollapse" class="collapse">
            <div class="card-body">
                <form method="GET" action="{{ route('fornitoris.index') }}" class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="name">Search</label>
                            <input type="text" name="name" id="name" class="form-control"
                                   value="{{ request('name') }}"
                                   placeholder="Search by name, nome, codice, COGE, P.IVA, email...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="coordinatore">Coordinatore</label>
                            <input type="text" name="coordinatore" id="coordinatore" class="form-control"
                                   value="{{ request('coordinatore') }}"
                                   placeholder="Search by coordinatore...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="sort_by">Sort by</label>
                            <select name="sort_by" id="sort_by" class="form-control">
                                <option value="name" {{ $sortBy === 'name' ? 'selected' : '' }}>Name</option>
                                <option value="codice" {{ $sortBy === 'codice' ? 'selected' : '' }}>Codice</option>
                                <option value="coge" {{ $sortBy === 'coge' ? 'selected' : '' }}>COGE</option>
                                <option value="piva" {{ $sortBy === 'piva' ? 'selected' : '' }}>P.IVA</option>
                                <option value="email" {{ $sortBy === 'email' ? 'selected' : '' }}>Email</option>
                                <option value="anticipo" {{ $sortBy === 'anticipo' ? 'selected' : '' }}>Anticipo</option>
                                <option value="contributo" {{ $sortBy === 'contributo' ? 'selected' : '' }}>Contributo</option>
                                <option value="regione" {{ $sortBy === 'regione' ? 'selected' : '' }}>Regione</option>
                                <option value="citta" {{ $sortBy === 'citta' ? 'selected' : '' }}>Città</option>
                                <option value="coordinatore" {{ $sortBy === 'coordinatore' ? 'selected' : '' }}>Coordinatore</option>
                                <option value="created_at" {{ $sortBy === 'created_at' ? 'selected' : '' }}>Created Date</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="sort_direction">Direction</label>
                            <select name="sort_direction" id="sort_direction" class="form-control">
                                <option value="asc" {{ $sortDirection === 'asc' ? 'selected' : '' }}>Ascending</option>
                                <option value="desc" {{ $sortDirection === 'desc' ? 'selected' : '' }}>Descending</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary mr-2">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <a href="{{ route('fornitoris.index') }}" class="btn btn-secondary">
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
            <h3 class="card-title">Fornitori List</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-success btn-sm mr-2" data-toggle="modal" data-target="#importModal">
                    <i class="fas fa-upload"></i> Import CSV
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped mb-0">
                    <thead>
                        <tr>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(array_merge(['sort_by' => 'codice', 'sort_direction' => $sortBy === 'codice' && $sortDirection === 'asc' ? 'desc' : 'asc'], array_filter(request()->only(['name', 'coordinatore'])))) }}"
                                   class="text-dark text-decoration-none">
                                    Codice
                                    @if($sortBy === 'codice')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(array_merge(['sort_by' => 'name', 'sort_direction' => $sortBy === 'name' && $sortDirection === 'asc' ? 'desc' : 'asc'], array_filter(request()->only(['name', 'coordinatore'])))) }}"
                                   class="text-dark text-decoration-none">
                                    Name
                                    @if($sortBy === 'name')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(array_merge(['sort_by' => 'coge', 'sort_direction' => $sortBy === 'coge' && $sortDirection === 'asc' ? 'desc' : 'asc'], array_filter(request()->only(['name', 'coordinatore'])))) }}"
                                   class="text-dark text-decoration-none">
                                    COGE
                                    @if($sortBy === 'coge')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(array_merge(['sort_by' => 'piva', 'sort_direction' => $sortBy === 'piva' && $sortDirection === 'asc' ? 'desc' : 'asc'], array_filter(request()->only(['name', 'coordinatore'])))) }}"
                                   class="text-dark text-decoration-none">
                                    P.IVA
                                    @if($sortBy === 'piva')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(array_merge(['sort_by' => 'email', 'sort_direction' => $sortBy === 'email' && $sortDirection === 'asc' ? 'desc' : 'asc'], array_filter(request()->only(['name', 'coordinatore'])))) }}"
                                   class="text-dark text-decoration-none">
                                    Email
                                    @if($sortBy === 'email')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(array_merge(['sort_by' => 'anticipo', 'sort_direction' => $sortBy === 'anticipo' && $sortDirection === 'asc' ? 'desc' : 'asc'], array_filter(request()->only(['name', 'coordinatore'])))) }}"
                                   class="text-dark text-decoration-none">
                                    Anticipo
                                    @if($sortBy === 'anticipo')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(array_merge(['sort_by' => 'contributo', 'sort_direction' => $sortBy === 'contributo' && $sortDirection === 'asc' ? 'desc' : 'asc'], array_filter(request()->only(['name', 'coordinatore'])))) }}"
                                   class="text-dark text-decoration-none">
                                    Contributo
                                    @if($sortBy === 'contributo')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(array_merge(['sort_by' => 'regione', 'sort_direction' => $sortBy === 'regione' && $sortDirection === 'asc' ? 'desc' : 'asc'], array_filter(request()->only(['name', 'coordinatore'])))) }}"
                                   class="text-dark text-decoration-none">
                                    Regione
                                    @if($sortBy === 'regione')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(array_merge(['sort_by' => 'citta', 'sort_direction' => $sortBy === 'citta' && $sortDirection === 'asc' ? 'desc' : 'asc'], array_filter(request()->only(['name', 'coordinatore'])))) }}"
                                   class="text-dark text-decoration-none">
                                    Città
                                    @if($sortBy === 'citta')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(array_merge(['sort_by' => 'coordinatore', 'sort_direction' => $sortBy === 'coordinatore' && $sortDirection === 'asc' ? 'desc' : 'asc'], array_filter(request()->only(['name', 'coordinatore'])))) }}"
                                   class="text-dark text-decoration-none">
                                    Coordinatore
                                    @if($sortBy === 'coordinatore')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Coge</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($fornitoris as $fornitore)
                            <tr>
                                <td>{{ $fornitore->codice }}</td>
                                <td>{{ $fornitore->name }}</td>
                                <td>{{ $fornitore->coge }}</td>
                                <td>{{ $fornitore->piva }}</td>
                                <td>{{ $fornitore->email }}</td>
                                <td class="text-right">{{ $fornitore->anticipo ? '€ ' . number_format($fornitore->anticipo, 2, ',', '.') : '-' }}</td>
                                <td class="text-right">{{ $fornitore->contributo ? '€ ' . number_format($fornitore->contributo, 2, ',', '.') : '-' }}</td>
                                <td>{{ $fornitore->regione }}</td>
                                <td>{{ $fornitore->citta }}</td>
                                <td>{{ $fornitore->coordinatore }}</td>
                                <td>
                                    @if($fornitore->coge)
                                        <a href="{{ route('fornitoris.invoices.show', $fornitore->id) }}">{{ $fornitore->coge }}</a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('fornitoris.edit', $fornitore) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">No fornitori found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer clearfix">
            {{ $fornitoris->links() }}
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">
                    <i class="fas fa-upload"></i> Import Fornitori from File
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('fornitoris.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="file">Select File</label>
                        <input type="file" class="form-control-file" id="file" name="file" accept=".csv,.tsv,.xlsx,.xls" required>
                        <small class="form-text text-muted">
                            Supported formats: CSV (comma-delimited), TSV (tab-delimited), XLSX, XLS. Maximum file size: 2MB.
                        </small>
                    </div>

                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> File Format Requirements:</h6>
                        <p class="mb-1">Your file should have the following columns (comma or tab-delimited):</p>
                        <ul class="mb-0 small">
                            <li><strong>codice</strong> - Supplier code</li>
                            <li><strong>denominazione</strong> - Company/Supplier name</li>
                            <li><strong>nome</strong> - Contact person name</li>
                            <li><strong>natoil</strong> - Birth date (format: dd/mm/yyyy)</li>
                            <li><strong>indirizzo</strong> - Address</li>
                            <li><strong>comune</strong> - City</li>
                            <li><strong>cap</strong> - Postal code</li>
                            <li><strong>prov</strong> - Province</li>
                            <li><strong>tel</strong> - Phone number</li>
                            <li><strong>email</strong> - Email address</li>
                            <li><strong>regione</strong> - Region</li>
                            <li><strong>coordinatore</strong> - Coordinator</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-upload"></i> Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.table th a {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.5rem;
    transition: background-color 0.2s;
}

.table th a:hover {
    background-color: #f8f9fa;
    border-radius: 4px;
}

.table th a i {
    margin-left: 0.5rem;
}

.table th a:hover i {
    color: #007bff !important;
}
</style>
@endsection
