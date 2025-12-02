@extends('admin.layout')

@section('title', 'Material Units')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Material Units</h1>
    <a href="{{ route('admin.material-units.create') }}" class="btn btn-primary">Add Unit</a>
</div>

<div class="card">
    <div class="card-header">
        <strong>Unit List</strong>
    </div>
    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($units as $unit)
                    <tr>
                        <td>{{ $unit->id }}</td>
                        <td>{{ $unit->name }}</td>
                        <td>{{ Str::limit($unit->description, 80) }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.material-units.edit', $unit) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            <form action="{{ route('admin.material-units.destroy', $unit) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this unit?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-3">No units found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($units->hasPages())
        <div class="card-footer">
            {{ $units->links() }}
        </div>
    @endif
</div>
@endsection


