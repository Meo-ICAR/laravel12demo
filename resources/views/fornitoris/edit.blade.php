@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Fornitore</h3>
        </div>
        <form action="{{ route('fornitoris.update', $fornitori) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="codice">Codice</label>
                            <input type="text" name="codice" class="form-control" value="{{ old('codice', $fornitori->codice) }}">
                            @error('codice')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="name">Nome su provvigioni</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $fornitori->name) }}" required>
                            @error('name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="nome">Nome su fatture</label>
                            <input type="text" name="nome" class="form-control" value="{{ old('nome', $fornitori->nome) }}">
                            @error('nome')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="piva">P.IVA</label>
                            <input type="text" name="piva" class="form-control" value="{{ old('piva', $fornitori->piva) }}">
                            @error('piva')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                         <div class="form-group">
                            <label for="enasarco">ENASARCO</label>
                            <select name="enasarco" id="enasarco" class="form-control">
                                <option value="" {{ old('enasarco', $fornitori->enasarco) == '' ? 'selected' : '' }}>Nessuno</option>
                                <option value="no" {{ old('enasarco', $fornitori->enasarco) == 'no' ? 'selected' : '' }}>No</option>
                                <option value="monomandatario" {{ old('enasarco', $fornitori->enasarco) == 'monomandatario' ? 'selected' : '' }}>Monomandatario</option>
                                <option value="plurimandatario" {{ old('enasarco', $fornitori->enasarco) == 'plurimandatario' ? 'selected' : '' }}>Plurimandatario</option>
                                   <option value="plurimandatario" {{ old('enasarco', $fornitori->enasarco) == 'società' ? 'selected' : '' }}>Societa</option>
                            </select>
                            @error('enasarco')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="coge">Conto Coge</label>
                            <input type="text" name="coge" class="form-control" value="{{ old('coge', $fornitori->coge) }}">
                            @error('coge')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $fornitori->email) }}">
                            @error('email')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="anticipo">Anticipo</label>
                            <input type="number" name="anticipo" class="form-control" value="{{ old('anticipo', $fornitori->anticipo) }}" step="0.01" min="0" max="999999999.99">
                            @error('anticipo')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="anticipo_description">Anticipo Descrizione</label>
                            <input type="text" name="anticipo_description" class="form-control" value="{{ old('anticipo_description', $fornitori->anticipo_description) }}">
                            @error('anticipo_description')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                                               <div class="form-group">
                            <label for="anticipo_residuo">Anticipo Residuo</label>
                            <input type="number" step="0.01" class="form-control" id="anticipo_residuo" name="anticipo_residuo" value="{{ old('anticipo_residuo', $fornitori->anticipo_residuo) }}">
                        </div>
                        <div class="form-group">
                            <label for="contributo">Contributo</label>
                            <input type="number" name="contributo" class="form-control" value="{{ old('contributo', $fornitori->contributo) }}" step="0.01" min="0" max="999999999.99">
                            @error('contributo')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="contributo_description">Contributo Description</label>
                            <input type="text" name="contributo_description" class="form-control" value="{{ old('contributo_description', $fornitori->contributo_description) }}">
                            @error('contributo_description')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="operatore">Operatore</label>
                            <input type="text" name="operatore" class="form-control" value="{{ old('operatore', $fornitori->operatore) }}">
                            @error('operatore')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="cf">CF</label>
                            <input type="text" name="cf" class="form-control" value="{{ old('cf', $fornitori->cf) }}" maxlength="16">
                            @error('cf')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="iscollaboratore">Is Collaboratore</label>
                            <select name="iscollaboratore" class="form-control">
                                <option value="0" {{ old('iscollaboratore', $fornitori->iscollaboratore) == 0 ? 'selected' : '' }}>No</option>
                                <option value="1" {{ old('iscollaboratore', $fornitori->iscollaboratore) == 1 ? 'selected' : '' }}>Yes</option>
                            </select>
                            @error('iscollaboratore')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="isdipendente">Is Dipendente</label>
                            <select name="isdipendente" class="form-control">
                                <option value="0" {{ old('isdipendente', $fornitori->isdipendente) == 0 ? 'selected' : '' }}>No</option>
                                <option value="1" {{ old('isdipendente', $fornitori->isdipendente) == 1 ? 'selected' : '' }}>Yes</option>
                            </select>
                            @error('isdipendente')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="issubfornitore">Is Subfornitore</label>
                            <select name="issubfornitore" class="form-control">
                                <option value="0" {{ old('issubfornitore', $fornitori->issubfornitore) == 0 ? 'selected' : '' }}>No</option>
                                <option value="1" {{ old('issubfornitore', $fornitori->issubfornitore) == 1 ? 'selected' : '' }}>Yes</option>
                            </select>
                            @error('issubfornitore')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Additional Information Section -->
                <div class="row mt-4">
                    <div class="col-12">
                        <h5 class="mb-3">Location Information</h5>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="regione">Regione</label>
                            <input type="text" name="regione" class="form-control" value="{{ old('regione', $fornitori->regione) }}">
                            @error('regione')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="citta">Città</label>
                            <input type="text" name="citta" class="form-control" value="{{ old('citta', $fornitori->citta) }}">
                            @error('citta')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="coordinatore">Coordinatore</label>
                            <input type="text" name="coordinatore" class="form-control" value="{{ old('coordinatore', $fornitori->coordinatore) }}">
                            @error('coordinatore')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="company_id">Company</label>
                            <select name="company_id" class="form-control">
                                <option value="">-- Select Company --</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ old('company_id', $fornitori->company_id) == $company->id ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('company_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('fornitoris.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
        <form action="{{ route('fornitoris.destroy', $fornitori) }}" method="POST" style="display:inline-block; margin-left: 10px;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this fornitore?')">
                <i class="fas fa-trash"></i> Delete
            </button>
        </form>
    </div>
</div>
@endsection
