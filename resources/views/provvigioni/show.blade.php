@extends('layouts.admin')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Provvigione Details</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('provvigioni.index') }}">Provvigioni</a></li>
                        <li class="breadcrumb-item active">Details</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Basic Information</h3>
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

                                <dt class="col-sm-4">Descrizione</dt>
                                <dd class="col-sm-8">{{ $provvigione->descrizione ?? '-' }}</dd>

                                <dt class="col-sm-4">Tipo</dt>
                                <dd class="col-sm-8">{{ $provvigione->tipo ?? '-' }}</dd>

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

                                <dt class="col-sm-4">Data Inserimento</dt>
                                <dd class="col-sm-8">{{ $provvigione->data_inserimento_compenso ?? '-' }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Personal Information</h3>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-4">Denominazione</dt>
                                <dd class="col-sm-8">{{ $provvigione->denominazione_riferimento ?? '-' }}</dd>

                                <dt class="col-sm-4">Nome Completo</dt>
                                <dd class="col-sm-8">{{ $provvigione->cognome }} {{ $provvigione->nome }}</dd>

                                <dt class="col-sm-4">Segnalatore</dt>
                                <dd class="col-sm-8">{{ $provvigione->segnalatore ?? '-' }}</dd>

                                <dt class="col-sm-4">Fonte</dt>
                                <dd class="col-sm-8">{{ $provvigione->fonte ?? '-' }}</dd>

                                <dt class="col-sm-4">Entrata/Uscita</dt>
                                <dd class="col-sm-8">{{ $provvigione->entrata_uscita ?? '-' }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Practice Information</h3>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-4">ID Pratica</dt>
                                <dd class="col-sm-8">{{ $provvigione->id_pratica ?? '-' }}</dd>

                                <dt class="col-sm-4">Tipo Pratica</dt>
                                <dd class="col-sm-8">{{ $provvigione->tipo_pratica ?? '-' }}</dd>

                                <dt class="col-sm-4">Data Inserimento</dt>
                                <dd class="col-sm-8">{{ $provvigione->data_inserimento_pratica ?? '-' }}</dd>

                                <dt class="col-sm-4">Data Stipula</dt>
                                <dd class="col-sm-8">{{ $provvigione->data_stipula ?? '-' }}</dd>

                                <dt class="col-sm-4">Status Pratica</dt>
                                <dd class="col-sm-8">{{ $provvigione->status_pratica ?? '-' }}</dd>

                                <dt class="col-sm-4">Data Status</dt>
                                <dd class="col-sm-8">{{ $provvigione->data_status_pratica ?? '-' }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Financial Details</h3>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-4">Istituto</dt>
                                <dd class="col-sm-8">{{ $provvigione->istituto_finanziario ?? '-' }}</dd>

                                <dt class="col-sm-4">Prodotto</dt>
                                <dd class="col-sm-8">{{ $provvigione->prodotto ?? '-' }}</dd>

                                <dt class="col-sm-4">Macrostatus</dt>
                                <dd class="col-sm-8">{{ $provvigione->macrostatus ?? '-' }}</dd>

                                <dt class="col-sm-4">Montante</dt>
                                <dd class="col-sm-8">
                                    <strong class="text-primary">€ {{ number_format($provvigione->montante, 2, ',', '.') }}</strong>
                                </dd>

                                <dt class="col-sm-4">Importo Erogato</dt>
                                <dd class="col-sm-8">
                                    <strong class="text-warning">€ {{ number_format($provvigione->importo_erogato, 2, ',', '.') }}</strong>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-footer">
                            <a href="{{ route('provvigioni.edit', $provvigione->id) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="{{ route('provvigioni.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to List
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
