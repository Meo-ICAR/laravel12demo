@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Filter Form -->
            <div class="card mb-3">
                <div class="card-header" id="filterHeader" style="cursor: pointer;" data-toggle="collapse" data-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-filter mr-2"></i> Filter Invoiceins
                        <i class="fas fa-chevron-down float-right" id="filterIcon"></i>
                    </h5>
                </div>
                <div id="filterCollapse" class="collapse">
                    <div class="card-body">
                        <form method="GET" action="{{ route('invoiceins.index') }}" class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="nome_fornitore">Search Fornitore</label>
                                    <input type="text" name="nome_fornitore" id="nome_fornitore" class="form-control"
                                           value="{{ request('nome_fornitore') }}"
                                           placeholder="Search by nome fornitore...">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="tipo_di_documento">Tipo Documento</label>
                                    <select name="tipo_di_documento" id="tipo_di_documento" class="form-control">
                                        <option value="">All Types</option>
                                        <option value="Fattura" {{ request('tipo_di_documento') == 'Fattura' ? 'selected' : '' }}>Fattura</option>
                                        <option value="Nota di Credito" {{ request('tipo_di_documento') == 'Nota di Credito' ? 'selected' : '' }}>Nota di Credito</option>
                                        <option value="Nota di Debito" {{ request('tipo_di_documento') == 'Nota di Debito' ? 'selected' : '' }}>Nota di Debito</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <div class="form-group mb-0">
                                    <button type="submit" class="btn btn-primary mr-2">
                                        <i class="fas fa-search"></i> Search
                                    </button>
                                    <a href="{{ route('invoiceins.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Clear
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="row mb-3">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ number_format($invoiceins->total()) }}</h3>
                            <p>Total Invoiceins</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ number_format($invoiceins->where('tipo_di_documento', 'Fattura')->count()) }}</h3>
                            <p>Fatture</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-receipt"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ number_format($invoiceins->where('tipo_di_documento', 'Nota di Credito')->count()) }}</h3>
                            <p>Note di Credito</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>{{ number_format($invoiceins->where('tipo_di_documento', 'Nota di Debito')->count()) }}</h3>
                            <p>Note di Debito</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-file-invoice mr-2"></i>Invoiceins List
                        @if(request()->has('nome_fornitore') || request()->has('tipo_di_documento'))
                            <span class="badge badge-info ml-2">Filtered Results</span>
                        @endif
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-success btn-sm mr-2" data-toggle="modal" data-target="#importModal">
                            <i class="fas fa-upload"></i> Import CSV/Excel
                        </button>
                        <a href="{{ route('invoiceins.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> New Invoicein
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible m-3">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible m-3">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('error') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Nome Fornitore</th>
                                    <th>Partita IVA</th>
                                    <th>Tipo Documento</th>
                                    <th>Nr Documento</th>
                                    <th>Data Documento</th>
                                    <th class="text-right">Importo</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoiceins as $inv)
                                    <tr>
                                        <td><span class="badge badge-secondary">{{ $inv->id }}</span></td>
                                        <td>
                                            <strong>{{ $inv->nome_fornitore ?: 'N/A' }}</strong>
                                        </td>
                                        <td>{{ $inv->partita_iva ?: 'N/A' }}</td>
                                        <td>
                                            <span class="badge badge-{{ $inv->tipo_di_documento == 'Fattura' ? 'success' : ($inv->tipo_di_documento == 'Nota di Credito' ? 'warning' : 'danger') }}">
                                                {{ $inv->tipo_di_documento ?: 'N/A' }}
                                            </span>
                                        </td>
                                        <td>{{ $inv->nr_documento ?: 'N/A' }}</td>
                                        <td>
                                            @if($inv->data_documento)
                                                <i class="fas fa-calendar-alt mr-1"></i>
                                                {{ \Carbon\Carbon::parse($inv->data_documento)->format('d/m/Y') }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            @if($inv->importo)
                                                <span class="text-success font-weight-bold">
                                                    € {{ number_format($inv->importo, 2, ',', '.') }}
                                                </span>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('invoiceins.show', $inv) }}" class="btn btn-info btn-sm" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('invoiceins.edit', $inv) }}" class="btn btn-warning btn-sm" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('invoiceins.destroy', $inv) }}" method="POST" style="display:inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this invoicein?')" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No invoiceins found</p>
                                            @if(request()->has('nome_fornitore') || request()->has('tipo_di_documento'))
                                                <a href="{{ route('invoiceins.index') }}" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-times"></i> Clear Filters
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer clearfix">
                    <div class="float-left">
                        <p class="text-muted">
                            Showing {{ $invoiceins->firstItem() ?? 0 }} to {{ $invoiceins->lastItem() ?? 0 }} of {{ $invoiceins->total() }} entries
                        </p>
                    </div>
                    <div class="float-right">
                        {{ $invoiceins->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('invoiceins.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">
                        <i class="fas fa-upload mr-2"></i>Import Invoiceins
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="file">Select File</label>
                        <input type="file" class="form-control-file" id="file" name="file" accept=".csv,.xlsx,.xls,.txt" required>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle mr-1"></i>
                            Supported formats: CSV, Excel (.xlsx, .xls), TSV files. Maximum size: 2MB.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
    .card-header h5 {
        margin-bottom: 0;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(0,123,255,0.1);
    }
    .badge {
        font-size: 0.8em;
    }
    .btn-group .btn {
        margin-right: 2px;
    }
    .btn-group .btn:last-child {
        margin-right: 0;
    }
    .filter-icon {
        transition: transform 0.3s ease;
    }
    .filter-icon.rotated {
        transform: rotate(180deg);
    }
</style>
@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filter section collapse functionality
    const filterHeader = document.getElementById('filterHeader');
    const filterIcon = document.getElementById('filterIcon');
    const filterCollapse = document.getElementById('filterCollapse');

    if (filterHeader && filterIcon && filterCollapse) {
        // Add smooth transition for icon
        filterIcon.style.transition = 'transform 0.3s ease';

        // Listen for Bootstrap collapse events
        filterCollapse.addEventListener('show.bs.collapse', function() {
            filterIcon.style.transform = 'rotate(180deg)';
        });

        filterCollapse.addEventListener('hide.bs.collapse', function() {
            filterIcon.style.transform = 'rotate(0deg)';
        });
    }

    // Auto-submit form on select change
    const tipoSelect = document.getElementById('tipo_di_documento');
    if (tipoSelect) {
        tipoSelect.addEventListener('change', function() {
            this.form.submit();
        });
    }

    // Confirm delete action
    const deleteButtons = document.querySelectorAll('form[action*="destroy"] button[type="submit"]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this invoicein? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });
});
</script>
@endsection
