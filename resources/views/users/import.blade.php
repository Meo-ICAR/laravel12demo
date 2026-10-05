@extends('layouts.admin')

@section('title', 'Import Users')

@section('content_header')
    <h1>Import Users</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title">Import Users from Excel</h3>
            </div>
            <div class="card-body">
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h5><i class="icon fas fa-ban"></i> Error!</h5>
                        {{ session('error') }}
                    </div>
                @endif
                <form action="{{ route('users.import.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="file">Excel File</label>
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input @error('file') is-invalid @enderror" id="file" name="file" accept=".xlsx,.xls,.csv">
                                <label class="custom-file-label" for="file">Choose file</label>
                            </div>
                        </div>
                        @error('file')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-upload mr-1"></i> Import Users
                    </button>
                    <a href="{{ route('users.index') }}" class="btn btn-default">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Users
                    </a>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">Import Instructions</h3>
            </div>
            <div class="card-body">
                <h5>Required Columns:</h5>
                <ul>
                    <li><strong>name</strong> - User's full name</li>
                    <li><strong>email</strong> - Valid email address</li>
                    <li><strong>password</strong> - Password (min 8 characters)</li>
                    <li><strong>roles</strong> (optional) - Comma-separated role names</li>
                </ul>
                <h5>File Requirements:</h5>
                <ul>
                    <li>File format: .xlsx, .xls, or .csv</li>
                    <li>Maximum file size: 10MB</li>
                    <li>First row should contain column headers</li>
                </ul>
                <div class="alert alert-info">
                    <h5><i class="icon fas fa-info"></i> Note</h5>
                    <p>If a user with the same email already exists, their information will be updated.</p>
                </div>
                <a href="{{ route('users.export') }}" class="btn btn-info btn-block">
                    <i class="fas fa-download mr-1"></i> Download Template
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
            $('input[type="file"]').on('change', function() {
                var fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').html(fileName);
            });
        });
    </script>
@stop
