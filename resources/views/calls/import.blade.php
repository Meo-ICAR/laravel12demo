@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-upload"></i> Import Calls
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('calls.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
                @csrf
                <div class="form-group mb-3">
                    <label for="import_file">Select CSV or Excel File:</label>
                    <input type="file" name="file" id="import_file" class="form-control" required accept=".csv,.xlsx,.xls">
                    <small class="form-text text-muted">
                        <strong>Supported formats:</strong> CSV (.csv), Excel (.xlsx, .xls)<br>
                        <strong>Note:</strong> CSV files should use semicolon (;) as delimiter<br>
                        <strong>Max size:</strong> 2MB
                    </small>
                    <div id="file-info" class="mt-2" style="display: none;">
                        <div class="alert alert-info">
                            <strong>Selected file:</strong> <span id="file-name"></span><br>
                            <strong>Size:</strong> <span id="file-size"></span>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" id="import-btn">
                    <i class="fas fa-upload"></i> Import Calls
                </button>
                <a href="{{ route('calls.index') }}" class="btn btn-secondary ml-2">
                    <i class="fas fa-arrow-left"></i> Back to Calls
                </a>
            </form>
        </div>
    </div>
</div>
@endsection
