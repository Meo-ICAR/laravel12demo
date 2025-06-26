@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Pratiche</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#importModal">
                            <i class="fas fa-upload"></i> Importa CSV
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Data Inserimento</th>
                                    <th>Cliente</th>
                                    <th>Agente</th>
                                    <th>Tipo</th>
                                    <th>Istituto Finanziario</th>
                                    <th>Azioni</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pratiches as $pratiche)
                                    <tr>
                                        <td>{{ $pratiche->pratica_id }}</td>
                                        <td>{{ $pratiche->Data_inserimento ? $pratiche->Data_inserimento->format('d/m/Y') : '' }}</td>
                                        <td>{{ $pratiche->Cliente }}</td>
                                        <td>{{ $pratiche->Agente }}</td>
                                        <td>
                                            <span class="badge badge-{{ $pratiche->Tipo == 'Cessione' ? 'success' : ($pratiche->Tipo == 'Mutuo' ? 'primary' : ($pratiche->Tipo == 'Prestito' ? 'warning' : 'info')) }}">
                                                {{ $pratiche->Tipo }}
                                            </span>
                                        </td>
                                        <td>{{ $pratiche->Istituto_finanziario }}</td>
                                        <td>
                                            <a href="{{ route('pratiches.show', $pratiche->id) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Nessuna pratica trovata</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center">
                        {{ $pratiches->links() }}
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
            <form action="{{ route('pratiches.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Importa File CSV</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="csv_file">Seleziona file CSV</label>
                        <input type="file" class="form-control-file" id="csv_file" name="csv_file" accept=".csv,.txt" required>
                        <small class="form-text text-muted">
                            Il file deve contenere le colonne: ID, Data_inserimento, Descrizione, Cliente, Agente, Segnalatore, Fonte, Tipo, Istituto finanziario
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Chiudi</button>
                    <button type="submit" class="btn btn-primary">Importa</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
