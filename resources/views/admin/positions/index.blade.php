@extends('admin.layout')

@section('title', 'Positions')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-3xl font-bold text-gray-900">Positions</h1>
    <button onclick="openCreatePositionModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center">
        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        <span class="position-btn-text">Add New Position</span>
    </button>
</div>

<div class="bg-white shadow-lg rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Salary Range</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Staff Count</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($positions as $position)
                    <tr data-position-id="{{ $position->id }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $position->name }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-500">{{ Str::limit($position->description, 50) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500">{{ $position->salary_range ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500">{{ $position->staff_count }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $position->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $position->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="d-flex gap-1 text-nowrap">
                                <button onclick="openViewPositionModal({{ $position->id }})" class="btn btn-outline-primary btn-sm" title="View">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button onclick="openEditPositionModal({{ $position->id }})" class="btn btn-outline-warning btn-sm" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button onclick="showDeletePositionConfirmation({{ $position->id }}, '{{ addslashes($position->name) }}')" class="btn btn-outline-danger btn-sm" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                            No positions found. <button onclick="openCreatePositionModal()" class="text-indigo-600 hover:text-indigo-900">Create one now</button>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<x-pagination :paginator="$positions" wrapper-class="mt-4" />

<!-- Delete Position Confirmation Modal -->
<div id="deletePositionConfirmationModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-2xl max-w-md w-full" onclick="event.stopPropagation()">
        <div class="p-6">
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 text-center mb-2">Delete Position</h3>
            <p class="text-gray-600 text-center mb-6">
                Are you sure you want to delete <span class="font-semibold text-gray-900" id="delete-position-name"></span>? This action cannot be undone.
            </p>
            <div class="flex space-x-3">
                <button onclick="closeDeletePositionConfirmation()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors font-medium">
                    Cancel
                </button>
                <button onclick="confirmDeletePosition()" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium">
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Position Modal -->
<div id="positionModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-6 border-b">
            <h3 class="text-xl font-semibold text-gray-900" id="position-modal-title">Add New Position</h3>
            <button onclick="closePositionModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6" id="position-modal-content">
            <form id="positionForm" onsubmit="submitPositionForm(event)">
                @csrf
                <input type="hidden" name="_method" id="position-method" value="POST">
                
                <div class="mb-4">
                    <label for="position-name" class="block text-sm font-medium text-gray-700 mb-2">Position Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="position-name" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           placeholder="e.g., Manager, Accountant, Sales Executive">
                    <div class="field-error text-red-600 text-sm mt-1" data-field="name" style="display: none;"></div>
                </div>

                <div class="mb-4">
                    <label for="position-description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" id="position-description" rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <div class="mb-4">
                    <label for="position-salary-range" class="block text-sm font-medium text-gray-700 mb-2">Salary Range</label>
                    <input type="text" name="salary_range" id="position-salary-range"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           placeholder="e.g., $30,000 - $50,000">
                </div>

                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" id="position-is-active" value="1" checked
                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-700">Active</span>
                    </label>
                </div>

                <div class="flex items-center justify-end space-x-4">
                    <button type="button" onclick="closePositionModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-200" id="position-submit-btn">
                        Create Position
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Position Modal -->
<div id="viewPositionModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-6 border-b">
            <h3 class="text-xl font-semibold text-gray-900">Position Details</h3>
            <button onclick="closeViewPositionModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6" id="view-position-content">
            <div class="flex items-center justify-center py-12">
                <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<style>
    @media (max-width: 768px) {
        .position-btn-text {
            display: none;
        }
    }
</style>

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
let currentPositionId = null;
let deletePositionId = null;

// Open Create Position Modal
function openCreatePositionModal() {
    currentPositionId = null;
    const modal = document.getElementById('positionModal');
    const title = document.getElementById('position-modal-title');
    const form = document.getElementById('positionForm');
    const methodInput = document.getElementById('position-method');
    const submitBtn = document.getElementById('position-submit-btn');
    
    modal.classList.remove('hidden');
    title.textContent = 'Add New Position';
    methodInput.value = 'POST';
    submitBtn.textContent = 'Create Position';
    form.reset();
    document.getElementById('position-is-active').checked = true;
    
    // Clear errors
    document.querySelectorAll('.field-error').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
}

// Open Edit Position Modal
function openEditPositionModal(positionId) {
    currentPositionId = positionId;
    const modal = document.getElementById('positionModal');
    const title = document.getElementById('position-modal-title');
    const form = document.getElementById('positionForm');
    const methodInput = document.getElementById('position-method');
    const submitBtn = document.getElementById('position-submit-btn');
    
    modal.classList.remove('hidden');
    title.textContent = 'Edit Position';
    methodInput.value = 'PUT';
    submitBtn.textContent = 'Update Position';
    
    // Clear errors
    document.querySelectorAll('.field-error').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
    
    // Load position data
    fetch(`/admin/positions/${positionId}/edit`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('position-name').value = data.position.name || '';
        document.getElementById('position-description').value = data.position.description || '';
        document.getElementById('position-salary-range').value = data.position.salary_range || '';
        document.getElementById('position-is-active').checked = data.position.is_active || false;
    })
    .catch(error => {
        console.error('Error loading position:', error);
        showNotification('Failed to load position data', 'error');
    });
}

// Submit Position Form
function submitPositionForm(e) {
    e.preventDefault();
    const form = e.target;
    const submitBtn = document.getElementById('position-submit-btn');
    const originalText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';
    
    const formData = new FormData(form);
    const url = currentPositionId 
        ? `/admin/positions/${currentPositionId}`
        : '/admin/positions';
    
    if (currentPositionId) {
        formData.append('_method', 'PUT');
    }
    
    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            closePositionModal();
            
            if (currentPositionId) {
                updatePositionRow(data.position);
            } else {
                addPositionRow(data.position);
            }
        } else {
            if (data.errors) {
                Object.keys(data.errors).forEach(field => {
                    const errorEl = document.querySelector(`.field-error[data-field="${field}"]`);
                    if (errorEl) {
                        errorEl.textContent = data.errors[field][0];
                        errorEl.style.display = 'block';
                    }
                });
            }
            showNotification(data.message || 'Validation failed', 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while saving', 'error');
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
}

// Close Position Modal
function closePositionModal() {
    document.getElementById('positionModal').classList.add('hidden');
    currentPositionId = null;
    document.getElementById('positionForm').reset();
}

// Add Position Row
function addPositionRow(position) {
    const tbody = document.querySelector('table tbody');
    const emptyRow = tbody.querySelector('tr td[colspan]');
    
    if (emptyRow) {
        emptyRow.closest('tr').remove();
    }
    
    const row = document.createElement('tr');
    row.setAttribute('data-position-id', position.id);
    row.innerHTML = `
        <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm font-medium text-gray-900">${position.name}</div>
        </td>
        <td class="px-6 py-4">
            <div class="text-sm text-gray-500">${(position.description || '').substring(0, 50)}</div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm text-gray-500">${position.salary_range || 'N/A'}</div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm text-gray-500">${position.staff_count || 0}</div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${position.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                ${position.is_active ? 'Active' : 'Inactive'}
            </span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
            <div class="d-flex gap-1 text-nowrap">
                <button onclick="openViewPositionModal(${position.id})" class="btn btn-outline-primary btn-sm" title="View">
                    <i class="bi bi-eye"></i>
                </button>
                <button onclick="openEditPositionModal(${position.id})" class="btn btn-outline-warning btn-sm" title="Edit">
                    <i class="bi bi-pencil"></i>
                </button>
                <button onclick="showDeletePositionConfirmation(${position.id}, '${(position.name || '').replace(/'/g, "\\'")}')" class="btn btn-outline-danger btn-sm" title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </td>
    `;
    
    tbody.insertBefore(row, tbody.firstChild);
}

// Update Position Row
function updatePositionRow(position) {
    const row = document.querySelector(`tr[data-position-id="${position.id}"]`);
    if (row) {
        row.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">${position.name}</div>
            </td>
            <td class="px-6 py-4">
                <div class="text-sm text-gray-500">${(position.description || '').substring(0, 50)}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-500">${position.salary_range || 'N/A'}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-500">${position.staff_count || 0}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${position.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                    ${position.is_active ? 'Active' : 'Inactive'}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="d-flex gap-1 text-nowrap">
                    <button onclick="openViewPositionModal(${position.id})" class="btn btn-outline-primary btn-sm" title="View">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button onclick="openEditPositionModal(${position.id})" class="btn btn-outline-warning btn-sm" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button onclick="showDeletePositionConfirmation(${position.id}, '${(position.name || '').replace(/'/g, "\\'")}')" class="btn btn-outline-danger btn-sm" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        `;
    }
}

// Open View Position Modal
function openViewPositionModal(positionId) {
    const modal = document.getElementById('viewPositionModal');
    const content = document.getElementById('view-position-content');
    
    modal.classList.remove('hidden');
    content.innerHTML = `
        <div class="flex items-center justify-center py-12">
            <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    `;
    
    fetch(`/admin/positions/${positionId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const pos = data.position;
        content.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white shadow-lg rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Position Information</h2>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Name</dt>
                            <dd class="mt-1 text-sm text-gray-900">${pos.name || ''}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Description</dt>
                            <dd class="mt-1 text-sm text-gray-900">${pos.description || 'N/A'}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Salary Range</dt>
                            <dd class="mt-1 text-sm text-gray-900">${pos.salary_range || 'N/A'}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${pos.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                                    ${pos.is_active ? 'Active' : 'Inactive'}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created At</dt>
                            <dd class="mt-1 text-sm text-gray-900">${pos.created_at || ''}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Updated At</dt>
                            <dd class="mt-1 text-sm text-gray-900">${pos.updated_at || ''}</dd>
                        </div>
                    </dl>
                </div>
                <div class="bg-white shadow-lg rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold text-gray-900">Staff Members</h2>
                        <span class="px-3 py-1 bg-indigo-100 text-indigo-800 rounded-full text-sm font-semibold">
                            ${pos.staff_count || 0} member(s)
                        </span>
                    </div>
                    ${pos.staff && pos.staff.length > 0 ? `
                        <ul class="space-y-2">
                            ${pos.staff.map(member => `
                                <li class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                    <div>
                                        <span class="font-medium text-gray-900">${member.name}</span>
                                        <p class="text-sm text-gray-500">${member.email}</p>
                                    </div>
                                </li>
                            `).join('')}
                        </ul>
                    ` : '<p class="text-sm text-gray-500">No staff members assigned to this position.</p>'}
                </div>
            </div>
            <div class="mt-4 flex justify-end gap-2">
                <button onclick="closeViewPositionModal(); openEditPositionModal(${pos.id})" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                    Edit
                </button>
                <button onclick="closeViewPositionModal()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    Close
                </button>
            </div>
        `;
    })
    .catch(error => {
        console.error('Error loading position:', error);
        content.innerHTML = `
            <div class="text-center py-12">
                <p class="text-red-600 mb-4">Failed to load position details</p>
                <button onclick="closeViewPositionModal()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    Close
                </button>
            </div>
        `;
    });
}

// Close View Position Modal
function closeViewPositionModal() {
    document.getElementById('viewPositionModal').classList.add('hidden');
}

// Delete Position Confirmation
function showDeletePositionConfirmation(positionId, positionName) {
    deletePositionId = positionId;
    document.getElementById('delete-position-name').textContent = positionName;
    document.getElementById('deletePositionConfirmationModal').classList.remove('hidden');
}

function closeDeletePositionConfirmation() {
    document.getElementById('deletePositionConfirmationModal').classList.add('hidden');
    deletePositionId = null;
}

function confirmDeletePosition() {
    if (!deletePositionId) return;
    
    const positionIdToDelete = deletePositionId;
    const row = document.querySelector(`tr[data-position-id="${positionIdToDelete}"]`);
    const deleteBtn = event.target;
    
    deleteBtn.disabled = true;
    deleteBtn.textContent = 'Deleting...';
    
    fetch(`/admin/positions/${positionIdToDelete}`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeDeletePositionConfirmation();
            showNotification(data.message, 'success');
            
            if (row) {
                row.style.transition = 'opacity 0.3s, transform 0.3s';
                row.style.opacity = '0';
                row.style.transform = 'translateX(-20px)';
                setTimeout(() => {
                    row.remove();
                    const tbody = document.querySelector('table tbody');
                    if (tbody && tbody.children.length === 0) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                    No positions found. <button onclick="openCreatePositionModal()" class="text-indigo-600 hover:text-indigo-900">Create one now</button>
                                </td>
                            </tr>
                        `;
                    }
                }, 300);
            }
        } else {
            showNotification(data.message || 'Failed to delete position', 'error');
            deleteBtn.disabled = false;
            deleteBtn.textContent = 'Delete';
        }
    })
    .catch(error => {
        console.error('Error deleting position:', error);
        showNotification('An error occurred while deleting', 'error');
        deleteBtn.disabled = false;
        deleteBtn.textContent = 'Delete';
    });
}

// Show Notification
function showNotification(message, type = 'success') {
    const notificationDiv = document.createElement('div');
    notificationDiv.className = `fixed top-4 right-4 px-6 py-4 rounded-lg shadow-2xl z-50 transition-all duration-300 flex items-center gap-3 min-w-[300px] max-w-[500px]`;
    
    if (type === 'success') {
        notificationDiv.className += ' bg-green-500 text-white';
    } else if (type === 'error') {
        notificationDiv.className += ' bg-red-500 text-white';
    } else if (type === 'warning') {
        notificationDiv.className += ' bg-yellow-500 text-white';
    } else {
        notificationDiv.className += ' bg-blue-500 text-white';
    }
    
    notificationDiv.innerHTML = `
        <span>${message}</span>
        <button onclick="this.parentElement.remove()" class="ml-2 text-white hover:text-gray-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    `;
    
    document.body.appendChild(notificationDiv);
    
    setTimeout(() => {
        notificationDiv.style.opacity = '0';
        setTimeout(() => notificationDiv.remove(), 300);
    }, 3000);
}

// Close modals on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        if (!document.getElementById('positionModal').classList.contains('hidden')) {
            closePositionModal();
        }
        if (!document.getElementById('viewPositionModal').classList.contains('hidden')) {
            closeViewPositionModal();
        }
        if (!document.getElementById('deletePositionConfirmationModal').classList.contains('hidden')) {
            closeDeletePositionConfirmation();
        }
    }
});

// Close modals when clicking outside
document.getElementById('positionModal').addEventListener('click', function(e) {
    if (e.target === this) closePositionModal();
});

document.getElementById('viewPositionModal').addEventListener('click', function(e) {
    if (e.target === this) closeViewPositionModal();
});

document.getElementById('deletePositionConfirmationModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeletePositionConfirmation();
});
</script>
@endpush
@endsection
