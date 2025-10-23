@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Pratiche</h3>
                   
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
                                    <th>Modifica</th>
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
                                        <td>{{ $pratiche->updated_at ? $pratiche->updated_at->format('d/m/Y') : '' }}</td>
                                        
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

@endsection
