@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Elenco Pratiche</h3>
                    <a href="{{ route('pratiches-crud.create') }}" class="btn btn-success">
                        <i class="fas fa-plus"></i> Nuova Pratica
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
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
                                            <a href="{{ route('pratiches-crud.show', $pratiche->id) }}" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                            <a href="{{ route('pratiches-crud.edit', $pratiche->id) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                            <form action="{{ route('pratiches-crud.destroy', $pratiche->id) }}" method="POST" style="display:inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Sei sicuro di voler eliminare questa pratica?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
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
@endsection
