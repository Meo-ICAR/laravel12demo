@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Fornitore Details</h3>
        </div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">Codice</dt>
                <dd class="col-sm-9">{{ $fornitori->codice }}</dd>
                <dt class="col-sm-3">Name</dt>
                <dd class="col-sm-9">{{ $fornitori->name }}</dd>
                <dt class="col-sm-3">P.IVA</dt>
                <dd class="col-sm-9">{{ $fornitori->piva }}</dd>
                <dt class="col-sm-3">Email</dt>
                <dd class="col-sm-9">{{ $fornitori->email }}</dd>
                <dt class="col-sm-3">Operatore</dt>
                <dd class="col-sm-9">{{ $fornitori->operatore }}</dd>
                <dt class="col-sm-3">Is Collaboratore</dt>
                <dd class="col-sm-9">{{ $fornitori->iscollaboratore }}</dd>
                <dt class="col-sm-3">Is Dipendente</dt>
                <dd class="col-sm-9">{{ $fornitori->isdipendente }}</dd>
                <dt class="col-sm-3">Regione</dt>
                <dd class="col-sm-9">{{ $fornitori->regione }}</dd>
                <dt class="col-sm-3">Città</dt>
                <dd class="col-sm-9">{{ $fornitori->citta }}</dd>
                <dt class="col-sm-3">Company</dt>
                <dd class="col-sm-9">
                    @if($fornitori->company_id)
                        @php
                            $company = \App\Models\Company::find($fornitori->company_id);
                        @endphp
                        {{ $company ? $company->name : 'Company not found (ID: ' . $fornitori->company_id . ')' }}
                    @else
                        <span class="text-muted">No company assigned</span>
                    @endif
                </dd>
            </dl>
        </div>
        <div class="card-footer">
            <a href="{{ route('fornitoris.index') }}" class="btn btn-secondary">Back to List</a>
        </div>
    </div>
</div>
@endsection
