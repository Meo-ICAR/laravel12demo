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
                <dd class="col-sm-9">{{ $fornitori->codice ?? '-' }}</dd>
                <dt class="col-sm-3">Name</dt>
                <dd class="col-sm-9">{{ $fornitori->name ?? '-' }}</dd>
                <dt class="col-sm-3">Nome</dt>
                <dd class="col-sm-9">{{ $fornitori->nome ?? '-' }}</dd>
                <dt class="col-sm-3">Nato il</dt>
                <dd class="col-sm-9">{{ $fornitori->natoil ? \Carbon\Carbon::parse($fornitori->natoil)->format('d/m/Y') : '-' }}</dd>
                <dt class="col-sm-3">Indirizzo</dt>
                <dd class="col-sm-9">{{ $fornitori->indirizzo ?? '-' }}</dd>
                <dt class="col-sm-3">Comune</dt>
                <dd class="col-sm-9">{{ $fornitori->comune ?? '-' }}</dd>
                <dt class="col-sm-3">CAP</dt>
                <dd class="col-sm-9">{{ $fornitori->cap ?? '-' }}</dd>
                <dt class="col-sm-3">Provincia</dt>
                <dd class="col-sm-9">{{ $fornitori->prov ?? '-' }}</dd>
                <dt class="col-sm-3">Telefono</dt>
                <dd class="col-sm-9">{{ $fornitori->tel ?? '-' }}</dd>
                <dt class="col-sm-3">P.IVA</dt>
                <dd class="col-sm-9">{{ $fornitori->piva ?? '-' }}</dd>
                <dt class="col-sm-3">Email</dt>
                <dd class="col-sm-9">{{ $fornitori->email ?? '-' }}</dd>
                <dt class="col-sm-3">Anticipo</dt>
                <dd class="col-sm-9">{{ $fornitori->anticipo ? '€ ' . number_format($fornitori->anticipo, 2, ',', '.') : '-' }}</dd>
                <dt class="col-sm-3">Anticipo Residuo</dt>
                <dd class="col-sm-9">{{ $fornitori->anticipo_residuo ? '€ ' . number_format($fornitori->anticipo_residuo, 2, ',', '.') : '-' }}</dd>
                <dt class="col-sm-3">Descrizione Anticipo</dt>
                <dd class="col-sm-9">{{ $fornitori->anticipo_description ?? '-' }}</dd>
                <dt class="col-sm-3">Contributo</dt>
                <dd class="col-sm-9">{{ $fornitori->contributo ? '€ ' . number_format($fornitori->contributo, 2, ',', '.') : '-' }}</dd>
                <dt class="col-sm-3">Descrizione Contributo</dt>
                <dd class="col-sm-9">{{ $fornitori->contributo_description ?? '-' }}</dd>
                <dt class="col-sm-3">ENASARCO</dt>
                <dd class="col-sm-9">
                    @if($fornitori->enasarco === 'monomandatario')
                        Monomandatario
                    @elseif($fornitori->enasarco === 'plurimandatario')
                        Plurimandatario
                    @elseif($fornitori->enasarco === 'no')
                        No
                    @else
                        -
                    @endif
                </dd>
                <dt class="col-sm-3">Operatore</dt>
                <dd class="col-sm-9">{{ $fornitori->operatore ?? '-' }}</dd>
                <dt class="col-sm-3">Is Collaboratore</dt>
                <dd class="col-sm-9">{{ $fornitori->iscollaboratore ? 'Sì' : 'No' }}</dd>
                <dt class="col-sm-3">Is Dipendente</dt>
                <dd class="col-sm-9">{{ $fornitori->isdipendente ? 'Sì' : 'No' }}</dd>
                <dt class="col-sm-3">Is Subfornitore</dt>
                <dd class="col-sm-9">{{ $fornitori->issubfornitore ? 'Sì' : 'No' }}</dd>
                <dt class="col-sm-3">Regione</dt>
                <dd class="col-sm-9">{{ $fornitori->regione ?? '-' }}</dd>
                <dt class="col-sm-3">Città</dt>
                <dd class="col-sm-9">{{ $fornitori->citta ?? '-' }}</dd>
                <dt class="col-sm-3">Coordinatore</dt>
                <dd class="col-sm-9">{{ $fornitori->coordinatore ?? '-' }}</dd>
                <dt class="col-sm-3">Coge</dt>
                <dd class="col-sm-9">{{ $fornitori->coge ?? '-' }}</dd>
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
