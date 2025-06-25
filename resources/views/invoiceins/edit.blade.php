@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Invoicein</h2>
    <form action="{{ route('invoiceins.update', $invoicein) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="nome_fornitore" class="form-label">Nome Fornitore</label>
            <input type="text" name="nome_fornitore" id="nome_fornitore" class="form-control" value="{{ old('nome_fornitore', $invoicein->nome_fornitore) }}">
        </div>
        <div class="mb-3">
            <label for="partita_iva" class="form-label">Partita IVA</label>
            <input type="text" name="partita_iva" id="partita_iva" class="form-control" value="{{ old('partita_iva', $invoicein->partita_iva) }}">
        </div>
        <div class="mb-3">
            <label for="tipo_di_documento" class="form-label">Tipo di Documento</label>
            <input type="text" name="tipo_di_documento" id="tipo_di_documento" class="form-control" value="{{ old('tipo_di_documento', $invoicein->tipo_di_documento) }}">
        </div>
        <div class="mb-3">
            <label for="nr_documento" class="form-label">Nr Documento</label>
            <input type="text" name="nr_documento" id="nr_documento" class="form-control" value="{{ old('nr_documento', $invoicein->nr_documento) }}">
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('invoiceins.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
