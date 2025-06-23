@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2><i class="fas fa-cog"></i> Help Content Management</h2>
            <p class="text-muted">Manage help content for all pages in the system</p>
        </div>
        <div class="col-md-6 text-right">
            <a href="{{ route('help.show', 'home') }}" class="btn btn-secondary">
                <i class="fas fa-eye"></i> View Help System
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Help Pages</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 50px">#</th>
                            <th>Page Name</th>
                            <th>Title</th>
                            <th>Sections</th>
                            <th>Screenshot</th>
                            <th style="width: 150px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pages as $pageKey => $pageName)
                            @php
                                $helpContent = $allHelpContent[$pageKey] ?? ['title' => 'No title', 'sections' => []];
                                $sectionCount = isset($helpContent['sections']) ? count($helpContent['sections']) : 0;
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <strong>{{ $pageName }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $pageKey }}</small>
                                </td>
                                <td>
                                    {{ $helpContent['title'] ?? 'No title' }}
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $sectionCount }} sections</span>
                                    @if($sectionCount > 0)
                                        <br>
                                        <small class="text-muted">
                                            {{ implode(', ', array_keys($helpContent['sections'])) }}
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    @if(isset($helpContent['screenshot']))
                                        <img src="{{ $helpContent['screenshot'] }}"
                                             alt="Screenshot"
                                             class="img-thumbnail"
                                             style="max-width: 60px; max-height: 40px;"
                                             onerror="this.style.display='none'">
                                    @else
                                        <span class="text-muted">No screenshot</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('help.admin.edit', $pageKey) }}"
                                           class="btn btn-sm btn-primary"
                                           title="Edit Help Content">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="{{ route('help.show', $pageKey) }}"
                                           class="btn btn-sm btn-info"
                                           title="View Help Page"
                                           target="_blank">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <a href="{{ route('help.show', 'home') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-eye"></i> View Help System
                        </a>
                        <a href="{{ route('help.admin.edit', 'home') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-edit"></i> Edit Dashboard Help
                        </a>
                        <a href="{{ route('help.admin.edit', 'users.index') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-edit"></i> Edit Users Help
                        </a>
                        <a href="{{ route('help.admin.edit', 'provvigioni.index') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-edit"></i> Edit Provvigioni Help
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <h4 class="text-primary">{{ count($pages) }}</h4>
                            <small class="text-muted">Total Pages</small>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success">{{ count($pages) }}</h4>
                            <small class="text-muted">Editable Pages</small>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            All help pages are now editable through this admin interface.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.table th {
    border-top: none;
    font-weight: 600;
}

.btn-group .btn {
    margin-right: 2px;
}

.img-thumbnail {
    border: 1px solid #dee2e6;
}
</style>
@endsection
