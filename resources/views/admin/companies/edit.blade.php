@extends('admin.layout')

@section('title', 'Edit Company')

@section('content')
<h1 class="text-2xl font-bold mb-4">Edit Company</h1>

<form method="POST" action="{{ route('admin.companies.update', $company) }}" enctype="multipart/form-data" class="bg-white p-6 rounded shadow max-w-xl">
  @csrf
  @method('PUT')

  <div class="mb-4">
    <label class="block text-sm font-medium mb-1">Name</label>
    <input type="text" name="name" value="{{ old('name', $company->name) }}" class="w-full border rounded px-3 py-2 @error('name') border-red-500 @enderror">
    @error('name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
  </div>

  <div class="mb-4">
    <label class="block text-sm font-medium mb-1">Address</label>
    <input type="text" name="address" value="{{ old('address', $company->address) }}" class="w-full border rounded px-3 py-2 @error('address') border-red-500 @enderror">
    @error('address')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
  </div>

  <div class="mb-4">
    <label class="block text-sm font-medium mb-1">Logo</label>
    @if($company->logo)
      <div class="mb-2">
        <img src="{{ asset('storage/' . $company->logo) }}" alt="Company Logo" class="h-16 rounded shadow">
      </div>
    @endif
    <input type="file" name="logo" accept="image/*" class="w-full border rounded px-3 py-2 @error('logo') border-red-500 @enderror">
    @error('logo')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
      <label class="block text-sm font-medium mb-1">Email</label>
      <input type="email" name="email" value="{{ old('email', $company->email) }}" class="w-full border rounded px-3 py-2 @error('email') border-red-500 @enderror">
      @error('email')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
      <label class="block text-sm font-medium mb-1">Phone</label>
      <input type="text" name="phone" value="{{ old('phone', $company->phone) }}" class="w-full border rounded px-3 py-2 @error('phone') border-red-500 @enderror">
      @error('phone')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
      <label class="block text-sm font-medium mb-1">Website</label>
      <input type="url" name="website" value="{{ old('website', $company->website) }}" class="w-full border rounded px-3 py-2 @error('website') border-red-500 @enderror">
      @error('website')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
      <label class="block text-sm font-medium mb-1">Tax Number</label>
      <input type="text" name="tax_number" value="{{ old('tax_number', $company->tax_number) }}" class="w-full border rounded px-3 py-2 @error('tax_number') border-red-500 @enderror">
      @error('tax_number')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
    <div>
      <label class="block text-sm font-medium mb-1">City</label>
      <input type="text" name="city" value="{{ old('city', $company->city) }}" class="w-full border rounded px-3 py-2 @error('city') border-red-500 @enderror">
      @error('city')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
      <label class="block text-sm font-medium mb-1">State</label>
      <input type="text" name="state" value="{{ old('state', $company->state) }}" class="w-full border rounded px-3 py-2 @error('state') border-red-500 @enderror">
      @error('state')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
      <label class="block text-sm font-medium mb-1">Country</label>
      <input type="text" name="country" value="{{ old('country', $company->country) }}" class="w-full border rounded px-3 py-2 @error('country') border-red-500 @enderror">
      @error('country')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
      <label class="block text-sm font-medium mb-1">ZIP</label>
      <input type="text" name="zip" value="{{ old('zip', $company->zip) }}" class="w-full border rounded px-3 py-2 @error('zip') border-red-500 @enderror">
      @error('zip')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
    </div>
  </div>

  <div class="flex space-x-2">
    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded">Update</button>
    <a href="{{ route('admin.companies.index') }}" class="px-4 py-2 rounded border">Cancel</a>
  </div>
</form>
@endsection


