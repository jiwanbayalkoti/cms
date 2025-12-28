@extends('admin.layout')

@section('title', 'Users')

@section('content')
<div class="flex justify-between mb-4">
  <h1 class="text-2xl font-bold">Users</h1>
  <a href="{{ route('admin.users.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded">New User</a>
  </div>

<div class="bg-white rounded shadow overflow-hidden">
  <div class="overflow-x-auto">
    <table class="min-w-full">
      <thead>
        <tr class="bg-gray-50 text-left">
          <th class="px-4 py-2">Name</th>
          <th class="px-4 py-2">Email</th>
          <th class="px-4 py-2">Company</th>
          <th class="px-4 py-2">Role</th>
          <th class="px-4 py-2 text-nowrap">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($users as $user)
          <tr class="border-t">
            <td class="px-4 py-2">{{ $user->name }}</td>
            <td class="px-4 py-2">{{ $user->email }}</td>
            <td class="px-4 py-2">{{ optional($user->company)->name ?: '-' }}</td>
            <td class="px-4 py-2">
              <span class="px-2 py-1 rounded text-xs font-semibold
                @if($user->role === 'super_admin') bg-purple-100 text-purple-800
                @elseif($user->role === 'admin') bg-blue-100 text-blue-800
                @else bg-gray-100 text-gray-800
                @endif">
                {{ ucfirst(str_replace('_', ' ', $user->role)) }}
              </span>
            </td>
            <td class="px-4 py-2 whitespace-nowrap">
              <div class="d-flex gap-1 text-nowrap">
                @php
                  $currentUser = auth()->user();
                  $canEdit = $currentUser->isSuperAdmin() || 
                            ($currentUser->isAdmin() && !$user->isSuperAdmin() && $user->company_id == $currentUser->company_id);
                  $canDelete = $currentUser->isSuperAdmin() || 
                              ($currentUser->isAdmin() && !$user->isSuperAdmin() && $user->company_id == $currentUser->company_id);
                @endphp
                @if($canEdit)
                  <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-warning">
                    <i class="bi bi-pencil me-1"></i> Edit
                  </a>
                @else
                  <button class="btn btn-sm btn-outline-warning" disabled>
                    <i class="bi bi-pencil me-1"></i> Edit
                  </button>
                @endif
                @if($canDelete && $currentUser->id !== $user->id)
                  <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" onclick="return confirm('Delete this user?')" class="btn btn-sm btn-outline-danger">
                      <i class="bi bi-trash me-1"></i> Delete
                    </button>
                  </form>
                @else
                  <button class="btn btn-sm btn-outline-danger" disabled>
                    <i class="bi bi-trash me-1"></i> Delete
                  </button>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="px-4 py-6 text-center text-gray-500">No users found.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <x-pagination :paginator="$users" wrapper-class="p-4" />
</div>
@endsection


