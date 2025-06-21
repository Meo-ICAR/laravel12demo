@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <!-- Filter Form -->
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-filter"></i> Filter Fornitori
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('fornitoris.index') }}" class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="name">Search</label>
                        <input type="text" name="name" id="name" class="form-control"
                               value="{{ request('name') }}"
                               placeholder="Search by name, codice, P.IVA, email, regione, città...">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="sort_by">Sort by</label>
                        <select name="sort_by" id="sort_by" class="form-control">
                            <option value="name" {{ $sortBy === 'name' ? 'selected' : '' }}>Name</option>
                            <option value="codice" {{ $sortBy === 'codice' ? 'selected' : '' }}>Codice</option>
                            <option value="piva" {{ $sortBy === 'piva' ? 'selected' : '' }}>P.IVA</option>
                            <option value="email" {{ $sortBy === 'email' ? 'selected' : '' }}>Email</option>
                            <option value="anticipo" {{ $sortBy === 'anticipo' ? 'selected' : '' }}>Anticipo</option>
                            <option value="issubfornitore" {{ $sortBy === 'issubfornitore' ? 'selected' : '' }}>Is Subfornitore</option>
                            <option value="regione" {{ $sortBy === 'regione' ? 'selected' : '' }}>Regione</option>
                            <option value="citta" {{ $sortBy === 'citta' ? 'selected' : '' }}>Città</option>
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
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary mr-2">
                            <i class="fas fa-search"></i> Search & Sort
                        </button>
                        <a href="{{ route('fornitoris.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Fornitori List</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-success btn-sm mr-2" data-toggle="modal" data-target="#importModal">
                    <i class="fas fa-upload"></i> Import CSV
                </button>
                <a href="{{ route('fornitoris.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add Fornitore
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped mb-0">
                    <thead>
                        <tr>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'codice', 'sort_direction' => $sortBy === 'codice' && $sortDirection === 'asc' ? 'desc' : 'asc']) }}"
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
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'name', 'sort_direction' => $sortBy === 'name' && $sortDirection === 'asc' ? 'desc' : 'asc']) }}"
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
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'piva', 'sort_direction' => $sortBy === 'piva' && $sortDirection === 'asc' ? 'desc' : 'asc']) }}"
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
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'email', 'sort_direction' => $sortBy === 'email' && $sortDirection === 'asc' ? 'desc' : 'asc']) }}"
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
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'anticipo', 'sort_direction' => $sortBy === 'anticipo' && $sortDirection === 'asc' ? 'desc' : 'asc']) }}"
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
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'issubfornitore', 'sort_direction' => $sortBy === 'issubfornitore' && $sortDirection === 'asc' ? 'desc' : 'asc']) }}"
                                   class="text-dark text-decoration-none">
                                    Is Subfornitore
                                    @if($sortBy === 'issubfornitore')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Operatore</th>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'regione', 'sort_direction' => $sortBy === 'regione' && $sortDirection === 'asc' ? 'desc' : 'asc']) }}"
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
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'citta', 'sort_direction' => $sortBy === 'citta' && $sortDirection === 'asc' ? 'desc' : 'asc']) }}"
                                   class="text-dark text-decoration-none">
                                    Città
                                    @if($sortBy === 'citta')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($fornitoris as $fornitore)
                            <tr>
                                <td>{{ $fornitore->codice }}</td>
                                <td>{{ $fornitore->name }}</td>
                                <td>{{ $fornitore->piva }}</td>
                                <td>{{ $fornitore->email }}</td>
                                <td class="text-right">{{ $fornitore->anticipo ? '€ ' . number_format($fornitore->anticipo, 2, ',', '.') : '-' }}</td>
                                <td class="text-center">
                                    @if($fornitore->issubfornitore)
                                        <span class="badge badge-success">Yes</span>
                                    @else
                                        <span class="badge badge-secondary">No</span>
                                    @endif
                                </td>
                                <td>{{ $fornitore->operatore }}</td>
                                <td>{{ $fornitore->regione }}</td>
                                <td>{{ $fornitore->citta }}</td>
                                <td>
                                    <a href="{{ route('fornitoris.show', $fornitore) }}" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('fornitoris.edit', $fornitore) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                                    <form action="{{ route('fornitoris.destroy', $fornitore) }}" method="POST" style="display:inline-block;">
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
                                <td colspan="10" class="text-center">No fornitori found.</td>
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
                    <i class="fas fa-upload"></i> Import Fornitori from CSV
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('fornitoris.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="file">Select CSV File</label>
                        <input type="file" class="form-control-file" id="file" name="file" accept=".csv,.xlsx,.xls" required>
                        <small class="form-text text-muted">
                            Supported formats: CSV, XLSX, XLS. Maximum file size: 2MB.
                        </small>
                    </div>

                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> CSV Format Requirements:</h6>
                        <p class="mb-1">Your CSV file should have the following columns:</p>
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
