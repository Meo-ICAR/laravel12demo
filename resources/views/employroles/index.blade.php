@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Employroles</h1>
    <a href="{{ route('employroles.create') }}" class="btn btn-primary mb-3">Add Employrole</a>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Company ID</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($employroles as $employrole)
                <tr>
                    <td>{{ $employrole->id }}</td>
                    <td>{{ $employrole->name }}</td>
                    <td>{{ $employrole->company_id }}</td>
                    <td>
                        <a href="{{ route('employroles.show', $employrole) }}" class="btn btn-info btn-sm">Show</a>
                        <a href="{{ route('employroles.edit', $employrole) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('employroles.destroy', $employrole) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
