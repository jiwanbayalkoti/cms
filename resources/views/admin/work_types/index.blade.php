@extends('admin.layout')

@section('title', 'Work Types')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Work Types</h1>
    <a href="{{ route('admin.work-types.create') }}" class="btn btn-primary">Add Work Type</a>
</div>

<div class="card">
    <div class="card-header">
        <strong>Work Type List</strong>
    </div>
    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($workTypes as $workType)
                    <tr>
                        <td>{{ $workType->id }}</td>
                        <td>{{ $workType->name }}</td>
                        <td>{{ Str::limit($workType->description, 80) }}</td>
                        <td>
                            <span class="badge {{ $workType->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $workType->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="{{ route('admin.work-types.show', $workType) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i> View
                                </a>
                                <a href="{{ route('admin.work-types.edit', $workType) }}" class="btn btn-sm btn-outline-warning">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </a>
                                <form action="{{ route('admin.work-types.destroy', $workType) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this work type?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash me-1"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-3">No work types found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pagination :paginator="$workTypes" wrapper-class="card-footer" />
</div>
@endsection


