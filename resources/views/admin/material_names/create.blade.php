@extends('admin.layout')
@section('title', 'Add Material Name')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Add Material Name</h1>
    <a href="{{ route('admin.material-names.index') }}" class="btn btn-outline-secondary">Back to List</a>
</div>
<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.material-names.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label">Name *</label>
                <input type="text" name="name" id="name" class="form-control" required value="{{ old('name') }}">
                @error('name')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('admin.material-names.index') }}" class="btn btn-secondary ms-2">Cancel</a>
        </form>
    </div>
</div>
@endsection
