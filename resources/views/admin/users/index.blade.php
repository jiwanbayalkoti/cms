@extends('admin.layout')

@section('title', 'Users')

@section('content')
<div class="flex justify-between mb-4">
  <h1 class="text-2xl font-bold">Users</h1>
  <a href="{{ route('admin.users.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded">New User</a>
  </div>

<div class="bg-white rounded shadow">
  <table class="min-w-full">
    <thead>
      <tr class="bg-gray-50 text-left">
        <th class="px-4 py-2">Name</th>
        <th class="px-4 py-2">Email</th>
        <th class="px-4 py-2">Company</th>
        <th class="px-4 py-2">Role</th>
        <th class="px-4 py-2">Actions</th>
      </tr>
    </thead>
    <tbody>
      @forelse($users as $user)
        <tr class="border-t">
          <td class="px-4 py-2">{{ $user->name }}</td>
          <td class="px-4 py-2">{{ $user->email }}</td>
          <td class="px-4 py-2">{{ optional($user->company)->name ?: '-' }}</td>
          <td class="px-4 py-2">{{ $user->role }}</td>
          <td class="px-4 py-2 space-x-2">
            <a href="{{ route('admin.users.edit', $user) }}" class="text-indigo-600">Edit</a>
            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline">
              @csrf
              @method('DELETE')
              <button type="submit" onclick="return confirm('Delete this user?')" class="text-red-600">Delete</button>
            </form>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="5" class="px-4 py-6 text-center text-gray-500">No users found.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
  <x-pagination :paginator="$users" wrapper-class="p-4" />
</div>
@endsection


