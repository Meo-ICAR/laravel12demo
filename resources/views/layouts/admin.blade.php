@extends('adminlte::page')

@section('title', config('app.name', 'Laravel'))

@section('adminlte_csrf_token')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
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
    @vite(['resources/css/app.css'])
@stop

@section('js')
    @vite(['resources/js/app.js'])
@stop

@section('footer')
    <div class="float-right d-none d-sm-block">
        <b>Version</b> 1.0.0
    </div>
    <strong>Copyright &copy; {{ date('Y') }} <a href="{{ config('app.url') }}">{{ config('app.name') }}</a>.</strong> All rights reserved.
@stop
