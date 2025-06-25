@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Fornitori List</h2>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>PIVA</th>
                <th>CF</th>
                <th>Coge</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($fornitoris as $f)
                <tr>
                    <td>{{ $f->id }}</td>
                    <td>{{ $f->name }}</td>
                    <td>{{ $f->piva }}</td>
                    <td>{{ $f->cf }}</td>
                    <td>{{ $f->coge }}</td>
                    <td>
                        <a href="{{ route('fornitoris.invoices.show', $f->id) }}" class="btn btn-info btn-sm">View Invoices</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $fornitoris->links() }}
</div>
@endsection
