@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit MFCompenso</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('mfcompensos.update', $mfcompenso->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id">ID</label>
                            <input type="text" class="form-control" value="{{ $mfcompenso->id }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="legacy_id">Legacy ID</label>
                            <input type="text" class="form-control" value="{{ $mfcompenso->legacy_id }}" readonly>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="descrizione">Descrizione</label>
                            <input type="text" class="form-control" value="{{ $mfcompenso->descrizione }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tipo">Tipo</label>
                            <input type="text" class="form-control" value="{{ $mfcompenso->tipo }}" readonly>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="importo">Importo</label>
                            <input type="number" step="0.01" name="importo" id="importo" class="form-control" value="{{ old('importo', $mfcompenso->importo) }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="invoice_number">Invoice Number</label>
                            <input type="text" name="invoice_number" id="invoice_number" class="form-control" value="{{ old('invoice_number', $mfcompenso->invoice_number) }}">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="stato">Stato *</label>
                            <select name="stato" id="stato" class="form-control @error('stato') is-invalid @enderror" required>
                                <option value="">Select Stato</option>
                                @foreach($statoOptions as $option)
                                    <option value="{{ $option }}" {{ $mfcompenso->stato == $option ? 'selected' : '' }}>
                                        {{ $option }}
                                    </option>
                                @endforeach
                            </select>
                            @error('stato')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="cognome">Cognome</label>
                            <input type="text" class="form-control" value="{{ $mfcompenso->cognome }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nome">Nome</label>
                            <input type="text" class="form-control" value="{{ $mfcompenso->nome }}" readonly>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="denominazione_riferimento">Denominazione Riferimento</label>
                    <input type="text" class="form-control" value="{{ $mfcompenso->denominazione_riferimento }}" readonly>
                </div>

                <div class="form-group">
                    <a href="{{ route('mfcompensos.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Stato</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
