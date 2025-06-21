@extends('layouts.admin')

@section('title', 'Employroles')

@section('content_header')
    <h1>Employroles</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Employroles List</h3>
            <div class="card-tools">
                <a href="{{ route('employroles.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add Employrole
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Company ID</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employroles as $employrole)
                            <tr>
                                <td>{{ $employrole->id }}</td>
                                <td>{{ $employrole->name }}</td>
                                <td>{{ $employrole->company_id }}</td>
                                <td>
                                    <a href="{{ route('employroles.show', $employrole) }}" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('employroles.edit', $employrole) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                                    <form action="{{ route('employroles.destroy', $employrole) }}" method="POST" style="display:inline-block;">
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
                                <td colspan="4" class="text-center">No employroles found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer clearfix">
            {{ $employroles->links() }}
        </div>
    </div>
</div>
@endsection
