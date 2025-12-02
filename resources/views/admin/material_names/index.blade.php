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
                            <a href="{{ route('admin.material-names.edit', $materialName) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('admin.material-names.destroy', $materialName) }}" method="POST" style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this material name?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3">No material names found.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($materialNames->hasPages())
            <div class="mt-3">
                {{ $materialNames->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
