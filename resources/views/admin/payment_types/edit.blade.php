@extends('admin.layout')
@section('title', 'Edit Payment Type')
@section('content')
<h1>Edit Payment Type</h1>
<form method="POST" action="{{ route('admin.payment-types.update', $paymentType) }}">
    @csrf @method('PUT')
    <div class="mb-3">
        <label class="form-label">Name *</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $paymentType->name) }}" required>
        @error('name') <div class="text-danger">{{ $message }}</div> @enderror
    </div>
    <div class="mb-3">
        <label class="form-label">Code (Optional)</label>
        <input type="text" name="code" class="form-control" value="{{ old('code', $paymentType->code) }}" placeholder="e.g., vehicle_rent, material_payment">
        <small class="text-muted">Used for backward compatibility with existing data</small>
        @error('code') <div class="text-danger">{{ $message }}</div> @enderror
    </div>
    <button type="submit" class="btn btn-primary">Update</button>
    <a href="{{ route('admin.payment-types.index') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection

