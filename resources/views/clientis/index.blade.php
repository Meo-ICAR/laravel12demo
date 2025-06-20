@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Clienti</h1>
    <a href="{{ route('clientis.create') }}" class="btn btn-primary mb-3">Add Cliente</a>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Codice</th>
                <th>Name</th>
                <th>PIVA</th>
                <th>Email</th>
                <th>Is Collaboratore</th>
                <th>Is Dipendente</th>
                <th>Regione</th>
                <th>Città</th>
                <th>Company ID</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clientis as $clienti)
                <tr>
                    <td>{{ $clienti->id }}</td>
                    <td>{{ $clienti->codice }}</td>
                    <td>{{ $clienti->name }}</td>
                    <td>{{ $clienti->piva }}</td>
                    <td>{{ $clienti->email }}</td>
                    <td>{{ $clienti->iscollaboratore }}</td>
                    <td>{{ $clienti->isdipendente }}</td>
                    <td>{{ $clienti->regione }}</td>
                    <td>{{ $clienti->citta }}</td>
                    <td>{{ $clienti->company_id }}</td>
                    <td>
                        <a href="{{ route('clientis.show', $clienti) }}" class="btn btn-info btn-sm">Show</a>
                        <a href="{{ route('clientis.edit', $clienti) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('clientis.destroy', $clienti) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
