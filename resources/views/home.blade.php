@extends('layouts.app')

@section('title', 'Dashboard')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Dashboard</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item active">Dashboard</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ \App\Models\User::count() }}</h3>
                <p>Users</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
            <a href="{{ route('users.index') }}" class="small-box-footer">
                More info <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ \App\Models\Company::count() }}</h3>
                <p>Companies</p>
            </div>
            <div class="icon">
                <i class="fas fa-building"></i>
            </div>
            <a href="{{ route('companies.index') }}" class="small-box-footer">
                More info <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ \Spatie\Permission\Models\Role::count() }}</h3>
                <p>Roles</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-tag"></i>
            </div>
            <a href="{{ route('roles.index') }}" class="small-box-footer">
                More info <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ \Spatie\Permission\Models\Permission::count() }}</h3>
                <p>Permissions</p>
            </div>
            <div class="icon">
                <i class="fas fa-key"></i>
            </div>
            <a href="{{ route('permissions.index') }}" class="small-box-footer">
                More info <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Recent Users</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(\App\Models\User::latest()->take(5)->get() as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->created_at->format('M d, Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer clearfix">
                <a href="{{ route('users.index') }}" class="btn btn-sm btn-default float-right">View All Users</a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Recent Companies</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(\App\Models\Company::latest()->take(5)->get() as $company)
                            <tr>
                                <td>{{ $company->name }}</td>
                                <td>{{ $company->created_at->format('M d, Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer clearfix">
                <a href="{{ route('companies.index') }}" class="btn btn-sm btn-default float-right">View All Companies</a>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .small-box {
        position: relative;
        display: block;
        margin-bottom: 20px;
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        border-radius: .25rem;
    }
    .small-box > .inner {
        padding: 10px;
    }
    .small-box h3 {
        font-size: 2.2rem;
        font-weight: 700;
        margin: 0 0 10px 0;
        white-space: nowrap;
        padding: 0;
    }
    .small-box p {
        font-size: 1rem;
    }
    .small-box .icon {
        color: rgba(0,0,0,.15);
        z-index: 0;
    }
    .small-box .icon > i {
        font-size: 70px;
        top: 20px;
        position: absolute;
        right: 15px;
        transition: transform .3s linear;
    }
    .small-box:hover .icon > i {
        transform: scale(1.1);
    }
    .small-box .small-box-footer {
        position: relative;
        text-align: center;
        padding: 3px 0;
        color: rgba(255,255,255,.8);
        display: block;
        z-index: 10;
        background: rgba(0,0,0,.1);
        text-decoration: none;
    }
    .small-box .small-box-footer:hover {
        color: #fff;
        background: rgba(0,0,0,.15);
    }
</style>
@stop
