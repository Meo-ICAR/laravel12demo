@extends('layouts.admin')

@section('title', 'Import from CSV/Excel')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Import from CSV/Excel</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">Invoices</a></li>
                <li class="breadcrumb-item active">Import</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Import Invoiceins from CSV/Excel</h3>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form action="{{ route('invoiceins.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label for="file">CSV or Excel File</label>
                            <input type="file" name="file" id="file" class="form-control" required>
                            <small class="form-text text-muted">Supported formats: CSV, XLSX, XLS</small>
                        </div>
                        <button type="submit" class="btn btn-primary mt-2">
                            <i class="fas fa-upload mr-1"></i> Import
                        </button>
                        <a href="{{ route('invoices.index') }}" class="btn btn-secondary mt-2">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Invoices
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
