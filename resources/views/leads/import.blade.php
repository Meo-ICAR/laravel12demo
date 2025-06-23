@extends('layouts.admin')

@section('title', 'Import Leads')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Import Leads</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('leads.index') }}">Leads</a></li>
                <li class="breadcrumb-item active">Import</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <i class="fas fa-check-circle mr-2"></i>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <i class="fas fa-exclamation-triangle mr-2"></i>
            {{ session('error') }}
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-upload mr-2"></i>
                        Import Leads from Excel/CSV
                    </h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('leads.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
                        @csrf
                        <div class="form-group">
                            <label for="file">Select File:</label>
                            <input type="file" name="file" id="file" class="form-control-file @error('file') is-invalid @enderror"
                                   accept=".xlsx,.xls,.csv" required>
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Supported formats: Excel (.xlsx, .xls) and CSV (.csv) files
                            </small>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="hasHeader" name="has_header" value="1" checked>
                                <label class="custom-control-label" for="hasHeader">
                                    File contains header row
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary" id="importBtn">
                                <i class="fas fa-upload mr-1"></i>
                                Import Leads
                            </button>
                            <a href="{{ route('leads.index') }}" class="btn btn-secondary ml-2">
                                <i class="fas fa-arrow-left mr-1"></i>
                                Back to List
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle mr-2"></i>
                        Import Instructions
                    </h3>
                </div>
                <div class="card-body">
                    <h6>Required Columns:</h6>
                    <ul class="list-unstyled">
                        <li><code>legacy_id</code> - Legacy ID</li>
                        <li><code>campagna</code> - Campaign</li>
                        <li><code>lista</code> - List</li>
                        <li><code>cognome</code> - Last name</li>
                        <li><code>nome</code> - First name</li>
                        <li><code>telefono</code> - Phone number</li>
                    </ul>

                    <h6>Optional Columns:</h6>
                    <ul class="list-unstyled">
                        <li><code>email</code> - Email address</li>
                        <li><code>comune</code> - City</li>
                        <li><code>provincia</code> - Province</li>
                        <li><code>ultimo_operatore</code> - Last operator</li>
                        <li><code>esito</code> - Outcome</li>
                        <li><code>ultima_chiamata</code> - Last call date</li>
                        <li><code>chiamate</code> - Total calls</li>
                        <li><code>chiamate_giornaliere</code> - Daily calls</li>
                        <li><code>chiamate_mensili</code> - Monthly calls</li>
                        <li><code>attivo</code> - Active status (true/false)</li>
                    </ul>

                    <div class="alert alert-info mt-3">
                        <i class="fas fa-lightbulb mr-2"></i>
                        <strong>Tip:</strong> Make sure your Excel/CSV file has the correct column headers matching the field names above.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
    .custom-file-input:lang(en)~.custom-file-label::after {
        content: "Browse";
    }
</style>
@endsection

@section('js')
<script>
$(document).ready(function() {
    // File input change handler
    $('#file').change(function() {
        var fileName = $(this).val().split('\\').pop();
        if (fileName) {
            $(this).next('.custom-file-label').html(fileName);
        }
    });

    // Form submission handler
    $('#importForm').submit(function(e) {
        var fileInput = $('#file')[0];
        if (!fileInput.files.length) {
            e.preventDefault();
            alert('Please select a file to import.');
            return false;
        }

        // Show loading state
        $('#importBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Importing...');
    });

    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>
@endsection
