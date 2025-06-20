@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Customer Types</h1>
    <a href="{{ route('customertypes.create') }}" class="btn btn-primary mb-3">Add Customer Type</a>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($customertypes as $customertype)
                <tr>
                    <td>{{ $customertype->id }}</td>
                    <td>{{ $customertype->name }}</td>
                    <td>
                        <a href="{{ route('customertypes.show', $customertype) }}" class="btn btn-info btn-sm">Show</a>
                        <a href="{{ route('customertypes.edit', $customertype) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('customertypes.destroy', $customertype) }}" method="POST" style="display:inline-block;">
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
