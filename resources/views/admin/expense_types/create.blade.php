@extends('admin.layout')
@section('title', 'Add Expense Type')
@section('content')
<h1>Add Expense Type</h1>
<form method="POST" action="{{ route('admin.expense-types.store') }}">
    @csrf
    <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control" required>
        @error('name') <div class="text-danger">{{ $message }}</div> @enderror
    </div>
    <button type="submit" class="btn btn-primary">Add</button>
    <a href="{{ route('admin.expense-types.index') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection

