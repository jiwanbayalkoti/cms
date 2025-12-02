@extends('admin.layout')

@section('title', 'Edit Material Unit')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Edit Material Unit</h1>
    <a href="{{ route('admin.material-units.index') }}" class="btn btn-outline-secondary">Back to List</a>
</div>

<div class="card">
    <div class="card-header">
        <strong>Unit Details</strong>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.material-units.update', $unit) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label">Name *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $unit->name) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" rows="3" class="form-control">{{ old('description', $unit->description) }}</textarea>
            </div>
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">Update Unit</button>
            </div>
        </form>
    </div>
</div>
@endsection


