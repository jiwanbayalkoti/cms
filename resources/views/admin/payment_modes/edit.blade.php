@extends('admin.layout')
@section('title', 'Edit Payment Mode')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Edit Payment Mode</h1>
    <a href="{{ route('admin.payment-modes.index') }}" class="btn btn-outline-secondary">Back to List</a>
</div>
<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.payment-modes.update', $paymentMode) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="name" class="form-label">Name *</label>
                <input type="text" name="name" id="name" class="form-control" required value="{{ old('name', $paymentMode->name) }}">
                @error('name')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('admin.payment-modes.index') }}" class="btn btn-secondary ms-2">Cancel</a>
        </form>
    </div>
</div>
@endsection
