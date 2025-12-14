@extends('admin.layout')

@section('title', 'Edit Staff')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Edit Staff Member</h1>
    <p class="mt-2 text-gray-600">Update staff member information</p>
</div>

<div class="bg-white shadow-lg rounded-lg p-6">
    <form action="{{ route('admin.staff.update', $staff) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="project_id" class="block text-sm font-medium text-gray-700 mb-2">Project</label>
                <select name="project_id" id="project_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('project_id') border-red-500 @enderror">
                    <option value="">Select a project (optional)</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" {{ old('project_id', $staff->project_id) == $project->id ? 'selected' : '' }}>
                            {{ $project->name }}
                        </option>
                    @endforeach
                </select>
                @error('project_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Full Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $staff->name) }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" id="email" value="{{ old('email', $staff->email) }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('email') border-red-500 @enderror">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone', $staff->phone) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('phone') border-red-500 @enderror">
                @error('phone')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="position_id" class="block text-sm font-medium text-gray-700 mb-2">Position <span class="text-red-500">*</span></label>
                <select name="position_id" id="position_id" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('position_id') border-red-500 @enderror">
                    <option value="">Select a position</option>
                    @foreach($positions as $position)
                        <option value="{{ $position->id }}" {{ old('position_id', $staff->position_id) == $position->id ? 'selected' : '' }}>
                            {{ $position->name }}
                        </option>
                    @endforeach
                </select>
                @error('position_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Don't see the position you need? <a href="{{ route('admin.positions.create') }}" target="_blank" class="text-indigo-600 hover:text-indigo-900">Create a new position</a></p>
            </div>

            <div>
                <label for="salary" class="block text-sm font-medium text-gray-700 mb-2">Salary</label>
                <input type="number" name="salary" id="salary" value="{{ old('salary', $staff->salary) }}" step="0.01" min="0"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('salary') border-red-500 @enderror">
                @error('salary')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="join_date" class="block text-sm font-medium text-gray-700 mb-2">Join Date</label>
                <input type="date" name="join_date" id="join_date" value="{{ old('join_date', $staff->join_date ? $staff->join_date->format('Y-m-d') : '') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('join_date') border-red-500 @enderror">
                @error('join_date')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                <textarea name="address" id="address" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('address') border-red-500 @enderror">{{ old('address', $staff->address) }}</textarea>
                @error('address')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $staff->is_active) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <span class="ml-2 text-sm text-gray-700">Active</span>
                </label>
            </div>
        </div>

        <div class="flex items-center justify-end space-x-4 mt-6">
            <a href="{{ route('admin.staff.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">
                Cancel
            </a>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-200">
                Update Staff Member
            </button>
        </div>
    </form>
</div>
@endsection

