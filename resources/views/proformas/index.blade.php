@extends('layouts.admin')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Proformas</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Proformas</li>
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
                            <h3 class="card-title">Proformas List</h3>
                            <div class="card-tools">
                                <a href="{{ route('proformas.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Add Proforma
                                </a>
                            </div>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover text-nowrap">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Fornitore</th>
                                        <th>Stato</th>
                                        <th class="text-right">Compenso</th>
                                        <th class="text-right">Contributo</th>
                                        <th class="text-right">Anticipo</th>
                                        <th class="text-right">Totale</th>
                                        <th class="text-right">Provvigioni</th>
                                        <th>Sended At</th>
                                        <th>Paid At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($proformas as $proforma)
                                        <tr>
                                            <td>{{ $proforma->id }}</td>
                                            <td>{{ $proforma->fornitori->name ?? '-' }}</td>
                                            <td>
                                                <span class="badge badge-{{ $proforma->stato == 'Inserito' ? 'secondary' : ($proforma->stato == 'Proforma' ? 'info' : ($proforma->stato == 'Fatturato' ? 'warning' : ($proforma->stato == 'Pagato' ? 'success' : 'danger'))) }}">
                                                    {{ $proforma->stato }}
                                                </span>
                                            </td>
                                            <td class="text-right">€ {{ number_format($proforma->compenso, 2, ',', '.') }}</td>
                                            <td class="text-right">€ {{ number_format($proforma->contributo ?? 0, 2, ',', '.') }}</td>
                                            <td class="text-right">€ {{ number_format($proforma->anticipo ?? 0, 2, ',', '.') }}</td>
                                            <td class="text-right">
                                                <strong class="text-{{ ($proforma->compenso + ($proforma->contributo ?? 0) - ($proforma->anticipo ?? 0)) >= 0 ? 'success' : 'danger' }}">
                                                    € {{ number_format($proforma->compenso + ($proforma->contributo ?? 0) - ($proforma->anticipo ?? 0), 2, ',', '.') }}
                                                </strong>
                                            </td>
                                            <td class="text-right">
                                                <span class="badge badge-info">{{ $proforma->provvigioni->count() }}</span>
                                            </td>
                                            <td>{{ $proforma->sended_at ? \Carbon\Carbon::parse($proforma->sended_at)->format('d/m/Y H:i') : '-' }}</td>
                                            <td>{{ $proforma->paid_at ? \Carbon\Carbon::parse($proforma->paid_at)->format('d/m/Y H:i') : '-' }}</td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('proformas.show', $proforma) }}" class="btn btn-info btn-sm">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('proformas.edit', $proforma) }}" class="btn btn-warning btn-sm">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('proformas.destroy', $proforma) }}" method="POST" style="display:inline-block;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="11" class="text-center">
                                                <div class="py-4">
                                                    <i class="fas fa-info-circle text-muted fa-2x mb-3"></i>
                                                    <p class="text-muted">No proformas found.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer clearfix">
                            {{ $proformas->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
