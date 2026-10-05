@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Add Proforma</h3>
        </div>
        <form action="{{ route('proformas.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="company_id">Company</label>
                            <select name="company_id" class="form-control">
                                <option value="">-- Select Company --</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="fornitori_id">Fornitore</label>
                            <select name="fornitori_id" class="form-control">
                                <option value="">-- Select Fornitore --</option>
                                @foreach($fornitoris as $fornitore)
                                    <option value="{{ $fornitore->id }}" {{ old('fornitori_id') == $fornitore->id ? 'selected' : '' }}>{{ $fornitore->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="anticipo">Anticipo</label>
                            <input type="number" name="anticipo" class="form-control" value="{{ old('anticipo') }}" step="0.01">
                        </div>
                        <div class="form-group">
                            <label for="anticipo_descrizione">Anticipo Descrizione</label>
                            <input type="text" name="anticipo_descrizione" class="form-control" value="{{ old('anticipo_descrizione') }}">
                        </div>
                        <div class="form-group">
                            <label for="compenso">Compenso</label>
                            <input type="text" class="form-control" value="0" readonly id="compenso-display">
                        </div>
                        <div class="form-group">
                            <label for="compenso_descrizione">Compenso Descrizione</label>
                            <textarea name="compenso_descrizione" class="form-control">{{ old('compenso_descrizione') }}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="contributo">Contributo</label>
                            <input type="number" name="contributo" class="form-control" value="{{ old('contributo') }}" step="0.01">
                        </div>
                        <div class="form-group">
                            <label for="contributo_descrizione">Contributo Descrizione</label>
                            <input type="text" name="contributo_descrizione" class="form-control" value="{{ old('contributo_descrizione') }}">
                        </div>
                        <div class="form-group">
                            <label for="stato">Stato</label>
                            <select name="stato" class="form-control">
                                <option value="Inserito" {{ old('stato', 'Inserito') == 'Inserito' ? 'selected' : '' }}>Inserito</option>
                                <option value="Proforma" {{ old('stato') == 'Proforma' ? 'selected' : '' }}>Proforma</option>
                                <option value="Fatturato" {{ old('stato') == 'Fatturato' ? 'selected' : '' }}>Fatturato</option>
                                <option value="Pagato" {{ old('stato') == 'Pagato' ? 'selected' : '' }}>Pagato</option>
                                <option value="Stornato" {{ old('stato') == 'Stornato' ? 'selected' : '' }}>Stornato</option>
                                <option value="Sospeso" {{ old('stato') == 'Sospeso' ? 'selected' : '' }}>Sospeso</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="emailfrom">Email From</label>
                            <input type="text" name="emailfrom" class="form-control" value="{{ old('emailfrom') }}">
                        </div>
                        <div class="form-group">
                            <label for="emailto">Email To</label>
                            <input type="text" name="emailto" class="form-control" value="{{ old('emailto') }}">
                        </div>
                        <div class="form-group">
                            <label for="emailsubject">Email Subject</label>
                            <input type="text" name="emailsubject" class="form-control" value="{{ old('emailsubject') }}">
                        </div>
                        <div class="form-group">
                            <label for="emailbody">Email Body</label>
                            <textarea name="emailbody" class="form-control">{{ old('emailbody') }}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="provvigioni">Provvigioni</label>
                            <select name="provvigioni[]" class="form-control" multiple>
                                @foreach($provvigioni as $provvigione)
                                    <option value="{{ $provvigione->id }}" data-importo="{{ $provvigione->importo }}" {{ (collect(old('provvigioni'))->contains($provvigione->id)) ? 'selected' : '' }}>
                                        {{ $provvigione->id }} - {{ $provvigione->descrizione }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="annotation">Annotation</label>
                            <textarea name="annotation" class="form-control">{{ old('annotation') }}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="sended_at">Sended At</label>
                            <input type="datetime-local" name="sended_at" class="form-control" value="{{ old('sended_at') }}">
                        </div>
                        <div class="form-group">
                            <label for="paid_at">Paid At</label>
                            <input type="datetime-local" name="paid_at" class="form-control" value="{{ old('paid_at') }}">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('proformas.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const provvigioniSelect = document.querySelector('select[name="provvigioni[]"]');
    const compensoDisplay = document.getElementById('compenso-display');
    if (provvigioniSelect) {
        provvigioniSelect.addEventListener('change', function() {
            let sum = 0;
            Array.from(this.selectedOptions).forEach(opt => {
                const importo = parseFloat(opt.getAttribute('data-importo'));
                if (!isNaN(importo)) sum += importo;
            });
            compensoDisplay.value = sum.toFixed(2);
        });
    }
});
</script>
@endsection
