@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Create New Lead</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('leads.store') }}" method="POST">
                @csrf

                <div class="row">
                    <!-- Basic Information -->
                    <div class="col-md-6">
                        <h5>Basic Information</h5>

                        <div class="form-group">
                            <label for="legacy_id">Legacy ID</label>
                            <input type="text" name="legacy_id" id="legacy_id" class="form-control @error('legacy_id') is-invalid @enderror"
                                   value="{{ old('legacy_id') }}" maxlength="20">
                            @error('legacy_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="campagna">Campagna</label>
                            <input type="text" name="campagna" id="campagna" class="form-control @error('campagna') is-invalid @enderror"
                                   value="{{ old('campagna') }}" maxlength="100">
                            @error('campagna')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="lista">Lista</label>
                            <input type="text" name="lista" id="lista" class="form-control @error('lista') is-invalid @enderror"
                                   value="{{ old('lista') }}" maxlength="100">
                            @error('lista')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="ragione_sociale">Ragione Sociale</label>
                            <input type="text" name="ragione_sociale" id="ragione_sociale" class="form-control @error('ragione_sociale') is-invalid @enderror"
                                   value="{{ old('ragione_sociale') }}" maxlength="255">
                            @error('ragione_sociale')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="cognome">Cognome</label>
                            <input type="text" name="cognome" id="cognome" class="form-control @error('cognome') is-invalid @enderror"
                                   value="{{ old('cognome') }}" maxlength="100">
                            @error('cognome')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="nome">Nome</label>
                            <input type="text" name="nome" id="nome" class="form-control @error('nome') is-invalid @enderror"
                                   value="{{ old('nome') }}" maxlength="100">
                            @error('nome')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="sesso">Sesso</label>
                            <select name="sesso" id="sesso" class="form-control @error('sesso') is-invalid @enderror">
                                <option value="">Select...</option>
                                <option value="uomo" {{ old('sesso') == 'uomo' ? 'selected' : '' }}>Uomo</option>
                                <option value="donna" {{ old('sesso') == 'donna' ? 'selected' : '' }}>Donna</option>
                            </select>
                            @error('sesso')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="col-md-6">
                        <h5>Contact Information</h5>

                        <div class="form-group">
                            <label for="telefono">Telefono</label>
                            <input type="text" name="telefono" id="telefono" class="form-control @error('telefono') is-invalid @enderror"
                                   value="{{ old('telefono') }}" maxlength="20">
                            @error('telefono')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="telefono2">Telefono 2</label>
                            <input type="text" name="telefono2" id="telefono2" class="form-control @error('telefono2') is-invalid @enderror"
                                   value="{{ old('telefono2') }}" maxlength="20">
                            @error('telefono2')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="telefono3">Telefono 3</label>
                            <input type="text" name="telefono3" id="telefono3" class="form-control @error('telefono3') is-invalid @enderror"
                                   value="{{ old('telefono3') }}" maxlength="20">
                            @error('telefono3')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="telefono4">Telefono 4</label>
                            <input type="text" name="telefono4" id="telefono4" class="form-control @error('telefono4') is-invalid @enderror"
                                   value="{{ old('telefono4') }}" maxlength="20">
                            @error('telefono4')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" maxlength="255">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="p_iva">P.IVA</label>
                            <input type="text" name="p_iva" id="p_iva" class="form-control @error('p_iva') is-invalid @enderror"
                                   value="{{ old('p_iva') }}" maxlength="50">
                            @error('p_iva')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="codice_fiscale">Codice Fiscale</label>
                            <input type="text" name="codice_fiscale" id="codice_fiscale" class="form-control @error('codice_fiscale') is-invalid @enderror"
                                   value="{{ old('codice_fiscale') }}" maxlength="20">
                            @error('codice_fiscale')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <!-- Address Information -->
                    <div class="col-md-6">
                        <h5>Address Information</h5>

                        <div class="form-group">
                            <label for="indirizzo1">Indirizzo 1</label>
                            <input type="text" name="indirizzo1" id="indirizzo1" class="form-control @error('indirizzo1') is-invalid @enderror"
                                   value="{{ old('indirizzo1') }}" maxlength="255">
                            @error('indirizzo1')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="indirizzo2">Indirizzo 2</label>
                            <input type="text" name="indirizzo2" id="indirizzo2" class="form-control @error('indirizzo2') is-invalid @enderror"
                                   value="{{ old('indirizzo2') }}" maxlength="255">
                            @error('indirizzo2')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="indirizzo3">Indirizzo 3</label>
                            <input type="text" name="indirizzo3" id="indirizzo3" class="form-control @error('indirizzo3') is-invalid @enderror"
                                   value="{{ old('indirizzo3') }}" maxlength="255">
                            @error('indirizzo3')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="cap">CAP</label>
                            <input type="text" name="cap" id="cap" class="form-control @error('cap') is-invalid @enderror"
                                   value="{{ old('cap') }}" maxlength="10">
                            @error('cap')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="comune">Comune</label>
                            <input type="text" name="comune" id="comune" class="form-control @error('comune') is-invalid @enderror"
                                   value="{{ old('comune') }}" maxlength="100">
                            @error('comune')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="provincia">Provincia</label>
                            <input type="text" name="provincia" id="provincia" class="form-control @error('provincia') is-invalid @enderror"
                                   value="{{ old('provincia') }}" maxlength="10">
                            @error('provincia')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="regione">Regione</label>
                            <input type="text" name="regione" id="regione" class="form-control @error('regione') is-invalid @enderror"
                                   value="{{ old('regione') }}" maxlength="100">
                            @error('regione')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="paese">Paese</label>
                            <input type="text" name="paese" id="paese" class="form-control @error('paese') is-invalid @enderror"
                                   value="{{ old('paese') }}" maxlength="100">
                            @error('paese')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Call Information -->
                    <div class="col-md-6">
                        <h5>Call Information</h5>

                        <div class="form-group">
                            <label for="ultimo_operatore">Ultimo Operatore</label>
                            <input type="text" name="ultimo_operatore" id="ultimo_operatore" class="form-control @error('ultimo_operatore') is-invalid @enderror"
                                   value="{{ old('ultimo_operatore') }}" maxlength="255">
                            @error('ultimo_operatore')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="esito">Esito</label>
                            <input type="text" name="esito" id="esito" class="form-control @error('esito') is-invalid @enderror"
                                   value="{{ old('esito') }}" maxlength="100">
                            @error('esito')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="data_richiamo">Data Richiamo</label>
                            <input type="datetime-local" name="data_richiamo" id="data_richiamo" class="form-control @error('data_richiamo') is-invalid @enderror"
                                   value="{{ old('data_richiamo') }}">
                            @error('data_richiamo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="operatore_richiamo">Operatore Richiamo</label>
                            <input type="text" name="operatore_richiamo" id="operatore_richiamo" class="form-control @error('operatore_richiamo') is-invalid @enderror"
                                   value="{{ old('operatore_richiamo') }}" maxlength="255">
                            @error('operatore_richiamo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="scadenza_anagrafica">Scadenza Anagrafica</label>
                            <input type="datetime-local" name="scadenza_anagrafica" id="scadenza_anagrafica" class="form-control @error('scadenza_anagrafica') is-invalid @enderror"
                                   value="{{ old('scadenza_anagrafica') }}">
                            @error('scadenza_anagrafica')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="chiamate">Chiamate</label>
                            <input type="number" name="chiamate" id="chiamate" class="form-control @error('chiamate') is-invalid @enderror"
                                   value="{{ old('chiamate', 0) }}" min="0">
                            @error('chiamate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="ultima_chiamata">Ultima Chiamata</label>
                            <input type="datetime-local" name="ultima_chiamata" id="ultima_chiamata" class="form-control @error('ultima_chiamata') is-invalid @enderror"
                                   value="{{ old('ultima_chiamata') }}">
                            @error('ultima_chiamata')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="durata_ultima_chiamata">Durata Ultima Chiamata</label>
                            <input type="text" name="durata_ultima_chiamata" id="durata_ultima_chiamata" class="form-control @error('durata_ultima_chiamata') is-invalid @enderror"
                                   value="{{ old('durata_ultima_chiamata') }}" maxlength="20" placeholder="00:00:00">
                            @error('durata_ultima_chiamata')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="totale_durata_chiamate">Totale Durata Chiamate</label>
                            <input type="text" name="totale_durata_chiamate" id="totale_durata_chiamate" class="form-control @error('totale_durata_chiamate') is-invalid @enderror"
                                   value="{{ old('totale_durata_chiamate') }}" maxlength="20" placeholder="00:00:00">
                            @error('totale_durata_chiamate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="chiamate_giornaliere">Chiamate Giornaliere</label>
                            <input type="number" name="chiamate_giornaliere" id="chiamate_giornaliere" class="form-control @error('chiamate_giornaliere') is-invalid @enderror"
                                   value="{{ old('chiamate_giornaliere', 0) }}" min="0">
                            @error('chiamate_giornaliere')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="chiamate_mensili">Chiamate Mensili</label>
                            <input type="number" name="chiamate_mensili" id="chiamate_mensili" class="form-control @error('chiamate_mensili') is-invalid @enderror"
                                   value="{{ old('chiamate_mensili', 0) }}" min="0">
                            @error('chiamate_mensili')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <!-- Additional Information -->
                    <div class="col-md-12">
                        <h5>Additional Information</h5>

                        <div class="form-group">
                            <label for="nota">Nota</label>
                            <textarea name="nota" id="nota" class="form-control @error('nota') is-invalid @enderror" rows="3">{{ old('nota') }}</textarea>
                            @error('nota')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="creato_da">Creato Da</label>
                            <input type="text" name="creato_da" id="creato_da" class="form-control @error('creato_da') is-invalid @enderror"
                                   value="{{ old('creato_da') }}" maxlength="255">
                            @error('creato_da')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="data_creazione">Data Creazione</label>
                            <input type="datetime-local" name="data_creazione" id="data_creazione" class="form-control @error('data_creazione') is-invalid @enderror"
                                   value="{{ old('data_creazione') }}">
                            @error('data_creazione')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="attivo">Status</label>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" name="attivo" id="attivo" class="custom-control-input" value="1" {{ old('attivo') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="attivo">Active</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Lead
                            </button>
                            <a href="{{ route('leads.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to List
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
