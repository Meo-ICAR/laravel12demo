@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2><i class="fas fa-edit"></i> Edit Help Content</h2>
            <p class="text-muted">Editing: {{ $helpContent['title'] ?? $page }}</p>
        </div>
        <div class="col-md-6 text-right">
            <a href="{{ route('help.admin.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Help Management
            </a>
            <a href="{{ route('help.show', $page) }}" class="btn btn-info" target="_blank">
                <i class="fas fa-eye"></i> View Page
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismissible="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Help Content</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('help.admin.update', $page) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="title">Page Title <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('title') is-invalid @enderror"
                                   id="title"
                                   name="title"
                                   value="{{ old('title', $helpContent['title'] ?? '') }}"
                                   required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="screenshot">Screenshot Path</label>
                            <input type="text"
                                   class="form-control @error('screenshot') is-invalid @enderror"
                                   id="screenshot"
                                   name="screenshot"
                                   value="{{ old('screenshot', $helpContent['screenshot'] ?? '/images/help/' . $page . '.png') }}"
                                   placeholder="/images/help/{{ $page }}.png">
                            <small class="form-text text-muted">
                                Path to the screenshot image. Default: /images/help/{{ $page }}.png
                            </small>
                            @error('screenshot')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Help Sections <span class="text-danger">*</span></label>
                            <div id="sections-container">
                                @if(isset($helpContent['sections']) && is_array($helpContent['sections']))
                                    @foreach($helpContent['sections'] as $sectionKey => $sectionContent)
                                        <div class="section-item border rounded p-3 mb-3">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label>Section Title</label>
                                                    <input type="text"
                                                           class="form-control"
                                                           name="section_titles[]"
                                                           value="{{ $sectionKey }}"
                                                           required>
                                                </div>
                                                <div class="col-md-7">
                                                    <label>Section Content</label>
                                                    <textarea class="form-control"
                                                              name="section_contents[]"
                                                              rows="3"
                                                              required>{{ $sectionContent }}</textarea>
                                                </div>
                                                <div class="col-md-1">
                                                    <label>&nbsp;</label>
                                                    <button type="button" class="btn btn-danger btn-sm remove-section" title="Remove Section">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="section-item border rounded p-3 mb-3">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label>Section Title</label>
                                                <input type="text"
                                                       class="form-control"
                                                       name="section_titles[]"
                                                       value="Overview"
                                                       required>
                                            </div>
                                            <div class="col-md-7">
                                                <label>Section Content</label>
                                                <textarea class="form-control"
                                                          name="section_contents[]"
                                                          rows="3"
                                                          placeholder="Enter help content for this section..."
                                                          required></textarea>
                                            </div>
                                            <div class="col-md-1">
                                                <label>&nbsp;</label>
                                                <button type="button" class="btn btn-danger btn-sm remove-section" title="Remove Section">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <button type="button" class="btn btn-success btn-sm" id="add-section">
                                <i class="fas fa-plus"></i> Add Section
                            </button>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                            <a href="{{ route('help.admin.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Current Screenshot</h5>
                </div>
                <div class="card-body text-center">
                    @if(isset($helpContent['screenshot']))
                        <img src="{{ $helpContent['screenshot'] }}"
                             alt="Current Screenshot"
                             class="img-fluid rounded border"
                             style="max-height: 300px;"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                        <div class="alert alert-warning" style="display: none;">
                            <i class="fas fa-exclamation-triangle"></i> Screenshot not found
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No screenshot set
                        </div>
                    @endif
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title">Page Navigation</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        @foreach($allPages as $pageKey => $pageName)
                            <a href="{{ route('help.admin.edit', $pageKey) }}"
                               class="list-group-item list-group-item-action {{ $pageKey === $page ? 'active' : '' }}">
                                <i class="fas fa-edit"></i> {{ $pageName }}
                                @if($pageKey === $page)
                                    <span class="badge badge-light float-right">Current</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="btn-group-vertical w-100">
                        <a href="{{ route('help.show', $page) }}"
                           class="btn btn-info btn-sm"
                           target="_blank">
                            <i class="fas fa-eye"></i> View Help Page
                        </a>
                        <a href="{{ route('help.admin.index') }}"
                           class="btn btn-secondary btn-sm">
                            <i class="fas fa-list"></i> All Help Pages
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add new section
    document.getElementById('add-section').addEventListener('click', function() {
        const container = document.getElementById('sections-container');
        const newSection = document.createElement('div');
        newSection.className = 'section-item border rounded p-3 mb-3';
        newSection.innerHTML = `
            <div class="row">
                <div class="col-md-4">
                    <label>Section Title</label>
                    <input type="text" class="form-control" name="section_titles[]" value="" required>
                </div>
                <div class="col-md-7">
                    <label>Section Content</label>
                    <textarea class="form-control" name="section_contents[]" rows="3" placeholder="Enter help content for this section..." required></textarea>
                </div>
                <div class="col-md-1">
                    <label>&nbsp;</label>
                    <button type="button" class="btn btn-danger btn-sm remove-section" title="Remove Section">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
        container.appendChild(newSection);
    });

    // Remove section
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-section') || e.target.closest('.remove-section')) {
            const sectionItem = e.target.closest('.section-item');
            if (sectionItem && document.querySelectorAll('.section-item').length > 1) {
                sectionItem.remove();
            } else {
                alert('You must have at least one section.');
            }
        }
    });

    // Form submission - convert arrays to object
    document.querySelector('form').addEventListener('submit', function(e) {
        e.preventDefault();

        const sectionTitles = document.querySelectorAll('input[name="section_titles[]"]');
        const sectionContents = document.querySelectorAll('textarea[name="section_contents[]"]');
        const sections = {};

        for (let i = 0; i < sectionTitles.length; i++) {
            const title = sectionTitles[i].value.trim();
            const content = sectionContents[i].value.trim();
            if (title && content) {
                sections[title] = content;
            }
        }

        // Add hidden input for sections
        const sectionsInput = document.createElement('input');
        sectionsInput.type = 'hidden';
        sectionsInput.name = 'sections';
        sectionsInput.value = JSON.stringify(sections);
        this.appendChild(sectionsInput);

        this.submit();
    });
});
</script>

<style>
.section-item {
    background-color: #f8f9fa;
}

.section-item:hover {
    background-color: #e9ecef;
}

.list-group-item.active {
    background-color: #007bff;
    border-color: #007bff;
}

.btn-group-vertical .btn {
    margin-bottom: 5px;
}
</style>
@endsection
