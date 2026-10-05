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
        <div class="mb-3">
            <label for="customertype_id" class="form-label">Customer Type</label>
            <select name="customertype_id" class="form-control">
                <option value="">-- Select --</option>
                @foreach($customertypes as $type)
                    <option value="{{ $type->id }}" {{ old('customertype_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
        <a href="{{ route('clientis.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
