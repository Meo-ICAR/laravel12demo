@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Add Fornitore</h3>
        </div>
        <form action="{{ route('fornitoris.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="codice">Codice</label>
                            <input type="text" name="codice" id="codice" class="form-control" value="{{ old('codice') }}">
                            @error('codice')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
                            @error('name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="piva">P.IVA</label>
                            <input type="text" name="piva" id="piva" class="form-control" value="{{ old('piva') }}">
                            @error('piva')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}">
                            @error('email')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="anticipo">Anticipo</label>
                            <input type="number" name="anticipo" id="anticipo" class="form-control" value="{{ old('anticipo') }}" step="0.01" min="0" max="999999999.99">
                            @error('anticipo')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="operatore">Operatore</label>
                            <input type="text" name="operatore" id="operatore" class="form-control" value="{{ old('operatore') }}">
                            @error('operatore')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="iscollaboratore">Is Collaboratore</label>
                            <select name="iscollaboratore" class="form-control">
                                <option value="0" {{ old('iscollaboratore') == 0 ? 'selected' : '' }}>No</option>
                                <option value="1" {{ old('iscollaboratore') == 1 ? 'selected' : '' }}>Yes</option>
                            </select>
                            @error('iscollaboratore')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="isdipendente">Is Dipendente</label>
                            <select name="isdipendente" class="form-control">
                                <option value="0" {{ old('isdipendente') == 0 ? 'selected' : '' }}>No</option>
                                <option value="1" {{ old('isdipendente') == 1 ? 'selected' : '' }}>Yes</option>
                            </select>
                            @error('isdipendente')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="issubfornitore">Is Subfornitore</label>
                            <select name="issubfornitore" class="form-control">
                                <option value="0" {{ old('issubfornitore') == 0 ? 'selected' : '' }}>No</option>
                                <option value="1" {{ old('issubfornitore') == 1 ? 'selected' : '' }}>Yes</option>
                            </select>
                            @error('issubfornitore')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="regione">Regione</label>
                            <input type="text" name="regione" class="form-control" value="{{ old('regione') }}">
                            @error('regione')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="citta">Città</label>
                            <input type="text" name="citta" class="form-control" value="{{ old('citta') }}">
                            @error('citta')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="company_id">Company</label>
                            <select name="company_id" class="form-control">
                                <option value="">-- Select Company --</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
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
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('fornitoris.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
