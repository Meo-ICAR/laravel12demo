@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Invoicein Details</h3>
                    <div class="card-tools">
                        <a href="{{ route('invoiceins.edit', $invoicein->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('invoiceins.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="30%">ID:</th>
                                    <td><strong>{{ $invoicein->id }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Nome Fornitore:</th>
                                    <td>{{ $invoicein->nome_fornitore ?: 'Not specified' }}</td>
                                </tr>
                                <tr>
                                    <th>Partita IVA:</th>
                                    <td>{{ $invoicein->partita_iva ?: 'Not specified' }}</td>
                                </tr>
                                <tr>
                                    <th>Tipo Documento:</th>
                                    <td>
                                        @if($invoicein->tipo_di_documento)
                                            <span class="badge badge-{{ $invoicein->tipo_di_documento == 'Fattura' ? 'success' : 'info' }}">
                                                {{ $invoicein->tipo_di_documento }}
                                            </span>
                                        @else
                                            Not specified
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Nr Documento:</th>
                                    <td>{{ $invoicein->nr_documento ?: 'Not specified' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="30%">Data Documento:</th>
                                    <td>{{ $invoicein->data_documento ? \Carbon\Carbon::parse($invoicein->data_documento)->format('d/m/Y') : 'Not specified' }}</td>
                                </tr>
                                <tr>
                                    <th>Importo:</th>
                                    <td>
                                        @if($invoicein->importo)
                                            <strong>€ {{ number_format($invoicein->importo, 2, ',', '.') }}</strong>
                                        @else
                                            Not specified
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Created At:</th>
                                    <td>{{ $invoicein->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At:</th>
                                    <td>{{ $invoicein->updated_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
