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
<table class="table table-bordered">
    <thead><tr><th>Name</th><th>Actions</th></tr></thead>
    <tbody>
    @foreach($expenseTypes as $type)
        <tr>
            <td>{{ $type->name }}</td>
            <td>
                <a href="{{ route('admin.expense-types.edit', $type) }}" class="btn btn-sm btn-warning">Edit</a>
                <form action="{{ route('admin.expense-types.destroy', $type) }}" method="POST" style="display:inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this expense type?')">Delete</button>
                </form>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
@endsection

