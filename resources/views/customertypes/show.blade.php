@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Customer Type Details</h1>
    <table class="table table-bordered">
        <tr><th>ID</th><td>{{ $customertype->id }}</td></tr>
        <tr><th>Name</th><td>{{ $customertype->name }}</td></tr>
        <tr><th>Created At</th><td>{{ $customertype->created_at }}</td></tr>
        <tr><th>Updated At</th><td>{{ $customertype->updated_at }}</td></tr>
    </table>
    <a href="{{ route('customertypes.index') }}" class="btn btn-secondary">Back</a>
    <a href="{{ route('customertypes.edit', $customertype) }}" class="btn btn-warning">Edit</a>
    <form action="{{ route('customertypes.destroy', $customertype) }}" method="POST" style="display:inline-block;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
    </form>
</div>
@endsection
