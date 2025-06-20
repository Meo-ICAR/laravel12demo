@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Employrole</h1>
    <form action="{{ route('employroles.update', $employrole) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $employrole->name) }}" required>
        </div>
        <div class="mb-3">
            <label for="company_id" class="form-label">Company ID</label>
            <input type="text" name="company_id" class="form-control" value="{{ old('company_id', $employrole->company_id) }}">
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('employroles.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
