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
    @if(Auth::user()->isAdmin())
    <a href="{{ route('admin.projects.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition duration-200">
        Add New Project
    </a>
    @endif
</div>

@if($companiesWithProjects->count() > 0)
    @foreach($companiesWithProjects as $companyGroup)
        <!-- Company Section -->
        <div class="mb-8">
            <div class="mb-4 flex items-center">
                @if($companyGroup['company'] && $companyGroup['company']->logo)
                    <img src="{{ $companyGroup['company']->getLogoUrl() }}" alt="{{ $companyGroup['company_name'] }}" class="h-8 w-8 rounded-lg object-cover mr-3">
                @else
                    <div class="h-8 w-8 rounded-lg bg-indigo-100 flex items-center justify-center mr-3">
                        <span class="text-indigo-600 font-bold text-sm">{{ strtoupper(substr($companyGroup['company_name'], 0, 1)) }}</span>
                    </div>
                @endif
                <h2 class="text-2xl font-bold text-gray-900">{{ $companyGroup['company_name'] }}</h2>
                <span class="ml-3 px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm font-medium">
                    {{ $companyGroup['projects']->count() }} {{ Str::plural('project', $companyGroup['projects']->count()) }}
                </span>
            </div>

            <!-- Projects Grid for this Company -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                @foreach($companyGroup['projects'] as $project)
                    <div class="bg-white shadow-lg rounded-lg overflow-hidden border border-gray-200 hover:shadow-xl transition-shadow duration-200">
                        <!-- Card Content -->
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900 flex-1">{{ $project->name }}</h3>
                                @php($color = $statusColors[$project->status] ?? 'bg-gray-100 text-gray-800')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $color }} whitespace-nowrap ml-2">
                                    {{ Str::headline($project->status) }}
                                </span>
                            </div>
                        </div>

                        <!-- Card Footer - Actions -->
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                            @if(Auth::user()->role === 'site_engineer')
                            <div class="mb-2">
                                <a href="{{ route('admin.projects.gallery', $project) }}" 
                                   class="w-full bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg text-sm font-medium text-center transition duration-200 flex items-center justify-center">
                                    <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    Gallery
                                </a>
                            </div>
                            @else
                            <div class="grid grid-cols-2 gap-2 mb-2">
                                <a href="{{ route('admin.projects.show', $project) }}" 
                                   class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-2 rounded-lg text-sm font-medium text-center transition duration-200 flex items-center justify-center">
                                    <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    View
                                </a>
                                <a href="{{ route('admin.projects.gallery', $project) }}" 
                                   class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg text-sm font-medium text-center transition duration-200 flex items-center justify-center">
                                    <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    Gallery
                                </a>
                            </div>
                            @endif
                            @if(Auth::user()->isAdmin())
                            <div class="flex items-center justify-between gap-2">
                                <a href="{{ route('admin.projects.edit', $project) }}" 
                                   class="flex-1 bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-2 rounded-lg text-sm font-medium text-center transition duration-200 flex items-center justify-center">
                                    <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    Edit
                                </a>
                                <form action="{{ route('admin.projects.destroy', $project) }}" 
                                      method="POST" 
                                      class="flex-1"
                                      onsubmit="return confirm('Are you sure you want to delete this project? This action cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="w-full bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-lg text-sm font-medium transition duration-200 flex items-center justify-center">
                                        <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                        Delete
                                    </button>
                                </form>
                            </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
@else
    <div class="bg-white shadow-lg rounded-lg p-12 text-center">
        <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
        </svg>
        <h3 class="mt-4 text-lg font-medium text-gray-900">No projects found</h3>
        <p class="mt-2 text-sm text-gray-500">Get started by creating a new project.</p>
        <div class="mt-6">
            <a href="{{ route('admin.projects.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-200">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Create New Project
            </a>
        </div>
    </div>
@endif

@endsection
