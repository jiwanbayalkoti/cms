@extends('admin.layout')
@section('title', 'Edit Expense Type')
@section('content')
<h1>Edit Expense Type</h1>
<form method="POST" action="{{ route('admin.expense-types.update', $expenseType) }}">
    @csrf @method('PUT')
    <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $expenseType->name) }}" required>
        @error('name') <div class="text-danger">{{ $message }}</div> @enderror
    </div>
    <button type="submit" class="btn btn-primary">Update</button>
    <a href="{{ route('admin.expense-types.index') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection

