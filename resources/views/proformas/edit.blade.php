@extends('layouts.admin')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Edit Proforma</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('proformas.index') }}">Proforma</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-edit mr-2"></i>
                                Edit Proforma #{{ $proforma->id }}
                            </h3>
                        </div>
                        <form action="{{ route('proformas.update', $proforma) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="fornitori_id">
                                                <i class="fas fa-building mr-1"></i>
                                                Fornitore
                                            </label>
                                            <select name="fornitori_id" class="form-control" disabled>
                                                <option value="">-- Select Fornitore --</option>
                                                @foreach($fornitoris as $fornitore)
                                                    <option value="{{ $fornitore->id }}" {{ old('fornitori_id', $proforma->fornitori_id) == $fornitore->id ? 'selected' : '' }}>{{ $fornitore->name }}</option>
                                                @endforeach
                                            </select>
                                            <small class="form-text text-muted">Fornitore cannot be changed after creation</small>
                                        </div>

                                        <div class="form-group">
                                            <label for="compenso_descrizione">
                                                <i class="fas fa-file-alt mr-1"></i>
                                                Compenso Descrizione
                                            </label>
                                            <textarea name="compenso_descrizione" class="form-control" rows="3" placeholder="Enter compenso description">{{ old('compenso_descrizione', $proforma->compenso_descrizione) }}</textarea>
                                        </div>

                                        <div class="form-group">
                                            <label for="compenso">
                                                <i class="fas fa-money-bill-wave mr-1"></i>
                                                Compenso
                                            </label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">€</span>
                                                </div>
                                                <input type="number" name="compenso" class="form-control" value="{{ old('compenso', $proforma->compenso) }}" step="0.01" placeholder="0.00" disabled>
                                            </div>
                                            <small class="form-text text-muted">Compenso is calculated automatically from provvigioni</small>
                                        </div>

                                        <div class="form-group">
                                            <label for="contributo_descrizione">
                                                <i class="fas fa-file-text mr-1"></i>
                                                Contributo Descrizione
                                            </label>
                                            <input type="text" name="contributo_descrizione" class="form-control" value="{{ old('contributo_descrizione', $proforma->contributo_descrizione) }}" placeholder="Enter contributo description">
                                        </div>

                                        <div class="form-group">
                                            <label for="contributo">
                                                <i class="fas fa-plus-circle mr-1"></i>
                                                Contributo
                                            </label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">€</span>
                                                </div>
                                                <input type="number" name="contributo" class="form-control" value="{{ old('contributo', $proforma->contributo) }}" step="0.01" placeholder="0.00">
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="anticipo_descrizione" style="color: #dc3545;">
                                                <i class="fas fa-file-text mr-1"></i>
                                                Anticipo Descrizione
                                            </label>
                                            <input type="text" name="anticipo_descrizione" class="form-control" value="{{ old('anticipo_descrizione', $proforma->anticipo_descrizione) }}" placeholder="Enter anticipo description">
                                        </div>

                                        <div class="form-group">
                                            <label for="anticipo" style="color: #dc3545;">
                                                <i class="fas fa-euro-sign mr-1"></i>
                                                Anticipo
                                            </label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">€</span>
                                                </div>
                                                <input type="number" name="anticipo" class="form-control" value="{{ old('anticipo', $proforma->anticipo) }}" step="0.01" placeholder="0.00">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="stato">
                                                <i class="fas fa-tag mr-1"></i>
                                                Stato
                                            </label>
                                            <select name="stato" class="form-control">
                                                <option value="Inserito" {{ old('stato', $proforma->stato) == 'Inserito' ? 'selected' : '' }}>Inserito</option>
                                                <option value="Proforma" {{ old('stato', $proforma->stato) == 'Proforma' ? 'selected' : '' }}>Proforma</option>
                                                <option value="Fatturato" {{ old('stato', $proforma->stato) == 'Fatturato' ? 'selected' : '' }}>Fatturato</option>
                                                <option value="Pagato" {{ old('stato', $proforma->stato) == 'Pagato' ? 'selected' : '' }}>Pagato</option>
                                                <option value="Stornato" {{ old('stato', $proforma->stato) == 'Stornato' ? 'selected' : '' }}>Stornato</option>
                                                <option value="Sospeso" {{ old('stato', $proforma->stato) == 'Sospeso' ? 'selected' : '' }}>Sospeso</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="emailfrom">
                                                <i class="fas fa-envelope mr-1"></i>
                                                Email From
                                            </label>
                                            <input type="email" name="emailfrom" class="form-control" value="{{ old('emailfrom', $proforma->emailfrom) }}" placeholder="sender@example.com">
                                        </div>

                                        <div class="form-group">
                                            <label for="emailto">
                                                <i class="fas fa-envelope mr-1"></i>
                                                Email To
                                            </label>
                                            <input type="email" name="emailto" class="form-control" value="{{ old('emailto', $proforma->emailto) }}" placeholder="recipient@example.com">
                                        </div>

                                        <div class="form-group">
                                            <label for="annotation">
                                                <i class="fas fa-sticky-note mr-1"></i>
                                                Annotazione
                                            </label>
                                            <textarea name="annotation" class="form-control" rows="4" placeholder="Enter internal annotations">{{ old('annotation', $proforma->annotation) }}</textarea>
                                        </div>

                                        <div class="form-group">
                                            <label for="sended_at">
                                                <i class="fas fa-paper-plane mr-1"></i>
                                                Inviato
                                            </label>
                                            <input type="datetime-local" name="sended_at" class="form-control" value="{{ old('sended_at', $proforma->sended_at ? \Carbon\Carbon::parse($proforma->sended_at)->format('Y-m-d\TH:i') : '') }}">
                                        </div>

                                        <div class="form-group">
                                            <label for="paid_at">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                Pagato
                                            </label>
                                            <input type="datetime-local" name="paid_at" class="form-control" value="{{ old('paid_at', $proforma->paid_at ? \Carbon\Carbon::parse($proforma->paid_at)->format('Y-m-d\TH:i') : '') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="btn-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save mr-1"></i> Update Proforma
                                    </button>
                                    <a href="{{ route('proformas.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times mr-1"></i> Cancel
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
