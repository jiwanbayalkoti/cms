@extends('admin.layout')
@section('title', 'Purchased By')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Purchased By</h1>
    <a href="{{ route('admin.purchased-bies.create') }}" class="btn btn-primary">Add Person</a>
</div>
<div class="card">
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchasedBies as $purchasedBy)
                    <tr>
                        <td>{{ $purchasedBy->id }}</td>
                        <td>{{ $purchasedBy->name }}</td>
                        <td>{{ $purchasedBy->contact ?? '—' }}</td>
                        <td>{{ $purchasedBy->email ?? '—' }}</td>
                        <td>
                            <span class="badge {{ $purchasedBy->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $purchasedBy->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.purchased-bies.edit', $purchasedBy) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i> View
                                </a>
                                <a href="{{ route('admin.purchased-bies.edit', $purchasedBy) }}" class="btn btn-sm btn-outline-warning">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </a>
                                <form action="{{ route('admin.purchased-bies.destroy', $purchasedBy) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this person?');">
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
                    <tr><td colspan="6">No persons found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <x-pagination :paginator="$purchasedBies" />
    </div>
</div>
@endsection

