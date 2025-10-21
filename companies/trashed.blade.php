@extends('layouts.app')

@section('title', 'Trashed Companies')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Trashed Companies</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('companies.index') }}">Companies</a></li>
                <li class="breadcrumb-item active">Trashed</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">List of Deleted Companies</h3>
        <div class="card-tools">
            <a href="{{ route('companies.index') }}" class="btn btn-default btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Companies
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th style="width: 10px">#</th>
                        <th>Name</th>
                        <th>PIVA</th>
                        <th>CRM</th>
                        <th>Call Center</th>
                        <th>Deleted By</th>
                        <th>Deleted At</th>
                        <th style="width: 200px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($companies as $company)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $company->name }}</td>
                        <td>{{ $company->piva }}</td>
                        <td>{{ $company->crm }}</td>
                        <td>{{ $company->callcenter }}</td>
                        <td>{{ $company->deletedByUser->name ?? 'System' }}</td>
                        <td>{{ $company->deleted_at->format('Y-m-d H:i:s') }}</td>
                        <td>
                            <div class="btn-group">
                                <form action="{{ route('companies.restore', $company->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Are you sure you want to restore this company?')">
                                        <i class="fas fa-trash-restore"></i> Restore
                                    </button>
                                </form>
                                <form action="{{ route('companies.force-delete', $company->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to permanently delete this company? This action cannot be undone.')">
                                        <i class="fas fa-trash"></i> Delete Permanently
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">No trashed companies found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($companies->hasPages())
    <div class="card-footer clearfix">
        {{ $companies->links() }}
    </div>
    @endif
</div>
@stop

@section('css')
<style>
    .btn-group {
        display: flex;
        gap: 5px;
    }
    .btn-group form {
        margin: 0;
    }
    .table td {
        vertical-align: middle;
    }
</style>
@stop
