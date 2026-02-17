@extends('admin.layout')

@section('title', 'Edit Work – ' . $boq_work->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Edit Work</h1>
    <a href="{{ route('admin.boq.work-index') }}" class="btn btn-sm btn-outline-secondary p-1" title="Back"><i class="bi bi-arrow-left"></i></a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.boq.works.update', $boq_work) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label">Work Name</label>
                <input type="text" name="name" class="form-control form-control-sm" value="{{ old('name', $boq_work->name) }}" required maxlength="255" placeholder="e.g. Foundation, Beam">
                @error('name')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary btn-sm" title="Save"><i class="bi bi-check-lg me-1"></i> Save</button>
            <a href="{{ route('admin.boq.work-index') }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
        </form>
    </div>
</div>
@endsection
