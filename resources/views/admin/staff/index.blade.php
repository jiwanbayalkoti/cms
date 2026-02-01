@extends('admin.layout')

@section('title', 'Staff Management')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-3xl font-bold text-gray-900">Staff Management</h1>
    <button onclick="openCreateStaffModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition duration-200">
        <i class="bi bi-plus-lg me-1"></i> <span class="staff-btn-text">Add New Staff</span>
    </button>
</div>

@if($projects->count() > 0)
<div class="mb-4 bg-white shadow-lg rounded-lg p-4">
    <form method="GET" action="{{ route('admin.staff.index') }}" class="flex gap-4 items-end">
        <div class="flex-1">
            <label for="project_id" class="block text-sm font-medium text-gray-700 mb-2">Filter by Project</label>
            <select name="project_id" id="project_id" onchange="this.form.submit()"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Projects</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                        {{ $project->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </form>
</div>
@endif

<div class="bg-white shadow-lg rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SN</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Join Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($staff as $member)
                    <tr data-staff-id="{{ $member->id }}">
                        <td class="px-6 py-4 whitespace-nowrap">{{ ($staff->currentPage() - 1) * $staff->perPage() + $loop->iteration }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $member->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500">{{ $member->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500">{{ $member->phone ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $member->project ? $member->project->name : 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $member->position ? $member->position->name : 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500">{{ $member->join_date ? $member->join_date->format('M d, Y') : 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $member->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $member->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="d-flex gap-1 text-nowrap">
                                <button onclick="openViewStaffModal({{ $member->id }})" class="btn btn-outline-primary btn-sm" title="View">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button onclick="openEditStaffModal({{ $member->id }})" class="btn btn-outline-warning btn-sm" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button onclick="showDeleteStaffConfirmation({{ $member->id }}, '{{ addslashes($member->name) }}')" class="btn btn-outline-danger btn-sm" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-6 py-4 text-center text-sm text-gray-500">
                            No staff members found. <button onclick="openCreateStaffModal()" class="text-indigo-600 hover:text-indigo-900">Add one now</button>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<x-pagination :paginator="$staff" wrapper-class="mt-4" />

<!-- Delete Confirmation Modal -->
<div id="deleteStaffConfirmationModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-2xl max-w-md w-full" onclick="event.stopPropagation()">
        <div class="p-6">
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 text-center mb-2">Delete Staff Member</h3>
            <p class="text-gray-600 text-center mb-6">
                Are you sure you want to delete <span class="font-semibold text-gray-900" id="delete-staff-name"></span>? This action cannot be undone.
            </p>
            <div class="flex space-x-3">
                <button onclick="closeDeleteStaffConfirmation()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors font-medium">
                    Cancel
                </button>
                <button onclick="confirmDeleteStaff()" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium">
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Modal -->
<div id="staffModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-6 border-b">
            <h3 class="text-xl font-semibold text-gray-900" id="staff-modal-title">Add New Staff Member</h3>
            <button onclick="closeStaffModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6">
            <form id="staffForm" onsubmit="submitStaffForm(event)" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_method" id="staff-method" value="POST">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="staff-project-id" class="block text-sm font-medium text-gray-700 mb-2">Project</label>
                        <select name="project_id" id="staff-project-id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select a project (optional)</option>
                        </select>
                        <div class="field-error text-red-600 text-sm mt-1" data-field="project_id" style="display: none;"></div>
                    </div>

                    <div>
                        <label for="staff-name" class="block text-sm font-medium text-gray-700 mb-2">Full Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="staff-name" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <div class="field-error text-red-600 text-sm mt-1" data-field="name" style="display: none;"></div>
                    </div>

                    <div>
                        <label for="staff-email" class="block text-sm font-medium text-gray-700 mb-2">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" id="staff-email" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <div class="field-error text-red-600 text-sm mt-1" data-field="email" style="display: none;"></div>
                    </div>

                    <div>
                        <label for="staff-phone" class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                        <input type="text" name="phone" id="staff-phone"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <div class="field-error text-red-600 text-sm mt-1" data-field="phone" style="display: none;"></div>
                    </div>

                    <div>
                        <label for="staff-position-id" class="block text-sm font-medium text-gray-700 mb-2">Position <span class="text-red-500">*</span></label>
                        <select name="position_id" id="staff-position-id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select a position</option>
                        </select>
                        <div class="field-error text-red-600 text-sm mt-1" data-field="position_id" style="display: none;"></div>
                        <p class="mt-1 text-xs text-gray-500">Don't see the position you need? <a href="{{ route('admin.positions.index') }}" target="_blank" class="text-indigo-600 hover:text-indigo-900">Create a new position</a></p>
                    </div>

                    <div>
                        <label for="staff-salary" class="block text-sm font-medium text-gray-700 mb-2">Salary</label>
                        <input type="number" name="salary" id="staff-salary" step="0.01" min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <div class="field-error text-red-600 text-sm mt-1" data-field="salary" style="display: none;"></div>
                    </div>

                    <div>
                        <label for="staff-marriage-status" class="block text-sm font-medium text-gray-700 mb-2">Marriage Status</label>
                        <select name="marriage_status" id="staff-marriage-status"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="single">Single</option>
                            <option value="married">Married</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">This will automatically set the Tax Assessment Type</p>
                    </div>

                    <div>
                        <label for="staff-join-date" class="block text-sm font-medium text-gray-700 mb-2">Join Date</label>
                        <input type="date" name="join_date" id="staff-join-date"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <div class="field-error text-red-600 text-sm mt-1" data-field="join_date" style="display: none;"></div>
                    </div>

                    <div class="md:col-span-2">
                        <label for="staff-address" class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                        <textarea name="address" id="staff-address" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                        <div class="field-error text-red-600 text-sm mt-1" data-field="address" style="display: none;"></div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_active" id="staff-is-active" value="1" checked
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-700">Active</span>
                        </label>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-4 mt-6">
                    <button type="button" onclick="closeStaffModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-200" id="staff-submit-btn">
                        Add Staff Member
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Modal -->
<div id="viewStaffModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-6 border-b">
            <h3 class="text-xl font-semibold text-gray-900">Staff Member Details</h3>
            <button onclick="closeViewStaffModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6" id="view-staff-content">
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
        .staff-btn-text {
            display: none;
        }
    }
</style>

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
let currentStaffId = null;
let deleteStaffId = null;

function openCreateStaffModal() {
    currentStaffId = null;
    const modal = document.getElementById('staffModal');
    const title = document.getElementById('staff-modal-title');
    const form = document.getElementById('staffForm');
    const methodInput = document.getElementById('staff-method');
    const submitBtn = document.getElementById('staff-submit-btn');
    
    modal.classList.remove('hidden');
    title.textContent = 'Add New Staff Member';
    methodInput.value = 'POST';
    submitBtn.textContent = 'Add Staff Member';
    form.reset();
    document.getElementById('staff-is-active').checked = true;
    
    document.querySelectorAll('.field-error').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
    
    // Load positions and projects
    fetch('/admin/staff/create', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const positionSelect = document.getElementById('staff-position-id');
        positionSelect.innerHTML = '<option value="">Select a position</option>';
        data.positions.forEach(pos => {
            const option = document.createElement('option');
            option.value = pos.id;
            option.textContent = pos.name;
            positionSelect.appendChild(option);
        });
        
        const projectSelect = document.getElementById('staff-project-id');
        projectSelect.innerHTML = '<option value="">Select a project (optional)</option>';
        data.projects.forEach(proj => {
            const option = document.createElement('option');
            option.value = proj.id;
            option.textContent = proj.name;
            projectSelect.appendChild(option);
        });
    })
    .catch(error => {
        console.error('Error loading form data:', error);
        showNotification('Failed to load form data', 'error');
    });
}

function openEditStaffModal(staffId) {
    currentStaffId = staffId;
    const modal = document.getElementById('staffModal');
    const title = document.getElementById('staff-modal-title');
    const form = document.getElementById('staffForm');
    const methodInput = document.getElementById('staff-method');
    const submitBtn = document.getElementById('staff-submit-btn');
    
    modal.classList.remove('hidden');
    title.textContent = 'Edit Staff Member';
    methodInput.value = 'PUT';
    submitBtn.textContent = 'Update Staff Member';
    
    document.querySelectorAll('.field-error').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
    
    // Load staff data
    fetch(`/admin/staff/${staffId}/edit`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const positionSelect = document.getElementById('staff-position-id');
        positionSelect.innerHTML = '<option value="">Select a position</option>';
        data.positions.forEach(pos => {
            const option = document.createElement('option');
            option.value = pos.id;
            option.textContent = pos.name;
            option.selected = pos.id == data.staff.position_id;
            positionSelect.appendChild(option);
        });
        
        const projectSelect = document.getElementById('staff-project-id');
        projectSelect.innerHTML = '<option value="">Select a project (optional)</option>';
        data.projects.forEach(proj => {
            const option = document.createElement('option');
            option.value = proj.id;
            option.textContent = proj.name;
            option.selected = proj.id == data.staff.project_id;
            projectSelect.appendChild(option);
        });
        
        document.getElementById('staff-name').value = data.staff.name || '';
        document.getElementById('staff-email').value = data.staff.email || '';
        document.getElementById('staff-phone').value = data.staff.phone || '';
        document.getElementById('staff-salary').value = data.staff.salary || '';
        document.getElementById('staff-marriage-status').value = data.staff.marriage_status || 'single';
        document.getElementById('staff-join-date').value = data.staff.join_date || '';
        document.getElementById('staff-address').value = data.staff.address || '';
        document.getElementById('staff-is-active').checked = data.staff.is_active || false;
    })
    .catch(error => {
        console.error('Error loading staff data:', error);
        showNotification('Failed to load staff data', 'error');
    });
}

function submitStaffForm(e) {
    e.preventDefault();
    const form = e.target;
    const submitBtn = document.getElementById('staff-submit-btn');
    const originalText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';
    
    const formData = new FormData(form);
    const url = currentStaffId 
        ? `/admin/staff/${currentStaffId}`
        : '/admin/staff';
    
    if (currentStaffId) {
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
            closeStaffModal();
            
            if (currentStaffId) {
                updateStaffRow(data.staff);
            } else {
                addStaffRow(data.staff);
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

function closeStaffModal() {
    document.getElementById('staffModal').classList.add('hidden');
    currentStaffId = null;
    document.getElementById('staffForm').reset();
}

function addStaffRow(staff) {
    const tbody = document.querySelector('table tbody');
    const emptyRow = tbody.querySelector('tr td[colspan]');
    
    if (emptyRow) {
        emptyRow.closest('tr').remove();
    }
    
    const row = document.createElement('tr');
    row.setAttribute('data-staff-id', staff.id);
    row.innerHTML = `
        <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm font-medium text-gray-900">${staff.name}</div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm text-gray-500">${staff.email}</div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm text-gray-500">${staff.phone || 'N/A'}</div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm text-gray-900">${staff.project_name || 'N/A'}</div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm font-medium text-gray-900">${staff.position_name || 'N/A'}</div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm text-gray-500">${staff.join_date || 'N/A'}</div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${staff.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                ${staff.is_active ? 'Active' : 'Inactive'}
            </span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
            <div class="d-flex gap-1 text-nowrap">
                <button onclick="openViewStaffModal(${staff.id})" class="btn btn-outline-primary btn-sm" title="View">
                    <i class="bi bi-eye"></i>
                </button>
                <button onclick="openEditStaffModal(${staff.id})" class="btn btn-outline-warning btn-sm" title="Edit">
                    <i class="bi bi-pencil"></i>
                </button>
                <button onclick="showDeleteStaffConfirmation(${staff.id}, '${(staff.name || '').replace(/'/g, "\\'")}')" class="btn btn-outline-danger btn-sm" title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </td>
    `;
    
    tbody.insertBefore(row, tbody.firstChild);
}

function updateStaffRow(staff) {
    const row = document.querySelector(`tr[data-staff-id="${staff.id}"]`);
    if (row) {
        row.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">${staff.name}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-500">${staff.email}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-500">${staff.phone || 'N/A'}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">${staff.project_name || 'N/A'}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">${staff.position_name || 'N/A'}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-500">${staff.join_date || 'N/A'}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${staff.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                    ${staff.is_active ? 'Active' : 'Inactive'}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="d-flex gap-1 text-nowrap">
                    <button onclick="openViewStaffModal(${staff.id})" class="btn btn-outline-primary btn-sm" title="View">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button onclick="openEditStaffModal(${staff.id})" class="btn btn-outline-warning btn-sm" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button onclick="showDeleteStaffConfirmation(${staff.id}, '${(staff.name || '').replace(/'/g, "\\'")}')" class="btn btn-outline-danger btn-sm" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        `;
    }
}

function openViewStaffModal(staffId) {
    const modal = document.getElementById('viewStaffModal');
    const content = document.getElementById('view-staff-content');
    
    modal.classList.remove('hidden');
    content.innerHTML = `
        <div class="flex items-center justify-center py-12">
            <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    `;
    
    fetch(`/admin/staff/${staffId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const s = data.staff;
        content.innerHTML = `
            <div class="space-y-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Name</dt>
                    <dd class="mt-1 text-sm text-gray-900">${s.name || ''}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                    <dd class="mt-1 text-sm text-gray-900">${s.email || ''}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Phone</dt>
                    <dd class="mt-1 text-sm text-gray-900">${s.phone || 'N/A'}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Address</dt>
                    <dd class="mt-1 text-sm text-gray-900">${s.address || 'N/A'}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Position</dt>
                    <dd class="mt-1 text-sm text-gray-900">${s.position_name || 'N/A'}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Project</dt>
                    <dd class="mt-1 text-sm text-gray-900">${s.project_name || 'N/A'}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Salary</dt>
                    <dd class="mt-1 text-sm text-gray-900">${s.salary ? parseFloat(s.salary).toLocaleString() : 'N/A'}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Marriage Status</dt>
                    <dd class="mt-1 text-sm text-gray-900">${s.marriage_status ? s.marriage_status.charAt(0).toUpperCase() + s.marriage_status.slice(1) : 'N/A'}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Join Date</dt>
                    <dd class="mt-1 text-sm text-gray-900">${s.join_date || 'N/A'}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                    <dd class="mt-1">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${s.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                            ${s.is_active ? 'Active' : 'Inactive'}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Created At</dt>
                    <dd class="mt-1 text-sm text-gray-900">${s.created_at || ''}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Updated At</dt>
                    <dd class="mt-1 text-sm text-gray-900">${s.updated_at || ''}</dd>
                </div>
            </div>
            <div class="mt-4 flex justify-end gap-2">
                <button onclick="closeViewStaffModal(); openEditStaffModal(${s.id})" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                    Edit
                </button>
                <button onclick="closeViewStaffModal()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    Close
                </button>
            </div>
        `;
    })
    .catch(error => {
        console.error('Error loading staff:', error);
        content.innerHTML = `
            <div class="text-center py-12">
                <p class="text-red-600 mb-4">Failed to load staff details</p>
                <button onclick="closeViewStaffModal()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    Close
                </button>
            </div>
        `;
    });
}

function closeViewStaffModal() {
    document.getElementById('viewStaffModal').classList.add('hidden');
}

function showDeleteStaffConfirmation(staffId, staffName) {
    deleteStaffId = staffId;
    document.getElementById('delete-staff-name').textContent = staffName;
    document.getElementById('deleteStaffConfirmationModal').classList.remove('hidden');
}

function closeDeleteStaffConfirmation() {
    document.getElementById('deleteStaffConfirmationModal').classList.add('hidden');
    deleteStaffId = null;
}

function confirmDeleteStaff() {
    if (!deleteStaffId) return;
    
    const staffIdToDelete = deleteStaffId;
    const row = document.querySelector(`tr[data-staff-id="${staffIdToDelete}"]`);
    const deleteBtn = event.target;
    
    deleteBtn.disabled = true;
    deleteBtn.textContent = 'Deleting...';
    
    fetch(`/admin/staff/${staffIdToDelete}`, {
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
            closeDeleteStaffConfirmation();
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
                                <td colspan="9" class="px-6 py-4 text-center text-sm text-gray-500">
                                    No staff members found. <button onclick="openCreateStaffModal()" class="text-indigo-600 hover:text-indigo-900">Add one now</button>
                                </td>
                            </tr>
                        `;
                    }
                }, 300);
            }
        } else {
            showNotification(data.message || 'Failed to delete staff member', 'error');
            deleteBtn.disabled = false;
            deleteBtn.textContent = 'Delete';
        }
    })
    .catch(error => {
        console.error('Error deleting staff:', error);
        showNotification('An error occurred while deleting', 'error');
        deleteBtn.disabled = false;
        deleteBtn.textContent = 'Delete';
    });
}

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

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        if (!document.getElementById('staffModal').classList.contains('hidden')) {
            closeStaffModal();
        }
        if (!document.getElementById('viewStaffModal').classList.contains('hidden')) {
            closeViewStaffModal();
        }
        if (!document.getElementById('deleteStaffConfirmationModal').classList.contains('hidden')) {
            closeDeleteStaffConfirmation();
        }
    }
});

document.getElementById('staffModal').addEventListener('click', function(e) {
    if (e.target === this) closeStaffModal();
});

document.getElementById('viewStaffModal').addEventListener('click', function(e) {
    if (e.target === this) closeViewStaffModal();
});

document.getElementById('deleteStaffConfirmationModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteStaffConfirmation();
});
</script>
@endpush
@endsection
