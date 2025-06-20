@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('mfcompensos.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group mb-2">
                    <input type="file" name="file" class="form-control-file" required>
                </div>
                <button type="submit" class="btn btn-primary">Import Excel/CSV</button>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">MFCompensos List</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Data Inserimento Compenso</th>
                            <th>Descrizione</th>
                            <th>Tipo</th>
                            <th>Importo</th>
                            <th>Importo Effettivo</th>
                            <th>Quota</th>
                            <th>Stato</th>
                            <th>Denominazione Riferimento</th>
                            <th>Entrata/Uscita</th>
                            <th>Cognome</th>
                            <th>Nome</th>
                            <th>Segnalatore</th>
                            <th>Fonte</th>
                            <th>ID Pratica</th>
                            <th>Tipo Pratica</th>
                            <th>Data Inserimento Pratica</th>
                            <th>Data Stipula</th>
                            <th>Istituto Finanziario</th>
                            <th>Prodotto</th>
                            <th>Macrostatus</th>
                            <th>Status Pratica</th>
                            <th>Data Status Pratica</th>
                            <th>Montante</th>
                            <th>Importo Erogato</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mfcompensos as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td>{{ $item->data_inserimento_compenso }}</td>
                                <td>{{ $item->descrizione }}</td>
                                <td>{{ $item->tipo }}</td>
                                <td>{{ $item->importo }}</td>
                                <td>{{ $item->importo_effettivo }}</td>
                                <td>{{ $item->quota }}</td>
                                <td>{{ $item->stato }}</td>
                                <td>{{ $item->denominazione_riferimento }}</td>
                                <td>{{ $item->entrata_uscita }}</td>
                                <td>{{ $item->cognome }}</td>
                                <td>{{ $item->nome }}</td>
                                <td>{{ $item->segnalatore }}</td>
                                <td>{{ $item->fonte }}</td>
                                <td>{{ $item->id_pratica }}</td>
                                <td>{{ $item->tipo_pratica }}</td>
                                <td>{{ $item->data_inserimento_pratica }}</td>
                                <td>{{ $item->data_stipula }}</td>
                                <td>{{ $item->istituto_finanziario }}</td>
                                <td>{{ $item->prodotto }}</td>
                                <td>{{ $item->macrostatus }}</td>
                                <td>{{ $item->status_pratica }}</td>
                                <td>{{ $item->data_status_pratica }}</td>
                                <td>{{ $item->montante }}</td>
                                <td>{{ $item->importo_erogato }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="25" class="text-center">No records found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer clearfix">
            {{ $mfcompensos->links() }}
        </div>
    </div>
</div>
@endsection
