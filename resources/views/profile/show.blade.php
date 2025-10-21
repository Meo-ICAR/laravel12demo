@extends('adminlte::page')

@section('title', 'User Profile')

@section('content_header')
    <h1>User Profile</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-3">
            <div class="card card-primary card-outline">
                <div class="card-body box-profile">
                    <div class="text-center">
                        @if($user->profile_picture)
                            <img class="profile-user-img img-fluid img-circle" 
                                 src="{{ asset('storage/' . $user->profile_picture) }}" 
                                 alt="User profile picture">
                        @else
                            <div class="img-circle bg-primary d-flex align-items-center justify-content-center" 
                                 style="width: 100px; height: 100px; margin: 0 auto; font-size: 3rem; color: white;">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>

                    <h3 class="profile-username text-center">{{ $user->name }}</h3>

                    <p class="text-muted text-center">{{ $user->email }}</p>

                    <a href="{{ route('profile.edit') }}" class="btn btn-primary btn-block">
                        <i class="fas fa-edit mr-2"></i>Edit Profile
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">About Me</h3>
                </div>
                <div class="card-body">
                    <strong><i class="fas fa-envelope mr-1"></i> Email</strong>
                    <p class="text-muted">{{ $user->email }}</p>
                    <hr>

                    <strong><i class="fas fa-calendar-alt mr-1"></i> Member Since</strong>
                    <p class="text-muted">{{ $user->created_at->format('F j, Y') }}</p>
                    <hr>

                    <strong><i class="fas fa-clock mr-1"></i> Last Updated</strong>
                    <p class="text-muted">{{ $user->updated_at->diffForHumans() }}</p>
                </div>
            </div>
        </div>
    </div>
@stop

@push('css')
    <style>
        .profile-user-img {
            width: 100px;
            height: 100px;
            object-fit: cover;
        }
    </style>
@endpush
