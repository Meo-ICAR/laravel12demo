@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <!-- Import Form -->
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-upload"></i> Import Leads
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('leads.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group mb-2">
                    <input type="file" name="file" class="form-control-file" required accept=".csv,.xlsx">
                    <small class="form-text text-muted">Supported formats: CSV, Excel (.xlsx)</small>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-upload"></i> Import Leads
                </button>
            </form>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-filter"></i> Filter Leads
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('leads.index') }}" method="GET" class="row" id="filterForm">
                <div class="col-md-2">
                    <label for="legacy_id">ID:</label>
                    <input type="text" name="legacy_id" id="legacy_id" class="form-control"
                           value="{{ request('legacy_id') }}" placeholder="Search...">
                </div>
                <div class="col-md-2">
                    <label for="campagna">Campagna:</label>
                    <select name="campagna" id="campagna" class="form-control">
                        <option value="">All Campagne</option>
                        @foreach($campagnaOptions as $option)
                            <option value="{{ $option }}" {{ request('campagna') == $option ? 'selected' : '' }}>
                                {{ $option }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="lista">Lista:</label>
                    <select name="lista" id="lista" class="form-control">
                        <option value="">All Liste</option>
                        @foreach($listaOptions as $option)
                            <option value="{{ $option }}" {{ request('lista') == $option ? 'selected' : '' }}>
                                {{ $option }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="cognome">Cognome:</label>
                    <input type="text" name="cognome" id="cognome" class="form-control"
                           value="{{ request('cognome') }}" placeholder="Search...">
                </div>
                <div class="col-md-2">
                    <label for="nome">Nome:</label>
                    <input type="text" name="nome" id="nome" class="form-control"
                           value="{{ request('nome') }}" placeholder="Search...">
                </div>
                <div class="col-md-2">
                    <label for="telefono">Telefono:</label>
                    <input type="text" name="telefono" id="telefono" class="form-control"
                           value="{{ request('telefono') }}" placeholder="Search...">
                </div>
                <div class="col-md-2">
                    <label for="ultimo_operatore">Operatore:</label>
                    <select name="ultimo_operatore" id="ultimo_operatore" class="form-control">
                        <option value="">All Operatori</option>
                        @foreach($operatoreOptions as $option)
                            <option value="{{ $option }}" {{ request('ultimo_operatore') == $option ? 'selected' : '' }}>
                                {{ $option }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="esito">Esito:</label>
                    <select name="esito" id="esito" class="form-control">
                        <option value="">All Esiti</option>
                        @foreach($esitoOptions as $option)
                            <option value="{{ $option }}" {{ request('esito') == $option ? 'selected' : '' }}>
                                {{ $option }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="comune">Comune:</label>
                    <select name="comune" id="comune" class="form-control">
                        <option value="">All Comuni</option>
                        @foreach($comuneOptions as $option)
                            <option value="{{ $option }}" {{ request('comune') == $option ? 'selected' : '' }}>
                                {{ $option }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="provincia">Provincia:</label>
                    <select name="provincia" id="provincia" class="form-control">
                        <option value="">All Province</option>
                        @foreach($provinciaOptions as $option)
                            <option value="{{ $option }}" {{ request('provincia') == $option ? 'selected' : '' }}>
                                {{ $option }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="email">Email:</label>
                    <input type="text" name="email" id="email" class="form-control"
                           value="{{ request('email') }}" placeholder="Search...">
                </div>
                <div class="col-md-2">
                    <label for="attivo">Status:</label>
                    <select name="attivo" id="attivo" class="form-control">
                        <option value="">All</option>
                        <option value="true" {{ request('attivo') === 'true' ? 'selected' : '' }}>Active</option>
                        <option value="false" {{ request('attivo') === 'false' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="ultima_chiamata_from">Ultima Chiamata From:</label>
                    <input type="date" name="ultima_chiamata_from" id="ultima_chiamata_from" class="form-control"
                           value="{{ request('ultima_chiamata_from') }}">
                </div>
                <div class="col-md-2">
                    <label for="ultima_chiamata_to">Ultima Chiamata To:</label>
                    <input type="date" name="ultima_chiamata_to" id="ultima_chiamata_to" class="form-control"
                           value="{{ request('ultima_chiamata_to') }}">
                </div>
                <div class="col-md-12 mt-3">
                    <div class="d-flex align-items-end">
                        <button type="submit" class="btn btn-info mr-2">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <button type="button" class="btn btn-secondary ml-2" onclick="clearFilters()">
                            <i class="fas fa-times"></i> Clear
                        </button>
                        <div class="ml-auto">
                            <label for="sort_by">Sort by:</label>
                            <select name="sort_by" id="sort_by" class="form-control d-inline-block" style="width: auto;">
                                <option value="data_creazione" {{ $sortBy === 'data_creazione' ? 'selected' : '' }}>Data Creazione</option>
                                <option value="legacy_id" {{ $sortBy === 'legacy_id' ? 'selected' : '' }}>ID</option>
                                <option value="campagna" {{ $sortBy === 'campagna' ? 'selected' : '' }}>Campagna</option>
                                <option value="lista" {{ $sortBy === 'lista' ? 'selected' : '' }}>Lista</option>
                                <option value="cognome" {{ $sortBy === 'cognome' ? 'selected' : '' }}>Cognome</option>
                                <option value="nome" {{ $sortBy === 'nome' ? 'selected' : '' }}>Nome</option>
                                <option value="telefono" {{ $sortBy === 'telefono' ? 'selected' : '' }}>Telefono</option>
                                <option value="ultimo_operatore" {{ $sortBy === 'ultimo_operatore' ? 'selected' : '' }}>Operatore</option>
                                <option value="esito" {{ $sortBy === 'esito' ? 'selected' : '' }}>Esito</option>
                                <option value="comune" {{ $sortBy === 'comune' ? 'selected' : '' }}>Comune</option>
                                <option value="provincia" {{ $sortBy === 'provincia' ? 'selected' : '' }}>Provincia</option>
                                <option value="email" {{ $sortBy === 'email' ? 'selected' : '' }}>Email</option>
                                <option value="ultima_chiamata" {{ $sortBy === 'ultima_chiamata' ? 'selected' : '' }}>Ultima Chiamata</option>
                                <option value="chiamate" {{ $sortBy === 'chiamate' ? 'selected' : '' }}>Chiamate</option>
                                <option value="chiamate_giornaliere" {{ $sortBy === 'chiamate_giornaliere' ? 'selected' : '' }}>Chiamate Giornaliere</option>
                                <option value="chiamate_mensili" {{ $sortBy === 'chiamate_mensili' ? 'selected' : '' }}>Chiamate Mensili</option>
                                <option value="created_at" {{ $sortBy === 'created_at' ? 'selected' : '' }}>Created Date</option>
                            </select>
                            <select name="sort_direction" id="sort_direction" class="form-control d-inline-block ml-2" style="width: auto;">
                                <option value="desc" {{ $sortDirection === 'desc' ? 'selected' : '' }}>Desc</option>
                                <option value="asc" {{ $sortDirection === 'asc' ? 'selected' : '' }}>Asc</option>
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Leads Table -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">Leads List</h3>
                <div>
                    <a href="{{ route('leads.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add Lead
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Campagna</th>
                            <th>Lista</th>
                            <th>Nome Completo</th>
                            <th>Telefono</th>
                            <th>Operatore</th>
                            <th>Esito</th>
                            <th>Comune</th>
                            <th>Provincia</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Ultima Chiamata</th>
                            <th>Chiamate</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leads as $lead)
                            <tr>
                                <td>{{ $lead->legacy_id }}</td>
                                <td>{{ $lead->campagna }}</td>
                                <td>{{ $lead->lista }}</td>
                                <td>{{ $lead->full_name }}</td>
                                <td>{{ $lead->primary_phone }}</td>
                                <td>{{ $lead->ultimo_operatore }}</td>
                                <td>
                                    <span class="badge {{ $lead->status_badge_class }}">
                                        {{ $lead->esito ?: 'N/A' }}
                                    </span>
                                </td>
                                <td>{{ $lead->comune }}</td>
                                <td>{{ $lead->provincia }}</td>
                                <td>{{ $lead->email ?: '-' }}</td>
                                <td>
                                    @if($lead->attivo)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ $lead->ultima_chiamata ? $lead->ultima_chiamata->format('d/m/Y H:i') : '-' }}</td>
                                <td class="text-center">{{ $lead->chiamate }}</td>
                                <td>
                                    <a href="{{ route('leads.show', $lead) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('leads.edit', $lead) }}" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('leads.destroy', $lead) }}" method="POST" style="display:inline-block;">
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
                                <td colspan="13" class="text-center">No leads found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer clearfix">
            {{ $leads->appends(request()->query())->links() }}
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filter form submission - omit empty values from URL
    const filterForm = document.getElementById('filterForm');
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default form submission

            // Get form data
            const formData = new FormData(this);
            const params = new URLSearchParams();

            // Only add non-empty values to URL parameters
            for (let [key, value] of formData.entries()) {
                if (value && value.trim() !== '') {
                    params.append(key, value.trim());
                }
            }

            // Build the URL with only non-empty parameters
            const baseUrl = '{{ route("leads.index") }}';
            const queryString = params.toString();
            const finalUrl = queryString ? `${baseUrl}?${queryString}` : baseUrl;

            // Navigate to the filtered URL
            window.location.href = finalUrl;
        });
    }
});

// Function to clear all filters and navigate to base URL
function clearFilters() {
    // Clear all form fields
    const fields = [
        'legacy_id', 'campagna', 'lista', 'cognome', 'nome', 'telefono',
        'ultimo_operatore', 'esito', 'comune', 'provincia', 'email',
        'attivo', 'ultima_chiamata_from', 'ultima_chiamata_to'
    ];

    fields.forEach(field => {
        const element = document.getElementById(field);
        if (element) {
            if (element.tagName === 'SELECT') {
                element.value = '';
            } else {
                element.value = '';
            }
        }
    });

    document.getElementById('sort_by').value = 'data_creazione';
    document.getElementById('sort_direction').value = 'desc';

    // Navigate to base URL without any parameters
    window.location.href = '{{ route("leads.index") }}';
}
</script>
@endsection
