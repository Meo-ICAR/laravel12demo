@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Fornitori List</h3>
            <div class="card-tools">
                <a href="{{ route('fornitoris.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add Fornitore
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Codice</th>
                            <th>Name</th>
                            <th>P.IVA</th>
                            <th>Email</th>
                            <th>Operatore</th>
                            <th>Regione</th>
                            <th>Città</th>
                            <th>Company ID</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($fornitoris as $fornitore)
                            <tr>
                                <td>{{ $fornitore->codice }}</td>
                                <td>{{ $fornitore->name }}</td>
                                <td>{{ $fornitore->piva }}</td>
                                <td>{{ $fornitore->email }}</td>
                                <td>{{ $fornitore->operatore }}</td>
                                <td>{{ $fornitore->regione }}</td>
                                <td>{{ $fornitore->citta }}</td>
                                <td>{{ $fornitore->company_id }}</td>
                                <td>
                                    <a href="{{ route('fornitoris.show', $fornitore) }}" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('fornitoris.edit', $fornitore) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                                    <form action="{{ route('fornitoris.destroy', $fornitore) }}" method="POST" style="display:inline-block;">
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
                                <td colspan="9" class="text-center">No fornitori found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer clearfix">
            {{ $fornitoris->links() }}
        </div>
    </div>
</div>
@endsection
