@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Filter Form -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-filter"></i> Filter Invoiceins
                        <button class="btn btn-link float-right p-0" type="button" data-toggle="collapse" data-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                            <i class="fas fa-chevron-down"></i>
                        </button>
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
                    <h3 class="card-title">Invoiceins List</h3>
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
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible m-3">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome Fornitore</th>
                                    <th>Partita IVA</th>
                                    <th>Tipo Documento</th>
                                    <th>Nr Documento</th>
                                    <th>Data Documento</th>
                                    <th>Importo</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoiceins as $inv)
                                    <tr>
                                        <td>{{ $inv->id }}</td>
                                        <td>{{ $inv->nome_fornitore }}</td>
                                        <td>{{ $inv->partita_iva }}</td>
                                        <td>
                                            <span class="badge badge-{{ $inv->tipo_di_documento == 'Fattura' ? 'success' : 'info' }}">
                                                {{ $inv->tipo_di_documento }}
                                            </span>
                                        </td>
                                        <td>{{ $inv->nr_documento }}</td>
                                        <td>{{ $inv->data_documento ? \Carbon\Carbon::parse($inv->data_documento)->format('d/m/Y') : '-' }}</td>
                                        <td class="text-right">
                                            @if($inv->importo)
                                                € {{ number_format($inv->importo, 2, ',', '.') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('invoiceins.show', $inv) }}" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('invoiceins.edit', $inv) }}" class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('invoiceins.destroy', $inv) }}" method="POST" style="display:inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this invoicein?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No invoiceins found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-center">
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
                    <h5 class="modal-title" id="importModalLabel">Import Invoiceins</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="file">Select File</label>
                        <input type="file" class="form-control-file" id="file" name="file" accept=".csv,.xlsx,.xls,.txt" required>
                        <small class="form-text text-muted">
                            Supported formats: CSV, Excel (.xlsx, .xls), TSV files. Maximum size: 2MB.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
