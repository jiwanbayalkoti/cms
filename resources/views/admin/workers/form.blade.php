@extends('admin.layout')

@section('title', isset($worker) ? 'Edit Worker' : 'Add Worker')

@section('content')
@php $isEdit = isset($worker); @endphp
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-3xl font-bold text-gray-900">{{ $isEdit ? 'Edit Worker' : 'Add Worker' }}</h1>
    <a href="{{ route('admin.workers.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">Back</a>
</div>

<div class="bg-white shadow-lg rounded-lg p-6 max-w-3xl">
    <form method="POST" action="{{ $isEdit ? route('admin.workers.update', $worker) : route('admin.workers.store') }}">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $worker->name ?? '') }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @error('name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                <input type="text" name="phone" value="{{ old('phone', $worker->phone ?? '') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input type="email" name="email" value="{{ old('email', $worker->email ?? '') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Project</label>
                <select name="project_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Optional</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" {{ (string)old('project_id', $worker->project_id ?? '') === (string)$project->id ? 'selected' : '' }}>{{ $project->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Join Date</label>
                <input type="date" name="join_date" value="{{ old('join_date', isset($worker) && $worker->join_date ? $worker->join_date->format('Y-m-d') : '') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Wage Type <span class="text-red-500">*</span></label>
                <select name="wage_type" id="wage_type" required onchange="toggleWageFields()"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="daily" {{ old('wage_type', $worker->wage_type ?? 'daily') === 'daily' ? 'selected' : '' }}>Daily</option>
                    <option value="monthly" {{ old('wage_type', $worker->wage_type ?? '') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                </select>
            </div>
            <div id="daily_wage_wrap">
                <label class="block text-sm font-medium text-gray-700 mb-2">Daily Wage (Rs.)</label>
                <input type="number" step="0.01" min="0" name="daily_wage" id="daily_wage"
                       value="{{ old('daily_wage', $worker->daily_wage ?? '') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @error('daily_wage')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div id="monthly_salary_wrap" class="hidden">
                <label class="block text-sm font-medium text-gray-700 mb-2">Monthly Salary (Rs.)</label>
                <input type="number" step="0.01" min="0" name="monthly_salary" id="monthly_salary"
                       value="{{ old('monthly_salary', $worker->monthly_salary ?? '') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @error('monthly_salary')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                <textarea name="address" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('address', $worker->address ?? '') }}</textarea>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                <textarea name="notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('notes', $worker->notes ?? '') }}</textarea>
            </div>
            <div class="md:col-span-2">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300"
                           {{ old('is_active', $worker->is_active ?? true) ? 'checked' : '' }}>
                    <span class="text-sm text-gray-700">Active</span>
                </label>
            </div>
        </div>

        <div class="mt-6 flex gap-3">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg">
                {{ $isEdit ? 'Update Worker' : 'Save Worker' }}
            </button>
            <a href="{{ route('admin.workers.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</a>
        </div>
    </form>
</div>

<script>
function toggleWageFields() {
    const type = document.getElementById('wage_type').value;
    document.getElementById('daily_wage_wrap').classList.toggle('hidden', type !== 'daily');
    document.getElementById('monthly_salary_wrap').classList.toggle('hidden', type !== 'monthly');
}
document.addEventListener('DOMContentLoaded', toggleWageFields);
</script>
@endsection
