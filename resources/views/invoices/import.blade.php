@extends('layouts.app')

@section('title', 'Import Fatture Elettroniche')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Import Fatture Elettroniche</h1>
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
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Import XML Invoices</h3>
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

                <form action="{{ route('invoices.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
                    @csrf
                    <div class="form-group">
                        <div class="custom-file">
                            <input type="file"
                                   class="custom-file-input @error('xml_file') is-invalid @enderror"
                                   id="xml_file"
                                   name="xml_file"
                                   accept=".xml">
                            <label class="custom-file-label" for="xml_file">Choose XML file</label>
                            @error('xml_file')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <h5><i class="icon fas fa-info"></i> Instructions</h5>
                        <ul>
                            <li>Upload XML files in FatturaPA format (version 1.2)</li>
                            <li>Maximum file size: 10MB</li>
                            <li>Multiple invoices can be imported from a single XML file</li>
                            <li>Companies will be automatically created or updated</li>
                        </ul>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Import Invoices
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Update file input label with selected filename
    $('input[type="file"]').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName);
    });

    // Form submission handling
    $('#importForm').on('submit', function() {
        var submitButton = $(this).find('button[type="submit"]');
        submitButton.prop('disabled', true);
        submitButton.html('<i class="fas fa-spinner fa-spin"></i> Importing...');
    });
});
</script>
@stop
