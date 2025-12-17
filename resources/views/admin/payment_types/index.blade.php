@extends('admin.layout')
@section('title', 'Payment Types')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Payment Types</h1>
    <a href="{{ route('admin.payment-types.create') }}" class="btn btn-primary">Add Payment Type</a>
</div>
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Name</th>
            <th>Code</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    @foreach($paymentTypes as $type)
        <tr>
            <td>{{ $type->name }}</td>
            <td>{{ $type->code ?? '-' }}</td>
            <td>
                <a href="{{ route('admin.payment-types.edit', $type) }}" class="btn btn-sm btn-warning">Edit</a>
                <form action="{{ route('admin.payment-types.destroy', $type) }}" method="POST" style="display:inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this payment type?')">Delete</button>
                </form>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
@endsection

