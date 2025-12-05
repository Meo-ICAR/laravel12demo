@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>{{ __('Dettagli Coge') }}</span>
                    <div class="btn-group">
                        <a href="{{ route('coges.edit', $coge->id) }}" class="btn btn-warning btn-sm">Modifica</a>
                        <a href="{{ route('coges.index') }}" class="btn btn-secondary btn-sm">Torna all'elenco</a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <tr>
                                <th>ID</th>
                                <td>{{ $coge->id }}</td>
                            </tr>
                            <tr>
                                <th>Fonte</th>
                                <td>{{ $coge->fonte }}</td>
                            </tr>
                            <tr>
                                <th>Conto Dare</th>
                                <td>{{ $coge->conto_dare }}</td>
                            </tr>
                            <tr>
                                <th>Descrizione Dare</th>
                                <td>{{ $coge->descrizione_dare }}</td>
                            </tr>
                            <tr>
                                <th>Conto Avere</th>
                                <td>{{ $coge->conto_avere }}</td>
                            </tr>
                            <tr>
                                <th>Descrizione Avere</th>
                                <td>{{ $coge->descrizione_avere }}</td>
                            </tr>
                            <tr>
                                <th>Annotazioni</th>
                                <td>{{ $coge->annotazioni ?? 'Nessuna annotazione' }}</td>
                            </tr>
                            <tr>
                                <th>Creato il</th>
                                <td>{{ $coge->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            <tr>
                                <th>Aggiornato il</th>
                                <td>{{ $coge->updated_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
