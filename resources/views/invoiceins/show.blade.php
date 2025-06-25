@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Invoicein Details</h2>
    <table class="table table-bordered">
        <tbody>
            <tr><th>ID</th><td>{{ $invoicein->id }}</td></tr>
            <tr><th>Nome Fornitore</th><td>{{ $invoicein->nome_fornitore }}</td></tr>
            <tr><th>Partita IVA</th><td>{{ $invoicein->partita_iva }}</td></tr>
            <tr><th>Tipo di Documento</th><td>{{ $invoicein->tipo_di_documento }}</td></tr>
            <tr><th>Nr Documento</th><td>{{ $invoicein->nr_documento }}</td></tr>
            <tr><th>Created At</th><td>{{ $invoicein->created_at }}</td></tr>
            <tr><th>Updated At</th><td>{{ $invoicein->updated_at }}</td></tr>
        </tbody>
    </table>
    <a href="{{ route('invoiceins.index') }}" class="btn btn-secondary">Back to List</a>
</div>
@endsection
