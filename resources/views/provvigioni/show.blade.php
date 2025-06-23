@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2>Provvigione Details</h2>
        </div>
        <div class="col-md-6 text-right">
            <a href="{{ route('provvigioni.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
            <a href="{{ route('provvigioni.edit', $provvigione->id) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Record Details</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="200">ID:</th>
                            <td>{{ $provvigione->id }}</td>
                        </tr>
                        <tr>
                            <th>Legacy ID:</th>
                            <td>{{ $provvigione->legacy_id }}</td>
                        </tr>
                        <tr>
                            <th>Data Inserimento Compenso:</th>
                            <td>{{ $provvigione->data_inserimento_compenso }}</td>
                        </tr>
                        <tr>
                            <th>Descrizione:</th>
                            <td>{{ $provvigione->descrizione }}</td>
                        </tr>
                        <tr>
                            <th>Tipo:</th>
                            <td>{{ $provvigione->tipo }}</td>
                        </tr>
                        <tr>
                            <th>Importo:</th>
                            <td class="font-weight-bold">€ {{ number_format($provvigione->importo, 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th>Importo Effettivo:</th>
                            <td class="font-weight-bold">€ {{ number_format($provvigione->importo_effettivo, 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th>Quota:</th>
                            <td>{{ $provvigione->quota }}</td>
                        </tr>
                        <tr>
                            <th>Stato:</th>
                            <td>
                                <span class="badge badge-{{ $provvigione->stato == 'Pagato' ? 'success' : ($provvigione->stato == 'Fatturato' ? 'info' : ($provvigione->stato == 'Proforma' ? 'warning' : ($provvigione->stato == 'Stornato' ? 'danger' : 'secondary'))) }}">
                                    {{ $provvigione->stato }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="200">Denominazione Riferimento:</th>
                            <td>{{ $provvigione->denominazione_riferimento }}</td>
                        </tr>
                        <tr>
                            <th>Entrata/Uscita:</th>
                            <td>{{ $provvigione->entrata_uscita }}</td>
                        </tr>
                        <tr>
                            <th>Cognome:</th>
                            <td>{{ $provvigione->cognome }}</td>
                        </tr>
                        <tr>
                            <th>Nome:</th>
                            <td>{{ $provvigione->nome }}</td>
                        </tr>
                        <tr>
                            <th>Segnalatore:</th>
                            <td>{{ $provvigione->segnalatore }}</td>
                        </tr>
                        <tr>
                            <th>Fonte:</th>
                            <td>{{ $provvigione->fonte }}</td>
                        </tr>
                        <tr>
                            <th>ID Pratica:</th>
                            <td>{{ $provvigione->id_pratica }}</td>
                        </tr>
                        <tr>
                            <th>Tipo Pratica:</th>
                            <td>{{ $provvigione->tipo_pratica }}</td>
                        </tr>
                        <tr>
                            <th>Data Inserimento Pratica:</th>
                            <td>{{ $provvigione->data_inserimento_pratica }}</td>
                        </tr>
                        <tr>
                            <th>Data Stipula:</th>
                            <td>{{ $provvigione->data_stipula }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-12">
                    <table class="table table-borderless">
                        <tr>
                            <th width="200">Istituto Finanziario:</th>
                            <td>{{ $provvigione->istituto_finanziario }}</td>
                        </tr>
                        <tr>
                            <th>Prodotto:</th>
                            <td>{{ $provvigione->prodotto }}</td>
                        </tr>
                        <tr>
                            <th>Macrostatus:</th>
                            <td>{{ $provvigione->macrostatus }}</td>
                        </tr>
                        <tr>
                            <th>Status Pratica:</th>
                            <td>{{ $provvigione->status_pratica }}</td>
                        </tr>
                        <tr>
                            <th>Data Status Pratica:</th>
                            <td>{{ $provvigione->data_status_pratica }}</td>
                        </tr>
                        <tr>
                            <th>Montante:</th>
                            <td class="font-weight-bold">€ {{ number_format($provvigione->montante, 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th>Importo Erogato:</th>
                            <td class="font-weight-bold">€ {{ number_format($provvigione->importo_erogato, 2, ',', '.') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
