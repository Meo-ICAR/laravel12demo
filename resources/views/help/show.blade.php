@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-3">
            <!-- Help Navigation -->
            <div class="card">
                <div class="card-header">
                    <h5>Help Topics</h5>
                    @if(auth()->user()->hasRole('super_admin'))
                        <div class="mt-2">
                            <a href="{{ route('help.admin.index') }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-cog"></i> Manage Help
                            </a>
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    <ul class="nav nav-pills flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ $page == 'home' ? 'active' : '' }}" href="{{ route('help.show', 'home') }}">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $page == 'users.index' ? 'active' : '' }}" href="{{ route('help.show', 'users.index') }}">Users</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $page == 'roles.index' ? 'active' : '' }}" href="{{ route('help.show', 'roles.index') }}">Roles</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $page == 'permissions.index' ? 'active' : '' }}" href="{{ route('help.show', 'permissions.index') }}">Permissions</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $page == 'companies.index' ? 'active' : '' }}" href="{{ route('help.show', 'companies.index') }}">Companies</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $page == 'provvigioni.index' ? 'active' : '' }}" href="{{ route('help.show', 'provvigioni.index') }}">Provvigioni</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $page == 'provvigioni.proformaSummary' ? 'active' : '' }}" href="{{ route('help.show', 'provvigioni.proformaSummary') }}">Proforma Summary</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $page == 'invoices.reconciliation' ? 'active' : '' }}" href="{{ route('help.show', 'invoices.reconciliation') }}">Invoice Reconciliation</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $page == 'fornitoris.index' ? 'active' : '' }}" href="{{ route('help.show', 'fornitoris.index') }}">Fornitori</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $page == 'calls.index' ? 'active' : '' }}" href="{{ route('help.show', 'calls.index') }}">Calls</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $page == 'leads.index' ? 'active' : '' }}" href="{{ route('help.show', 'leads.index') }}">Leads</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $page == 'clientis.index' ? 'active' : '' }}" href="{{ route('help.show', 'clientis.index') }}">Clienti</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <!-- Help Content -->
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3>{{ $helpContent['title'] ?? 'Help' }}</h3>
                        @if(auth()->user()->hasRole('super_admin'))
                            <a href="{{ route('help.admin.edit', $page) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i> Edit This Page
                            </a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @if(isset($helpContent['screenshot']))
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="screenshot-container">
                                    <img src="{{ $helpContent['screenshot'] }}"
                                         alt="Screenshot of {{ $helpContent['title'] }}"
                                         class="img-fluid rounded border screenshot-img"
                                         style="max-width: 100%; height: auto; cursor: pointer;"
                                         onclick="openScreenshotModal(this.src, '{{ $helpContent['title'] }}')"
                                         title="Click to enlarge">
                                    <div class="text-center mt-2">
                                        <small class="text-muted">
                                            <i class="fas fa-mouse-pointer"></i> Click to enlarge
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(isset($helpContent['sections']))
                        @foreach($helpContent['sections'] as $section => $content)
                            <h5>{{ ucfirst($section) }}</h5>
                            <p>{{ $content }}</p>
                        @endforeach
                    @else
                        <p>No help content available for this section.</p>
                    @endif
                </div>
            </div>

            <!-- Admin Section for Screenshot Management -->
            @if(auth()->user()->hasRole('super_admin'))
                <div class="card mt-4">
                    <div class="card-header">
                        <h5><i class="fas fa-cog"></i> Screenshot Management (Admin Only)</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Upload Screenshot</h6>
                                <form id="uploadScreenshotForm" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label for="screenshotFile">Select Image File:</label>
                                        <input type="file" class="form-control-file" id="screenshotFile" name="screenshot" accept="image/*" required>
                                        <small class="form-text text-muted">Max size: 2MB. Supported formats: JPEG, PNG, JPG, GIF</small>
                                    </div>
                                    <input type="hidden" name="page" value="{{ $page }}">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-upload"></i> Upload Screenshot
                                    </button>
                                </form>
                            </div>
                            <div class="col-md-6">
                                <h6>Capture Screenshot</h6>
                                <form id="captureScreenshotForm">
                                    <div class="form-group">
                                        <label for="captureUrl">Page URL to Capture:</label>
                                        <input type="url" class="form-control" id="captureUrl" name="url"
                                               placeholder="https://your-domain.com/users" required>
                                        <small class="form-text text-muted">Enter the full URL of the page you want to capture</small>
                                    </div>
                                    <input type="hidden" name="page" value="{{ $page }}">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-camera"></i> Capture Screenshot
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div id="uploadResult" class="alert" style="display: none;"></div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Screenshot Modal -->
<div class="modal fade" id="screenshotModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="screenshotModalTitle">Screenshot</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="screenshotModalImg" src="" alt="" class="img-fluid">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="downloadScreenshot()">
                    <i class="fas fa-download"></i> Download
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.screenshot-container {
    position: relative;
}

.screenshot-img {
    transition: transform 0.2s ease-in-out;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.screenshot-img:hover {
    transform: scale(1.02);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

#screenshotModal .modal-body {
    max-height: 80vh;
    overflow-y: auto;
}

#screenshotModal img {
    max-width: 100%;
    height: auto;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle screenshot upload
    const uploadForm = document.getElementById('uploadScreenshotForm');
    if (uploadForm) {
        uploadForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const resultDiv = document.getElementById('uploadResult');

            fetch('{{ route("help.uploadScreenshot") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                resultDiv.style.display = 'block';
                if (data.success) {
                    resultDiv.className = 'alert alert-success';
                    resultDiv.innerHTML = '<i class="fas fa-check"></i> ' + data.message;
                    // Reload page to show new screenshot
                    setTimeout(() => location.reload(), 1500);
                } else {
                    resultDiv.className = 'alert alert-danger';
                    resultDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> ' + data.message;
                }
            })
            .catch(error => {
                resultDiv.style.display = 'block';
                resultDiv.className = 'alert alert-danger';
                resultDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Error: ' + error.message;
            });
        });
    }

    // Handle screenshot capture
    const captureForm = document.getElementById('captureScreenshotForm');
    if (captureForm) {
        captureForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const resultDiv = document.getElementById('uploadResult');
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Capturing...';
            submitBtn.disabled = true;

            fetch('{{ route("help.captureScreenshot") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                resultDiv.style.display = 'block';
                if (data.success) {
                    resultDiv.className = 'alert alert-success';
                    resultDiv.innerHTML = '<i class="fas fa-check"></i> ' + data.message;
                    // Reload page to show new screenshot
                    setTimeout(() => location.reload(), 1500);
                } else {
                    resultDiv.className = 'alert alert-danger';
                    resultDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> ' + data.message;
                }
            })
            .catch(error => {
                resultDiv.style.display = 'block';
                resultDiv.className = 'alert alert-danger';
                resultDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Error: ' + error.message;
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    }
});

function openScreenshotModal(src, title) {
    document.getElementById('screenshotModalImg').src = src;
    document.getElementById('screenshotModalTitle').textContent = title;
    $('#screenshotModal').modal('show');
}

function downloadScreenshot() {
    const img = document.getElementById('screenshotModalImg');
    const link = document.createElement('a');
    link.download = 'screenshot.png';
    link.href = img.src;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>
@endsection
