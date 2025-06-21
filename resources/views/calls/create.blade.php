@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Add New Call</h3>
        </div>
        <form action="{{ route('calls.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="numero_chiamato">Numero Chiamato</label>
                            <input type="text" name="numero_chiamato" class="form-control" value="{{ old('numero_chiamato') }}" maxlength="20">
                            @error('numero_chiamato')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="data_inizio">Data Inizio</label>
                            <input type="datetime-local" name="data_inizio" class="form-control" value="{{ old('data_inizio') }}">
                            @error('data_inizio')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="durata">Durata (HH:MM:SS or MM:SS)</label>
                            <input type="text" name="durata" class="form-control" value="{{ old('durata') }}" placeholder="00:22 or 01:30:45" maxlength="10">
                            @error('durata')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="stato_chiamata">Stato Chiamata</label>
                            <select name="stato_chiamata" class="form-control">
                                <option value="">-- Select Stato --</option>
                                <option value="ANSWER" {{ old('stato_chiamata') == 'ANSWER' ? 'selected' : '' }}>ANSWER</option>
                                <option value="BUSY" {{ old('stato_chiamata') == 'BUSY' ? 'selected' : '' }}>BUSY</option>
                                <option value="Non Risposto" {{ old('stato_chiamata') == 'Non Risposto' ? 'selected' : '' }}>Non Risposto</option>
                                <option value="CHANUNAVAIL" {{ old('stato_chiamata') == 'CHANUNAVAIL' ? 'selected' : '' }}>CHANUNAVAIL</option>
                                <option value="CONGESTION" {{ old('stato_chiamata') == 'CONGESTION' ? 'selected' : '' }}>CONGESTION</option>
                            </select>
                            @error('stato_chiamata')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="esito">Esito</label>
                            <input type="text" name="esito" class="form-control" value="{{ old('esito') }}" maxlength="100">
                            @error('esito')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="utente">Utente</label>
                            <input type="text" name="utente" class="form-control" value="{{ old('utente') }}" maxlength="255">
                            @error('utente')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('calls.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
