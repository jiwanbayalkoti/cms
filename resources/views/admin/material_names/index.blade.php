@extends('admin.layout')
@section('title', 'Material Names')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Material Names</h1>
    <a href="{{ route('admin.material-names.create') }}" class="btn btn-primary">Add Material Name</a>
</div>
<div class="card">
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($materialNames as $materialName)
                    <tr>
                        <td>{{ $materialName->id }}</td>
                        <td>{{ $materialName->name }}</td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.material-names.show', $materialName) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i> View
                                </a>
                                <a href="{{ route('admin.material-names.edit', $materialName) }}" class="btn btn-sm btn-outline-warning">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </a>
                                <form action="{{ route('admin.material-names.destroy', $materialName) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this material name?');">
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
                    <tr><td colspan="3">No material names found.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($materialNames->hasPages())
            <div class="mt-3">
                <x-pagination :paginator="$materialNames" />
            </div>
        @endif
    </div>
</div>
@endsection
