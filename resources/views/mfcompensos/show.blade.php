@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2>MFCompenso Details</h2>
        </div>
        <div class="col-md-6 text-right">
            <a href="{{ route('mfcompensos.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
            <a href="{{ route('mfcompensos.edit', $mfcompenso->id) }}" class="btn btn-primary">
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
                            <td>{{ $mfcompenso->id }}</td>
                        </tr>
                        <tr>
                            <th>Legacy ID:</th>
                            <td>{{ $mfcompenso->legacy_id }}</td>
                        </tr>
                        <tr>
                            <th>Data Inserimento Compenso:</th>
                            <td>{{ $mfcompenso->data_inserimento_compenso }}</td>
                        </tr>
                        <tr>
                            <th>Descrizione:</th>
                            <td>{{ $mfcompenso->descrizione }}</td>
                        </tr>
                        <tr>
                            <th>Tipo:</th>
                            <td>{{ $mfcompenso->tipo }}</td>
                        </tr>
                        <tr>
                            <th>Importo:</th>
                            <td class="font-weight-bold">€ {{ number_format($mfcompenso->importo, 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th>Importo Effettivo:</th>
                            <td class="font-weight-bold">€ {{ number_format($mfcompenso->importo_effettivo, 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th>Quota:</th>
                            <td>{{ $mfcompenso->quota }}</td>
                        </tr>
                        <tr>
                            <th>Stato:</th>
                            <td>
                                <span class="badge badge-{{ $mfcompenso->stato == 'Pagato' ? 'success' : ($mfcompenso->stato == 'Fatturato' ? 'info' : ($mfcompenso->stato == 'Proforma' ? 'warning' : ($mfcompenso->stato == 'Stornato' ? 'danger' : 'secondary'))) }}">
                                    {{ $mfcompenso->stato }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="200">Denominazione Riferimento:</th>
                            <td>{{ $mfcompenso->denominazione_riferimento }}</td>
                        </tr>
                        <tr>
                            <th>Entrata/Uscita:</th>
                            <td>{{ $mfcompenso->entrata_uscita }}</td>
                        </tr>
                        <tr>
                            <th>Cognome:</th>
                            <td>{{ $mfcompenso->cognome }}</td>
                        </tr>
                        <tr>
                            <th>Nome:</th>
                            <td>{{ $mfcompenso->nome }}</td>
                        </tr>
                        <tr>
                            <th>Segnalatore:</th>
                            <td>{{ $mfcompenso->segnalatore }}</td>
                        </tr>
                        <tr>
                            <th>Fonte:</th>
                            <td>{{ $mfcompenso->fonte }}</td>
                        </tr>
                        <tr>
                            <th>ID Pratica:</th>
                            <td>{{ $mfcompenso->id_pratica }}</td>
                        </tr>
                        <tr>
                            <th>Tipo Pratica:</th>
                            <td>{{ $mfcompenso->tipo_pratica }}</td>
                        </tr>
                        <tr>
                            <th>Data Inserimento Pratica:</th>
                            <td>{{ $mfcompenso->data_inserimento_pratica }}</td>
                        </tr>
                        <tr>
                            <th>Data Stipula:</th>
                            <td>{{ $mfcompenso->data_stipula }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-12">
                    <table class="table table-borderless">
                        <tr>
                            <th width="200">Istituto Finanziario:</th>
                            <td>{{ $mfcompenso->istituto_finanziario }}</td>
                        </tr>
                        <tr>
                            <th>Prodotto:</th>
                            <td>{{ $mfcompenso->prodotto }}</td>
                        </tr>
                        <tr>
                            <th>Macrostatus:</th>
                            <td>{{ $mfcompenso->macrostatus }}</td>
                        </tr>
                        <tr>
                            <th>Status Pratica:</th>
                            <td>{{ $mfcompenso->status_pratica }}</td>
                        </tr>
                        <tr>
                            <th>Data Status Pratica:</th>
                            <td>{{ $mfcompenso->data_status_pratica }}</td>
                        </tr>
                        <tr>
                            <th>Montante:</th>
                            <td class="font-weight-bold">€ {{ number_format($mfcompenso->montante, 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th>Importo Erogato:</th>
                            <td class="font-weight-bold">€ {{ number_format($mfcompenso->importo_erogato, 2, ',', '.') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
