@extends('layouts.admin')

@section('title', 'Edit Cliente')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Edit Cliente</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('clientis.index') }}">Clienti</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Cliente: {{ $clienti->name }}</h3>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('clientis.update', $clienti) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="codice">Codice</label>
                                    <input type="text" name="codice" id="codice" class="form-control @error('codice') is-invalid @enderror" value="{{ old('codice', $clienti->codice) }}">
                                    @error('codice')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Name</label>
                                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $clienti->name) }}">
                                    @error('name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="piva">PIVA</label>
                                    <input type="text" name="piva" id="piva" class="form-control @error('piva') is-invalid @enderror" value="{{ old('piva', $clienti->piva) }}">
                                    @error('piva')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="cf">CF</label>
                                    <input type="text" name="cf" id="cf" class="form-control @error('cf') is-invalid @enderror" value="{{ old('cf', $clienti->cf) }}">
                                    @error('cf')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="coge">COGE</label>
                                    <input type="text" name="coge" id="coge" class="form-control @error('coge') is-invalid @enderror" value="{{ old('coge', $clienti->coge) }}">
                                    @error('coge')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $clienti->email) }}">
                                    @error('email')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="customertype_id">Customer Type</label>
                                    <select name="customertype_id" id="customertype_id" class="form-control @error('customertype_id') is-invalid @enderror">
                                        <option value="">-- Select --</option>
                                        @foreach($customertypes as $type)
                                            <option value="{{ $type->id }}" {{ old('customertype_id', $clienti->customertype_id) == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('customertype_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="regione">Regione</label>
                                    <input type="text" name="regione" id="regione" class="form-control @error('regione') is-invalid @enderror" value="{{ old('regione', $clienti->regione) }}">
                                    @error('regione')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="citta">Città</label>
                                    <input type="text" name="citta" id="citta" class="form-control @error('citta') is-invalid @enderror" value="{{ old('citta', $clienti->citta) }}">
                                    @error('citta')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="company_id">Company ID</label>
                                    <input type="text" name="company_id" id="company_id" class="form-control @error('company_id') is-invalid @enderror" value="{{ old('company_id', $clienti->company_id) }}">
                                    @error('company_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-1"></i> Update Cliente
                                </button>
                                <a href="{{ route('clientis.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left mr-1"></i> Back to List
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
