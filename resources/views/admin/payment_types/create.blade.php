@extends('admin.layout')
@section('title', 'Add Payment Type')
@section('content')
<h1>Add Payment Type</h1>
<form method="POST" action="{{ route('admin.payment-types.store') }}">
    @csrf
    <div class="mb-3">
        <label class="form-label">Name *</label>
        <input type="text" name="name" class="form-control" required>
        @error('name') <div class="text-danger">{{ $message }}</div> @enderror
    </div>
    <div class="mb-3">
        <label class="form-label">Code (Optional)</label>
        <input type="text" name="code" class="form-control" placeholder="e.g., vehicle_rent, material_payment">
        <small class="text-muted">Used for backward compatibility with existing data</small>
        @error('code') <div class="text-danger">{{ $message }}</div> @enderror
    </div>
    <button type="submit" class="btn btn-primary">Add</button>
    <a href="{{ route('admin.payment-types.index') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection

