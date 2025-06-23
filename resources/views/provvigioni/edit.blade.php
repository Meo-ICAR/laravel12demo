@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Provvigione</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('provvigioni.update', $provvigione->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="legacy_id">Legacy ID</label>
                            <input type="text" class="form-control" value="{{ $provvigione->legacy_id }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="data_stipula">Data Stipula</label>
                            <input type="date" name="data_stipula" id="data_stipula" class="form-control @error('data_stipula') is-invalid @enderror" value="{{ old('data_stipula', $provvigione->data_stipula ? $provvigione->data_stipula->format('Y-m-d') : '') }}">
                            @error('data_stipula')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="descrizione">Descrizione</label>
                            <input type="text" class="form-control" value="{{ $provvigione->descrizione }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tipo">Tipo</label>
                            <input type="text" class="form-control" value="{{ $provvigione->tipo }}" readonly>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="importo">Importo</label>
                            <input type="number" step="0.01" name="importo" id="importo" class="form-control" value="{{ old('importo', $provvigione->importo) }}" required disabled>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="stato">Stato *</label>
                            <select name="stato" id="stato" class="form-control @error('stato') is-invalid @enderror" required>
                                <option value="">Select Stato</option>
                                @foreach($statoOptions as $option)
                                    <option value="{{ $option }}" {{ old('stato', $provvigione->stato) == $option ? 'selected' : '' }}>
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
                            <label for="sended_at">Sended At</label>
                            <input type="datetime-local" name="sended_at" id="sended_at" class="form-control @error('sended_at') is-invalid @enderror"
                                   value="{{ old('sended_at', $provvigione->sended_at ? $provvigione->sended_at->format('Y-m-d\TH:i') : '') }}">
                            @error('sended_at')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="invoice_number">Invoice Number</label>
                            <input type="text" name="invoice_number" id="invoice_number" class="form-control @error('invoice_number') is-invalid @enderror" value="{{ old('invoice_number', $provvigione->invoice_number) }}">
                            @error('invoice_number')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="received_at">Received At</label>
                            <input type="datetime-local" name="received_at" id="received_at" class="form-control @error('received_at') is-invalid @enderror"
                                   value="{{ old('received_at', $provvigione->received_at ? $provvigione->received_at->format('Y-m-d\TH:i') : '') }}">
                            @error('received_at')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="paided_at">Paid At</label>
                            <input type="datetime-local" name="paided_at" id="paided_at" class="form-control @error('paided_at') is-invalid @enderror"
                                   value="{{ old('paided_at', $provvigione->paided_at ? $provvigione->paided_at->format('Y-m-d\TH:i') : '') }}">
                            @error('paided_at')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="cognome">Cognome</label>
                            <input type="text" class="form-control" value="{{ $provvigione->cognome }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nome">Nome</label>
                            <input type="text" class="form-control" value="{{ $provvigione->nome }}" readonly>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="denominazione_riferimento">Denominazione Riferimento</label>
                    <input type="text" class="form-control" value="{{ $provvigione->denominazione_riferimento }}" readonly>
                </div>

                <div class="form-group">
                    <a href="{{ route('provvigioni.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Provvigione</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
