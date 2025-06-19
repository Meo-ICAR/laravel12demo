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
                            <input type="text" name="codice" class="form-control" value="{{ old('codice') }}">
                        </div>
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}">
                        </div>
                        <div class="form-group">
                            <label for="piva">P.IVA</label>
                            <input type="text" name="piva" class="form-control" value="{{ old('piva') }}">
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                        </div>
                        <div class="form-group">
                            <label for="operatore">Operatore</label>
                            <input type="text" name="operatore" class="form-control" value="{{ old('operatore') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="iscollaboratore">Is Collaboratore</label>
                            <input type="text" name="iscollaboratore" class="form-control" value="{{ old('iscollaboratore') }}">
                        </div>
                        <div class="form-group">
                            <label for="isdipendente">Is Dipendente</label>
                            <input type="text" name="isdipendente" class="form-control" value="{{ old('isdipendente') }}">
                        </div>
                        <div class="form-group">
                            <label for="regione">Regione</label>
                            <input type="text" name="regione" class="form-control" value="{{ old('regione') }}">
                        </div>
                        <div class="form-group">
                            <label for="citta">Città</label>
                            <input type="text" name="citta" class="form-control" value="{{ old('citta') }}">
                        </div>
                        <div class="form-group">
                            <label for="company_id">Company ID</label>
                            <input type="text" name="company_id" class="form-control" value="{{ old('company_id') }}">
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
