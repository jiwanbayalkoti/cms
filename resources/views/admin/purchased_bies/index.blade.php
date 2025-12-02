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
                            <a href="{{ route('admin.purchased-bies.edit', $purchasedBy) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('admin.purchased-bies.destroy', $purchasedBy) }}" method="POST" style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this person?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">No persons found.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($purchasedBies->hasPages())
            <div class="mt-3">
                {{ $purchasedBies->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

