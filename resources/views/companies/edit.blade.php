@extends('adminlte::page')

@section('title', 'Edit Company')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-building mr-2"></i>Edit Company: {{ $company->name }}</h1>
        <div>
            <a href="{{ route('companies.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back to List
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <i class="icon fas fa-check"></i> {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <h5><i class="icon fas fa-ban"></i> There were some errors:</h5>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('companies.update', $company->id) }}" method="POST" id="companyForm">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-primary card-outline card-outline-tabs">
                            <div class="card-header p-0 border-bottom-0">
                                <ul class="nav nav-tabs" id="companyTabs" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="details-tab" data-toggle="pill" href="#details" role="tab" aria-controls="details" aria-selected="true">
                                            <i class="fas fa-info-circle mr-1"></i> Company Details
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="emails-tab" data-toggle="pill" href="#emails" role="tab" aria-controls="emails" aria-selected="false">
                                            <i class="fas fa-envelope mr-1"></i> Email Settings
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="integration-tab" data-toggle="pill" href="#integration" role="tab" aria-controls="integration" aria-selected="false">
                                            <i class="fas fa-plug mr-1"></i> Integrations
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="ai-tab" data-toggle="pill" href="#ai" role="tab" aria-controls="ai" aria-selected="false">
                                            <i class="fas fa-robot mr-1"></i> AI Configuration
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-body">
                                <div class="tab-content" id="companyTabsContent">
                                    <!-- Company Details Tab -->
                                    <div class="tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="details-tab">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="name">Company Name <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><i class="fas fa-building"></i></span>
                                                        </div>
                                                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                                                               value="{{ old('name', $company->name) }}" required>
                                                    </div>
                                                    @error('name')
                                                        <span class="error invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="piva">P.IVA</label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                                        </div>
                                                        <input type="text" name="piva" id="piva" class="form-control @error('piva') is-invalid @enderror" 
                                                               value="{{ old('piva', $company->piva) }}">
                                                    </div>
                                                    @error('piva')
                                                        <span class="error invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Email Settings Tab -->
                                    <div class="tab-pane fade" id="emails" role="tabpanel" aria-labelledby="emails-tab">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="email">Primary Email</label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                                        </div>
                                                        <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" 
                                                               value="{{ old('email', $company->email) }}">
                                                    </div>
                                                    @error('email')
                                                        <span class="error invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>

                                                <div class="form-group">
                                                    <label for="email_cc">CC Email</label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><i class="fas fa-copy"></i></span>
                                                        </div>
                                                        <input type="email" name="email_cc" id="email_cc" class="form-control @error('email_cc') is-invalid @enderror" 
                                                               value="{{ old('email_cc', $company->email_cc) }}">
                                                    </div>
                                                    <small class="form-text text-muted">Additional email addresses to CC</small>
                                                    @error('email_cc')
                                                        <span class="error invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>

                                                <div class="form-group">
                                                    <label for="email_bcc">BCC Email</label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><i class="fas fa-eye-slash"></i></span>
                                                        </div>
                                                        <input type="email" name="email_bcc" id="email_bcc" class="form-control @error('email_bcc') is-invalid @enderror" 
                                                               value="{{ old('email_bcc', $company->email_bcc) }}">
                                                    </div>
                                                    <small class="form-text text-muted">Hidden email addresses to BCC</small>
                                                    @error('email_bcc')
                                                        <span class="error invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="emailsubject">Default Email Subject</label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                                        </div>
                                                        <input type="text" name="emailsubject" id="emailsubject" class="form-control @error('emailsubject') is-invalid @enderror" 
                                                               value="{{ old('emailsubject', $company->emailsubject) }}" placeholder="Proforma compensi provvigionali">
                                                    </div>
                                                    @error('emailsubject')
                                                        <span class="error invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>

                                                <div class="form-group">
                                                    <label for="compenso_descrizione">Default Compensation Description</label>
                                                    <textarea name="compenso_descrizione" id="compenso_descrizione" class="form-control @error('compenso_descrizione') is-invalid @enderror" 
                                                              rows="4" placeholder="Inserisci la descrizione predefinita del compenso">{{ old('compenso_descrizione', $company->compenso_descrizione) }}</textarea>
                                                    @error('compenso_descrizione')
                                                        <span class="error invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Integrations Tab -->
                                    <div class="tab-pane fade" id="integration" role="tabpanel" aria-labelledby="integration-tab">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="crm">CRM Integration</label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><i class="fas fa-link"></i></span>
                                                        </div>
                                                        <input type="url" name="crm" id="crm" class="form-control @error('crm') is-invalid @enderror" 
                                                           value="{{ old('crm', $company->crm) }}" placeholder="https://">
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-outline-secondary update-timestamp" data-target="crm_last_activation">
                                                            <i class="fas fa-sync-alt"></i> Update Now
                                                        </button>
                                                    </div>
                                                </div>
                                                <small class="form-text text-muted">URL or identifier for CRM system</small>
                                                @error('crm')
                                                    <span class="error invalid-feedback">{{ $message }}</span>
                                                @enderror
                                                <div class="form-group mt-2">
                                                    <label for="crm_last_activation">Last CRM Activation</label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><i class="far fa-clock"></i></span>
                                                        </div>
                                                        <input type="datetime-local" name="crm_last_activation" id="crm_last_activation" 
                                                               class="form-control @error('crm_last_activation') is-invalid @enderror"
                                                               value="{{ old('crm_last_activation', $company->crm_last_activation ? $company->crm_last_activation->format('Y-m-d\TH:i') : '') }}">
                                                    </div>
                                                    <small class="form-text text-muted">Last time the CRM was activated/synced</small>
                                                    @error('crm_last_activation')
                                                        <span class="error invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="callcenter">Call Center Integration</label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                                        </div>
                                                        <input type="text" name="callcenter" id="callcenter" class="form-control @error('callcenter') is-invalid @enderror" 
                                                           value="{{ old('callcenter', $company->callcenter) }}" placeholder="URL or identifier">
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-outline-secondary update-timestamp" data-target="call_last_activation">
                                                            <i class="fas fa-sync-alt"></i> Update Now
                                                        </button>
                                                    </div>
                                                </div>
                                                <small class="form-text text-muted">URL or identifier for Call Center system</small>
                                                @error('callcenter')
                                                    <span class="error invalid-feedback">{{ $message }}</span>
                                                @enderror
                                                <div class="form-group mt-2">
                                                    <label for="call_last_activation">Last Call Center Activation</label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><i class="far fa-clock"></i></span>
                                                        </div>
                                                        <input type="datetime-local" name="call_last_activation" id="call_last_activation" 
                                                               class="form-control @error('call_last_activation') is-invalid @enderror"
                                                               value="{{ old('call_last_activation', $company->call_last_activation ? $company->call_last_activation->format('Y-m-d\TH:i') : '') }}">
                                                    </div>
                                                    <small class="form-text text-muted">Last time the Call Center was activated/synced</small>
                                                    @error('call_last_activation')
                                                        <span class="error invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- AI Configuration Tab -->
                                    <div class="tab-pane fade" id="ai" role="tabpanel" aria-labelledby="ai-tab">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="form-group">
                                                    <label for="aibackground">AI Context & Background</label>
                                                    <textarea name="aibackground" id="aibackground" class="form-control @error('aibackground') is-invalid @enderror" 
                                                              rows="10" placeholder="Add any AI context or background information here...">{{ old('aibackground', is_array($company->aibackground) ? json_encode($company->aibackground, JSON_PRETTY_PRINT) : $company->aibackground) }}</textarea>
                                                    <small class="form-text text-muted">This information helps the AI understand the context of this company. Use JSON format for structured data.</small>
                                                    @error('aibackground')
                                                        <span class="error invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-right">
                                <a href="{{ route('companies.index') }}" class="btn btn-secondary mr-2">
                                    <i class="fas fa-times mr-1"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-1"></i> Save Changes
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle update timestamp buttons
        document.querySelectorAll('.update-timestamp').forEach(button => {
            button.addEventListener('click', function() {
                const targetField = this.getAttribute('data-target');
                const now = new Date();
                // Format as YYYY-MM-DDThh:mm (local time)
                const formattedDate = now.toISOString().slice(0, 16);
                
                // Update the corresponding datetime-local input
                document.getElementById(targetField).value = formattedDate;
                
                // Show feedback
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-check"></i> Updated!';
                setTimeout(() => {
                    this.innerHTML = originalText;
                }, 2000);
            });
        });
    });
</script>
@endpush

@push('css')
<style>
    .nav-tabs .nav-link {
        color: #6c757d;
        font-weight: 500;
        padding: 0.75rem 1.25rem;
    }
    .nav-tabs .nav-link.active {
        color: #007bff;
        background-color: #fff;
        border-color: #dee2e6 #dee2e6 #fff;
        border-top: 3px solid #007bff;
    }
    .nav-tabs .nav-link:hover:not(.active) {
        border-color: #e9ecef #e9ecef #dee2e6;
    }
    .tab-content {
        padding: 1.25rem 0;
    }
    .input-group-text {
        min-width: 2.5rem;
        justify-content: center;
    }
    /* Make form elements full width in tabs */
    .tab-content .form-group {
        margin-bottom: 1.5rem;
    }
    /* Add some spacing between form groups */
    .form-group {
        margin-bottom: 1.25rem;
    }
    /* Style for required field indicators */
    .required:after {
        content: " *";
        color: #dc3545;
    }
</style>
@endpush

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize any plugins or custom JS here
        
        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();
        
        // Handle form submission with confirmation
        $('#companyForm').on('submit', function(e) {
            // Add any client-side validation here if needed
            return true;
        });
        
        // Auto-format P.IVA if needed
        $('#piva').on('blur', function() {
            let value = $(this).val().replace(/\D/g, '');
            if (value.length > 0) {
                value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                $(this).val(value);
            }
        });
        
        // Auto-format URLs if they don't start with http
        $('input[type="url"]').on('blur', function() {
            let value = $(this).val();
            if (value && !value.match(/^https?:\/\//i) && value !== '') {
                $(this).val('https://' + value);
            }
        });
    });
</script>
@endpush
