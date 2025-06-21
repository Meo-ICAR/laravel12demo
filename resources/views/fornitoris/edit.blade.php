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
                            <label for="name">Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $fornitori->name) }}" required>
                            @error('name')
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
                            <label for="email">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $fornitori->email) }}">
                            @error('email')
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
                            <label for="regione">Regione</label>
                            <input type="text" name="regione" class="form-control" value="{{ old('regione', $fornitori->regione) }}">
                            @error('regione')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="citta">Città</label>
                            <input type="text" name="citta" class="form-control" value="{{ old('citta', $fornitori->citta) }}">
                            @error('citta')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
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
    </div>
</div>
@endsection
