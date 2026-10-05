@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Nuova Pratica</h3>
                    <div class="card-tools">
                        <a href="{{ route('pratiches-crud.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Torna alla lista
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('pratiches-crud.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pratica_id">ID Pratica *</label>
                                    <input type="text" class="form-control @error('pratica_id') is-invalid @enderror"
                                           id="pratica_id" name="pratica_id" value="{{ old('pratica_id') }}" required>
                                    @error('pratica_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="Data_inserimento">Data Inserimento</label>
                                    <input type="date" class="form-control @error('Data_inserimento') is-invalid @enderror"
                                           id="Data_inserimento" name="Data_inserimento" value="{{ old('Data_inserimento') }}">
                                    @error('Data_inserimento')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="Descrizione">Descrizione</label>
                            <textarea class="form-control @error('Descrizione') is-invalid @enderror"
                                      id="Descrizione" name="Descrizione" rows="3">{{ old('Descrizione') }}</textarea>
                            @error('Descrizione')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="Cliente">Cliente</label>
                                    <input type="text" class="form-control @error('Cliente') is-invalid @enderror"
                                           id="Cliente" name="Cliente" value="{{ old('Cliente') }}">
                                    @error('Cliente')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="Agente">Agente</label>
                                    <input type="text" class="form-control @error('Agente') is-invalid @enderror"
                                           id="Agente" name="Agente" value="{{ old('Agente') }}">
                                    @error('Agente')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="Segnalatore">Segnalatore</label>
                                    <input type="text" class="form-control @error('Segnalatore') is-invalid @enderror"
                                           id="Segnalatore" name="Segnalatore" value="{{ old('Segnalatore') }}">
                                    @error('Segnalatore')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="Fonte">Fonte</label>
                                    <input type="text" class="form-control @error('Fonte') is-invalid @enderror"
                                           id="Fonte" name="Fonte" value="{{ old('Fonte') }}">
                                    @error('Fonte')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="Tipo">Tipo</label>
                                    <select class="form-control @error('Tipo') is-invalid @enderror" id="Tipo" name="Tipo">
                                        <option value="">Seleziona tipo</option>
                                        <option value="Cessione" {{ old('Tipo') == 'Cessione' ? 'selected' : '' }}>Cessione</option>
                                        <option value="Mutuo" {{ old('Tipo') == 'Mutuo' ? 'selected' : '' }}>Mutuo</option>
                                        <option value="Prestito" {{ old('Tipo') == 'Prestito' ? 'selected' : '' }}>Prestito</option>
                                        <option value="Delega" {{ old('Tipo') == 'Delega' ? 'selected' : '' }}>Delega</option>
                                        <option value="Polizza" {{ old('Tipo') == 'Polizza' ? 'selected' : '' }}>Polizza</option>
                                    </select>
                                    @error('Tipo')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="Istituto_finanziario">Istituto Finanziario</label>
                                    <input type="text" class="form-control @error('Istituto_finanziario') is-invalid @enderror"
                                           id="Istituto_finanziario" name="Istituto_finanziario" value="{{ old('Istituto_finanziario') }}">
                                    @error('Istituto_finanziario')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Salva
                            </button>
                            <a href="{{ route('pratiches-crud.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Annulla
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
