@extends('admin.layout')

@section('title', 'Bill Category Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Bill Category Details</h1>
    <div>
        <a href="{{ route('admin.bill-categories.edit', $category) }}" class="btn btn-warning me-2">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
        <a href="{{ route('admin.bill-categories.index') }}" class="btn btn-outline-secondary">Back to List</a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Category Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="200">Name:</th>
                        <td><strong>{{ $category->name }}</strong></td>
                    </tr>
                    @if($category->description)
                        <tr>
                            <th>Description:</th>
                            <td>{{ $category->description }}</td>
                        </tr>
                    @endif
                    <tr>
                        <th>Sort Order:</th>
                        <td>{{ $category->sort_order }}</td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>
                            <span class="badge {{ $category->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Subcategories</h5>
                <a href="{{ route('admin.bill-subcategories.create', ['bill_category_id' => $category->id]) }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Add Subcategory
                </a>
            </div>
            <div class="card-body">
                @if($category->subcategories->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Sort Order</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($category->subcategories as $subcategory)
                                    <tr>
                                        <td>{{ $subcategory->name }}</td>
                                        <td>{{ $subcategory->description ?? '—' }}</td>
                                        <td>{{ $subcategory->sort_order }}</td>
                                        <td>
                                            <span class="badge {{ $subcategory->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $subcategory->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex gap-1 justify-content-end">
                                                <a href="{{ route('admin.bill-subcategories.show', $subcategory) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye me-1"></i> View
                                                </a>
                                                <a href="{{ route('admin.bill-subcategories.edit', $subcategory) }}" class="btn btn-sm btn-outline-warning">
                                                    <i class="bi bi-pencil me-1"></i> Edit
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted mb-0">No subcategories found.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

