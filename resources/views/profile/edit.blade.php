@extends('layouts.admin')

@section('title', 'Profile')

@section('content_header')
    <h1>Profile</h1>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-md-3">
            <!-- Profile Image -->
            <div class="card card-primary card-outline">
                <div class="card-body box-profile">
                    <div class="text-center">
                        @if(auth()->user()->profile_picture)
                            <img class="profile-user-img img-fluid img-circle"
                                 src="{{ asset('storage/' . auth()->user()->profile_picture) }}"
                                 alt="User profile picture">
                        @else
                            <img class="profile-user-img img-fluid img-circle"
                                 src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=random"
                                 alt="User profile picture">
                        @endif
                    </div>

                    <h3 class="profile-username text-center">{{ auth()->user()->name }}</h3>

                    <p class="text-muted text-center">{{ auth()->user()->email }}</p>

                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b>Roles</b> <span class="float-right">
                                @foreach(auth()->user()->roles as $role)
                                    <span class="badge badge-info">{{ $role->name }}</span>
                                @endforeach
                            </span>
                        </li>
                        <li class="list-group-item">
                            <b>Member Since</b> <span class="float-right">{{ auth()->user()->created_at->format('M d, Y') }}</span>
                        </li>
                        @if(auth()->user()->microsoft_id)
                            <li class="list-group-item">
                                <b>Microsoft Account</b> 
                                <span class="float-right text-success">
                                    <i class="fas fa-check-circle"></i> Connected
                                </span>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card">
                <div class="card-header p-2">
                    <ul class="nav nav-pills">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->has('tab') && request('tab') === 'profile' || !request()->has('tab') ? 'active' : '' }}" 
                               href="{{ route('profile.edit', ['tab' => 'profile']) }}">
                                <i class="fas fa-user-edit mr-1"></i> Profile
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request('tab') === 'password' ? 'active' : '' }}" 
                               href="{{ route('profile.edit', ['tab' => 'password']) }}">
                                <i class="fas fa-key mr-1"></i> Password
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request('tab') === 'delete' ? 'active text-white' : 'text-danger' }}" 
                               href="{{ route('profile.edit', ['tab' => 'delete']) }}">
                                <i class="fas fa-trash-alt mr-1"></i> Delete Account
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <!-- Profile Tab -->
                        <div class="tab-pane {{ request()->has('tab') && request('tab') === 'profile' || !request()->has('tab') ? 'show active' : '' }}" id="profile">
                            <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="form-horizontal">
                                @csrf
                                @method('patch')
                                
                                <div class="form-group row">
                                    <label for="profile_picture" class="col-sm-2 col-form-label">Profile Picture</label>
                                    <div class="col-sm-10">
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="profile_picture" name="profile_picture">
                                            <label class="custom-file-label" for="profile_picture">Choose file</label>
                                        </div>
                                        <small class="form-text text-muted">Max 2MB. Allowed types: jpg, jpeg, png, gif</small>
                                    </div>
                                </div>
                                
                                <div class="form-group row">
                                    <label for="name" class="col-sm-2 col-form-label">Full Name</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                               id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required>
                                        @error('name')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="form-group row">
                                    <label for="email" class="col-sm-2 col-form-label">Email</label>
                                    <div class="col-sm-10">
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                               id="email" name="email" value="{{ old('email', auth()->user()->email) }}" required>
                                        @error('email')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="form-group row">
                                    <div class="offset-sm-2 col-sm-10">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save mr-1"></i> Update Profile
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Password Tab -->
                        <div class="tab-pane {{ request('tab') === 'password' ? 'show active' : '' }}" id="password">
                            <form method="post" action="{{ route('profile.password') }}" class="form-horizontal">
                                @csrf
                                @method('put')
                                
                                <div class="form-group row">
                                    <label for="current_password" class="col-sm-3 col-form-label">Current Password</label>
                                    <div class="col-sm-9">
                                        <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                                               id="current_password" name="current_password" required>
                                        @error('current_password')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="form-group row">
                                    <label for="password" class="col-sm-3 col-form-label">New Password</label>
                                    <div class="col-sm-9">
                                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                               id="password" name="password" required>
                                        @error('password')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="form-group row">
                                    <label for="password_confirmation" class="col-sm-3 col-form-label">Confirm Password</label>
                                    <div class="col-sm-9">
                                        <input type="password" class="form-control" 
                                               id="password_confirmation" name="password_confirmation" required>
                                    </div>
                                </div>
                                
                                <div class="form-group row">
                                    <div class="offset-sm-3 col-sm-9">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-key mr-1"></i> Update Password
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Delete Account Tab -->
                        <div class="tab-pane {{ request('tab') === 'delete' ? 'show active' : '' }}" id="delete">
                            <div class="alert alert-danger">
                                <h5><i class="icon fas fa-ban"></i> Warning!</h5>
                                Once your account is deleted, all of its resources and data will be permanently deleted.
                            </div>

                            <form method="post" action="{{ route('profile.destroy') }}" class="form-horizontal">
                                @csrf
                                @method('delete')

                                <div class="form-group row">
                                    <label for="password" class="col-sm-2 col-form-label">Password</label>
                                    <div class="col-sm-10">
                                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                                               id="password" name="password" required>
                                        @error('password')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="offset-sm-2 col-sm-10">
                                        <button type="submit" class="btn btn-danger"
                                                onclick="return confirm('Are you sure you want to delete your account? This action cannot be undone.')">
                                            <i class="fas fa-trash-alt mr-1"></i> Delete Account
                                        </button>
                                    </div>
                                </div>
                            </form>
                            
                            <p class="text-muted mt-3">
                                Once your account is deleted, all of its resources and data will be permanently deleted.
                            </p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@push('js')
<script>
    // Update the file input label with the selected file name
    document.querySelector('.custom-file-input').addEventListener('change', function(e) {
        var fileName = e.target.files[0] ? e.target.files[0].name : 'Choose file';
        var nextSibling = e.target.nextElementSibling;
        nextSibling.innerText = fileName;
    });
    
    // Handle tab switching with URL parameters
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab');
        
        if (tab) {
            const tabLink = document.querySelector(`.nav-pills a[href*="tab=${tab}"]`);
            if (tabLink) {
                tabLink.click();
            }
        }
    });
</script>
@endpush
