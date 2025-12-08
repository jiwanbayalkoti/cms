@extends('admin.layout')

@section('title', 'Bill Subcategory Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Bill Subcategory Details</h1>
    <div>
        <a href="{{ route('admin.bill-subcategories.edit', $subcategory) }}" class="btn btn-warning me-2">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
        <a href="{{ route('admin.bill-subcategories.index') }}" class="btn btn-outline-secondary">Back to List</a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Subcategory Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="200">Category:</th>
                        <td>
                            @if($subcategory->category)
                                <a href="{{ route('admin.bill-categories.show', $subcategory->category) }}" class="text-decoration-none">
                                    <strong>{{ $subcategory->category->name }}</strong>
                                </a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Name:</th>
                        <td><strong>{{ $subcategory->name }}</strong></td>
                    </tr>
                    @if($subcategory->description)
                        <tr>
                            <th>Description:</th>
                            <td>{{ $subcategory->description }}</td>
                        </tr>
                    @endif
                    <tr>
                        <th>Sort Order:</th>
                        <td>{{ $subcategory->sort_order }}</td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>
                            <span class="badge {{ $subcategory->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $subcategory->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

