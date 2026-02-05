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

<style>
    /* Icon-only buttons styling */
    .project-add-btn {
        padding: 0.5rem;
        min-width: 40px;
        justify-content: center;
    }
</style>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-xl md:text-2xl lg:text-3xl font-bold text-gray-900">Projects</h1>
        <p class="text-gray-600">Track initiatives across your company</p>
    </div>
    @if(Auth::user()->isAdmin())
    <button onclick="openCreateProjectModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-lg transition duration-200 flex items-center project-add-btn" title="Add New Project">
        <i class="bi bi-plus-lg project-add-icon"></i>
    </button>
    @endif
</div>

@if($companiesWithProjects->count() > 0)
    @foreach($companiesWithProjects as $companyGroup)
        <!-- Company Section -->
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

            <!-- Projects Grid for this Company -->
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

<!-- Create/Edit Project Modal -->
<div id="projectModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-6 border-b">
            <h3 class="text-xl font-semibold text-gray-900" id="project-modal-title">Create New Project</h3>
            <button onclick="closeProjectModal()" class="text-gray-400 hover:text-gray-600">
                <i class="bi bi-x-lg text-xl"></i>
            </button>
        </div>
        <form id="projectForm" enctype="multipart/form-data" class="p-6">
            @csrf
            <input type="hidden" name="_method" id="project-form-method" value="POST">
            <input type="hidden" name="project_id" id="project-id" value="">
            <div id="project-form-errors" class="mb-4 hidden">
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                    <ul class="list-disc list-inside text-sm" id="project-error-list"></ul>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Project Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="project-name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                    <div class="field-error text-red-600 text-sm mt-1" data-field="name" style="display: none;"></div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Client / Stakeholder</label>
                    <input type="text" name="client_name" id="project-client-name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <div class="field-error text-red-600 text-sm mt-1" data-field="client_name" style="display: none;"></div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status <span class="text-red-500">*</span></label>
                    <select name="status" id="project-status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                        <!-- Options will be loaded dynamically -->
                    </select>
                    <div class="field-error text-red-600 text-sm mt-1" data-field="status" style="display: none;"></div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Budget (optional)</label>
                    <input type="number" step="0.01" name="budget" id="project-budget" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <div class="field-error text-red-600 text-sm mt-1" data-field="budget" style="display: none;"></div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                    <input type="date" name="start_date" id="project-start-date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <div class="field-error text-red-600 text-sm mt-1" data-field="start_date" style="display: none;"></div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                    <input type="date" name="end_date" id="project-end-date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <div class="field-error text-red-600 text-sm mt-1" data-field="end_date" style="display: none;"></div>
                </div>
            </div>

            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea name="description" id="project-description" rows="5" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                <div class="field-error text-red-600 text-sm mt-1" data-field="description" style="display: none;"></div>
            </div>

            <!-- Files Section -->
            <div class="mt-6">
                <div class="flex items-center justify-between mb-4">
                    <label class="block text-sm font-medium text-gray-700">Project Files</label>
                    <button type="button" onclick="addProjectFileField()" class="px-3 py-1.5 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition duration-200 flex items-center">
                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add File
                    </button>
                </div>
                <div id="project-fileFieldsContainer" class="space-y-4">
                    <!-- File fields will be added here dynamically -->
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end space-x-4">
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-200 flex items-center" id="project-submit-btn">
                    <span id="project-submit-text">Create Project</span>
                    <span id="project-submit-loader" class="hidden ml-2">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                </button>
                <button type="button" onclick="closeProjectModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- View Project Modal -->
<div id="viewProjectModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between z-10">
            <h2 class="text-2xl font-bold text-gray-900" id="view-project-title">Project Details</h2>
            <button onclick="closeViewProjectModal()" class="text-gray-400 hover:text-gray-600 transition duration-200">
                <i class="bi bi-x-lg text-xl"></i>
            </button>
        </div>
        
        <!-- Tabs -->
        <div class="border-b border-gray-200 px-6">
            <nav class="flex space-x-8" aria-label="Tabs">
                <button onclick="switchProjectTab('details')" id="project-tab-details" class="project-tab-button border-b-2 border-indigo-500 py-4 px-1 text-sm font-medium text-indigo-600">
                    Details
                </button>
                <button onclick="switchProjectTab('files')" id="project-tab-files" class="project-tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    Files
                </button>
            </nav>
        </div>
        
        <div class="flex-1 overflow-y-auto">
            <div class="p-6" id="view-project-content">
                <div class="flex items-center justify-center py-12">
                    <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>
            <div class="p-6 hidden" id="view-project-files">
                <div class="flex items-center justify-center py-12">
                    <p class="text-gray-500">Loading files...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Gallery Modal -->
<div id="galleryModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-7xl w-full max-h-[95vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between z-10">
            <h2 class="text-lg md:text-xl lg:text-2xl font-bold text-gray-900" id="gallery-modal-title">Project Gallery</h2>
            <button onclick="closeGalleryModal()" class="text-gray-400 hover:text-gray-600 transition duration-200">
                <i class="bi bi-x-lg text-xl"></i>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6" id="gallery-modal-content">
            <div class="flex items-center justify-center py-12">
                <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Delete Project Confirmation Modal -->
<div id="deleteProjectConfirmationModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-2xl max-w-md w-full" onclick="event.stopPropagation()">
        <div class="p-6">
            <!-- Icon -->
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full">
                <i class="bi bi-exclamation-triangle text-3xl text-red-600"></i>
            </div>
            
            <!-- Title -->
            <h3 class="text-xl font-semibold text-gray-900 text-center mb-2">Delete Project</h3>
            
            <!-- Message -->
            <p class="text-gray-600 text-center mb-6">
                Are you sure you want to delete <span class="font-semibold text-gray-900" id="delete-project-name"></span>? This action cannot be undone.
            </p>
            
            <!-- Buttons -->
            <div class="flex space-x-3">
                <button onclick="closeDeleteProjectConfirmation()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors font-medium">
                    Cancel
                </button>
                <form id="delete-project-form" method="POST" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Use window object to avoid redeclaration errors when scripts are executed multiple times
if (typeof window.csrfToken === 'undefined') {
    window.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
}
// Use window.csrfToken directly or create a local reference without const
var csrfToken = window.csrfToken;
let currentProjectId = null;
let projectFileFieldIndex = 0;

// Project Modal Functions
window.openCreateProjectModal = function() {
    currentProjectId = null;
    document.getElementById('project-modal-title').textContent = 'Create New Project';
    document.getElementById('project-form-method').value = 'POST';
    document.getElementById('project-id').value = '';
    document.getElementById('projectModal').classList.remove('hidden');
    document.getElementById('projectForm').reset();
    document.getElementById('project-form-errors').classList.add('hidden');
    document.getElementById('project-fileFieldsContainer').innerHTML = '';
    projectFileFieldIndex = 0;
    document.querySelectorAll('.field-error').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
    const submitBtn = document.getElementById('project-submit-btn');
    submitBtn.disabled = false;
    document.getElementById('project-submit-text').textContent = 'Create Project';
    
    // Load statuses
    fetch('{{ route("admin.projects.create") }}', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => {
                throw new Error(err.message || 'Failed to load statuses');
            });
        }
        return response.json();
    })
    .then(data => {
        console.log('Statuses loaded:', data);
        if (data.statuses && Array.isArray(data.statuses)) {
            const statusSelect = document.getElementById('project-status');
            if (statusSelect) {
                statusSelect.innerHTML = '';
                data.statuses.forEach(status => {
                    const option = document.createElement('option');
                    option.value = status;
                    option.textContent = status.charAt(0).toUpperCase() + status.slice(1).replace('_', ' ');
                    if (status === 'planned') option.selected = true;
                    statusSelect.appendChild(option);
                });
            }
        } else {
            console.error('Invalid statuses data:', data);
        }
    })
    .catch(error => {
        console.error('Error loading statuses:', error);
        showNotification('Failed to load project statuses. Please refresh the page.', 'error');
    });
    
    // Add initial file field
    addProjectFileField();
}

window.openEditProjectModal = function(projectId) {
    currentProjectId = projectId;
    document.getElementById('project-modal-title').textContent = 'Edit Project';
    document.getElementById('project-form-method').value = 'PUT';
    document.getElementById('project-id').value = projectId;
    
    // Fetch project data
    fetch(`/admin/projects/${projectId}/edit`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.project && data.statuses) {
            const project = data.project;
            document.getElementById('project-name').value = project.name || '';
            document.getElementById('project-client-name').value = project.client_name || '';
            document.getElementById('project-description').value = project.description || '';
            document.getElementById('project-budget').value = project.budget || '';
            document.getElementById('project-start-date').value = project.start_date || '';
            document.getElementById('project-end-date').value = project.end_date || '';
            
            // Load statuses
            const statusSelect = document.getElementById('project-status');
            statusSelect.innerHTML = '';
            data.statuses.forEach(status => {
                const option = document.createElement('option');
                option.value = status;
                option.textContent = status.charAt(0).toUpperCase() + status.slice(1).replace('_', ' ');
                if (status === project.status) option.selected = true;
                statusSelect.appendChild(option);
            });
            
            // Load files
            document.getElementById('project-fileFieldsContainer').innerHTML = '';
            projectFileFieldIndex = 0;
            if (project.files && project.files.length > 0) {
                project.files.forEach((file, index) => {
                    addProjectFileField(file.name || '', file.path || '', true, index);
                });
            } else {
                addProjectFileField();
            }
            
            document.getElementById('projectModal').classList.remove('hidden');
            document.getElementById('project-form-errors').classList.add('hidden');
            document.querySelectorAll('.field-error').forEach(el => {
                el.style.display = 'none';
                el.textContent = '';
            });
            const submitBtn = document.getElementById('project-submit-btn');
            submitBtn.disabled = false;
            document.getElementById('project-submit-text').textContent = 'Update Project';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Failed to load project data', 'error');
    });
}

function closeProjectModal() {
    document.getElementById('projectModal').classList.add('hidden');
    document.getElementById('projectForm').reset();
    document.getElementById('project-form-errors').classList.add('hidden');
    document.getElementById('project-fileFieldsContainer').innerHTML = '';
    projectFileFieldIndex = 0;
    document.querySelectorAll('.field-error').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
    const submitBtn = document.getElementById('project-submit-btn');
    submitBtn.disabled = false;
    document.getElementById('project-submit-text').textContent = 'Create Project';
    currentProjectId = null;
}

function addProjectFileField(fileName = '', filePath = '', isExisting = false, existingIndex = null) {
    const container = document.getElementById('project-fileFieldsContainer');
    const fieldId = `project-file-field-${projectFileFieldIndex++}`;
    
    const fileFieldHtml = `
        <div id="${fieldId}" class="flex items-center gap-4 p-4 border border-gray-300 rounded-lg bg-gray-50">
            <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">File Name</label>
                    <input type="text" name="file_names[]" value="${escapeHtml(fileName)}" placeholder="e.g., Agreement, Bid Doc, BOQ File" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    ${isExisting ? `<input type="hidden" name="existing_file_indices[]" value="${existingIndex}">` : ''}
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">File</label>
                    ${isExisting ? `
                        <div class="mb-2">
                            <a href="/storage/${escapeHtml(filePath)}" target="_blank" class="text-indigo-600 hover:text-indigo-800 text-sm flex items-center">
                                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                </svg>
                                View Current File
                            </a>
                        </div>
                        <input type="file" name="files[]" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                        <p class="text-xs text-gray-500 mt-1">Leave empty to keep current file, or upload a new one</p>
                        <label class="flex items-center mt-2">
                            <input type="checkbox" name="delete_files[]" value="${existingIndex}" class="mr-2">
                            <span class="text-sm text-red-600">Delete this file</span>
                        </label>
                    ` : `
                        <input type="file" name="files[]" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                    `}
                </div>
            </div>
            <button type="button" onclick="removeProjectFileField('${fieldId}')" class="px-3 py-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition duration-200">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', fileFieldHtml);
}

function removeProjectFileField(fieldId) {
    const field = document.getElementById(fieldId);
    if (field) {
        field.remove();
    }
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Form submission
document.getElementById('projectForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const formData = new FormData(form);
    const submitBtn = document.getElementById('project-submit-btn');
    const submitText = document.getElementById('project-submit-text');
    const submitLoader = document.getElementById('project-submit-loader');
    const isEdit = currentProjectId !== null;
    const url = isEdit ? `/admin/projects/${currentProjectId}` : '{{ route("admin.projects.store") }}';
    const method = isEdit ? 'POST' : 'POST';
    
    // Hide previous errors
    document.getElementById('project-form-errors').classList.add('hidden');
    document.querySelectorAll('.field-error').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
    
    submitBtn.disabled = true;
    submitText.textContent = isEdit ? 'Updating...' : 'Creating...';
    submitLoader.classList.remove('hidden');
    
    fetch(url, {
        method: method,
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => {
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            return response.text().then(html => {
                console.error('Server returned HTML instead of JSON:', html.substring(0, 500));
                throw new Error('Server returned HTML instead of JSON');
            });
        }
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success && data.project) {
            submitBtn.disabled = false;
            submitText.textContent = isEdit ? 'Update Project' : 'Create Project';
            submitLoader.classList.add('hidden');
            
            closeProjectModal();
            
            if (isEdit) {
                console.log('Updating project card:', data.project);
                showNotification('Project updated successfully!', 'success');
                // Reload page so list shows all updated data (including files)
                setTimeout(() => {
                    window.location.reload();
                }, 600);
            } else {
                console.log('Adding project card:', data.project);
                addProjectCard(data.project);
                setTimeout(() => {
                    showNotification('Project created successfully!', 'success');
                }, 100);
            }
        } else {
            // Handle validation errors
            if (data.errors) {
                const errorList = document.getElementById('project-error-list');
                errorList.innerHTML = '';
                document.getElementById('project-form-errors').classList.remove('hidden');
                
                Object.keys(data.errors).forEach(field => {
                    const errorMsg = Array.isArray(data.errors[field]) ? data.errors[field][0] : data.errors[field];
                    const errorEl = document.querySelector(`.field-error[data-field="${field}"]`);
                    if (errorEl) {
                        errorEl.textContent = errorMsg;
                        errorEl.style.display = 'block';
                    }
                    const li = document.createElement('li');
                    li.textContent = errorMsg;
                    errorList.appendChild(li);
                });
            } else {
                showNotification(data.message || (isEdit ? 'Failed to update project' : 'Failed to create project'), 'error');
            }
            submitBtn.disabled = false;
            submitText.textContent = isEdit ? 'Update Project' : 'Create Project';
            submitLoader.classList.add('hidden');
        }
    })
    .catch(error => {
        console.error('Error submitting form:', error);
        console.error('Error details:', {
            message: error.message,
            stack: error.stack
        });
        showNotification('An error occurred: ' + (error.message || 'Please try again'), 'error');
        submitBtn.disabled = false;
        submitText.textContent = isEdit ? 'Update Project' : 'Create Project';
        submitLoader.classList.add('hidden');
    });
});

// Function to add new project card
function addProjectCard(project) {
    console.log('addProjectCard called with:', project);
    
    if (!project || !project.id) {
        console.error('Invalid project data:', project);
        showNotification('Error: Invalid project data received', 'error');
        return;
    }
    
    const statusColors = {
        'planned': 'bg-gray-100 text-gray-800',
        'active': 'bg-green-100 text-green-800',
        'on_hold': 'bg-yellow-100 text-yellow-800',
        'completed': 'bg-blue-100 text-blue-800',
        'cancelled': 'bg-red-100 text-red-800',
    };
    
    const statusColor = statusColors[project.status] || 'bg-gray-100 text-gray-800';
    const statusText = project.status.charAt(0).toUpperCase() + project.status.slice(1).replace('_', ' ');
    
    // Create the card first
    const card = document.createElement('div');
    card.className = 'bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200 p-6';
    card.setAttribute('data-project-id', project.id);
    
    const userRole = '{{ Auth::user()->role ?? "user" }}';
    const isSiteEngineer = userRole === 'site_engineer';
    const isAdmin = userRole === 'admin' || userRole === 'super_admin';
    
    const clientNameHtml = project.client_name ? `
        <div class="mb-4">
            <div class="flex items-center text-sm text-gray-600">
                <i class="bi bi-person me-2"></i>
                <span>${escapeHtml(project.client_name)}</span>
            </div>
        </div>
    ` : '';
    
    let actionsHtml = '<div class="flex items-center justify-end gap-1 pt-4 border-t">';
    
    if (isSiteEngineer) {
        actionsHtml += `
            <a href="/admin/projects/${project.id}/gallery" class="btn btn-sm btn-outline-success" title="Gallery">
                <i class="bi bi-images"></i>
            </a>
        `;
    } else {
        actionsHtml += `
            <button onclick="openViewProjectModal(${project.id})" class="btn btn-sm btn-outline-primary" title="View">
                <i class="bi bi-eye"></i>
            </button>
            <button onclick="openGalleryModal(${project.id})" class="btn btn-sm btn-outline-success" title="Gallery">
                <i class="bi bi-images"></i>
            </button>
        `;
    }
    
    if (isAdmin) {
        actionsHtml += `
            <button onclick="openEditProjectModal(${project.id})" class="btn btn-sm btn-outline-warning" title="Edit">
                <i class="bi bi-pencil"></i>
            </button>
            <button onclick="showDeleteProjectConfirmation(${project.id}, '${escapeHtml(project.name)}')" class="btn btn-sm btn-outline-danger" title="Delete">
                <i class="bi bi-trash"></i>
            </button>
        `;
    }
    
    actionsHtml += '</div>';
    
    card.innerHTML = `
        <div class="flex items-start justify-between mb-4">
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">${escapeHtml(project.name)}</h3>
                <span class="px-2 py-1 text-xs font-semibold rounded-full ${statusColor} whitespace-nowrap">${statusText}</span>
            </div>
        </div>
        ${clientNameHtml}
        ${actionsHtml}
    `;
    
    // Find the company section or create new one
    const companyId = project.company_id || '';
    let companySection = document.querySelector(`[data-company-id="${companyId}"]`);
    
    console.log('Looking for company section with ID:', companyId);
    console.log('Found company section:', companySection);
    
    if (!companySection) {
        console.log('Company section not found, creating new one');
        // Create new company section
        const mainContent = document.querySelector('main') || document.querySelector('.content') || document.body;
        companySection = document.createElement('div');
        companySection.className = 'mb-8';
        companySection.setAttribute('data-company-id', companyId);
        companySection.innerHTML = `
            <div class="mb-4 flex items-center">
                <div class="h-8 w-8 rounded-lg bg-indigo-100 flex items-center justify-center mr-3">
                    <span class="text-indigo-600 font-bold text-sm">${escapeHtml((project.company?.name || 'P').charAt(0).toUpperCase())}</span>
                </div>
                <h2 class="text-2xl font-bold text-gray-900">${escapeHtml(project.company?.name || 'Projects')}</h2>
                <span class="ml-3 px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm font-medium">1 project</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 gap-6"></div>
        `;
        
        // Insert the company section
        const firstCompanySection = mainContent.querySelector('.mb-8[data-company-id]');
        if (firstCompanySection) {
            firstCompanySection.parentElement.insertBefore(companySection, firstCompanySection);
        } else {
            // Insert after the header section
            const headerSection = document.querySelector('.mb-6');
            if (headerSection && headerSection.nextElementSibling) {
                headerSection.parentElement.insertBefore(companySection, headerSection.nextElementSibling);
            } else {
                mainContent.appendChild(companySection);
            }
        }
    }
    
    // Get or create grid
    let grid = companySection.querySelector('.grid');
    if (!grid) {
        console.log('Grid not found in company section, creating new one');
        grid = document.createElement('div');
        grid.className = 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 gap-6';
        companySection.appendChild(grid);
    }
    
    console.log('Inserting card into grid');
    // Insert card at the beginning of the grid
    if (grid.firstChild) {
        grid.insertBefore(card, grid.firstChild);
    } else {
        grid.appendChild(card);
    }
    
    // Add fade-in animation
    card.style.opacity = '0';
    card.style.transform = 'translateY(-10px)';
    setTimeout(() => {
        card.style.transition = 'all 0.3s ease';
        card.style.opacity = '1';
        card.style.transform = 'translateY(0)';
    }, 10);
    
    // Update project count
    const countSpan = companySection.querySelector('.bg-gray-100');
    if (countSpan) {
        const countText = countSpan.textContent.trim();
        const match = countText.match(/(\d+)/);
        const currentCount = match ? parseInt(match[1]) : 0;
        countSpan.textContent = `${currentCount + 1} ${currentCount === 0 ? 'project' : 'projects'}`;
    }
    
    // Remove empty state if it exists
    const emptyState = document.querySelector('.bg-white.shadow-lg.rounded-lg.p-12.text-center');
    if (emptyState) {
        emptyState.remove();
    }
}

// Function to update existing project card
function updateProjectCard(project) {
    console.log('updateProjectCard called with:', project);
    
    if (!project || !project.id) {
        console.error('Invalid project data:', project);
        showNotification('Error: Invalid project data received', 'error');
        return;
    }
    
    const card = document.querySelector(`[data-project-id="${project.id}"]`);
    if (card) {
        const statusColors = {
            'planned': 'bg-gray-100 text-gray-800',
            'active': 'bg-green-100 text-green-800',
            'on_hold': 'bg-yellow-100 text-yellow-800',
            'completed': 'bg-blue-100 text-blue-800',
            'cancelled': 'bg-red-100 text-red-800',
        };
        
        const statusColor = statusColors[project.status] || 'bg-gray-100 text-gray-800';
        const statusText = project.status.charAt(0).toUpperCase() + project.status.slice(1).replace('_', ' ');
        
        const nameElement = card.querySelector('h3');
        const statusElement = card.querySelector('.rounded-full');
        
        if (nameElement) nameElement.textContent = project.name || '';
        if (statusElement) {
            statusElement.className = `px-2 py-1 text-xs font-semibold rounded-full ${statusColor} whitespace-nowrap`;
            statusElement.textContent = statusText;
        }
        
        // Update client name if exists
        const clientContainer = card.querySelector('.mb-4');
        if (project.client_name) {
            if (!clientContainer || !clientContainer.querySelector('.bi-person')) {
                const clientDiv = document.createElement('div');
                clientDiv.className = 'mb-4';
                clientDiv.innerHTML = `
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="bi bi-person me-2"></i>
                        <span>${escapeHtml(project.client_name)}</span>
                    </div>
                `;
                const nameContainer = card.querySelector('.flex-1');
                if (nameContainer && nameContainer.parentElement) {
                    nameContainer.parentElement.insertBefore(clientDiv, nameContainer.nextElementSibling);
                }
            } else {
                const clientSpan = clientContainer.querySelector('span');
                if (clientSpan) clientSpan.textContent = project.client_name;
            }
        } else if (clientContainer && clientContainer.querySelector('.bi-person')) {
            clientContainer.remove();
        }
        
        // Add highlight animation
        card.style.backgroundColor = '#fef3c7';
        setTimeout(() => {
            card.style.transition = 'background-color 0.5s ease';
            card.style.backgroundColor = '';
        }, 10);
        setTimeout(() => {
            card.style.backgroundColor = '';
        }, 2000);
    }
}

// Function to show notification
function showNotification(message, type = 'success') {
    // Create a temporary notification element
    const notificationDiv = document.createElement('div');
    notificationDiv.className = `fixed top-4 right-4 px-6 py-4 rounded-lg shadow-2xl z-50 transition-all duration-300 flex items-center gap-3 min-w-[300px] max-w-[500px]`;
    
    // Set colors based on type
    if (type === 'success') {
        notificationDiv.className += ' bg-green-500 text-white';
    } else if (type === 'error') {
        notificationDiv.className += ' bg-red-500 text-white';
    } else if (type === 'warning') {
        notificationDiv.className += ' bg-yellow-500 text-white';
    } else {
        notificationDiv.className += ' bg-blue-500 text-white';
    }
    
    // Add icon
    const iconSvg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    iconSvg.setAttribute('class', 'w-6 h-6 flex-shrink-0');
    iconSvg.setAttribute('fill', 'none');
    iconSvg.setAttribute('stroke', 'currentColor');
    iconSvg.setAttribute('viewBox', '0 0 24 24');
    
    const iconPath = document.createElementNS('http://www.w3.org/2000/svg', 'path');
    if (type === 'success') {
        iconPath.setAttribute('stroke-linecap', 'round');
        iconPath.setAttribute('stroke-linejoin', 'round');
        iconPath.setAttribute('stroke-width', '2');
        iconPath.setAttribute('d', 'M5 13l4 4L19 7');
    } else if (type === 'error') {
        iconPath.setAttribute('stroke-linecap', 'round');
        iconPath.setAttribute('stroke-linejoin', 'round');
        iconPath.setAttribute('stroke-width', '2');
        iconPath.setAttribute('d', 'M6 18L18 6M6 6l12 12');
    } else {
        iconPath.setAttribute('stroke-linecap', 'round');
        iconPath.setAttribute('stroke-linejoin', 'round');
        iconPath.setAttribute('stroke-width', '2');
        iconPath.setAttribute('d', 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z');
    }
    iconSvg.appendChild(iconPath);
    
    // Add message text
    const messageText = document.createElement('span');
    messageText.className = 'flex-1';
    messageText.textContent = message;
    
    // Add close button
    const closeBtn = document.createElement('button');
    closeBtn.className = 'ml-2 text-white hover:text-gray-200 transition-colors';
    closeBtn.innerHTML = `
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
    `;
    closeBtn.onclick = () => {
        notificationDiv.style.opacity = '0';
        notificationDiv.style.transform = 'translateX(100%)';
        setTimeout(() => notificationDiv.remove(), 300);
    };
    
    notificationDiv.appendChild(iconSvg);
    notificationDiv.appendChild(messageText);
    notificationDiv.appendChild(closeBtn);
    
    // Initial state (hidden, slide from right)
    notificationDiv.style.opacity = '0';
    notificationDiv.style.transform = 'translateX(100%)';
    document.body.appendChild(notificationDiv);
    
    // Animate in
    setTimeout(() => {
        notificationDiv.style.opacity = '1';
        notificationDiv.style.transform = 'translateX(0)';
    }, 10);
    
    // Auto remove after 5 seconds (or 3 seconds for success)
    const autoRemoveTime = type === 'success' ? 3000 : 5000;
    setTimeout(() => {
        notificationDiv.style.opacity = '0';
        notificationDiv.style.transform = 'translateX(100%)';
        setTimeout(() => {
            notificationDiv.remove();
        }, 300);
    }, autoRemoveTime);
}

// View Project Modal Functions
window.openViewProjectModal = function(projectId) {
    const modal = document.getElementById('viewProjectModal');
    const content = document.getElementById('view-project-content');
    const filesContent = document.getElementById('view-project-files');
    const title = document.getElementById('view-project-title');
    
    modal.classList.remove('hidden');
    title.textContent = 'Project Details';
    
    // Reset tabs to details
    switchProjectTab('details');
    
    // Show loading state
    content.innerHTML = `
        <div class="flex items-center justify-center py-12">
            <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    `;
    filesContent.innerHTML = `
        <div class="flex items-center justify-center py-12">
            <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    `;
    
    fetch(`/admin/projects/${projectId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => {
                throw new Error(err.error || 'Failed to load project details');
            });
        }
        return response.json();
    })
    .then(data => {
        const project = data.project;
        const isAdmin = {{ Auth::user()->isAdmin() ? 'true' : 'false' }};
        
        // Render files tab
        const filesContent = document.getElementById('view-project-files');
        if (project.files && project.files.length > 0) {
            filesContent.innerHTML = `
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Project Files</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        ${project.files.map(file => `
                            <div class="border rounded-lg p-4 bg-gray-50 hover:bg-gray-100 transition duration-200">
                                <div class="flex items-start justify-between mb-2">
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-gray-900">${escapeHtml(file.name || 'Document')}</h3>
                                        <p class="text-sm text-gray-500 mt-1">${escapeHtml(file.original_name || '')}</p>
                                        ${file.size ? `<p class="text-xs text-gray-400 mt-1">${(file.size / 1024).toFixed(2)} KB</p>` : ''}
                                    </div>
                                    <i class="bi bi-file-earmark text-2xl text-indigo-500 flex-shrink-0"></i>
                                </div>
                                <a href="${file.url || '#'}" target="_blank" 
                                   class="mt-3 inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800">
                                    <i class="bi bi-download me-1"></i>
                                    Download File
                                </a>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
        } else {
            filesContent.innerHTML = `
                <div class="text-center py-12">
                    <i class="bi bi-folder-x text-6xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500 text-lg">No files uploaded for this project</p>
                </div>
            `;
        }
        
        // Render details tab
        const companyCol = project.show_company ? '<div class="border rounded-lg p-4"><p class="text-sm text-gray-500">Company</p><p class="mt-2 text-gray-900">' + escapeHtml(project.company_name) + '</p></div>' : '';
        const gridCols = project.show_company ? 'md:grid-cols-4' : 'md:grid-cols-3';
        
        content.innerHTML = `
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">${escapeHtml(project.name)}</h1>
                    <p class="text-gray-600">${escapeHtml(project.client_name || 'Internal project')}</p>
                </div>
                ${isAdmin ? `
                    <button onclick="closeViewProjectModal(); openEditProjectModal(${project.id});" 
                        class="px-3 py-1.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-200 text-sm" title="Edit Project">
                        <i class="bi bi-pencil"></i>
                    </button>
                ` : ''}
            </div>
            
            <div class="bg-white shadow-lg rounded-lg p-6 space-y-6">
                <div class="grid grid-cols-1 ${gridCols} gap-6">
                    <div class="border rounded-lg p-4">
                        <p class="text-sm text-gray-500">Status</p>
                        <span class="mt-2 inline-block px-3 py-1 rounded-full text-xs font-semibold ${project.status_color}">
                            ${escapeHtml(project.status_text)}
                        </span>
                    </div>
                    ${companyCol}
                    <div class="border rounded-lg p-4">
                        <p class="text-sm text-gray-500">Budget</p>
                        <p class="mt-2 text-xl font-semibold text-gray-900">${escapeHtml(project.budget_formatted)}</p>
                    </div>
                    <div class="border rounded-lg p-4">
                        <p class="text-sm text-gray-500">Timeline</p>
                        <p class="mt-2 text-gray-900">${escapeHtml(project.timeline)}</p>
                    </div>
                </div>
                
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 mb-2">Description</h2>
                    <p class="text-gray-700 whitespace-pre-line">${escapeHtml(project.description || 'No description provided.')}</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="border rounded-lg p-4">
                        <p class="text-sm text-gray-500">Created by</p>
                        <p class="mt-1 text-gray-900">${escapeHtml(project.created_by)}</p>
                        <p class="text-sm text-gray-500">${escapeHtml(project.created_at)}</p>
                    </div>
                    <div class="border rounded-lg p-4">
                        <p class="text-sm text-gray-500">Last updated</p>
                        <p class="mt-1 text-gray-900">${escapeHtml(project.updated_by)}</p>
                        <p class="text-sm text-gray-500">${escapeHtml(project.updated_at)}</p>
                    </div>
                </div>
                
                <div class="flex items-center justify-between">
                    <button onclick="closeViewProjectModal()" class="text-indigo-600 hover:text-indigo-900">Close</button>
                    ${isAdmin ? `
                        <button onclick="closeViewProjectModal(); showDeleteProjectConfirmation(${project.id}, '${escapeHtml(project.name)}');" class="text-red-600 hover:text-red-900" title="Delete Project">
                            <i class="bi bi-trash"></i>
                        </button>
                    ` : ''}
                </div>
            </div>
        `;
        
        // Show details tab by default
        switchProjectTab('details');
    })
    .catch(error => {
        content.innerHTML = `
            <div class="text-center py-12">
                <p class="text-red-600 mb-4">${escapeHtml(error.message || 'Failed to load project details')}</p>
                <button onclick="closeViewProjectModal()" class="px-3 py-1.5 bg-gray-600 text-white rounded-lg hover:bg-gray-700 text-sm">
                    Close
                </button>
            </div>
        `;
    });
}

window.closeViewProjectModal = function() {
    document.getElementById('viewProjectModal').classList.add('hidden');
}

// Function to switch between tabs
function switchProjectTab(tab) {
    const detailsTab = document.getElementById('project-tab-details');
    const filesTab = document.getElementById('project-tab-files');
    const detailsContent = document.getElementById('view-project-content');
    const filesContent = document.getElementById('view-project-files');
    
    if (tab === 'details') {
        // Activate details tab
        detailsTab.classList.add('border-indigo-500', 'text-indigo-600');
        detailsTab.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
        filesTab.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
        filesTab.classList.remove('border-indigo-500', 'text-indigo-600');
        
        // Show details content
        detailsContent.classList.remove('hidden');
        filesContent.classList.add('hidden');
    } else if (tab === 'files') {
        // Activate files tab
        filesTab.classList.add('border-indigo-500', 'text-indigo-600');
        filesTab.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
        detailsTab.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
        detailsTab.classList.remove('border-indigo-500', 'text-indigo-600');
        
        // Show files content
        filesContent.classList.remove('hidden');
        detailsContent.classList.add('hidden');
    }
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        if (!document.getElementById('projectModal').classList.contains('hidden')) {
            closeProjectModal();
        }
        if (!document.getElementById('viewProjectModal').classList.contains('hidden')) {
            closeViewProjectModal();
        }
        if (!document.getElementById('deleteProjectConfirmationModal').classList.contains('hidden')) {
            closeDeleteProjectConfirmation();
        }
    }
});

// Close modal when clicking outside
document.getElementById('projectModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeProjectModal();
    }
});

document.getElementById('viewProjectModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeViewProjectModal();
    }
});

// Delete Project Confirmation Functions
let deleteProjectId = null;

window.showDeleteProjectConfirmation = function(projectId, projectName) {
    deleteProjectId = projectId;
    document.getElementById('delete-project-name').textContent = projectName;
    document.getElementById('delete-project-form').action = `/admin/projects/${projectId}`;
    document.getElementById('deleteProjectConfirmationModal').classList.remove('hidden');
}

window.closeDeleteProjectConfirmation = function() {
    document.getElementById('deleteProjectConfirmationModal').classList.add('hidden');
    deleteProjectId = null;
}

function deleteProjectCard(projectId) {
    // Find the project card
    const card = document.querySelector(`[data-project-id="${projectId}"]`);
    if (card) {
        // Animate out
        card.style.transition = 'all 0.3s ease';
        card.style.opacity = '0';
        card.style.transform = 'scale(0.9)';
        
        setTimeout(() => {
            // Update project count before removing
            const companySection = card.closest('.mb-8[data-company-id]');
            if (companySection) {
                const countSpan = companySection.querySelector('.bg-gray-100');
                if (countSpan) {
                    const countText = countSpan.textContent.trim();
                    const match = countText.match(/(\d+)/);
                    const currentCount = match ? parseInt(match[1]) : 1;
                    const newCount = currentCount - 1;
                    if (newCount > 0) {
                        countSpan.textContent = `${newCount} ${newCount === 1 ? 'project' : 'projects'}`;
                    }
                }
            }
            
            card.remove();
            
            // Check if the grid is empty and remove the company section if needed
            const grid = card.closest('.grid');
            if (grid && grid.children.length === 0) {
                const companySection = grid.closest('.mb-8');
                if (companySection) {
                    companySection.remove();
                }
            }
            
            // Check if all projects are gone
            const mainContent = document.querySelector('main') || document.querySelector('.content');
            const companySections = mainContent.querySelectorAll('.mb-8[data-company-id]');
            if (companySections.length === 0) {
                // Show empty state
                const emptyState = document.querySelector('.bg-white.shadow-lg.rounded-lg.p-12.text-center');
                if (!emptyState) {
                    const emptyDiv = document.createElement('div');
                    emptyDiv.className = 'bg-white shadow-lg rounded-lg p-12 text-center';
                    emptyDiv.innerHTML = `
                        <i class="bi bi-folder-x mx-auto text-6xl text-gray-400"></i>
                        <p class="mt-4 text-gray-600">No projects found.</p>
                    `;
                    const headerSection = document.querySelector('.mb-6');
                    if (headerSection && headerSection.nextElementSibling) {
                        headerSection.parentElement.insertBefore(emptyDiv, headerSection.nextElementSibling);
                    } else {
                        mainContent.appendChild(emptyDiv);
                    }
                }
            }
        }, 300);
    } else {
        // If card not found, reload the page
        window.location.reload();
    }
}

// Handle delete form submission
document.getElementById('delete-project-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Deleting...';
    
    const projectIdToDelete = deleteProjectId;
    
    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: new FormData(form)
    })
    .then(response => {
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            if (response.ok) {
                return { success: true };
            } else {
                return response.text().then(html => {
                    throw new Error('Server returned HTML instead of JSON');
                });
            }
        }
    })
    .then(data => {
        if (data.success) {
            closeDeleteProjectConfirmation();
            deleteProjectCard(projectIdToDelete);
            showNotification('Project deleted successfully!', 'success');
        } else {
            showNotification(data.message || 'Failed to delete project', 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while deleting the project: ' + error.message, 'error');
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
});

// Close delete modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        if (!document.getElementById('deleteProjectConfirmationModal').classList.contains('hidden')) {
            closeDeleteProjectConfirmation();
        }
    }
});

// Close delete modal when clicking outside
document.getElementById('deleteProjectConfirmationModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteProjectConfirmation();
    }
});

// Gallery Modal Functions
let currentGalleryProjectId = null;

// Make functions globally accessible
window.openGalleryModal = function(projectId) {
    currentGalleryProjectId = projectId;
    const modal = document.getElementById('galleryModal');
    const content = document.getElementById('gallery-modal-content');
    const title = document.getElementById('gallery-modal-title');
    
    modal.classList.remove('hidden');
    title.textContent = 'Project Gallery';
    content.innerHTML = `
        <div class="flex items-center justify-center py-12">
            <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    `;
    
    // Fetch gallery content via AJAX (returns JSON with HTML)
    fetch(`/admin/projects/${projectId}/gallery`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => {
                throw new Error(err.error || 'Failed to load gallery');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.html) {
            title.textContent = `${data.project.name} - Photo Gallery`;
            
            // Create a temporary container to parse and execute scripts
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = data.html;
            
            // Extract and execute scripts first
            const scripts = tempDiv.querySelectorAll('script');
            scripts.forEach(oldScript => {
                const newScript = document.createElement('script');
                if (oldScript.src) {
                    newScript.src = oldScript.src;
                } else {
                    newScript.textContent = oldScript.textContent;
                }
                document.body.appendChild(newScript);
            });
            
            // Remove scripts from HTML before injecting
            scripts.forEach(script => script.remove());
            
            // Inject the HTML content
            content.innerHTML = tempDiv.innerHTML;
            
            // Initialize photo collection for lightbox after a short delay
            setTimeout(() => {
                if (typeof initializePhotoCollection === 'function') {
                    initializePhotoCollection();
                }
                // Open first album by default
                const firstIcon = document.getElementById('toggle-icon-0');
                if (firstIcon && typeof toggleAlbum === 'function') {
                    toggleAlbum(0);
                }
            }, 100);
        } else {
            throw new Error('No gallery content received');
        }
    })
    .catch(error => {
        console.error('Error loading gallery:', error);
        content.innerHTML = `
            <div class="text-center py-12">
                <p class="text-red-600 mb-4">${escapeHtml(error.message || 'Failed to load gallery')}</p>
                <button onclick="closeGalleryModal()" class="px-3 py-1.5 bg-gray-600 text-white rounded-lg hover:bg-gray-700 text-sm">
                    Close
                </button>
            </div>
        `;
    });
};

window.closeGalleryModal = function() {
    document.getElementById('galleryModal').classList.add('hidden');
    currentGalleryProjectId = null;
    setTimeout(() => {
        document.getElementById('gallery-modal-content').innerHTML = `
            <div class="flex items-center justify-center py-12">
                <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        `;
    }, 300);
};;

// Close gallery modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        if (!document.getElementById('galleryModal').classList.contains('hidden')) {
            window.closeGalleryModal();
        }
    }
});

// Close gallery modal when clicking outside
document.getElementById('galleryModal').addEventListener('click', function(e) {
    if (e.target === this) {
        window.closeGalleryModal();
    }
});
</script>
@endpush

@endsection
