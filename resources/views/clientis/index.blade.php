@extends('layouts.admin')

@section('title', 'Clienti')

@section('content_header')
    <h1>Clienti</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Clienti List</h3>
            <div class="card-tools">
                <a href="{{ route('clientis.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add Cliente
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Customer Type</th>
                            <th>PIVA</th>
                            <th>Email</th>
                            <th>Regione</th>
                            <th>Città</th>
                            <th>Codice</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clientis as $clienti)
                            <tr>
                                <td>{{ $clienti->name }}</td>
                                <td>{{ $clienti->customertype ? $clienti->customertype->name : '' }}</td>
                                <td>{{ $clienti->piva }}</td>
                                <td>{{ $clienti->email }}</td>
                                <td>{{ $clienti->regione }}</td>
                                <td>{{ $clienti->citta }}</td>
                                <td>{{ $clienti->codice }}</td>
                                <td>
                                    <a href="{{ route('clientis.show', $clienti) }}" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('clientis.edit', $clienti) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                                    <form action="{{ route('clientis.destroy', $clienti) }}" method="POST" style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No clienti found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer clearfix">
            <!-- Pagination removed - using Collection instead of paginated results -->
        </div>
    </div>
</div>
@endsection
