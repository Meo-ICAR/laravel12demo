@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    {{ __('Modifica Coge') }}
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('coges.update', $coge->id) }}">
                        @csrf
                        @method('PUT')
                        
                        @include('coges._form')
                        
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
