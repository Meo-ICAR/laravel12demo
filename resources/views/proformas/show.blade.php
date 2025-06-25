@extends('layouts.admin')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Proforma Details</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('proformas.index') }}">Proformas</a></li>
                        <li class="breadcrumb-item active">Details</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Proforma Information</h3>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-4">ID</dt>
                                <dd class="col-sm-8">{{ $proforma->id }}</dd>

                                <dt class="col-sm-4">Fornitore</dt>
                                <dd class="col-sm-8">{{ $proforma->fornitori->name ?? '-' }}</dd>

                                <dt class="col-sm-4">Stato</dt>
                                <dd class="col-sm-8">
                                    <span class="badge badge-{{ $proforma->stato == 'Inserito' ? 'secondary' : ($proforma->stato == 'Proforma' ? 'info' : ($proforma->stato == 'Fatturato' ? 'warning' : ($proforma->stato == 'Pagato' ? 'success' : 'danger'))) }}">
                                        {{ $proforma->stato }}
                                    </span>
                                </dd>

                                <dt class="col-sm-4">Compenso</dt>
                                <dd class="col-sm-8">€ {{ number_format($proforma->compenso, 2, ',', '.') }}</dd>

                                <dt class="col-sm-4">Contributo</dt>
                                <dd class="col-sm-8">€ {{ number_format($proforma->contributo ?? 0, 2, ',', '.') }}</dd>

                                <dt class="col-sm-4">Anticipo</dt>
                                <dd class="col-sm-8">€ {{ number_format($proforma->anticipo ?? 0, 2, ',', '.') }}</dd>

                                <dt class="col-sm-4">Totale</dt>
                                <dd class="col-sm-8">
                                    <strong class="text-{{ ($proforma->compenso + ($proforma->contributo ?? 0) - ($proforma->anticipo ?? 0)) >= 0 ? 'success' : 'danger' }}">
                                        € {{ number_format($proforma->compenso + ($proforma->contributo ?? 0) - ($proforma->anticipo ?? 0), 2, ',', '.') }}
                                    </strong>
                                </dd>

                                <dt class="col-sm-4">Compenso Descrizione</dt>
                                <dd class="col-sm-8">{{ $proforma->compenso_descrizione ?? '-' }}</dd>

                                <dt class="col-sm-4">Contributo Descrizione</dt>
                                <dd class="col-sm-8">{{ $proforma->contributo_descrizione ?? '-' }}</dd>

                                <dt class="col-sm-4">Anticipo Descrizione</dt>
                                <dd class="col-sm-8">{{ $proforma->anticipo_descrizione ?? '-' }}</dd>

                                <dt class="col-sm-4">Annotation</dt>
                                <dd class="col-sm-8">{{ $proforma->annotation ?? '-' }}</dd>

                                <dt class="col-sm-4">Sended At</dt>
                                <dd class="col-sm-8">{{ $proforma->sended_at ? \Carbon\Carbon::parse($proforma->sended_at)->format('d/m/Y H:i') : '-' }}</dd>

                                <dt class="col-sm-4">Paid At</dt>
                                <dd class="col-sm-8">{{ $proforma->paid_at ? \Carbon\Carbon::parse($proforma->paid_at)->format('d/m/Y H:i') : '-' }}</dd>
                            </dl>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('proformas.edit', $proforma) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="{{ route('proformas.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Associated Provvigioni ({{ $proforma->provvigioni->count() }})</h3>
                        </div>
                        <div class="card-body p-0">
                            @if($proforma->provvigioni->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Legacy ID</th>
                                                <th>Descrizione</th>
                                                <th class="text-right">Importo</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($proforma->provvigioni as $provvigione)
                                                <tr>
                                                    <td>{{ $provvigione->cognome }} {{ $provvigione->nome }}</td>
                                                    <td>{{ $provvigione->legacy_id ?? '-' }}</td>
                                                    <td>{{ $provvigione->descrizione ?? '-' }}</td>
                                                    <td class="text-right">€ {{ number_format($provvigione->importo, 2, ',', '.') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-dark">
                                                <td colspan="3"><strong>Total</strong></td>
                                                <td class="text-right">
                                                    <strong>€ {{ number_format($proforma->provvigioni->sum('importo'), 2, ',', '.') }}</strong>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-info-circle text-muted fa-2x mb-3"></i>
                                    <p class="text-muted">No provvigioni associated with this proforma.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
