@extends('layouts.admin')

@section('title', 'Users')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Users</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item active">Users</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">User List</h3>
                <div class="card-tools">
                    @can('import users')
                    <a href="{{ route('users.import') }}" class="btn btn-success btn-sm">
                        <i class="fas fa-file-import"></i> Import Users
                    </a>
                    @endcan
                    @can('export users')
                    <a href="{{ route('users.export') }}" class="btn btn-info btn-sm">
                        <i class="fas fa-file-export"></i> Export Users
                    </a>
                    @endcan
                    @can('create users')
                    <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add New User
                    </a>
                    @endcan
                    <a href="{{ route('users.trashed') }}" class="btn btn-default btn-sm">
                        <i class="fas fa-trash"></i> Trashed Users
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if(session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h5><i class="icon fas fa-check"></i> Success!</h5>
                    {{ session('success') }}
                </div>
                @endif

                @if(session('error'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h5><i class="icon fas fa-ban"></i> Error!</h5>
                    {{ session('error') }}
                </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th style="width: 5%">#</th>
                                <th style="width: 20%">Name</th>
                                <th style="width: 20%">Email</th>
                                <th style="width: 15%">Role</th>
                                <th style="width: 15%">Company</th>
                                <th style="width: 10%">Created By</th>
                                <th style="width: 10%">Created At</th>
                                <th style="width: 5%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <a href="{{ route('users.show', $user) }}" class="text-primary">
                                        {{ $user->name }}
                                    </a>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @foreach($user->roles as $role)
                                    <span class="badge badge-primary">{{ $role->name }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    @if($user->company)
                                    <a href="{{ route('companies.show', $user->company) }}" class="text-info">
                                        {{ $user->company->name }}
                                    </a>
                                    @else
                                    <span class="text-muted">Not assigned</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->created_by)
                                    {{ $user->createdBy->name }}
                                    @else
                                    <span class="text-muted">System</span>
                                    @endif
                                </td>
                                <td>{{ $user->created_at->format('M d, Y H:i') }}</td>
                                <td>
                                    <div class="btn-group">
                                        @can('view users')
                                        <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-info" data-toggle="tooltip" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @endcan
                                        @can('edit users')
                                        <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-primary" data-toggle="tooltip" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan
                                        @can('delete users')
                                        <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" data-toggle="tooltip" title="Delete" onclick="return confirm('Are you sure you want to delete this user?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">No users found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer clearfix">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .table th, .table td {
        vertical-align: middle;
    }
    .btn-group .btn {
        margin: 0 2px;
    }
    .badge {
        margin: 0 2px;
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
@stop
