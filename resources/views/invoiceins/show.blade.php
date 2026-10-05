@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-eye mr-2"></i>Dettaglio Fattura/Nota
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('invoiceins.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Torna all'elenco
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Document Header -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="callout callout-info">
                                <h5><i class="fas fa-info-circle"></i> Informazioni Documento</h5>
                                <div class="row mt-3">
                                    <div class="col-md-4">
                                        <p><strong>Tipo Documento:</strong>
                                            @if($invoicein->tipo_di_documento)
                                                <span class="badge badge-{{ $invoicein->tipo_di_documento == 'Fattura' ? 'success' : ($invoicein->tipo_di_documento == 'Nota di Credito' ? 'warning' : 'danger') }}">
                                                    {{ $invoicein->tipo_di_documento }}
                                                </span>
                                            @else
                                                <span class="text-muted">Non specificato</span>
                                            @endif
                                        </p>
                                        <p><strong>Numero Documento:</strong> {{ $invoicein->nr_documento ?? 'Non specificato' }}</p>
                                        <p><strong>Numero Fattura Acq. Registrata:</strong> {{ $invoicein->nr_fatt_acq_registrata ?? 'Non specificato' }}</p>
                                        <p><strong>Numero Nota Cr. Acq. Registrata:</strong> {{ $invoicein->nr_nota_cr_acq_registrata ?? 'Non specificato' }}</p>
                                        <p><strong>Codice TD:</strong> {{ $invoicein->codice_td ?? 'Non specificato' }}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <p><strong>Data Ricezione Fattura:</strong>
                                            {{ $invoicein->data_ricezione_fatt ? \Carbon\Carbon::parse($invoicein->data_ricezione_fatt)->format('d/m/Y H:i:s') : 'Non specificata' }}
                                        </p>
                                        <p><strong>Data Documento Fornitore:</strong>
                                            {{ $invoicein->data_documento_fornitore ? \Carbon\Carbon::parse($invoicein->data_documento_fornitore)->format('d/m/Y') : 'Non specificata' }}
                                        </p>
                                        <p><strong>Data Primo Pagamento Previsto:</strong>
                                            {{ $invoicein->data_primo_pagamento_prev ? \Carbon\Carbon::parse($invoicein->data_primo_pagamento_prev)->format('d/m/Y') : 'Non specificata' }}
                                        </p>
                                        <p><strong>Data/Ora Invio/Ricezione:</strong>
                                            {{ $invoicein->data_ora_invio_ricezione ? \Carbon\Carbon::parse($invoicein->data_ora_invio_ricezione)->format('d/m/Y H:i:s') : 'Non specificata' }}
                                        </p>
                                    </div>
                                    <div class="col-md-4">
                                        <p><strong>Stato:</strong> {{ $invoicein->stato ?? 'Non specificato' }}</p>
                                        <p><strong>ID Documento:</strong> {{ $invoicein->id_documento ?? 'Non specificato' }}</p>
                                        <p><strong>ID SDI:</strong> {{ $invoicein->id_sdi ?? 'Non specificato' }}</p>
                                        <p><strong>Numero Lotto Documento:</strong> {{ $invoicein->nr_lotto_documento ?? 'Non specificato' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Fornitore Section -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card card-primary card-outline">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-building mr-2"></i>Dati Fornitore</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <p><strong>Nome Fornitore:</strong> {{ $invoicein->nome_fornitore ?? 'Non specificato' }}</p>
                                            <p><strong>Partita IVA:</strong> {{ $invoicein->partita_iva ?? 'Non specificata' }}</p>
                                            <p><strong>Numero Cliente/Fornitore:</strong> {{ $invoicein->nr_cliente_fornitore ?? 'Non specificato' }}</p>
                                        </div>
                                        <div class="col-md-4">
                                            <p><strong>Numero Documento Fornitore:</strong> {{ $invoicein->nr_documento_fornitore ?? 'Non specificato' }}</p>
                                            <p><strong>Allegato:</strong> {{ $invoicein->allegato ? 'Sì' : 'No' }}</p>
                                            <p><strong>Allegato in File XML:</strong> {{ $invoicein->allegato_in_file_xml ? 'Sì' : 'No' }}</p>
                                        </div>
                                        <div class="col-md-4">
                                            <p><strong>CDC Codice:</strong> {{ $invoicein->cdc_codice ?? 'Non specificato' }}</p>
                                            <p><strong>Cod. Colleg. Dimen. 2:</strong> {{ $invoicein->cod_colleg_dimen_2 ?? 'Non specificato' }}</p>
                                            <p><strong>Filtro Carichi:</strong> {{ $invoicein->filtro_carichi ? 'Sì' : 'No' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Importi Section -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card card-success card-outline">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-euro-sign mr-2"></i>Dati Economici</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-success"><i class="fas fa-euro-sign"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Imponibile IVA</span>
                                                    <span class="info-box-number">
                                                        {{ number_format($invoicein->imponibile_iva, 2, ',', '.') }} €
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-warning"><i class="fas fa-percentage"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Importo IVA</span>
                                                    <span class="info-box-number">
                                                        {{ number_format($invoicein->importo_iva, 2, ',', '.') }} €
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-primary"><i class="fas fa-calculator"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Importo Totale Fornitore</span>
                                                    <span class="info-box-number">
                                                        {{ number_format($invoicein->importo_totale_fornitore, 2, ',', '.') }} €
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-info"><i class="fas fa-link"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Importo Totale Collegato</span>
                                                    <span class="info-box-number">
                                                        {{ number_format($invoicein->importo_totale_collegato, 2, ',', '.') }} €
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Note Section -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="far fa-sticky-note mr-2"></i>Note 1</h3>
                                </div>
                                <div class="card-body">
                                    {{ $invoicein->note_1 ?? 'Nessuna nota' }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="far fa-sticky-note mr-2"></i>Note 2</h3>
                                </div>
                                <div class="card-body">
                                    {{ $invoicein->note_2 ?? 'Nessuna nota' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- File Section -->
                    @if($invoicein->nome_file_doc_elettronico)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card card-warning">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="far fa-file-alt mr-2"></i>Documento Elettronico</h3>
                                </div>
                                <div class="card-body">
                                    <p><strong>Nome File:</strong> {{ $invoicein->nome_file_doc_elettronico }}</p>
                                    @if(file_exists(storage_path('app/private/' . $invoicein->nome_file_doc_elettronico)))
                                        <a href="{{ route('invoiceins.download', $invoicein->id) }}" class="btn btn-primary">
                                            <i class="fas fa-download"></i> Scarica Documento
                                        </a>
                                    @else
                                        <span class="text-danger">File non trovato nel percorso specificato.</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                                    <span class="info-box-number">
                                        @if($invoicein->importo)
                                            <strong class="text-success">€ {{ number_format($invoicein->importo, 2, ',', '.') }}</strong>
                                        @else
                                            Not specified
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card card-outline card-info">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-clock mr-2"></i>Timestamps
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Created At:</strong> {{ $invoicein->created_at->format('d/m/Y H:i') }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Updated At:</strong> {{ $invoicein->updated_at->format('d/m/Y H:i') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
    .info-box {
        margin-bottom: 1rem;
    }
    .info-box-number {
        font-size: 1.1rem;
        font-weight: 600;
    }
    .card-outline {
        border-top: 3px solid #17a2b8;
    }
</style>
@endsection
