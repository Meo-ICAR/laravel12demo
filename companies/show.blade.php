@extends('layouts.app')

@section('title', 'Company Details')

@section('content_header')
    <h1>Company Details</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Company Information</h3>
                <div class="card-tools">
                    <a href="{{ route('companies.edit', $company) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="{{ route('companies.index') }}" class="btn btn-default btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-info"><i class="fas fa-building"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Company Name</span>
                                <span class="info-box-number">{{ $company->name }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-success"><i class="fas fa-hashtag"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">P.IVA</span>
                                <span class="info-box-number">{{ $company->piva ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-warning"><i class="fas fa-database"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">CRM</span>
                                <span class="info-box-number">{{ $company->crm ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-danger"><i class="fas fa-phone"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Call Center</span>
                                <span class="info-box-number">{{ $company->callcenter ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-primary"><i class="fas fa-envelope"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Email</span>
                                <span class="info-box-number">{{ $company->email ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Additional Information</h3>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-striped">
                                    <tr>
                                        <th style="width: 200px;">Created By</th>
                                        <td>
                                            <span class="badge badge-info">
                                                <i class="fas fa-user mr-1"></i> {{ $company->created_by }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Created At</th>
                                        <td>
                                            <i class="fas fa-calendar mr-1"></i> {{ $company->created_at->format('Y-m-d H:i:s') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Last Updated By</th>
                                        <td>
                                            <span class="badge badge-info">
                                                <i class="fas fa-user mr-1"></i> {{ $company->updated_by }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Last Updated At</th>
                                        <td>
                                            <i class="fas fa-calendar mr-1"></i> {{ $company->updated_at->format('Y-m-d H:i:s') }}
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <dt class="col-sm-4">Compenso Descrizione</dt>
                <dd class="col-sm-8">{!! $company->compenso_descrizione ? nl2br(e($company->compenso_descrizione)) : '-' !!}</dd>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">Quick Actions</h3>
            </div>
            <div class="card-body">
                <a href="{{ route('companies.edit', $company) }}" class="btn btn-primary btn-block">
                    <i class="fas fa-edit mr-1"></i> Edit Company
                </a>
                <form action="{{ route('companies.destroy', $company) }}" method="POST" class="mt-2">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-block" onclick="return confirm('Are you sure you want to delete this company?')">
                        <i class="fas fa-trash mr-1"></i> Delete Company
                    </button>
                </form>
                <a href="{{ route('companies.index') }}" class="btn btn-default btn-block mt-2">
                    <i class="fas fa-arrow-left mr-1"></i> Back to List
                </a>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Enable tooltips
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
@stop
