@extends('layouts.admin')

@section('title', 'Customer Types')

@section('content_header')
    <h1>Customer Types</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Customer Types List</h3>
            <div class="card-tools">
                <a href="{{ route('customertypes.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add Customer Type
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customertypes as $customertype)
                            <tr>
                                <td>{{ $customertype->id }}</td>
                                <td>{{ $customertype->name }}</td>
                                <td>
                                    <a href="{{ route('customertypes.show', $customertype) }}" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('customertypes.edit', $customertype) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                                    <form action="{{ route('customertypes.destroy', $customertype) }}" method="POST" style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">No customer types found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer clearfix">
            {{ $customertypes->links() }}
        </div>
    </div>
</div>
@endsection
