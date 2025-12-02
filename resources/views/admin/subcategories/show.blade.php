@extends('admin.layout')

@section('title', 'View Subcategory')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-3xl font-bold text-gray-900">Subcategory Details</h1>
    <div class="space-x-2">
        <a href="{{ route('admin.subcategories.edit', $subcategory) }}" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg transition duration-200">
            Edit
        </a>
        <a href="{{ route('admin.subcategories.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition duration-200">
            Back to List
        </a>
    </div>
</div>

<div class="bg-white shadow-lg rounded-lg p-6">
    <h2 class="text-xl font-semibold text-gray-900 mb-4">Subcategory Information</h2>
    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <dt class="text-sm font-medium text-gray-500">Name</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ $subcategory->name }}</dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500">Category</dt>
            <dd class="mt-1">
                <a href="{{ route('admin.categories.show', $subcategory->category) }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                    {{ $subcategory->category->name }}
                </a>
                <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $subcategory->category->type === 'income' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ ucfirst($subcategory->category->type) }}
                </span>
            </dd>
        </div>
        <div class="md:col-span-2">
            <dt class="text-sm font-medium text-gray-500">Description</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ $subcategory->description ?? 'N/A' }}</dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500">Status</dt>
            <dd class="mt-1">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $subcategory->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                    {{ $subcategory->is_active ? 'Active' : 'Inactive' }}
                </span>
            </dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500">Created At</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ $subcategory->created_at->format('M d, Y H:i') }}</dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500">Updated At</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ $subcategory->updated_at->format('M d, Y H:i') }}</dd>
        </div>
    </dl>
</div>
@endsection

