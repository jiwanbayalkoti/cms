@extends('admin.layout')

@section('title', 'Add Work Type')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Add Work Type</h1>
    <a href="{{ route('admin.work-types.index') }}" class="btn btn-outline-secondary">Back to List</a>
</div>

<div class="card">
    <div class="card-header">
        <strong>Work Type Details</strong>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.work-types.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label">Name *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" rows="3" class="form-control">{{ old('description') }}</textarea>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">
                    Active
                </label>
            </div>
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">Save Work Type</button>
            </div>
        </form>
    </div>
</div>
@endsection


