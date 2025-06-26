@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Create New Invoicein</h3>
                    <div class="card-tools">
                        <a href="{{ route('invoiceins.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('invoiceins.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nome_fornitore">Nome Fornitore</label>
                                    <input type="text" class="form-control @error('nome_fornitore') is-invalid @enderror"
                                           id="nome_fornitore" name="nome_fornitore" value="{{ old('nome_fornitore') }}">
                                    @error('nome_fornitore')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="partita_iva">Partita IVA</label>
                                    <input type="text" class="form-control @error('partita_iva') is-invalid @enderror"
                                           id="partita_iva" name="partita_iva" value="{{ old('partita_iva') }}">
                                    @error('partita_iva')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tipo_di_documento">Tipo Documento</label>
                                    <select class="form-control @error('tipo_di_documento') is-invalid @enderror"
                                            id="tipo_di_documento" name="tipo_di_documento">
                                        <option value="">Select type</option>
                                        <option value="Fattura" {{ old('tipo_di_documento') == 'Fattura' ? 'selected' : '' }}>Fattura</option>
                                        <option value="Nota di Credito" {{ old('tipo_di_documento') == 'Nota di Credito' ? 'selected' : '' }}>Nota di Credito</option>
                                        <option value="Nota di Debito" {{ old('tipo_di_documento') == 'Nota di Debito' ? 'selected' : '' }}>Nota di Debito</option>
                                    </select>
                                    @error('tipo_di_documento')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nr_documento">Nr Documento</label>
                                    <input type="text" class="form-control @error('nr_documento') is-invalid @enderror"
                                           id="nr_documento" name="nr_documento" value="{{ old('nr_documento') }}">
                                    @error('nr_documento')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="data_documento">Data Documento</label>
                                    <input type="date" class="form-control @error('data_documento') is-invalid @enderror"
                                           id="data_documento" name="data_documento" value="{{ old('data_documento') }}">
                                    @error('data_documento')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="importo">Importo</label>
                                    <input type="number" step="0.01" class="form-control @error('importo') is-invalid @enderror"
                                           id="importo" name="importo" value="{{ old('importo') }}">
                                    @error('importo')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Invoicein
                            </button>
                            <a href="{{ route('invoiceins.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
