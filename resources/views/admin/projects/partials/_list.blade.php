@php
    $statusColors = $statusColors ?? [
        'planned' => 'bg-gray-100 text-gray-800',
        'active' => 'bg-green-100 text-green-800',
        'on_hold' => 'bg-yellow-100 text-yellow-800',
        'completed' => 'bg-blue-100 text-blue-800',
        'cancelled' => 'bg-red-100 text-red-800',
    ];
@endphp
@if(isset($averageProgress))
@php
    $avgPct = (float)($averageProgress ?? 0);
    $avgBarColor = $avgPct >= 100 ? 'bg-green-500' : ($avgPct >= 50 ? 'bg-blue-500' : ($avgPct > 0 ? 'bg-yellow-500' : 'bg-gray-300'));
    $avgTextColor = $avgPct >= 100 ? 'text-green-600' : ($avgPct >= 50 ? 'text-blue-600' : 'text-gray-600');
    $avgBarWidth = min(100, $avgPct);
@endphp
<div class="mb-6 bg-white rounded-lg shadow-md p-6">
    <div class="flex items-center justify-between mb-2">
        <span class="text-sm font-semibold text-gray-700">Average Work Progress (All Completed Work)</span>
        <span class="text-sm font-bold {{ $avgTextColor }}">{{ number_format($avgPct, 1) }}%</span>
    </div>
    <div class="w-full bg-gray-200 rounded-full h-3">
        <div class="{{ $avgBarColor }} h-3 rounded-full transition-all duration-300" style="width: {{ $avgBarWidth }}%"></div>
    </div>
</div>
@endif

@if($companiesWithProjects->count() > 0)
    @foreach($companiesWithProjects as $companyGroup)
        <div class="mb-8" data-company-id="{{ $companyGroup['company_id'] ?? '' }}">
            <div class="mb-4 flex items-center">
                @if($companyGroup['company'] && $companyGroup['company']->logo)
                    <img src="{{ $companyGroup['company']->getLogoUrl() }}" alt="{{ $companyGroup['company_name'] }}" class="h-8 w-8 rounded-lg object-cover mr-3">
                @else
                    <div class="h-8 w-8 rounded-lg bg-indigo-100 flex items-center justify-center mr-3">
                        <span class="text-indigo-600 font-bold text-sm">{{ strtoupper(substr($companyGroup['company_name'], 0, 1)) }}</span>
                    </div>
                @endif
                <h2 class="text-lg md:text-xl lg:text-2xl font-bold text-gray-900">{{ $companyGroup['company_name'] }}</h2>
                <span class="ml-3 px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm font-medium">
                    {{ $companyGroup['projects']->count() }} {{ Str::plural('project', $companyGroup['projects']->count()) }}
                </span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                @foreach($companyGroup['projects'] as $project)
                    <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200 p-6" data-project-id="{{ $project->id }}">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h3 class="text-base md:text-lg font-semibold text-gray-900 mb-1">{{ $project->name }}</h3>
                                @php($color = $statusColors[$project->status] ?? 'bg-gray-100 text-gray-800')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $color }} whitespace-nowrap">
                                    {{ Str::headline($project->status) }}
                                </span>
                            </div>
                        </div>
                        @if($project->client_name)
                        <div class="mb-4">
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="bi bi-person me-2"></i>
                                <span>{{ $project->client_name }}</span>
                            </div>
                        </div>
                        @endif
                        @php($prog = $project->progress ?? ['progress_percent' => 0, 'total_completed_qty' => 0, 'total_boq_qty' => 0])
                        @php($pct = (float)($prog['progress_percent'] ?? 0))
                        @php($compQty = (float)($prog['total_completed_qty'] ?? 0))
                        @php($boqQty = (float)($prog['total_boq_qty'] ?? 0))
                        @php($barColor = $pct >= 100 ? 'bg-green-500' : ($pct >= 50 ? 'bg-blue-500' : ($pct > 0 ? 'bg-yellow-500' : 'bg-gray-300')))
                        @php($textColor = $pct >= 100 ? 'text-green-600' : ($pct >= 50 ? 'text-blue-600' : 'text-gray-600'))
                        @php($barWidth = min(100, $pct))
                        <div class="mb-4">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-medium text-gray-600">Work Progress</span>
                                <span class="text-xs font-semibold {{ $textColor }}">{{ number_format($pct, 1) }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="{{ $barColor }} h-2 rounded-full transition-all duration-300" style="width: {{ $barWidth }}%"></div>
                            </div>
                            <div class="flex items-center justify-between mt-1 text-xs text-gray-500">
                                <span>Completed: {{ number_format($compQty, 2) }}</span>
                                <span>Total: {{ number_format($boqQty, 2) }}</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-1 pt-4 border-t">
                            @if(Auth::user()->role === 'site_engineer')
                            <a href="{{ route('admin.projects.gallery', $project) }}" class="btn btn-sm btn-outline-success" title="Gallery">
                                <i class="bi bi-images"></i>
                            </a>
                            @else
                            <button onclick="openViewProjectModal({{ $project->id }})" class="btn btn-sm btn-outline-primary" title="View">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button onclick="openGalleryModal({{ $project->id }})" class="btn btn-sm btn-outline-success" title="Gallery">
                                <i class="bi bi-images"></i>
                            </button>
                            @endif
                            @if(Auth::user()->isAdmin())
                            <button onclick="openEditProjectModal({{ $project->id }})" class="btn btn-sm btn-outline-warning" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button onclick="showDeleteProjectConfirmation({{ $project->id }}, '{{ addslashes($project->name) }}')" class="btn btn-sm btn-outline-danger" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
@else
    <div class="bg-white shadow-lg rounded-lg p-12 text-center">
        <i class="bi bi-folder-x mx-auto text-6xl text-gray-400"></i>
        <h3 class="mt-4 text-lg font-medium text-gray-900">No projects found</h3>
        <p class="mt-2 text-sm text-gray-500">Get started by creating a new project.</p>
        <div class="mt-6">
            <button onclick="openCreateProjectModal()" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-200" title="Create New Project">
                <i class="bi bi-plus-lg"></i>
            </button>
        </div>
    </div>
@endif
