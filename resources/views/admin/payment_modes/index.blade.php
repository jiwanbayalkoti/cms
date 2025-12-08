@extends('admin.layout')
@section('title', 'Payment Modes')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Payment Modes</h1>
    <a href="{{ route('admin.payment-modes.create') }}" class="btn btn-primary">Add Payment Mode</a>
</div>
<div class="card">
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($paymentModes as $paymentMode)
                    <tr>
                        <td>{{ $paymentMode->id }}</td>
                        <td>{{ $paymentMode->name }}</td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.payment-modes.show', $paymentMode) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i> View
                                </a>
                                <a href="{{ route('admin.payment-modes.edit', $paymentMode) }}" class="btn btn-sm btn-outline-warning">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </a>
                                <form action="{{ route('admin.payment-modes.destroy', $paymentMode) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this payment mode?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash me-1"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3">No payment modes found.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($paymentModes->hasPages())
            <div class="mt-3">
                <x-pagination :paginator="$paymentModes" />
            </div>
        @endif
    </div>
</div>
@endsection
