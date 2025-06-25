@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Invoiceins List</h2>
    <form method="GET" action="{{ route('invoiceins.index') }}" class="mb-3">
        <div class="row g-2 align-items-center">
            <div class="col-auto">
                <input type="text" name="nome_fornitore" class="form-control" placeholder="Filter by Nome Fornitore" value="{{ request('nome_fornitore') }}">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
            <div class="col-auto">
                <a href="{{ route('invoiceins.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </div>
    </form>
    <a href="{{ route('invoiceins.create') }}" class="btn btn-success mb-3">Create New Invoicein</a>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome Fornitore</th>
                <th>Partita IVA</th>
                <th>Tipo Documento</th>
                <th>Nr Documento</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoiceins as $inv)
                <tr>
                    <td>{{ $inv->id }}</td>
                    <td>{{ $inv->nome_fornitore }}</td>
                    <td>{{ $inv->partita_iva }}</td>
                    <td>{{ $inv->tipo_di_documento }}</td>
                    <td>{{ $inv->nr_documento }}</td>
                    <td>
                        <a href="{{ route('invoiceins.show', $inv) }}" class="btn btn-info btn-sm">View</a>
                        <a href="{{ route('invoiceins.edit', $inv) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('invoiceins.destroy', $inv) }}" method="POST" style="display:inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $invoiceins->withQueryString()->links() }}
</div>
@endsection
