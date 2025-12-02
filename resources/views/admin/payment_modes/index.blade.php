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
                            <a href="{{ route('admin.payment-modes.edit', $paymentMode) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('admin.payment-modes.destroy', $paymentMode) }}" method="POST" style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this payment mode?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3">No payment modes found.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($paymentModes->hasPages())
            <div class="mt-3">
                {{ $paymentModes->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
