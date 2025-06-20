@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Add Cliente</h1>
    <form action="{{ route('clientis.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="codice" class="form-label">Codice</label>
            <input type="text" name="codice" class="form-control" value="{{ old('codice') }}">
        </div>
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}">
        </div>
        <div class="mb-3">
            <label for="piva" class="form-label">PIVA</label>
            <input type="text" name="piva" class="form-control" value="{{ old('piva') }}">
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}">
        </div>
        <div class="mb-3">
            <label for="iscollaboratore" class="form-label">Is Collaboratore</label>
            <input type="text" name="iscollaboratore" class="form-control" value="{{ old('iscollaboratore') }}">
        </div>
        <div class="mb-3">
            <label for="isdipendente" class="form-label">Is Dipendente</label>
            <input type="text" name="isdipendente" class="form-control" value="{{ old('isdipendente') }}">
        </div>
        <div class="mb-3">
            <label for="regione" class="form-label">Regione</label>
            <input type="text" name="regione" class="form-control" value="{{ old('regione') }}">
        </div>
        <div class="mb-3">
            <label for="citta" class="form-label">Città</label>
            <input type="text" name="citta" class="form-control" value="{{ old('citta') }}">
        </div>
        <div class="mb-3">
            <label for="company_id" class="form-label">Company ID</label>
            <input type="text" name="company_id" class="form-control" value="{{ old('company_id') }}">
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
        <a href="{{ route('clientis.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
