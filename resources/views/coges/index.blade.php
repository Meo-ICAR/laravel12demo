@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>{{ __('Elenco Coge') }}</span>
                    <a href="{{ route('coges.create') }}" class="btn btn-primary btn-sm">Aggiungi Nuovo</a>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Fonte</th>
                                    <th>Conto Dare</th>
                                    <th>Descrizione Dare</th>
                                    <th>Conto Avere</th>
                                    <th>Descrizione Avere</th>
                                    <th>Azioni</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($coges as $coge)
                                    <tr>
                                        <td>{{ $coge->id }}</td>
                                        <td>{{ $coge->fonte }}</td>
                                        <td>{{ $coge->conto_dare }}</td>
                                        <td>{{ $coge->descrizione_dare }}</td>
                                        <td>{{ $coge->conto_avere }}</td>
                                        <td>{{ $coge->descrizione_avere }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('coges.show', $coge->id) }}" class="btn btn-info btn-sm">Vedi</a>
                                                <a href="{{ route('coges.edit', $coge->id) }}" class="btn btn-warning btn-sm">Modifica</a>
                                                <form action="{{ route('coges.destroy', $coge->id) }}" method="POST" onsubmit="return confirm('Sei sicuro di voler eliminare questo record?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm">Elimina</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Nessun record trovato</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        
                        {{ $coges->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
