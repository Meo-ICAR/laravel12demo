@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2>Inserito Summary</h2>
            <p class="text-muted">Records with stato 'Inserito' grouped by Denominazione Riferimento</p>
        </div>
        <div class="col-md-6 text-right">
            <a href="{{ route('provvigioni.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Provvigioni
            </a>
        </div>
    </div>

    <!-- Sorting Controls -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body py-2">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <label for="orderBy" class="mb-0"><strong>Sort by:</strong></label>
                            <select id="orderBy" class="form-control form-control-sm">
                                <option value="denominazione_riferimento" {{ $orderBy === 'denominazione_riferimento' ? 'selected' : '' }}>Denominazione Riferimento</option>
                                <option value="totale" {{ $orderBy === 'totale' ? 'selected' : '' }}>Total Amount</option>
                                <option value="n" {{ $orderBy === 'n' ? 'selected' : '' }}>Number of Records</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="orderDirection" class="mb-0"><strong>Direction:</strong></label>
                            <select id="orderDirection" class="form-control form-control-sm">
                                <option value="asc" {{ $orderDirection === 'asc' ? 'selected' : '' }}>Ascending</option>
                                <option value="desc" {{ $orderDirection === 'desc' ? 'selected' : '' }}>Descending</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button id="applySort" class="btn btn-primary btn-sm mt-4">
                                <i class="fas fa-sort"></i> Apply Sort
                            </button>
                        </div>
                        <div class="col-md-3 text-right">
                            <small class="text-muted">
                                Current: {{ ucfirst(str_replace('_', ' ', $orderBy)) }}
                                ({{ $orderDirection === 'asc' ? 'A-Z' : 'Z-A' }})
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form id="proforma-all-form" action="{{ route('provvigioni.createProformaFromSummary') }}" method="POST">
        @csrf
        <div class="mb-2">
            <button type="submit" class="btn btn-primary" id="proforma-all-btn" disabled>
                <i class="fas fa-file-invoice"></i> Proforma All
            </button>
        </div>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Inserito Records Summary</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped mb-0">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="check-all"></th>
                                <th>Denominazione Riferimento</th>
                                <th>Email</th>
                                <th class="text-right">N</th>
                                <th class="text-right">Total</th>
                                <th class="text-right">Contributo</th>
                                <th class="text-right">Anticipo</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($proformaSummary as $index => $item)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="row-checkbox" name="denominazioni[]" value="{{ $item->denominazione_riferimento }}">
                                    </td>
                                    <td>
                                        <a href="{{ route('provvigioni.index', ['stato' => 'Inserito', 'denominazione_riferimento' => $item->denominazione_riferimento]) }}"
                                           class="text-primary font-weight-bold">
                                            {{ $item->denominazione_riferimento ?: 'N/A' }}
                                        </a>
                                    </td>
                                    <td>
                                        @if($item->email)
                                            <a href="mailto:{{ $item->email }}" class="text-info">
                                                <i class="fas fa-envelope"></i> {{ $item->email }}
                                            </a>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <span class="badge badge-info">{{ $item->n }}</span>
                                    </td>
                                    <td class="text-right">
                                        <span class="text-success font-weight-bold">
                                            € {{ number_format($item->totale, 2, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <span>{{ $item->contributo !== null ? number_format($item->contributo, 2, ',', '.') : '-' }}</span>
                                    </td>
                                    <td class="text-right">
                                        <span>{{ $item->anticipo !== null ? number_format($item->anticipo, 2, ',', '.') : '-' }}</span>
                                    </td>
                                    <td>
                                        <form action="{{ route('provvigioni.createProformaFromSummary') }}" method="POST" style="display:inline;">
                                            @csrf
                                            <input type="hidden" name="denominazione_riferimento" value="{{ $item->denominazione_riferimento }}">
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="fas fa-file-invoice"></i> Proforma
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">
                                        <div class="py-4">
                                            <i class="fas fa-info-circle text-muted fa-2x mb-3"></i>
                                            <p class="text-muted">No Inserito records found.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($proformaSummary->count() > 0)
                            <tfoot>
                                <tr class="table-dark">
                                    <td colspan="4"><strong>TOTALS</strong></td>
                                    <td class="text-right">
                                        <strong>€ {{ number_format($proformaSummary->sum('totale'), 2, ',', '.') }}</strong>
                                    </td>
                                    <td colspan="4"></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </form>

    @if($proformaSummary->count() > 0)
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Summary Statistics</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="text-center">
                                    <h4 class="text-primary">{{ $proformaSummary->count() }}</h4>
                                    <small class="text-muted">Unique Denominazioni</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <h4 class="text-success">{{ $proformaSummary->sum('n') }}</h4>
                                    <small class="text-muted">Total Records</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Financial Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="text-center">
                                    <h4 class="text-success">€ {{ number_format($proformaSummary->sum('totale'), 2, ',', '.') }}</h4>
                                    <small class="text-muted">Total Amount</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <h4 class="text-info">€ {{ $proformaSummary->sum('n') > 0 ? number_format($proformaSummary->sum('totale') / $proformaSummary->sum('n'), 2, ',', '.') : '0,00' }}</h4>
                                    <small class="text-muted">Average per Record</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('applySort').addEventListener('click', function() {
        const orderBy = document.getElementById('orderBy').value;
        const orderDirection = document.getElementById('orderDirection').value;
        const url = new URL(window.location);
        url.searchParams.set('order_by', orderBy);
        url.searchParams.set('order_direction', orderDirection);
        window.location.href = url.toString();
    });
    document.getElementById('orderBy').addEventListener('change', function() {
        document.getElementById('applySort').click();
    });
    document.getElementById('orderDirection').addEventListener('change', function() {
        document.getElementById('applySort').click();
    });
    const checkAll = document.getElementById('check-all');
    const checkboxes = document.querySelectorAll('.row-checkbox');
    const proformaAllBtn = document.getElementById('proforma-all-btn');

    checkAll.addEventListener('change', function() {
        checkboxes.forEach(cb => cb.checked = checkAll.checked);
        proformaAllBtn.disabled = !Array.from(checkboxes).some(cb => cb.checked);
    });
    checkboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            proformaAllBtn.disabled = !Array.from(checkboxes).some(cb => cb.checked);
            if (!cb.checked) checkAll.checked = false;
        });
    });
});
</script>
@endsection
