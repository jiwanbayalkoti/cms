@extends('admin.layout')

@section('title', 'Edit User')

@section('content')
<h1 class="text-2xl font-bold mb-4">Edit User</h1>

<form method="POST" action="{{ route('admin.users.update', $user) }}" class="bg-white p-6 rounded shadow max-w-xl"
      data-validate="true"
      data-validation-route="{{ route('admin.users.validate.edit', $user) }}"
      id="userForm">
  @csrf
  @method('PUT')

  <div class="mb-4">
    <label class="block text-sm font-medium mb-1">Name</label>
    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full border rounded px-3 py-2 @error('name') border-red-500 @enderror">
    <div class="field-error text-red-600 text-sm mt-1" data-field="name" style="display: none;"></div>
    @error('name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
  </div>

  <div class="mb-4">
    <label class="block text-sm font-medium mb-1">Email</label>
    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full border rounded px-3 py-2 @error('email') border-red-500 @enderror">
    <div class="field-error text-red-600 text-sm mt-1" data-field="email" style="display: none;"></div>
    @error('email')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
      <label class="block text-sm font-medium mb-1">New Password</label>
      <input type="password" name="password" class="w-full border rounded px-3 py-2 @error('password') border-red-500 @enderror">
      <div class="field-error text-red-600 text-sm mt-1" data-field="password" style="display: none;"></div>
      @error('password')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
      <label class="block text-sm font-medium mb-1">Confirm Password</label>
      <input type="password" name="password_confirmation" class="w-full border rounded px-3 py-2">
      <div class="field-error text-red-600 text-sm mt-1" data-field="password_confirmation" style="display: none;"></div>
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
    <div class="field-error text-red-600 text-sm mt-1" data-field="company_id" style="display: none;"></div>
    @error('company_id')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
  </div>

  <div class="mb-4">
    <label class="block text-sm font-medium mb-1">Role</label>
    @php
      $currentUser = auth()->user();
      $isOwnAccount = $currentUser->id === $user->id;
    @endphp
    @if($isOwnAccount)
      <input type="hidden" name="role" value="{{ $user->role }}">
      <input type="text" value="{{ ucfirst(str_replace('_', ' ', $user->role)) }}" class="w-full border rounded px-3 py-2 bg-gray-100" readonly>
      <p class="text-gray-500 text-xs mt-1">You cannot change your own role.</p>
    @else
      <select name="role" class="w-full border rounded px-3 py-2 @error('role') border-red-500 @enderror" 
              {{ $user->isSuperAdmin() && !$currentUser->isSuperAdmin() ? 'disabled' : '' }}>
        @php
          $roles = ['super_admin' => 'Super Admin', 'admin' => 'Admin', 'user' => 'User'];
          // Regular admin cannot change roles to admin or super_admin
          if (!$currentUser->isSuperAdmin()) {
              $roles = ['user' => 'User'];
          }
          // If editing a super_admin as regular admin, show current role but disabled
          if ($user->isSuperAdmin() && !$currentUser->isSuperAdmin()) {
              $roles = ['super_admin' => 'Super Admin'];
          }
        @endphp
        @foreach($roles as $value => $label)
          <option value="{{ $value }}" {{ (old('role', $user->role) == $value) ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
      </select>
      @if($user->isSuperAdmin() && !$currentUser->isSuperAdmin())
        <input type="hidden" name="role" value="super_admin">
        <p class="text-gray-500 text-xs mt-1">You cannot change the role of a super admin user.</p>
      @elseif(!$currentUser->isSuperAdmin())
        <p class="text-gray-500 text-xs mt-1">You can only assign regular user role.</p>
      @endif
    @endif
    <div class="field-error text-red-600 text-sm mt-1" data-field="role" style="display: none;"></div>
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


