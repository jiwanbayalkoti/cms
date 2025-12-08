@extends('admin.layout')

@section('title', 'Projects')

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

<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Projects</h1>
        <p class="text-gray-600">Track initiatives across your company</p>
    </div>
    <a href="{{ route('admin.projects.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition duration-200">
        Add New Project
    </a>
</div>

<div class="bg-white shadow-lg rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project</th>
                @if((int) $activeCompanyId === 1)
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                @endif
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Budget</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timeline</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($projects as $project)
                <tr>
                    <td class="px-6 py-4">
                        <div class="text-sm font-semibold text-gray-900">{{ $project->name }}</div>
                        <div class="text-sm text-gray-500">{{ Str::limit($project->description, 60) }}</div>
                    </td>
                    @if((int) $activeCompanyId === 1)
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ optional($project->company)->name ?? '—' }}
                        </td>
                    @endif
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $project->client_name ?? '—' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php($color = $statusColors[$project->status] ?? 'bg-gray-100 text-gray-800')
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $color }}">
                            {{ Str::headline($project->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $project->budget ? number_format($project->budget, 2) : 'Not set' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        @if($project->start_date || $project->end_date)
                            {{ optional($project->start_date)?->format('M d, Y') ?? 'TBD' }} —
                            {{ optional($project->end_date)?->format('M d, Y') ?? 'TBD' }}
                        @else
                            <span class="text-gray-500">No dates</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.projects.show', $project) }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-eye me-1"></i> View
                            </a>
                            <a href="{{ route('admin.projects.edit', $project) }}" class="btn btn-outline-warning btn-sm">
                                <i class="bi bi-pencil me-1"></i> Edit
                            </a>
                            <form action="{{ route('admin.projects.destroy', $project) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this project?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                    <i class="bi bi-trash me-1"></i> Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                        No projects found. <a href="{{ route('admin.projects.create') }}" class="text-indigo-600 hover:text-indigo-900">Create one now</a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<x-pagination :paginator="$projects" wrapper-class="mt-4" />
@endsection

