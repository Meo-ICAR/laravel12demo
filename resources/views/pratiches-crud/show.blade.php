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
                            <table class="table table-borderless">
                                <tr>
                                    <th width="30%">ID Pratica:</th>
                                    <td><strong>{{ $pratiche->pratica_id }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Data Inserimento:</th>
                                    <td>{{ $pratiche->Data_inserimento ? $pratiche->Data_inserimento->format('d/m/Y') : 'Non specificata' }}</td>
                                </tr>
                                <tr>
                                    <th>Cliente:</th>
                                    <td>{{ $pratiche->Cliente ?: 'Non specificato' }}</td>
                                </tr>
                                <tr>
                                    <th>Agente:</th>
                                    <td>{{ $pratiche->Agente ?: 'Non specificato' }}</td>
                                </tr>
                                <tr>
                                    <th>Segnalatore:</th>
                                    <td>{{ $pratiche->Segnalatore ?: 'Non specificato' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="30%">Tipo:</th>
                                    <td>
                                        @if($pratiche->Tipo)
                                            <span class="badge badge-{{ $pratiche->Tipo == 'Cessione' ? 'success' : ($pratiche->Tipo == 'Mutuo' ? 'primary' : ($pratiche->Tipo == 'Prestito' ? 'warning' : 'info')) }}">
                                                {{ $pratiche->Tipo }}
                                            </span>
                                        @else
                                            Non specificato
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Istituto Finanziario:</th>
                                    <td>{{ $pratiche->Istituto_finanziario ?: 'Non specificato' }}</td>
                                </tr>
                                <tr>
                                    <th>Fonte:</th>
                                    <td>{{ $pratiche->Fonte ?: 'Non specificata' }}</td>
                                </tr>
                                <tr>
                                    <th>Data Creazione:</th>
                                    <td>{{ $pratiche->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Ultimo Aggiornamento:</th>
                                    <td>{{ $pratiche->updated_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($pratiche->Descrizione)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>Descrizione</h5>
                            <div class="alert alert-info">
                                {{ $pratiche->Descrizione }}
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
