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

    <!-- Import Form -->
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-upload"></i> Import Calls
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('calls.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
                @csrf
                <div class="form-group mb-3">
                    <label for="import_file">Select CSV or Excel File:</label>
                    <input type="file" name="file" id="import_file" class="form-control" required accept=".csv,.xlsx,.xls">
                    <small class="form-text text-muted">
                        <strong>Supported formats:</strong> CSV (.csv), Excel (.xlsx, .xls)<br>
                        <strong>Note:</strong> CSV files should use semicolon (;) as delimiter<br>
                        <strong>Max size:</strong> 2MB
                    </small>
                    <div id="file-info" class="mt-2" style="display: none;">
                        <div class="alert alert-info">
                            <strong>Selected file:</strong> <span id="file-name"></span><br>
                            <strong>Size:</strong> <span id="file-size"></span>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" id="import-btn">
                    <i class="fas fa-upload"></i> Import Calls
                </button>
                <button type="button" class="btn btn-secondary ml-2" onclick="clearImportForm()">
                    <i class="fas fa-times"></i> Clear
                </button>
            </form>

            <!-- Test Form for Debugging -->
            <hr class="my-3">
            <div class="alert alert-info">
                <strong>Debug:</strong> If import is not working, try this test form:
            </div>
            <form action="{{ route('test.upload') }}" method="POST" enctype="multipart/form-data" id="testForm">
                @csrf
                <div class="form-group mb-2">
                    <label for="test_file">Test File Upload:</label>
                    <input type="file" name="file" id="test_file" class="form-control" accept=".csv,.xlsx,.xls">
                </div>
                <button type="submit" class="btn btn-warning btn-sm">
                    <i class="fas fa-bug"></i> Test Upload
                </button>
            </form>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-filter"></i> Filter Calls
            </h5>
        </div>
        <div class="card-body">
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

    <!-- Calls Table -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">Calls List</h3>
                <div>
                    <a href="{{ route('calls.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add Call
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // File input change handler
    const fileInput = document.getElementById('import_file');
    const fileInfo = document.getElementById('file-info');
    const fileName = document.getElementById('file-name');
    const fileSize = document.getElementById('file-size');
    const importBtn = document.getElementById('import-btn');

    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Show file info
                fileName.textContent = file.name;
                fileSize.textContent = formatFileSize(file.size);
                fileInfo.style.display = 'block';

                // Enable import button
                importBtn.disabled = false;

                // Validate file type
                const allowedTypes = ['.csv', '.xlsx', '.xls'];
                const fileExtension = '.' + file.name.split('.').pop().toLowerCase();

                if (!allowedTypes.includes(fileExtension)) {
                    alert('Please select a valid file type: CSV, XLSX, or XLS');
                    clearImportForm();
                    return;
                }
            } else {
                fileInfo.style.display = 'none';
                importBtn.disabled = true;
            }
        });
    }

    // Import form submission handler
    const importForm = document.getElementById('importForm');
    if (importForm) {
        importForm.addEventListener('submit', function(e) {
            const file = fileInput.files[0];
            if (!file) {
                e.preventDefault();
                alert('Please select a file to import');
                return;
            }

            // Show loading state
            importBtn.disabled = true;
            importBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Importing...';
        });
    }

    // Test form submission handler
    const testForm = document.getElementById('testForm');
    if (testForm) {
        testForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const testFile = document.getElementById('test_file').files[0];

            if (!testFile) {
                alert('Please select a file for testing');
                return;
            }

            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing...';

            fetch('{{ route("test.upload") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                console.log('Test upload result:', data);
                alert('Test result: ' + JSON.stringify(data, null, 2));
            })
            .catch(error => {
                console.error('Test upload error:', error);
                alert('Test upload error: ' + error.message);
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }

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

// Function to format file size
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

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

// Function to clear import form
function clearImportForm() {
    // Reset form fields
    document.getElementById('import_file').value = '';
    document.getElementById('file-info').style.display = 'none';

    // Reset button state
    const importBtn = document.getElementById('import-btn');
    if (importBtn) {
        importBtn.disabled = false;
        importBtn.innerHTML = '<i class="fas fa-upload"></i> Import Calls';
    }
}
</script>
@endsection
