@extends('layouts.admin')

@section('title', 'Invoice Check - ' . $invoice->invoice_number)

<meta name="csrf-token" content="{{ csrf_token() }}">

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Invoice Check</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">Invoices</a></li>
                <li class="breadcrumb-item active">Check</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <h2>Invoice Details</h2>
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <strong>Fornitore:</strong> {{ $invoice->fornitore }}<br>
                    <strong>COGE:</strong> {{ $invoice->coge }}<br>
                    <strong>Invoice Number:</strong> {{ $invoice->invoice_number }}<br>
                    <strong>Date:</strong> {{ $invoice->invoice_date ? $invoice->invoice_date->format('d/m/Y') : '-' }}<br>
                </div>
                <div class="col-md-6">
                    <strong>Total Amount:</strong> € {{ number_format($invoice->total_amount, 2, ',', '.') }}<br>
                    <strong>Status:</strong> {{ ucfirst($invoice->status) }}<br>
                    <strong>Paid At:</strong> {{ $invoice->paid_at ? $invoice->paid_at->format('d/m/Y') : '-' }}<br>
                    <strong>Delta:</strong> € <span id="delta-amount">{{ number_format($provvigioni->sum('importo') - $invoice->total_amount, 2, ',', '.') }}</span><br>
                    <button type="submit" class="btn btn-success mt-2" id="reconcile-btn">
                        <i class="fas fa-balance-scale mr-1"></i> Reconcile
                    </button>
                </div>
            </div>
        </div>
    </div>

    <h3>Provvigioni (Unpaid, before Invoice Date)</h3>
    <form method="POST" action="{{ route('invoices.reconcileChecked', $invoice->id) }}" id="reconcile-form">
        @csrf
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all"></th>
                        <th>Data Status Pratica</th>
                        <th>Prodotto</th>
                        <th>Status</th>
                        <th>Cognome</th>
                        <th>Nome</th>
                        <th>Importo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($provvigioni as $provvigione)
                        <tr>
                            <td><input type="checkbox" class="provvigione-checkbox" name="provvigioni[]" value="{{ $provvigione->id }}" data-importo="{{ $provvigione->importo }}" checked></td>
                            <td>{{ $provvigione->data_status_pratica ? \Carbon\Carbon::parse($provvigione->data_status_pratica)->format('d/m/Y') : '-' }}</td>
                            <td>{{ $provvigione->prodotto }}</td>
                            <td>{{ $provvigione->stato }}</td>
                            <td>{{ $provvigione->cognome }}</td>
                            <td>{{ $provvigione->nome }}</td>
                            <td>€ {{ number_format($provvigione->importo, 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No provvigioni found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-2 text-right">
                <strong>Somma Importo selezionati:</strong> € <span id="sum-importo">{{ number_format($provvigioni->sum('importo'), 2, ',', '.') }}</span>
            </div>
        </div>
    </form>
</div>

<script>
    function updateSumImportoAndDelta() {
        let sum = 0;
        document.querySelectorAll('.provvigione-checkbox:checked').forEach(cb => {
            sum += parseFloat(cb.getAttribute('data-importo'));
        });
        document.getElementById('sum-importo').textContent = sum.toLocaleString('it-IT', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        const invoiceTotal = {{ $invoice->total_amount }};
        const delta = sum - invoiceTotal;
        document.getElementById('delta-amount').textContent = delta.toLocaleString('it-IT', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        // Disable Reconcile button if delta > 0
        const reconcileBtn = document.getElementById('reconcile-btn');
        if (delta > 0) {
            reconcileBtn.disabled = true;
        } else {
            reconcileBtn.disabled = false;
        }
    }
    document.querySelectorAll('.provvigione-checkbox, #select-all').forEach(cb => {
        cb.addEventListener('change', updateSumImportoAndDelta);
    });

    // Add client-side validation for reconcile form
    document.getElementById('reconcile-form').addEventListener('submit', function(e) {
        const checked = document.querySelectorAll('.provvigione-checkbox:checked').length;
        if (checked === 0) {
            e.preventDefault();
            alert('Please select at least one record to reconcile.');
            return;
        }
        // Check delta
        const delta = parseFloat(document.getElementById('delta-amount').textContent.replace(',', '.'));
        if (delta < 0) {
            if (!confirm('The sum of selected provvigioni is less than the invoice total. Do you want to continue?')) {
                e.preventDefault();
            }
        }
    });
    // Redirect to invoices index after successful reconciliation
    if (window.location.search.includes('success')) {
        window.location.href = "{{ route('invoices.index') }}";
    }
</script>
@endsection
