@extends('adminlte::page')

@section('title', 'Companies')

@section('content_header')
    <h1>Companies</h1>
    <div class="float-right">
        <a href="{{ route('companies.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Company
        </a>
    </div>
    <div class="clearfix"></div>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>P.IVA</th>
                            <th>Last Updated By</th>
                            <th>Updated At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($companies as $company)
                            <tr>
                                <td>{{ $company->id }}</td>
                                <td>{{ $company->name }}</td>
                                <td>{{ $company->piva ?? 'N/A' }}</td>
                                <td>{{ $company->updatedByUser ? $company->updatedByUser->name : 'System' }}</td>
                                <td>{{ $company->updated_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('companies.edit', $company->id) }}" class="btn btn-sm btn-info" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('companies.destroy', $company->id) }}" method="POST" style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this company?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No companies found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $companies->links() }}
            </div>
        </div>
    </div>
@stop

@push('css')
    <style>
        .table th, .table td {
            vertical-align: middle;
        }
        .action-buttons .btn {
            margin-right: 5px;
        }
    </style>
@endpush
