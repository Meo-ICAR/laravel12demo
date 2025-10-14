@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Importa Pratiche da API') }}</div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {!! nl2br(e(session('success'))) !!}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if (session('info'))
                        <div class="alert alert-info" role="alert">
                            {{ session('info') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('pratiches.import.api') }}">
                        @csrf

                        <div class="form-group row mb-3">
                            <label for="data_inizio" class="col-md-4 col-form-label text-md-right">
                                {{ __('Data Inizio') }}
                            </label>

                            <div class="col-md-6">
                                <input id="data_inizio" 
                                       type="date" 
                                       class="form-control @error('data_inizio') is-invalid @enderror" 
                                       name="data_inizio" 
                                       value="{{ old('data_inizio') }}" 
                                       required 
                                       autofocus>

                                @error('data_inizio')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label for="data_fine" class="col-md-4 col-form-label text-md-right">
                                {{ __('Data Fine') }}
                            </label>

                            <div class="col-md-6">
                                <input id="data_fine" 
                                       type="date" 
                                       class="form-control @error('data_fine') is-invalid @enderror" 
                                       name="data_fine" 
                                       value="{{ old('data_fine') }}" 
                                       required>

                                @error('data_fine')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Importa Pratiche') }}
                                </button>
                                
                                <a href="{{ route('pratiches.index') }}" class="btn btn-secondary">
                                    {{ __('Annulla') }}
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Set default date range (last 30 days)
        const today = new Date();
        const thirtyDaysAgo = new Date();
        thirtyDaysAgo.setDate(today.getDate() - 30);
        
        const dataFineInput = document.getElementById('data_fine');
        const dataInizioInput = document.getElementById('data_inizio');
        
        if (dataFineInput && !dataFineInput.value) {
            dataFineInput.valueAsDate = today;
        }
        
        if (dataInizioInput && !dataInizioInput.value) {
            dataInizioInput.valueAsDate = thirtyDaysAgo;
        }
    });
</script>
@endpush

@endsection
