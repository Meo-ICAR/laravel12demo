@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Filter Form -->
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-filter"></i> Filter Calls
            </h5>
            <div class="d-flex align-items-center">

                <button class="btn btn-link text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                    <i class="fas fa-chevron-down"></i> Show Filters
                </button>
            </div>
        </div>
        <div class="collapse" id="filterCollapse">
        <div class="card-body">
            @include('calls.import-modal')
            <form action="{{ route('calls.index') }}" method="GET" class="row" id="filterForm">
                <div class="col-md-2">
                    <label for="numero_chiamato">Numero Chiamato:</label>
                    <input type="text" name="numero_chiamato" id="numero_chiamato" class="form-control"
                           value="{{ request('numero_chiamato') }}" placeholder="Search...">
                </div>
                <div class="col-md-2">
                    <label for="stato_chiamata">Stato Chiamata:</label>
                    <select name="stato_chiamata" id="stato_chiamata" class="form-control">
                        <option value="">All Stati</option>
                        @foreach($statoChiamataOptions as $option)
                            <option value="{{ $option }}" {{ request('stato_chiamata') == $option ? 'selected' : '' }}>
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
                    <label for="utente">Utente:</label>
                    <select name="utente" id="utente" class="form-control">
                        <option value="">All Utenti</option>
                        @foreach($utenteOptions as $option)
                            <option value="{{ $option }}" {{ request('utente') == $option ? 'selected' : '' }}>
                                {{ $option }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="data_from">Data From:</label>
                    <input type="date" name="data_from" id="data_from" class="form-control"
                           value="{{ request('data_from') }}">
                </div>
                <div class="col-md-2">
                    <label for="data_to">Data To:</label>
                    <input type="date" name="data_to" id="data_to" class="form-control"
                           value="{{ request('data_to') }}">
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
                                <option value="data_inizio" {{ $sortBy === 'data_inizio' ? 'selected' : '' }}>Data Inizio</option>
                                <option value="numero_chiamato" {{ $sortBy === 'numero_chiamato' ? 'selected' : '' }}>Numero Chiamato</option>
                                <option value="durata" {{ $sortBy === 'durata' ? 'selected' : '' }}>Durata</option>
                                <option value="stato_chiamata" {{ $sortBy === 'stato_chiamata' ? 'selected' : '' }}>Stato Chiamata</option>
                                <option value="esito" {{ $sortBy === 'esito' ? 'selected' : '' }}>Esito</option>
                                <option value="utente" {{ $sortBy === 'utente' ? 'selected' : '' }}>Utente</option>
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
    </div>

    <!-- Calls Table -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">Calls List</h3>
                <div>

                    <a href="{{ route('calls.dashboard') }}" class="btn btn-secondary btn-sm mr-2">
                        <i class="fas fa-chart-line"></i> Calls Dashboard
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
                            <th>Numero Chiamato</th>
                            <th>Data Inizio</th>
                            <th>Durata</th>
                            <th>Stato Chiamata</th>
                            <th>Esito</th>
                            <th>Utente</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($calls as $call)
                            <tr>
                                <td>{{ $call->id }}</td>
                                <td>{{ $call->numero_chiamato }}</td>
                                <td>{{ $call->data_inizio ? $call->data_inizio->format('d/m/Y H:i:s') : '-' }}</td>
                                <td class="text-center">{{ $call->getFormattedDuration() }}</td>
                                <td>
                                    <span class="badge badge-{{ $call->stato_chiamata === 'ANSWER' ? 'success' : ($call->stato_chiamata === 'BUSY' ? 'warning' : 'secondary') }}">
                                        {{ $call->stato_chiamata }}
                                    </span>
                                </td>
                                <td>{{ $call->esito }}</td>
                                <td>{{ $call->utente }}</td>
                                <td>
                                    <a href="{{ route('calls.show', $call) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('calls.edit', $call) }}" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('calls.destroy', $call) }}" method="POST" style="display:inline-block;">
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
                                <td colspan="7" class="text-center">No calls found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer clearfix">
            {{ $calls->appends(request()->query())->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Set default dates for import form (last 7 days)
    let endDate = new Date();
    let startDate = new Date();
    startDate.setDate(startDate.getDate() - 7);

    // Format dates as YYYY-MM-DD
    const formatDate = (date) => {
        return date.toISOString().split('T')[0];
    };

    $('#from_date').val(formatDate(startDate));
    $('#to_date').val(formatDate(endDate));

    // Handle import form submission
    $('#importEsitiForm').on('submit', function(e) {
        e.preventDefault();

        const formData = {
            from_date: $('#from_date').val(),
            to_date: $('#to_date').val(),
            dry_run: $('#dry_run').is(':checked') ? 1 : 0,
            _token: '{{ csrf_token() }}'
        };

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            beforeSend: function() {
                $('#importEsitiForm button[type="submit"]')
                    .prop('disabled', true)
                    .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Importazione in corso...');
            },
            success: function(response) {
                new Noty({
                    type: 'success',
                    text: response.message || 'Importazione completata con successo!',
                    timeout: 5000
                }).show();

                $('#importEsitiModal').modal('hide');

                setTimeout(function() {
                    window.location.reload();
                }, 1000);
            },
            error: function(xhr) {
                let errorMessage = 'Si è verificato un errore durante l\'importazione';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }

                new Noty({
                    type: 'error',
                    text: errorMessage,
                    timeout: 5000
                }).show();
            },
            complete: function() {
                $('#importEsitiForm button[type="submit"]')
                    .prop('disabled', false)
                    .text('Importa');
            }
        });
    });

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
            const baseUrl = '{{ route("calls.index") }}';
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
        'numero_chiamato', 'stato_chiamata', 'esito', 'utente',
        'data_from', 'data_to'
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

    document.getElementById('sort_by').value = 'data_inizio';
    document.getElementById('sort_direction').value = 'desc';

    // Navigate to base URL without any parameters
    window.location.href = '{{ route("calls.index") }}';
}
</script>
@endsection
