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
<div class="card">
    <div class="card-body p-0">
        <div class="overflow-x-auto">
            <table class="table table-bordered mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Code</th>
                        <th class="text-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($paymentTypes as $type)
                    <tr>
                        <td>{{ $type->name }}</td>
                        <td>{{ $type->code ?? '-' }}</td>
                        <td>
                            <div class="d-flex gap-1 text-nowrap">
                                <a href="{{ route('admin.payment-types.edit', $type) }}" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </a>
                                <form action="{{ route('admin.payment-types.destroy', $type) }}" method="POST" style="display:inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this payment type?')">
                                        <i class="bi bi-trash me-1"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

