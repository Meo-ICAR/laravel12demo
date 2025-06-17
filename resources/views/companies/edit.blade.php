@extends('layouts.app')

@section('title', 'Edit Company')

@section('content_header')
    <h1>Edit Company</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Edit Company Information</h3>
            </div>
            <form action="{{ route('companies.update', $company) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Company Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-building"></i></span>
                            </div>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                id="name" name="name" value="{{ old('name', $company->name) }}" required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="piva">P.IVA</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                            </div>
                            <input type="text" class="form-control @error('piva') is-invalid @enderror"
                                id="piva" name="piva" value="{{ old('piva', $company->piva) }}">
                            @error('piva')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="crm">CRM</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-database"></i></span>
                            </div>
                            <input type="text" class="form-control @error('crm') is-invalid @enderror"
                                id="crm" name="crm" value="{{ old('crm', $company->crm) }}">
                            @error('crm')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="callcenter">Call Center</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            </div>
                            <input type="text" class="form-control @error('callcenter') is-invalid @enderror"
                                id="callcenter" name="callcenter" value="{{ old('callcenter', $company->callcenter) }}">
                            @error('callcenter')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Update Company
                    </button>
                    <a href="{{ route('companies.index') }}" class="btn btn-default">
                        <i class="fas fa-arrow-left mr-1"></i> Back to List
                    </a>
                </div>
            </form>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">Information</h3>
            </div>
            <div class="card-body">
                <p><strong>Note:</strong> Fields marked with <span class="text-danger">*</span> are required.</p>
                <p>The P.IVA should be in the format: 12345678901</p>
                <hr>
                <p><strong>Last Updated:</strong></p>
                <ul>
                    <li>By: {{ $company->updatedByUser->name ?? 'N/A' }}</li>
                    <li>At: {{ $company->updated_at->format('Y-m-d H:i:s') }}</li>
                </ul>
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
