@extends('layouts.admin')

@push('styles')
<style>
    .sort-arrow {
        margin-left: 5px;
        color: #6c757d;
    }
    .sort-arrow.asc:after {
        content: '↑';
    }
    .sort-arrow.desc:after {
        content: '↓';
    }
    .sortable {
        cursor: pointer;
        position: relative;
        padding-right: 20px !important;
    }
    .sortable:hover {
        background-color: #f8f9fa;
    }
</style>
@endpush

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
                        <form method="GET" action="{{ route('invoiceins.index') }}" class="row" id="filterForm">
                            <input type="hidden" name="sort" value="{{ request('sort', 'id') }}">
                            <input type="hidden" name="direction" value="{{ request('direction', 'asc') }}">

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="nome_fornitore">Search Fornitore</label>
                                    <input type="text" name="nome_fornitore" id="nome_fornitore" class="form-control"
                                           value="{{ request('nome_fornitore') }}"
                                           placeholder="Search by nome fornitore...">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="partita_iva">Partita IVA</label>
                                    <input type="text" name="partita_iva" id="partita_iva" class="form-control"
                                           value="{{ request('partita_iva') }}"
                                           placeholder="Filter by P.IVA...">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="tipo_di_documento">Tipo Documento</label>
                                    <select name="tipo_di_documento" id="tipo_di_documento" class="form-control">
                                        <option value="">All Types</option>
                                        <option value="Fattura" {{ request('tipo_di_documento') == 'Fattura' ? 'selected' : '' }}>Fattura</option>
                                        <option value="Nota credito" {{ request('tipo_di_documento') == 'Nota credito' ? 'selected' : '' }}">Nota Credito</option>
                                        <option value="Nota debito" {{ request('tipo_di_documento') == 'Nota debito' ? 'selected' : '' }}">Nota Debito</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="is_imported">Stato Importazione</label>
                                    <select name="is_imported" id="is_imported" class="form-control">
                                        <option value="">Tutti</option>
                                        <option value="1" {{ request('is_imported') === '1' ? 'selected' : '' }}>Importati</option>
                                        <option value="0" {{ request('is_imported') === '0' ? 'selected' : '' }}>Non Importati</option>
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



            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-file-invoice mr-2"></i>Invoiceins List
                        @if(request()->has('nome_fornitore') || request()->has('tipo_di_documento'))
                            <span class="badge badge-info ml-2">Filtered Results</span>
                        @endif
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm mr-2" data-toggle="modal" data-target="#importCustomModal">
                            <i class="fas fa-upload"></i> Import CSV/Excel
                        </button>
                        <form action="{{ route('fornitoris.importInvoiceinsToInvoices') }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to import all eligible Invoiceins to Invoices?');">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="fas fa-random"></i> Filtra e trasferisci in Invoices
                            </button>
                        </form>
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
                                    <th>@sortablelink('id', 'ID')</th>
                                    <th>@sortablelink('nome_fornitore', 'Nome Fornitore')</th>
                                    <th>@sortablelink('partita_iva', 'Partita IVA')</th>
                                    <th>@sortablelink('tipo_di_documento', 'Tipo Documento')</th>
                                    <th>@sortablelink('nr_documento', 'Nr Documento')</th>
                                    <th>@sortablelink('data_ora_invio_ricezione', 'Data Ricezione')</th>
                                    <th class="text-right">@sortablelink('importo', 'Importo')</th>
                                    <th class="text-center">@sortablelink('is_imported', 'Stato Importazione')</th>
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
                                            @if($inv->data_ora_invio_ricezione)
                                                <i class="fas fa-calendar-alt mr-1"></i>
                                                {{ \Carbon\Carbon::parse($inv->data_ora_invio_ricezione)->format('d/m/Y') }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            @if($inv->importo)
                                                <strong class="text-success">€ {{ number_format($inv->importo, 2, ',', '.') }}</strong>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($inv->is_imported)
                                                <span class="badge badge-success" data-toggle="tooltip" title="Importato in Fatture">
                                                    <i class="fas fa-check-circle"></i> Importato
                                                </span>
                                            @else
                                                <span class="badge badge-secondary" data-toggle="tooltip" title="Non ancora importato">
                                                    <i class="fas fa-times-circle"></i> Non importato
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('invoiceins.show', $inv) }}" class="btn btn-info btn-sm" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
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

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal for Custom Import -->
<div class="modal fade" id="importCustomModal" tabindex="-1" role="dialog" aria-labelledby="importCustomModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="importCustomModalLabel">Import Invoiceins (CSV/XLS/XLSX)</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ route('invoiceins.import.custom') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
          <div class="form-group">
            <label for="customImportFile">Select CSV or Excel file</label>
            <input type="file" name="file" id="customImportFile" class="form-control" accept=".csv,.xls,.xlsx" required>
            <small class="form-text text-muted">Supported formats: CSV, XLSX, XLS. Maximum size: 2MB.</small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-upload"></i> Import
          </button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
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
