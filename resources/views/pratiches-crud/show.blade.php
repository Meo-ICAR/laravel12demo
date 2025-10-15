@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Dettagli Pratica</h3>
                    <div class="card-tools">
                        <a href="{{ route('pratiches-crud.edit', $pratiche->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Modifica
                        </a>
                        <a href="{{ route('pratiches-crud.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Torna alla lista
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="mb-3">Informazioni Generali</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Codice Pratica:</th>
                                    <td><strong>{{ $pratiche->codice_pratica }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Data Inserimento:</th>
                                    <td>
                                        @if($pratiche->data_inserimento_pratica)
                                            {{ is_string($pratiche->data_inserimento_pratica) ? \Carbon\Carbon::parse($pratiche->data_inserimento_pratica)->format('d/m/Y') : $pratiche->data_inserimento_pratica->format('d/m/Y') }}
                                        @else
                                            Non specificata
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Stato:</th>
                                    <td>
                                        <span class="badge badge-{{ $pratiche->stato_pratica == 'completata' ? 'success' : ($pratiche->stato_pratica == 'in_lavorazione' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst(str_replace('_', ' ', $pratiche->stato_pratica)) }}
                                        </span>
                                    </td>
                                </tr>
                            </table>

                            <h5 class="mt-4 mb-3">Dati Cliente</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Nome:</th>
                                    <td>{{ $pratiche->nome_cliente ?: 'Non specificato' }}</td>
                                </tr>
                                <tr>
                                    <th>Cognome:</th>
                                    <td>{{ $pratiche->cognome_cliente ?: 'Non specificato' }}</td>
                                </tr>
                                <tr>
                                    <th>Codice Fiscale:</th>
                                    <td>{{ $pratiche->codice_fiscale ?: 'Non specificato' }}</td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-md-6">
                            <h5 class="mb-3">Informazioni Aggiuntive</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Tipo Prodotto:</th>
                                    <td>
                                        <span class="badge badge-info">
                                            {{ ucfirst($pratiche->tipo_prodotto) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Denominazione Banca:</th>
                                    <td>{{ $pratiche->denominazione_banca ?: 'Non specificata' }}</td>
                                </tr>
                                <tr>
                                    <th>Denominazione Agente:</th>
                                    <td>{{ $pratiche->denominazione_agente ?: 'Non specificato' }}</td>
                                </tr>
                                <tr>
                                    <th>Partita IVA Agente:</th>
                                    <td>{{ $pratiche->partita_iva_agente ?: 'Non specificata' }}</td>
                                </tr>
                            </table>

                            <h5 class="mt-4 mb-3">Dati di Sistema</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Data Creazione:</th>
                                    <td>{{ $pratiche->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Ultimo Aggiornamento:</th>
                                    <td>{{ $pratiche->updated_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($pratiche->descrizione_prodotto)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>Descrizione Prodotto</h5>
                            <div class="card">
                                <div class="card-body">
                                    {!! nl2br(e($pratiche->descrizione_prodotto)) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
