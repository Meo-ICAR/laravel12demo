<div class="form-group mb-3">
    <label for="fonte">Fonte</label>
    <input type="text" class="form-control @error('fonte') is-invalid @enderror" id="fonte" name="fonte" value="{{ old('fonte', $coge->fonte ?? '') }}" required>
    @error('fonte')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group mb-3">
            <label for="conto_dare">Conto Dare</label>
            <input type="text" class="form-control @error('conto_dare') is-invalid @enderror" id="conto_dare" name="conto_dare" value="{{ old('conto_dare', $coge->conto_dare ?? '') }}" required>
            @error('conto_dare')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group mb-3">
            <label for="descrizione_dare">Descrizione Dare</label>
            <input type="text" class="form-control @error('descrizione_dare') is-invalid @enderror" id="descrizione_dare" name="descrizione_dare" value="{{ old('descrizione_dare', $coge->descrizione_dare ?? '') }}" required>
            @error('descrizione_dare')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group mb-3">
            <label for="conto_avere">Conto Avere</label>
            <input type="text" class="form-control @error('conto_avere') is-invalid @enderror" id="conto_avere" name="conto_avere" value="{{ old('conto_avere', $coge->conto_avere ?? '') }}" required>
            @error('conto_avere')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group mb-3">
            <label for="descrizione_avere">Descrizione Avere</label>
            <input type="text" class="form-control @error('descrizione_avere') is-invalid @enderror" id="descrizione_avere" name="descrizione_avere" value="{{ old('descrizione_avere', $coge->descrizione_avere ?? '') }}" required>
            @error('descrizione_avere')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="form-group mb-3">
    <label for="annotazioni">Annotazioni</label>
    <textarea class="form-control @error('annotazioni') is-invalid @enderror" id="annotazioni" name="annotazioni" rows="3">{{ old('annotazioni', $coge->annotazioni ?? '') }}</textarea>
    @error('annotazioni')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<button type="submit" class="btn btn-primary">Salva</button>
<a href="{{ route('coges.index') }}" class="btn btn-secondary">Annulla</a>
