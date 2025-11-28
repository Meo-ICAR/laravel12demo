@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    {{ __('Aggiungi Nuovo Coge') }}
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('coges.store') }}">
                        @csrf
                        
                        @include('coges._form')
                        
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
