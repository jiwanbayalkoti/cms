@extends('admin.layout')

@section('title', 'Companies')

@section('content')
<div class="flex justify-between mb-4">
    <h1 class="text-2xl font-bold">Companies</h1>
    <a href="{{ route('admin.companies.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded">New Company</a>
  </div>

  <div class="bg-white rounded shadow">
    <table class="min-w-full">
      <thead>
        <tr class="bg-gray-50 text-left">
          <th class="px-4 py-2">Name</th>
          <th class="px-4 py-2">Email</th>
          <th class="px-4 py-2">Phone</th>
          <th class="px-4 py-2">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($companies as $company)
          <tr class="border-t">
            <td class="px-4 py-2">{{ $company->name }}</td>
            <td class="px-4 py-2">{{ $company->email }}</td>
            <td class="px-4 py-2">{{ $company->phone }}</td>
            <td class="px-4 py-2 space-x-2">
              <a href="{{ route('admin.companies.show', $company) }}" class="text-gray-700">View</a>
              <a href="{{ route('admin.companies.edit', $company) }}" class="text-indigo-600">Edit</a>
              <form method="POST" action="{{ route('admin.companies.destroy', $company) }}" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" onclick="return confirm('Delete this company?')" class="text-red-600">Delete</button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="3" class="px-4 py-6 text-center text-gray-500">No companies found.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
    <x-pagination :paginator="$companies" wrapper-class="p-4" />
  </div>
@endsection


