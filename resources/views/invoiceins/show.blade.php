@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-eye mr-2"></i>Invoicein Details
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('invoiceins.edit', $invoicein->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('invoiceins.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-info">
                                    <i class="fas fa-building"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Nome Fornitore</span>
                                    <span class="info-box-number">{{ $invoicein->nome_fornitore ?: 'Not specified' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning">
                                    <i class="fas fa-id-card"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Partita IVA</span>
                                    <span class="info-box-number">{{ $invoicein->partita_iva ?: 'Not specified' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-file-alt"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Tipo Documento</span>
                                    <span class="info-box-number">
                                        @if($invoicein->tipo_di_documento)
                                            <span class="badge badge-{{ $invoicein->tipo_di_documento == 'Fattura' ? 'success' : ($invoicein->tipo_di_documento == 'Nota di Credito' ? 'warning' : 'danger') }}">
                                                {{ $invoicein->tipo_di_documento }}
                                            </span>
                                        @else
                                            Not specified
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary">
                                    <i class="fas fa-hashtag"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Nr Documento</span>
                                    <span class="info-box-number">{{ $invoicein->nr_documento ?: 'Not specified' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-info">
                                    <i class="fas fa-calendar"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Data Documento</span>
                                    <span class="info-box-number">
                                        {{ $invoicein->data_documento ? \Carbon\Carbon::parse($invoicein->data_documento)->format('d/m/Y') : 'Not specified' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-euro-sign"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Importo</span>
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
