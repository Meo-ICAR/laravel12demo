@extends('adminlte::auth.login')

@section('title', 'Login')

@section('auth_header', 'Sign in to start your session')

@section('auth_body')
    <form method="POST" action="{{ route('login') }}">
        @csrf

        {{-- Email field --}}
        <div class="input-group mb-3">
            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email') }}" placeholder="Email" autofocus>
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-envelope"></span>
                </div>
            </div>
            @error('email')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        {{-- Password field --}}
        <div class="input-group mb-3">
            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror"
                   placeholder="Password">
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-lock"></span>
                </div>
            </div>
            @error('password')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        {{-- Remember me checkbox --}}
        <div class="row">
            <div class="col-8">
                <div class="icheck-primary">
                    <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label for="remember">
                        Remember Me
                    </label>
                </div>
            </div>
            <div class="col-4">
                <button type="submit" class="btn btn-primary btn-block">
                    Sign In
                </button>
            </div>
        </div>
    </form>

    {{-- Microsoft Login Button --}}
    <div class="social-auth-links text-center mt-3">
        <p>- OR -</p>
        <a href="{{ route('microsoft.login') }}" class="btn btn-block btn-outline-primary" style="background-color: #0078d4; color: white; border-color: #0078d4;">
            <i class="fab fa-microsoft mr-2"></i> Sign in with Microsoft
        </a>
    </div>
@stop

@section('auth_footer')
    @if (Route::has('password.request'))
        <p class="my-0">
            <a href="{{ route('password.request') }}">
                I forgot my password
            </a>
        </p>
    @endif
    @if (Route::has('register'))
        <p class="my-0">
            <a href="{{ route('register') }}">
                Register a new membership
            </a>
        </p>
    @endif
@stop
