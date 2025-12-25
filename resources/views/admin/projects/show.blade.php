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
    @if(Auth::user()->isAdmin())
    <a href="{{ route('admin.projects.edit', $project) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-200">
        Edit Project
    </a>
    @endif
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

    @if($project->files && count($project->files) > 0)
        <div>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Project Files</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($project->files as $file)
                    <div class="border rounded-lg p-4 bg-gray-50 hover:bg-gray-100 transition duration-200">
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900">{{ $file['name'] ?? 'Document' }}</h3>
                                <p class="text-sm text-gray-500 mt-1">{{ $file['original_name'] ?? '' }}</p>
                                @if(isset($file['size']))
                                    <p class="text-xs text-gray-400 mt-1">{{ number_format($file['size'] / 1024, 2) }} KB</p>
                                @endif
                            </div>
                            <svg class="h-6 w-6 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <a href="{{ \App\Helpers\StorageHelper::url($file['path'] ?? '') }}" target="_blank" 
                           class="mt-3 inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800">
                            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                            </svg>
                            View File
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

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
        @if(Auth::user()->isAdmin())
        <form action="{{ route('admin.projects.destroy', $project) }}" method="POST" onsubmit="return confirm('Delete this project?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-red-600 hover:text-red-900">Delete Project</button>
        </form>
        @endif
    </div>
</div>
@endsection

