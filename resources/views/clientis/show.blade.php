@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Cliente Details</h1>
    <table class="table table-bordered">
        <tr><th>ID</th><td>{{ $clienti->id }}</td></tr>
        <tr><th>Codice</th><td>{{ $clienti->codice }}</td></tr>
        <tr><th>Name</th><td>{{ $clienti->name }}</td></tr>
        <tr><th>PIVA</th><td>{{ $clienti->piva }}</td></tr>
        <tr><th>Email</th><td>{{ $clienti->email }}</td></tr>
        <tr><th>Is Collaboratore</th><td>{{ $clienti->iscollaboratore }}</td></tr>
        <tr><th>Is Dipendente</th><td>{{ $clienti->isdipendente }}</td></tr>
        <tr><th>Regione</th><td>{{ $clienti->regione }}</td></tr>
        <tr><th>Città</th><td>{{ $clienti->citta }}</td></tr>
        <tr><th>Company ID</th><td>{{ $clienti->company_id }}</td></tr>
        <tr><th>Created At</th><td>{{ $clienti->created_at }}</td></tr>
        <tr><th>Updated At</th><td>{{ $clienti->updated_at }}</td></tr>
    </table>
    <a href="{{ route('clientis.index') }}" class="btn btn-secondary">Back</a>
    <a href="{{ route('clientis.edit', $clienti) }}" class="btn btn-warning">Edit</a>
    <form action="{{ route('clientis.destroy', $clienti) }}" method="POST" style="display:inline-block;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
    </form>
</div>
@endsection
