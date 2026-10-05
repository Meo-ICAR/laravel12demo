@extends('layouts.admin')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Dettaglio Provvigione</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('provvigioni.index') }}">Provvigioni</a></li>
                        <li class="breadcrumb-item active">Dettaglio</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <!-- First Row -->
            <div class="row">
                <!-- Basic Information -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Informazioni Base</h3>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-4">ID</dt>
                                <dd class="col-sm-8">{{ $provvigione->id }}</dd>

                                <dt class="col-sm-4">Legacy ID</dt>
                                <dd class="col-sm-8">{{ $provvigione->legacy_id ?? '-' }}</dd>

                                <dt class="col-sm-4">Stato</dt>
                                <dd class="col-sm-8">
                                    <span class="badge badge-{{ $provvigione->stato == 'Pagato' ? 'success' : ($provvigione->stato == 'Fatturato' ? 'info' : ($provvigione->stato == 'Proforma' ? 'warning' : ($provvigione->stato == 'Stornato' ? 'danger' : 'secondary'))) }}">
                                        {{ $provvigione->stato }}
                                    </span>
                                </dd>

                                <dt class="col-sm-4">Status Pagamento</dt>
                                <dd class="col-sm-8">
                                    <span class="badge badge-{{ $provvigione->status_pagamento == 'Pagato' ? 'success' : 'secondary' }}">
                                        {{ $provvigione->status_pagamento ?? 'Non Pagato' }}
                                    </span>
                                </dd>

                                <dt class="col-sm-4">Status Compenso</dt>
                                <dd class="col-sm-8">
                                    <span class="badge badge-{{ $provvigione->status_compenso ? 'info' : 'secondary' }}">
                                        {{ $provvigione->status_compenso ?? 'Non specificato' }}
                                    </span>
                                </dd>

                                <dt class="col-sm-4">Descrizione</dt>
                                <dd class="col-sm-8">{{ $provvigione->descrizione ?? '-' }}</dd>

                                <dt class="col-sm-4">Tipo</dt>
                                <dd class="col-sm-8">{{ $provvigione->tipo ?? '-' }}</dd>

                                <dt class="col-sm-4">Fonte</dt>
                                <dd class="col-sm-8">{{ $provvigione->fonte ?? '-' }}</dd>

                                <dt class="col-sm-4">Entrata/Uscita</dt>
                                <dd class="col-sm-8">{{ $provvigione->entrata_uscita ?? '-' }}</dd>

                                <dt class="col-sm-4">Data Inserimento</dt>
                                <dd class="col-sm-8">{{ $provvigione->data_inserimento_compenso ? $provvigione->data_inserimento_compenso->format('d/m/Y H:i') : '-' }}</dd>

                                <dt class="col-sm-4">Data Status</dt>
                                <dd class="col-sm-8">{{ $provvigione->data_status ? $provvigione->data_status->format('d/m/Y') : '-' }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>

                <!-- Financial Information -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Dati Finanziari</h3>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-4">Importo</dt>
                                <dd class="col-sm-8">
                                    <strong class="text-success">€ {{ number_format($provvigione->importo, 2, ',', '.') }}</strong>
                                </dd>

                                <dt class="col-sm-4">Importo Effettivo</dt>
                                <dd class="col-sm-8">
                                    <strong class="text-info">€ {{ number_format($provvigione->importo_effettivo, 2, ',', '.') }}</strong>
                                </dd>

                                <dt class="col-sm-4">Quota</dt>
                                <dd class="col-sm-8">{{ $provvigione->quota ?? '-' }}</dd>

                                <dt class="col-sm-4">Montante</dt>
                                <dd class="col-sm-8">
                                    <strong class="text-primary">€ {{ number_format($provvigione->montante, 2, ',', '.') }}</strong>
                                </dd>

                                <dt class="col-sm-4">Importo Erogato</dt>
                                <dd class="col-sm-8">
                                    <strong class="text-warning">€ {{ number_format($provvigione->importo_erogato, 2, ',', '.') }}</strong>
                                </dd>

                                <dt class="col-sm-4">Numero Fattura</dt>
                                <dd class="col-sm-8">{{ $provvigione->n_fattura ?? '-' }}</dd>

                                <dt class="col-sm-4">Data Fattura</dt>
                                <dd class="col-sm-8">{{ $provvigione->data_fattura ? $provvigione->data_fattura->format('d/m/Y') : '-' }}</dd>

                                <dt class="col-sm-4">Data Pagamento</dt>
                                <dd class="col-sm-8">{{ $provvigione->data_pagamento ? $provvigione->data_pagamento->format('d/m/Y H:i') : '-' }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Second Row -->
            <div class="row">
                <!-- Personal Information -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Dati Anagrafici</h3>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-4">Denominazione</dt>
                                <dd class="col-sm-8">{{ $provvigione->denominazione_riferimento ?? '-' }}</dd>

                                <dt class="col-sm-4">Cognome</dt>
                                <dd class="col-sm-8">{{ $provvigione->cognome ?? '-' }}</dd>

                                <dt class="col-sm-4">Nome</dt>
                                <dd class="col-sm-8">{{ $provvigione->nome ?? '-' }}</dd>

                                <dt class="col-sm-4">Segnalatore</dt>
                                <dd class="col-sm-8">{{ $provvigione->segnalatore ?? '-' }}</dd>

                                <dt class="col-sm-4">Partita IVA</dt>
                                <dd class="col-sm-8">{{ $provvigione->piva ?? '-' }}</dd>

                                <dt class="col-sm-4">Codice Fiscale</dt>
                                <dd class="col-sm-8">{{ $provvigione->cf ?? '-' }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>

                <!-- Practice Information -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Dati Pratica</h3>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-4">ID Pratica</dt>
                                <dd class="col-sm-8">{{ $provvigione->id_pratica ?? '-' }}</dd>

                                <dt class="col-sm-4">Tipo Pratica</dt>
                                <dd class="col-sm-8">{{ $provvigione->tipo_pratica ?? '-' }}</dd>

                                <dt class="col-sm-4">Istituto Finanziario</dt>
                                <dd class="col-sm-8">{{ $provvigione->istituto_finanziario ?? '-' }}</dd>

                                <dt class="col-sm-4">Prodotto</dt>
                                <dd class="col-sm-8">{{ $provvigione->prodotto ?? '-' }}</dd>

                                <dt class="col-sm-4">Macrostatus</dt>
                                <dd class="col-sm-8">{{ $provvigione->macrostatus ?? '-' }}</dd>

                                <dt class="col-sm-4">Data Inserimento</dt>
                                <dd class="col-sm-8">{{ $provvigione->data_inserimento_pratica ? $provvigione->data_inserimento_pratica->format('d/m/Y H:i') : '-' }}</dd>

                                <dt class="col-sm-4">Data Stipula</dt>
                                <dd class="col-sm-8">{{ $provvigione->data_stipula ? $provvigione->data_stipula->format('d/m/Y') : '-' }}</dd>

                                <dt class="col-sm-4">Status Pratica</dt>
                                <dd class="col-sm-8">{{ $provvigione->status_pratica ?? '-' }}</dd>

                                <dt class="col-sm-4">Data Status Pratica</dt>
                                <dd class="col-sm-8">{{ $provvigione->data_status_pratica ? $provvigione->data_status_pratica->format('d/m/Y H:i') : '-' }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Third Row: Timestamps -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Timestamps di Sistema</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <dl class="row">
                                        <dt class="col-sm-6">Inviato il:</dt>
                                        <dd class="col-sm-6">{{ $provvigione->sended_at ? $provvigione->sended_at->format('d/m/Y H:i:s') : '-' }}</dd>

                                        <dt class="col-sm-6">Ricevuto il:</dt>
                                        <dd class="col-sm-6">{{ $provvigione->received_at ? $provvigione->received_at->format('d/m/Y H:i:s') : '-' }}</dd>
                                    </dl>
                                </div>
                                <div class="col-md-4">
                                    <dl class="row">
                                        <dt class="col-sm-6">Pagato il:</dt>
                                        <dd class="col-sm-6">{{ $provvigione->paided_at ? $provvigione->paided_at->format('d/m/Y H:i:s') : '-' }}</dd>

                                        <dt class="col-sm-6">Eliminato il:</dt>
                                        <dd class="col-sm-6">{{ $provvigione->deleted_at ? $provvigione->deleted_at->format('d/m/Y H:i:s') : '-' }}</dd>
                                    </dl>
                                </div>
                                <div class="col-md-4">
                                    <dl class="row">
                                        <dt class="col-sm-6">Creato il:</dt>
                                        <dd class="col-sm-6">{{ $provvigione->created_at->format('d/m/Y H:i:s') }}</dd>

                                        <dt class="col-sm-6">Aggiornato il:</dt>
                                        <dd class="col-sm-6">{{ $provvigione->updated_at->format('d/m/Y H:i:s') }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-footer">
                            <a href="{{ route('provvigioni.edit', $provvigione->id) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Modifica
                            </a>
                            <a href="{{ route('provvigioni.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Torna all'elenco
                            </a>
                            
                            @if($provvigione->deleted_at)
                                <form action="{{ route('provvigioni.restore', $provvigione->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-info" onclick="return confirm('Ripristinare questa provvigione?')">
                                        <i class="fas fa-trash-restore"></i> Ripristina
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('provvigioni.destroy', $provvigione->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Eliminare questa provvigione?')">
                                        <i class="fas fa-trash"></i> Elimina
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@push('styles')
<style>
    .card {
        margin-bottom: 20px;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid rgba(0,0,0,.125);
    }
    dt {
        font-weight: 600;
    }
    .badge {
        font-size: 90%;
        padding: 0.35em 0.65em;
    }
</style>
@endpush

@endsection
