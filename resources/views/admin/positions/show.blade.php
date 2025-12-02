@extends('admin.layout')

@section('title', 'View Position')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-3xl font-bold text-gray-900">Position Details</h1>
    <div class="space-x-2">
        <a href="{{ route('admin.positions.edit', $position) }}" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg transition duration-200">
            Edit
        </a>
        <a href="{{ route('admin.positions.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition duration-200">
            Back to List
        </a>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="bg-white shadow-lg rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Position Information</h2>
        <dl class="space-y-4">
            <div>
                <dt class="text-sm font-medium text-gray-500">Name</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $position->name }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Description</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $position->description ?? 'N/A' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Salary Range</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $position->salary_range ?? 'N/A' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Status</dt>
                <dd class="mt-1">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $position->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ $position->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Created At</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $position->created_at->format('M d, Y H:i') }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Updated At</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $position->updated_at->format('M d, Y H:i') }}</dd>
            </div>
        </dl>
    </div>

    <div class="bg-white shadow-lg rounded-lg p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-900">Staff Members</h2>
            <span class="px-3 py-1 bg-indigo-100 text-indigo-800 rounded-full text-sm font-semibold">
                {{ $position->staff->count() }} member(s)
            </span>
        </div>
        @if($position->staff->count() > 0)
            <ul class="space-y-2">
                @foreach($position->staff as $member)
                    <li class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                        <div>
                            <span class="font-medium text-gray-900">{{ $member->name }}</span>
                            <p class="text-sm text-gray-500">{{ $member->email }}</p>
                        </div>
                        <a href="{{ route('admin.staff.show', $member) }}" class="text-indigo-600 hover:text-indigo-900 text-sm">View</a>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-sm text-gray-500">No staff members assigned to this position.</p>
        @endif
    </div>
</div>
@endsection

