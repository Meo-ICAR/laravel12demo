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
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-file-invoice mr-2"></i>
                                Proforma #{{ $proforma->id }} - {{ $proforma->fornitori->name ?? 'N/A' }}
                            </h3>
                            <div class="card-tools">
                                <span class="badge badge-{{ $proforma->stato == 'Inserito' ? 'secondary' : ($proforma->stato == 'Proforma' ? 'info' : ($proforma->stato == 'Fatturato' ? 'warning' : ($proforma->stato == 'Pagato' ? 'success' : 'danger'))) }}">
                                    {{ $proforma->stato }}
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="text-primary mb-3">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        Informazioni
                                    </h5>
                                    <dl class="row">
                                        <dt class="col-sm-4">ID</dt>
                                        <dd class="col-sm-8">
                                            <span class="badge badge-info">{{ $proforma->id }}</span>
                                        </dd>

                                        <dt class="col-sm-4">Fornitore</dt>
                                        <dd class="col-sm-8">
                                            <strong>{{ $proforma->fornitori->name ?? '-' }}</strong>
                                        </dd>

                                        <dt class="col-sm-4">Compenso</dt>
                                        <dd class="col-sm-8">
                                            <span class="text-success font-weight-bold">€ {{ number_format($proforma->compenso, 2, ',', '.') }}</span>
                                        </dd>

                                        <dt class="col-sm-4">Contributo</dt>
                                        <dd class="col-sm-8">€ {{ number_format($proforma->contributo ?? 0, 2, ',', '.') }}</dd>

                                        <dt class="col-sm-4">Anticipo</dt>
                                        <dd class="col-sm-8">
                                            <span class="text-danger font-weight-bold">€ {{ number_format($proforma->anticipo ?? 0, 2, ',', '.') }}</span>
                                        </dd>

                                        <dt class="col-sm-4">Totale</dt>
                                        <dd class="col-sm-8">
                                            <span class="badge badge-{{ ($proforma->compenso + ($proforma->contributo ?? 0) - ($proforma->anticipo ?? 0)) >= 0 ? 'success' : 'danger' }} badge-lg">
                                                € {{ number_format($proforma->compenso + ($proforma->contributo ?? 0) - ($proforma->anticipo ?? 0), 2, ',', '.') }}
                                            </span>
                                        </dd>
                                    </dl>
                                </div>
                                <div class="col-md-6">
                                    <h5 class="text-primary mb-3">
                                        <i class="fas fa-clock mr-2"></i>
                                        Dates & Status
                                    </h5>
                                    <dl class="row">
                                        <dt class="col-sm-4">Sended At</dt>
                                        <dd class="col-sm-8">
                                            @if($proforma->sended_at)
                                                <span class="badge badge-success">
                                                    <i class="fas fa-check mr-1"></i>
                                                    {{ \Carbon\Carbon::parse($proforma->sended_at)->format('d/m/Y H:i') }}
                                                </span>
                                            @else
                                                <span class="badge badge-secondary">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    Not sent
                                                </span>
                                            @endif
                                        </dd>

                                        <dt class="col-sm-4">Paid At</dt>
                                        <dd class="col-sm-8">
                                            @if($proforma->paid_at)
                                                <span class="badge badge-success">
                                                    <i class="fas fa-check mr-1"></i>
                                                    {{ \Carbon\Carbon::parse($proforma->paid_at)->format('d/m/Y H:i') }}
                                                </span>
                                            @else
                                                <span class="badge badge-warning">
                                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                                    Not paid
                                                </span>
                                            @endif
                                        </dd>

                                        <dt class="col-sm-4">Provvigioni</dt>
                                        <dd class="col-sm-8">
                                            <span class="badge badge-info badge-lg">{{ $proforma->provvigioni->count() }} items</span>
                                        </dd>
                                    </dl>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="text-primary mb-3">
                                        <i class="fas fa-file-alt mr-2"></i>
                                        Descriptions
                                    </h5>
                                    <dl class="row">
                                        <dt class="col-sm-4">Compenso Descrizione</dt>
                                        <dd class="col-sm-8">{{ $proforma->compenso_descrizione ?? '-' }}</dd>

                                        <dt class="col-sm-4">Contributo Descrizione</dt>
                                        <dd class="col-sm-8">{{ $proforma->contributo_descrizione ?? '-' }}</dd>

                                        <dt class="col-sm-4">Anticipo Descrizione</dt>
                                        <dd class="col-sm-8">{{ $proforma->anticipo_descrizione ?? '-' }}</dd>
                                    </dl>
                                </div>
                                <div class="col-md-6">
                                    <h5 class="text-primary mb-3">
                                        <i class="fas fa-sticky-note mr-2"></i>
                                        Additional Information
                                    </h5>
                                    <dl class="row">
                                        <dt class="col-sm-4">Annotazioni</dt>
                                        <dd class="col-sm-8">
                                            @if($proforma->annotation)
                                                <div class="alert alert-info mb-0">
                                                    {{ $proforma->annotation }}
                                                </div>
                                            @else
                                                -
                                            @endif
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="btn-group">
                            @if($proforma->stato === 'Inserito')
                                            <a href="{{ route('proformas.edit', $proforma) }}" class="btn btn-primary btn-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @else
                                            <button type="button" class="btn btn-primary btn-sm" disabled title="Editing is only available for 'Inserito' status">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            @endif
                                </a>
                                    <button type="button" class="btn btn-info" onclick="sendProformaEmail()">
                                        <i class="fas fa-envelope mr-1"></i> Send Email
                                    </button>

                                <a href="{{ route('proformas.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left mr-1"></i> Back
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">
                                Linked Provvigioni
                            </h4>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>Cognome</th>
                                            <th>Nome</th>
                                            <th>Descrizione</th>
                                            <th>Prodotto</th>
                                            <th class="text-right">Importo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $totalImporto = 0; @endphp
                                        @foreach($proforma->provvigioni as $provvigione)
                                            <tr>
                                                <td>{{ $provvigione->cognome }}</td>
                                                <td>{{ $provvigione->nome }}</td>
                                                <td>{{ $provvigione->descrizione }}</td>
                                                <td>{{ $provvigione->prodotto }}</td>
                                                <td class="text-right">€ {{ number_format($provvigione->importo, 2, ',', '.') }}</td>
                                            </tr>
                                            @php $totalImporto += $provvigione->importo; @endphp
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="4" class="text-right">Total</th>
                                            <th class="text-right">€ {{ number_format($totalImporto, 2, ',', '.') }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
function sendProformaEmail() {
    if (confirm('Send email to {{ $proforma->emailto }}?')) {
        // Create form data
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('proforma_id', '{{ $proforma->id }}');

        // Show loading state
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Sending...';
        btn.disabled = true;

        fetch('{{ route("proformas.sendEmail", $proforma) }}', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Email sent successfully!');
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error sending email: ' + error.message);
        })
        .finally(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }
}
</script>
@endsection
