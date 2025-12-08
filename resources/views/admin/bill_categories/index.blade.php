@extends('admin.layout')

@section('title', 'Bill Categories')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Bill Categories</h1>
    <a href="{{ route('admin.bill-categories.create') }}" class="btn btn-primary">Add Category</a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Sort Order</th>
                    <th>Status</th>
                    <th>Subcategories</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                    <tr>
                        <td>{{ $category->id }}</td>
                        <td>{{ $category->name }}</td>
                        <td>{{ $category->description ?? '—' }}</td>
                        <td>{{ $category->sort_order }}</td>
                        <td>
                            <span class="badge {{ $category->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>{{ $category->subcategories->count() }}</td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.bill-categories.show', $category) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i> View
                                </a>
                                <a href="{{ route('admin.bill-categories.edit', $category) }}" class="btn btn-sm btn-outline-warning">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </a>
                                <form action="{{ route('admin.bill-categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this category?');">
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
                    <tr><td colspan="7">No categories found.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($categories->hasPages())
            <div class="mt-3">
                <x-pagination :paginator="$categories" />
            </div>
        @endif
    </div>
</div>
@endsection

