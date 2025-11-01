@extends('adminlte::page')

@section('title', config('app.name', 'Laravel'))

@section('adminlte_csrf_token')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('favicon.jpg') }}" type="image/jpeg">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
@stop

@section('content_header')
    @if (isset($content_header))
        <h1>{{ $content_header }}</h1>
    @endif
@stop

@section('content')
    @include('layouts.menu')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @yield('content')
@stop

@section('css')
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <!-- AdminLTE -->
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    @vite(['resources/css/app.css'])
@stop

@section('js')
    <!-- jQuery -->
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <!-- Bootstrap 4 -->
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!-- AdminLTE App -->
    <script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>
    @vite(['resources/js/app.js'])

    <script>
        // Debug: Log when document is ready
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Document ready');
            console.log('jQuery version:', $.fn.jquery);
            console.log('Bootstrap version:', $.fn.tooltip ? $.fn.tooltip.Constructor.VERSION : 'Bootstrap not loaded');
        });
    </script>
    @stack('scripts')
@stop

@section('footer')
    <div class="float-right d-none d-sm-block">
        <b>Version</b> 1.0.0
    </div>
    <strong>Copyright &copy; {{ date('Y') }} <a href="{{ config('app.url') }}">{{ config('app.name') }}</a>.</strong> All rights reserved.
@stop
