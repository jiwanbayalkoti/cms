@extends('admin.layout')

@section('title', 'Edit User')

@section('content')
<h1 class="text-2xl font-bold mb-4">Edit User</h1>

<form method="POST" action="{{ route('admin.users.update', $user) }}" class="bg-white p-6 rounded shadow max-w-xl">
  @csrf
  @method('PUT')

  <div class="mb-4">
    <label class="block text-sm font-medium mb-1">Name</label>
    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full border rounded px-3 py-2 @error('name') border-red-500 @enderror">
    @error('name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
  </div>

  <div class="mb-4">
    <label class="block text-sm font-medium mb-1">Email</label>
    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full border rounded px-3 py-2 @error('email') border-red-500 @enderror">
    @error('email')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
      <label class="block text-sm font-medium mb-1">New Password</label>
      <input type="password" name="password" class="w-full border rounded px-3 py-2 @error('password') border-red-500 @enderror">
      @error('password')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
      <label class="block text-sm font-medium mb-1">Confirm Password</label>
      <input type="password" name="password_confirmation" class="w-full border rounded px-3 py-2">
    </div>
  </div>

  <div class="mb-4">
    <label class="block text-sm font-medium mb-1">Company</label>
    <select name="company_id" class="w-full border rounded px-3 py-2 @error('company_id') border-red-500 @enderror">
      <option value="">None</option>
      @foreach($companies as $company)
        <option value="{{ $company->id }}" {{ (old('company_id', $user->company_id) == $company->id) ? 'selected' : '' }}>{{ $company->name }}</option>
      @endforeach
    </select>
    @error('company_id')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
  </div>

  <div class="mb-4">
    <label class="block text-sm font-medium mb-1">Role</label>
    <select name="role" class="w-full border rounded px-3 py-2 @error('role') border-red-500 @enderror">
      @foreach(['super_admin' => 'Super Admin', 'admin' => 'Admin', 'user' => 'User'] as $value => $label)
        <option value="{{ $value }}" {{ (old('role', $user->role) == $value) ? 'selected' : '' }}>{{ $label }}</option>
      @endforeach
    </select>
    @error('role')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
  </div>

  <div class="mb-6">
    <label class="inline-flex items-center">
      <input type="checkbox" name="is_admin" value="1" {{ old('is_admin', $user->is_admin) ? 'checked' : '' }} class="mr-2">
      <span>Legacy is_admin (keep compatibility)</span>
    </label>
  </div>

  <div class="flex space-x-2">
    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded">Update</button>
    <a href="{{ route('admin.users.index') }}" class="px-4 py-2 rounded border">Cancel</a>
  </div>
</form>
@endsection


