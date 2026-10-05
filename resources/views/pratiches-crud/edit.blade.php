@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Modifica Pratica</h3>
                    <div class="card-tools">
                        <a href="{{ route('pratiches-crud.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Torna alla lista
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('pratiches-crud.update', $pratiche->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="codice_pratica">Codice Pratica *</label>
                                    <input type="text" class="form-control @error('codice_pratica') is-invalid @enderror"
                                           id="codice_pratica" name="codice_pratica"
                                           value="{{ old('codice_pratica', $pratiche->codice_pratica) }}" required>
                                    @error('codice_pratica')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="data_inserimento_pratica">Data Inserimento</label>
                                    <input type="date" class="form-control @error('data_inserimento_pratica') is-invalid @enderror"
                                           id="data_inserimento_pratica" name="data_inserimento_pratica"
                                           value="{{ old('data_inserimento_pratica', $pratiche->data_inserimento_pratica ? $pratiche->data_inserimento_pratica->format('Y-m-d') : '') }}">
                                    @error('data_inserimento_pratica')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nome_cliente">Nome Cliente *</label>
                                    <input type="text" class="form-control @error('nome_cliente') is-invalid @enderror"
                                           id="nome_cliente" name="nome_cliente"
                                           value="{{ old('nome_cliente', $pratiche->nome_cliente) }}" required>
                                    @error('nome_cliente')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="cognome_cliente">Cognome Cliente *</label>
                                    <input type="text" class="form-control @error('cognome_cliente') is-invalid @enderror"
                                           id="cognome_cliente" name="cognome_cliente"
                                           value="{{ old('cognome_cliente', $pratiche->cognome_cliente) }}" required>
                                    @error('cognome_cliente')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="codice_fiscale">Codice Fiscale *</label>
                                    <input type="text" class="form-control @error('codice_fiscale') is-invalid @enderror"
                                           id="codice_fiscale" name="codice_fiscale"
                                           value="{{ old('codice_fiscale', $pratiche->codice_fiscale) }}" required>
                                    @error('codice_fiscale')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="denominazione_agente">Denominazione Agente</label>
                                    <input type="text" class="form-control @error('denominazione_agente') is-invalid @enderror"
                                           id="denominazione_agente" name="denominazione_agente"
                                           value="{{ old('denominazione_agente', $pratiche->denominazione_agente) }}">
                                    @error('denominazione_agente')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="partita_iva_agente">Partita IVA Agente</label>
                                    <input type="text" class="form-control @error('partita_iva_agente') is-invalid @enderror"
                                           id="partita_iva_agente" name="partita_iva_agente"
                                           value="{{ old('partita_iva_agente', $pratiche->partita_iva_agente) }}">
                                    @error('partita_iva_agente')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="denominazione_banca">Banca</label>
                                    <input type="text" class="form-control @error('denominazione_banca') is-invalid @enderror"
                                           id="denominazione_banca" name="denominazione_banca"
                                           value="{{ old('denominazione_banca', $pratiche->denominazione_banca) }}">
                                    @error('denominazione_banca')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tipo_prodotto">Tipo Prodotto *</label>
                                    <select class="form-control @error('tipo_prodotto') is-invalid @enderror"
                                            id="tipo_prodotto" name="tipo_prodotto" required>
                                        <option value="">Seleziona tipo prodotto</option>
                                        <option value="prestito" {{ old('tipo_prodotto', $pratiche->tipo_prodotto) == 'prestito' ? 'selected' : '' }}>Prestito</option>
                                        <option value="mutuo" {{ old('tipo_prodotto', $pratiche->tipo_prodotto) == 'mutuo' ? 'selected' : '' }}>Mutuo</option>
                                        <option value="cessione" {{ old('tipo_prodotto', $pratiche->tipo_prodotto) == 'cessione' ? 'selected' : '' }}>Cessione del Quinto</option>
                                    </select>
                                    @error('tipo_prodotto')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="stato_pratica">Stato Pratica *</label>
                                    <select class="form-control @error('stato_pratica') is-invalid @enderror"
                                            id="stato_pratica" name="stato_pratica" required>
                                        <option value="">Seleziona stato</option>
                                        <option value="in_attesa" {{ old('stato_pratica', $pratiche->stato_pratica) == 'in_attesa' ? 'selected' : '' }}>In Attesa</option>
                                        <option value="in_lavorazione" {{ old('stato_pratica', $pratiche->stato_pratica) == 'in_lavorazione' ? 'selected' : '' }}>In Lavorazione</option>
                                        <option value="completata" {{ old('stato_pratica', $pratiche->stato_pratica) == 'completata' ? 'selected' : '' }}>Completata</option>
                                        <option value="respinta" {{ old('stato_pratica', $pratiche->stato_pratica) == 'respinta' ? 'selected' : '' }}>Respinta</option>
                                    </select>
                                    @error('stato_pratica')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="descrizione_prodotto">Descrizione Prodotto</label>
                            <textarea class="form-control @error('descrizione_prodotto') is-invalid @enderror"
                                    id="descrizione_prodotto" name="descrizione_prodotto" rows="3">{{ old('descrizione_prodotto', $pratiche->descrizione_prodotto) }}</textarea>
                            @error('descrizione_prodotto')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group text-right">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Aggiorna Pratica
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
