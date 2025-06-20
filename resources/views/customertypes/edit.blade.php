@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Customer Type</h1>
    <form action="{{ route('customertypes.update', $customertype) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $customertype->name) }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('customertypes.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
