@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Call Details</h3>
        </div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">ID</dt>
                <dd class="col-sm-9">{{ $call->id }}</dd>

                <dt class="col-sm-3">Numero Chiamato</dt>
                <dd class="col-sm-9">{{ $call->numero_chiamato ?: '-' }}</dd>

                <dt class="col-sm-3">Data Inizio</dt>
                <dd class="col-sm-9">{{ $call->data_inizio ? $call->data_inizio->format('d/m/Y H:i:s') : '-' }}</dd>

                <dt class="col-sm-3">Durata</dt>
                <dd class="col-sm-9">{{ $call->getFormattedDuration() }}</dd>

                <dt class="col-sm-3">Stato Chiamata</dt>
                <dd class="col-sm-9">
                    @if($call->stato_chiamata)
                        <span class="badge badge-{{ $call->stato_chiamata === 'ANSWER' ? 'success' : ($call->stato_chiamata === 'BUSY' ? 'warning' : 'secondary') }}">
                            {{ $call->stato_chiamata }}
                        </span>
                    @else
                        -
                    @endif
                </dd>

                <dt class="col-sm-3">Esito</dt>
                <dd class="col-sm-9">{{ $call->esito ?: '-' }}</dd>

                <dt class="col-sm-3">Utente</dt>
                <dd class="col-sm-9">{{ $call->utente ?: '-' }}</dd>

                <dt class="col-sm-3">Company</dt>
                <dd class="col-sm-9">
                    @if($call->company_id)
                        @php
                            $company = \App\Models\Company::find($call->company_id);
                        @endphp
                        {{ $company ? $company->name : 'Company not found (ID: ' . $call->company_id . ')' }}
                    @else
                        <span class="text-muted">No company assigned</span>
                    @endif
                </dd>

                <dt class="col-sm-3">Created At</dt>
                <dd class="col-sm-9">{{ $call->created_at->format('d/m/Y H:i:s') }}</dd>

                <dt class="col-sm-3">Updated At</dt>
                <dd class="col-sm-9">{{ $call->updated_at->format('d/m/Y H:i:s') }}</dd>
            </dl>
        </div>
        <div class="card-footer">
            <a href="{{ route('calls.index') }}" class="btn btn-secondary">Back to List</a>
            <a href="{{ route('calls.edit', $call) }}" class="btn btn-warning">Edit</a>
        </div>
    </div>
</div>
@endsection
