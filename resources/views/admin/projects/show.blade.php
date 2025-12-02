@extends('admin.layout')

@section('title', 'Project Details')

@section('content')
@php
    use App\Support\CompanyContext;

    $statusColors = [
        'planned' => 'bg-gray-100 text-gray-800',
        'active' => 'bg-green-100 text-green-800',
        'on_hold' => 'bg-yellow-100 text-yellow-800',
        'completed' => 'bg-blue-100 text-blue-800',
        'cancelled' => 'bg-red-100 text-red-800',
    ];
    $activeCompanyId = CompanyContext::getActiveCompanyId();
@endphp

<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">{{ $project->name }}</h1>
        <p class="text-gray-600">{{ $project->client_name ?? 'Internal project' }}</p>
    </div>
    <a href="{{ route('admin.projects.edit', $project) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-200">
        Edit Project
    </a>
</div>

<div class="bg-white shadow-lg rounded-lg p-6 space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-{{ (int) $activeCompanyId === 1 ? '4' : '3' }} gap-6">
        <div class="border rounded-lg p-4">
            <p class="text-sm text-gray-500">Status</p>
            @php($color = $statusColors[$project->status] ?? 'bg-gray-100 text-gray-800')
            <span class="mt-2 inline-block px-3 py-1 rounded-full text-xs font-semibold {{ $color }}">
                {{ Str::headline($project->status) }}
            </span>
        </div>
        @if((int) $activeCompanyId === 1)
            <div class="border rounded-lg p-4">
                <p class="text-sm text-gray-500">Company</p>
                <p class="mt-2 text-gray-900">{{ optional($project->company)->name ?? '—' }}</p>
            </div>
        @endif
        <div class="border rounded-lg p-4">
            <p class="text-sm text-gray-500">Budget</p>
            <p class="mt-2 text-xl font-semibold text-gray-900">
                {{ $project->budget ? number_format($project->budget, 2) : 'Not set' }}
            </p>
        </div>
        <div class="border rounded-lg p-4">
            <p class="text-sm text-gray-500">Timeline</p>
            <p class="mt-2 text-gray-900">
                {{ optional($project->start_date)?->format('M d, Y') ?? 'TBD' }} —
                {{ optional($project->end_date)?->format('M d, Y') ?? 'TBD' }}
            </p>
        </div>
    </div>

    <div>
        <h2 class="text-xl font-semibold text-gray-900 mb-2">Description</h2>
        <p class="text-gray-700 whitespace-pre-line">{{ $project->description ?? 'No description provided.' }}</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="border rounded-lg p-4">
            <p class="text-sm text-gray-500">Created by</p>
            <p class="mt-1 text-gray-900">{{ $project->creator->name ?? 'System' }}</p>
            <p class="text-sm text-gray-500">{{ $project->created_at?->format('M d, Y H:i') }}</p>
        </div>
        <div class="border rounded-lg p-4">
            <p class="text-sm text-gray-500">Last updated</p>
            <p class="mt-1 text-gray-900">{{ $project->updater->name ?? $project->creator->name ?? 'System' }}</p>
            <p class="text-sm text-gray-500">{{ $project->updated_at?->format('M d, Y H:i') }}</p>
        </div>
    </div>

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.projects.index') }}" class="text-indigo-600 hover:text-indigo-900">Back to list</a>
        <form action="{{ route('admin.projects.destroy', $project) }}" method="POST" onsubmit="return confirm('Delete this project?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-red-600 hover:text-red-900">Delete Project</button>
        </form>
    </div>
</div>
@endsection

