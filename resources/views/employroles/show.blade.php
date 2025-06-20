@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Employrole Details</h1>
    <table class="table table-bordered">
        <tr><th>ID</th><td>{{ $employrole->id }}</td></tr>
        <tr><th>Name</th><td>{{ $employrole->name }}</td></tr>
        <tr><th>Company ID</th><td>{{ $employrole->company_id }}</td></tr>
        <tr><th>Created At</th><td>{{ $employrole->created_at }}</td></tr>
        <tr><th>Updated At</th><td>{{ $employrole->updated_at }}</td></tr>
    </table>
    <a href="{{ route('employroles.index') }}" class="btn btn-secondary">Back</a>
    <a href="{{ route('employroles.edit', $employrole) }}" class="btn btn-warning">Edit</a>
    <form action="{{ route('employroles.destroy', $employrole) }}" method="POST" style="display:inline-block;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
    </form>
</div>
@endsection
