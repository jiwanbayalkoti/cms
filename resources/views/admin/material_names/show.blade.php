@extends('admin.layout')

@section('title', 'Material Name Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Material Name Details</h1>
    <div>
        <a href="{{ route('admin.material-names.edit', $materialName) }}" class="btn btn-warning me-2">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
        <a href="{{ route('admin.material-names.index') }}" class="btn btn-outline-secondary">Back to List</a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Material Name Information</h5>
    </div>
    <div class="card-body">
        <table class="table table-borderless">
            <tr>
                <th width="200">ID:</th>
                <td>{{ $materialName->id }}</td>
            </tr>
            <tr>
                <th>Name:</th>
                <td><strong>{{ $materialName->name }}</strong></td>
            </tr>
            <tr>
                <th>Created At:</th>
                <td>{{ $materialName->created_at->format('M d, Y H:i') }}</td>
            </tr>
            <tr>
                <th>Updated At:</th>
                <td>{{ $materialName->updated_at->format('M d, Y H:i') }}</td>
            </tr>
        </table>
    </div>
</div>
@endsection

