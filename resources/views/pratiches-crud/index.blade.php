@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Elenco Pratiche</h3>


                </div>


                @push('scripts')
                <script src="https://cdn.jsdelivr.net/npm/noty@3.2.0-beta-deprecated/lib/noty.min.js"></script>
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/noty@3.2.0-beta-deprecated/lib/noty.css">
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/noty@3.2.0-beta-deprecated/lib/themes/mint.css">
                @endpush
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <!-- Search Form -->
                    <div class="mb-4">
                        <form action="{{ route('pratiches-crud.index') }}" method="GET" class="form-inline">
                            <div class="input-group w-100">
                                <input type="text" name="search" class="form-control" placeholder="Cerca per codice, cliente, agente o banca..." value="{{ request('search') }}">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="fas fa-search"></i> Cerca
                                    </button>
                                    @if(request()->has('search'))
                                        <a href="{{ route('pratiches-crud.index') }}" class="btn btn-outline-danger">
                                            <i class="fas fa-times"></i> Reset
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    @php
                                        $sortDirection = request('direction', 'desc');
                                        $sortField = request('sort', 'data_inserimento_pratica');
                                        $newSortDirection = $sortDirection === 'asc' ? 'desc' : 'asc';
                                    @endphp

                                    <th>
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'codice_pratica', 'direction' => $sortField === 'codice_pratica' && $sortDirection === 'asc' ? 'desc' : 'asc']) }}" class="text-dark">
                                            Codice Pratica
                                            @if($sortField === 'codice_pratica')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                            @else
                                                <i class="fas fa-sort"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'data_inserimento_pratica', 'direction' => $sortField === 'data_inserimento_pratica' && $sortDirection === 'asc' ? 'desc' : 'asc']) }}" class="text-dark">
                                            Data Inserimento
                                            @if($sortField === 'data_inserimento_pratica')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                            @else
                                                <i class="fas fa-sort"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'cognome_cliente', 'direction' => $sortField === 'cognome_cliente' && $sortDirection === 'asc' ? 'desc' : 'asc']) }}" class="text-dark">
                                            Cliente
                                            @if($sortField === 'cognome_cliente')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                            @else
                                                <i class="fas fa-sort"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>Codice Fiscale</th>
                                    <th>
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'denominazione_agente', 'direction' => $sortField === 'denominazione_agente' && $sortDirection === 'asc' ? 'desc' : 'asc']) }}" class="text-dark">
                                            Agente
                                            @if($sortField === 'denominazione_agente')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                            @else
                                                <i class="fas fa-sort"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>Banca</th>
                                    <th>Tipo Prodotto</th>
                                    <th>Stato</th>

                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pratiches as $pratica)
                                    <tr>
                                        <td>{{ $pratica->codice_pratica }}</td>
                                        <td>{{ $pratica->data_inserimento_pratica ? $pratica->data_inserimento_pratica->format('d/m/Y') : '' }}</td>
                                        <td>{{ $pratica->nome_cliente }} {{ $pratica->cognome_cliente }}</td>
                                        <td>{{ $pratica->codice_fiscale }}</td>
                                        <td>{{ $pratica->denominazione_agente }}</td>
                                        <td>{{ $pratica->denominazione_banca }}</td>
                                        <td>
                                            <span class="badge badge-info">
                                                {{ $pratica->tipo_prodotto }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $pratica->stato_pratica == 'completata' ? 'success' : ($pratica->stato_pratica == 'in_lavorazione' ? 'warning' : 'secondary') }}">
                                                {{ ucfirst(str_replace('_', ' ', $pratica->stato_pratica)) }}
                                            </span>
                                        </td>

                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">Nessuna pratica trovata</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-4">
                        {{ $pratiches->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    th a {
        text-decoration: none;
        color: inherit;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    th a:hover {
        color: #007bff;
    }
    .fa-sort, .fa-sort-up, .fa-sort-down {
        margin-left: 5px;
    }
</style>
@endpush

@endsection
