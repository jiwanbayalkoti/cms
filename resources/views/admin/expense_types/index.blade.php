@extends('admin.layout')
@section('title', 'Expense Types')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Expense Types</h1>
    <a href="{{ route('admin.expense-types.create') }}" class="btn btn-primary">Add Type</a>
</div>
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
<div class="card">
    <div class="card-body p-0">
        <div class="overflow-x-auto">
            <table class="table table-bordered mb-0">
                <thead><tr><th>Name</th><th class="text-nowrap">Actions</th></tr></thead>
                <tbody>
                @foreach($expenseTypes as $type)
                    <tr>
                        <td>{{ $type->name }}</td>
                        <td>
                            <div class="d-flex gap-1 text-nowrap">
                                <a href="{{ route('admin.expense-types.edit', $type) }}" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </a>
                                <form action="{{ route('admin.expense-types.destroy', $type) }}" method="POST" style="display:inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this expense type?')">
                                        <i class="bi bi-trash me-1"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

