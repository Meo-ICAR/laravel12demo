@extends('layouts.app')

@section('title', 'Roles and Users for ' . ($company ? $company->name : 'Company'))

@section('content_header')
    <h1>Roles and Users for {{ $company ? $company->name : 'Company' }}</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Roles and Users</h3>
    </div>
    <div class="card-body">
        @if($roles->isEmpty())
            <p>No roles found for this company.</p>
        @else
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Role</th>
                            <th>Users</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($roles as $role)
                            <tr>
                                <td><strong>{{ $role->name }}</strong></td>
                                <td>
                                    @if($role->users->isEmpty())
                                        <span class="text-muted">No users assigned</span>
                                    @else
                                        <ul class="mb-0">
                                            @foreach($role->users as $user)
                                                <li>{{ $user->name }} <span class="text-muted">({{ $user->email }})</span></li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
    <div class="card-footer">
        <a href="{{ route('companies.show', $company) }}" class="btn btn-default">
            <i class="fas fa-arrow-left mr-1"></i> Back to Company
        </a>
    </div>
</div>
@stop
