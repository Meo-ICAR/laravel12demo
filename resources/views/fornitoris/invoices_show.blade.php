@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Fornitore: {{ $fornitore->name }}</h3>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <strong>PIVA:</strong> {{ $fornitore->piva }} |
                <strong>CF:</strong> {{ $fornitore->cf }} |
                <strong>Coge:</strong> {{ $fornitore->coge }}
            </div>

            <!-- Filter Section -->
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Filtri</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('fornitoris.invoices.show', $fornitore->id) }}">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="date_from">Data da:</label>
                                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="date_to">Data a:</label>
                                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="amount_from">Importo da:</label>
                                    <input type="number" name="amount_from" class="form-control" step="0.01" value="{{ request('amount_from') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="amount_to">Importo a:</label>
                                    <input type="number" name="amount_to" class="form-control" step="0.01" value="{{ request('amount_to') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="status">Stato:</label>
                                    <select name="status" class="form-control">
                                        <option value="">Tutti</option>
                                        <option value="inserito" {{ request('status') == 'inserito' ? 'selected' : '' }}>Inserito</option>
                                        <option value="abbinato" {{ request('status') == 'abbinato' ? 'selected' : '' }}>Abbinato</option>
                                        <option value="difforme" {{ request('status') == 'difforme' ? 'selected' : '' }}>Difforme</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label><br>
                                    <button type="submit" class="btn btn-primary">Filtra</button>
                                    <a href="{{ route('fornitoris.invoices.show', $fornitore->id) }}" class="btn btn-secondary">Reset</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <h4>Invoices</h4>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'invoice_date', 'direction' => request('sort') == 'invoice_date' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-dark">
                                    Date
                                    @if(request('sort') == 'invoice_date')
                                        <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'invoice_number', 'direction' => request('sort') == 'invoice_number' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-dark">
                                    Invoice Number
                                    @if(request('sort') == 'invoice_number')
                                        <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th class="text-end">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'tax_amount', 'direction' => request('sort') == 'tax_amount' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-dark">
                                    Tax Amount
                                    @if(request('sort') == 'tax_amount')
                                        <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th class="text-end">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'total_amount', 'direction' => request('sort') == 'total_amount' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-dark">
                                    Total Amount
                                    @if(request('sort') == 'total_amount')
                                        <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'status', 'direction' => request('sort') == 'status' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-dark">
                                    Status
                                    @if(request('sort') == 'status')
                                        <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort text-muted"></i>
                                    @endif
                                </a>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $inv)
                            <tr>
                                <td>{{ $inv->invoice_date ? \Carbon\Carbon::parse($inv->invoice_date)->format('Y-m-d') : '' }}</td>
                                <td>{{ $inv->invoice_number }}</td>
                                <td class="text-end">{{ number_format($inv->tax_amount, 2, ',', '.') }}</td>
                                <td class="text-end">{{ number_format($inv->total_amount, 2, ',', '.') }}</td>
                                <td>{{ $inv->status }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $invoices->links() }}
            <a href="{{ route('fornitoris.invoices.index') }}" class="btn btn-secondary mt-3">Back to Fornitori List</a>
        </div>
    </div>
</div>
@endsection
