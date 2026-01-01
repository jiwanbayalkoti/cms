@extends('admin.layout')

@section('title', 'Users')

@section('content')
<div class="flex justify-between mb-4">
  <h1 class="text-2xl font-bold">Users</h1>
  <button onclick="openCreateUserModal()" class="bg-indigo-600 text-white px-4 py-2 rounded">
    <i class="bi bi-plus-lg me-1"></i> <span class="user-btn-text">New User</span>
  </button>
</div>

<div class="bg-white rounded shadow overflow-hidden">
  <div class="overflow-x-auto">
    <table class="min-w-full">
      <thead>
        <tr class="bg-gray-50 text-left">
          <th class="px-4 py-2">Name</th>
          <th class="px-4 py-2">Email</th>
          <th class="px-4 py-2">Company</th>
          <th class="px-4 py-2">Role</th>
          <th class="px-4 py-2 text-nowrap">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($users as $user)
          <tr class="border-t" data-user-id="{{ $user->id }}">
            <td class="px-4 py-2">{{ $user->name }}</td>
            <td class="px-4 py-2">{{ $user->email }}</td>
            <td class="px-4 py-2">{{ optional($user->company)->name ?: '-' }}</td>
            <td class="px-4 py-2">
              <span class="px-2 py-1 rounded text-xs font-semibold
                @if($user->role === 'super_admin') bg-purple-100 text-purple-800
                @elseif($user->role === 'admin') bg-blue-100 text-blue-800
                @else bg-gray-100 text-gray-800
                @endif">
                {{ ucfirst(str_replace('_', ' ', $user->role)) }}
              </span>
            </td>
            <td class="px-4 py-2 whitespace-nowrap">
              <div class="d-flex gap-1 text-nowrap">
                @php
                  $currentUser = auth()->user();
                  $canEdit = $currentUser->isSuperAdmin() || 
                            ($currentUser->isAdmin() && !$user->isSuperAdmin() && $user->company_id == $currentUser->company_id);
                  $canDelete = $currentUser->isSuperAdmin() || 
                              ($currentUser->isAdmin() && !$user->isSuperAdmin() && $user->company_id == $currentUser->company_id);
                @endphp
                @if($canEdit)
                  <button onclick="openEditUserModal({{ $user->id }})" class="btn btn-sm btn-outline-warning" title="Edit">
                    <i class="bi bi-pencil"></i>
                  </button>
                @else
                  <button class="btn btn-sm btn-outline-warning" disabled title="Edit">
                    <i class="bi bi-pencil"></i>
                  </button>
                @endif
                @if($canDelete && $currentUser->id !== $user->id)
                  <button onclick="showDeleteUserConfirmation({{ $user->id }}, '{{ addslashes($user->name) }}')" class="btn btn-sm btn-outline-danger" title="Delete">
                    <i class="bi bi-trash"></i>
                  </button>
                @else
                  <button class="btn btn-sm btn-outline-danger" disabled title="Delete">
                    <i class="bi bi-trash"></i>
                  </button>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="px-4 py-6 text-center text-gray-500">No users found.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <x-pagination :paginator="$users" wrapper-class="p-4" />
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteUserConfirmationModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-2xl max-w-md w-full" onclick="event.stopPropagation()">
        <div class="p-6">
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 text-center mb-2">Delete User</h3>
            <p class="text-gray-600 text-center mb-6">
                Are you sure you want to delete <span class="font-semibold text-gray-900" id="delete-user-name"></span>? This action cannot be undone.
            </p>
            <div class="flex space-x-3">
                <button onclick="closeDeleteUserConfirmation()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors font-medium">
                    Cancel
                </button>
                <button onclick="confirmDeleteUser()" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium">
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Modal -->
<div id="userModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-6 border-b">
            <h3 class="text-xl font-semibold text-gray-900" id="user-modal-title">Create User</h3>
            <button onclick="closeUserModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6">
            <form id="userForm" onsubmit="submitUserForm(event)">
                @csrf
                <input type="hidden" name="_method" id="user-method" value="POST">
                
                <div class="mb-4">
                    <label for="user-name" class="block text-sm font-medium text-gray-700 mb-2">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="user-name" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <div class="field-error text-red-600 text-sm mt-1" data-field="name" style="display: none;"></div>
                </div>

                <div class="mb-4">
                    <label for="user-email" class="block text-sm font-medium text-gray-700 mb-2">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" id="user-email" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <div class="field-error text-red-600 text-sm mt-1" data-field="email" style="display: none;"></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="user-password" class="block text-sm font-medium text-gray-700 mb-2">
                            <span id="user-password-label">Password</span> <span class="text-red-500" id="user-password-required">*</span>
                        </label>
                        <input type="password" name="password" id="user-password"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <div class="field-error text-red-600 text-sm mt-1" data-field="password" style="display: none;"></div>
                    </div>
                    <div>
                        <label for="user-password-confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                            <span id="user-password-confirmation-label">Confirm Password</span> <span class="text-red-500" id="user-password-confirmation-required">*</span>
                        </label>
                        <input type="password" name="password_confirmation" id="user-password-confirmation"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <div class="field-error text-red-600 text-sm mt-1" data-field="password_confirmation" style="display: none;"></div>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="user-company-id" class="block text-sm font-medium text-gray-700 mb-2">Company</label>
                    <select name="company_id" id="user-company-id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">None</option>
                    </select>
                    <div class="field-error text-red-600 text-sm mt-1" data-field="company_id" style="display: none;"></div>
                </div>

                <div class="mb-4" id="user-project-access-container" style="display: none;">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Project Access (optional)</label>
                    <p class="text-xs text-gray-500 mb-2">Check one or more projects for this user. Leave all unchecked to allow all projects in the selected company.</p>
                    <div id="user-project-checkboxes" class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-48 overflow-auto border rounded p-3">
                    </div>
                    <input type="hidden" name="project_ids_sent" value="1">
                    <div class="field-error text-red-600 text-sm mt-1" data-field="project_ids" style="display: none;"></div>
                </div>

                <div class="mb-4">
                    <label for="user-role" class="block text-sm font-medium text-gray-700 mb-2">Role <span class="text-red-500">*</span></label>
                    <select name="role" id="user-role" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </select>
                    <p class="text-gray-500 text-xs mt-1" id="user-role-help"></p>
                    <div class="field-error text-red-600 text-sm mt-1" data-field="role" style="display: none;"></div>
                </div>

                <div class="mb-6">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="is_admin" id="user-is-admin" value="1"
                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-700">Legacy is_admin (keep compatibility)</span>
                    </label>
                </div>

                <div class="flex space-x-2">
                    <button type="button" onclick="closeUserModal()" class="px-4 py-2 rounded border">Cancel</button>
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded" id="user-submit-btn">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    @media (max-width: 768px) {
        .user-btn-text {
            display: none;
        }
    }
</style>

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
let currentUserId = null;
const currentAuthUserId = {{ auth()->user()->id ?? 'null' }};
let deleteUserId = null;
let allProjects = [];
let selectedProjects = new Set();
let currentUserIsSuperAdmin = {{ auth()->user()->isSuperAdmin() ? 'true' : 'false' }};
let currentUserCompanyId = {{ auth()->user()->company_id ?? 'null' }};

function openCreateUserModal() {
    currentUserId = null;
    const modal = document.getElementById('userModal');
    const title = document.getElementById('user-modal-title');
    const form = document.getElementById('userForm');
    const methodInput = document.getElementById('user-method');
    const submitBtn = document.getElementById('user-submit-btn');
    const passwordLabel = document.getElementById('user-password-label');
    const passwordRequired = document.getElementById('user-password-required');
    const passwordConfirmationLabel = document.getElementById('user-password-confirmation-label');
    const passwordConfirmationRequired = document.getElementById('user-password-confirmation-required');
    
    modal.classList.remove('hidden');
    title.textContent = 'Create User';
    methodInput.value = 'POST';
    submitBtn.textContent = 'Save';
    form.reset();
    passwordLabel.textContent = 'Password';
    passwordRequired.style.display = 'inline';
    passwordConfirmationLabel.textContent = 'Confirm Password';
    passwordConfirmationRequired.style.display = 'inline';
    document.getElementById('user-password').required = true;
    document.getElementById('user-password-confirmation').required = true;
    document.getElementById('user-project-access-container').style.display = 'none';
    selectedProjects.clear();
    
    document.querySelectorAll('.field-error').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
    
    // Load companies and projects
    fetch('/admin/users/create', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const companySelect = document.getElementById('user-company-id');
        companySelect.innerHTML = '<option value="">None</option>';
        data.companies.forEach(comp => {
            const option = document.createElement('option');
            option.value = comp.id;
            option.textContent = comp.name;
            companySelect.appendChild(option);
        });
        
        allProjects = data.projects || [];
        
        const roleSelect = document.getElementById('user-role');
        roleSelect.innerHTML = '';
        const roles = currentUserIsSuperAdmin 
            ? { 'super_admin': 'Super Admin', 'admin': 'Admin', 'user': 'User', 'site_engineer': 'Site Engineer' }
            : { 'user': 'User', 'site_engineer': 'Site Engineer' };
        
        Object.keys(roles).forEach(role => {
            const option = document.createElement('option');
            option.value = role;
            option.textContent = roles[role];
            if (role === 'user') option.selected = true;
            roleSelect.appendChild(option);
        });
        
        document.getElementById('user-role-help').textContent = currentUserIsSuperAdmin 
            ? '' 
            : 'You can only create regular users.';
        
        // Handle company change
        companySelect.addEventListener('change', function() {
            renderProjectOptions(this.value);
        });
        
        // Initial render
        renderProjectOptions(companySelect.value);
    })
    .catch(error => {
        console.error('Error loading form data:', error);
        showNotification('Failed to load form data', 'error');
    });
}

function openEditUserModal(userId) {
    currentUserId = userId;
    const modal = document.getElementById('userModal');
    const title = document.getElementById('user-modal-title');
    const form = document.getElementById('userForm');
    const methodInput = document.getElementById('user-method');
    const submitBtn = document.getElementById('user-submit-btn');
    const passwordLabel = document.getElementById('user-password-label');
    const passwordRequired = document.getElementById('user-password-required');
    const passwordConfirmationLabel = document.getElementById('user-password-confirmation-label');
    const passwordConfirmationRequired = document.getElementById('user-password-confirmation-required');
    
    modal.classList.remove('hidden');
    title.textContent = 'Edit User';
    methodInput.value = 'PUT';
    submitBtn.textContent = 'Update';
    passwordLabel.textContent = 'New Password';
    passwordRequired.style.display = 'none';
    passwordConfirmationLabel.textContent = 'Confirm New Password';
    passwordConfirmationRequired.style.display = 'none';
    document.getElementById('user-password').required = false;
    document.getElementById('user-password-confirmation').required = false;
    
    document.querySelectorAll('.field-error').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
    
    // Load user data
    fetch(`/admin/users/${userId}/edit`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const companySelect = document.getElementById('user-company-id');
        companySelect.innerHTML = '<option value="">None</option>';
        data.companies.forEach(comp => {
            const option = document.createElement('option');
            option.value = comp.id;
            option.textContent = comp.name;
            option.selected = comp.id == data.user.company_id;
            companySelect.appendChild(option);
        });
        
        allProjects = data.projects || [];
        selectedProjects = new Set((data.selectedProjectIds || []).map(id => String(id)));
        
        // Handle role restrictions
        const roleSelect = document.getElementById('user-role');
        roleSelect.innerHTML = '';
        const isOwnAccount = currentAuthUserId === data.user.id;
        const userRole = data.user.role;
        
        if (isOwnAccount) {
            // User editing own account - role is readonly
            roleSelect.innerHTML = `<input type="hidden" name="role" value="${userRole}"><input type="text" value="${userRole.charAt(0).toUpperCase() + userRole.slice(1).replace('_', ' ')}" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100" readonly>`;
            document.getElementById('user-role-help').textContent = 'You cannot change your own role.';
        } else if (!currentUserIsSuperAdmin && ['admin', 'super_admin'].includes(userRole)) {
            // Regular admin editing admin/super_admin - role is readonly
            roleSelect.innerHTML = `<input type="hidden" name="role" value="${userRole}"><input type="text" value="${userRole.charAt(0).toUpperCase() + userRole.slice(1).replace('_', ' ')}" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100" readonly>`;
            document.getElementById('user-role-help').textContent = 'You cannot change the role of admin or super admin users.';
        } else {
            // Can change role
            const roles = currentUserIsSuperAdmin 
                ? { 'super_admin': 'Super Admin', 'admin': 'Admin', 'user': 'User', 'site_engineer': 'Site Engineer' }
                : { 'user': 'User', 'site_engineer': 'Site Engineer' };
            
            Object.keys(roles).forEach(role => {
                const option = document.createElement('option');
                option.value = role;
                option.textContent = roles[role];
                option.selected = role === userRole;
                roleSelect.appendChild(option);
            });
            
            document.getElementById('user-role-help').textContent = currentUserIsSuperAdmin 
                ? '' 
                : 'You can assign user or site engineer role.';
        }
        
        document.getElementById('user-name').value = data.user.name || '';
        document.getElementById('user-email').value = data.user.email || '';
        document.getElementById('user-company-id').value = data.user.company_id || '';
        document.getElementById('user-is-admin').checked = data.user.is_admin || false;
        
        // Handle company change
        companySelect.addEventListener('change', function() {
            renderProjectOptions(this.value);
        });
        
        // Initial render
        renderProjectOptions(companySelect.value);
    })
    .catch(error => {
        console.error('Error loading user data:', error);
        showNotification('Failed to load user data', 'error');
    });
}

function renderProjectOptions(companyId) {
    const container = document.getElementById('user-project-checkboxes');
    const projectAccessContainer = document.getElementById('user-project-access-container');
    
    if (!companyId) {
        projectAccessContainer.style.display = 'none';
        return;
    }
    
    projectAccessContainer.style.display = 'block';
    container.innerHTML = '';
    
    const filtered = allProjects.filter(p => String(p.company_id) === String(companyId));
    
    if (!filtered.length) {
        container.innerHTML = '<p class="text-xs text-gray-500">No projects for this company.</p>';
        return;
    }
    
    filtered.forEach(p => {
        const wrapper = document.createElement('label');
        wrapper.className = 'flex items-center space-x-2 text-sm bg-gray-50 hover:bg-gray-100 rounded px-2 py-1';
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.name = 'project_ids[]';
        checkbox.value = p.id;
        checkbox.className = 'rounded border-gray-300 text-indigo-600 focus:ring-indigo-500';
        if (selectedProjects.has(String(p.id))) {
            checkbox.checked = true;
        }
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                selectedProjects.add(String(p.id));
            } else {
                selectedProjects.delete(String(p.id));
            }
        });
        const span = document.createElement('span');
        span.textContent = p.name + (p.company_name ? ` (${p.company_name})` : '');
        wrapper.appendChild(checkbox);
        wrapper.appendChild(span);
        container.appendChild(wrapper);
    });
}

function submitUserForm(e) {
    e.preventDefault();
    const form = e.target;
    const submitBtn = document.getElementById('user-submit-btn');
    const originalText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';
    
    const formData = new FormData(form);
    const url = currentUserId 
        ? `/admin/users/${currentUserId}`
        : '/admin/users';
    
    if (currentUserId) {
        formData.append('_method', 'PUT');
    }
    
    // Ensure project_ids_sent is included
    formData.append('project_ids_sent', '1');
    
    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => {
                throw err;
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            closeUserModal();
            
            if (currentUserId) {
                updateUserRow(data.user);
            } else {
                addUserRow(data.user);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (error.errors) {
            Object.keys(error.errors).forEach(field => {
                const errorEl = document.querySelector(`.field-error[data-field="${field}"]`);
                if (errorEl) {
                    errorEl.textContent = error.errors[field][0];
                    errorEl.style.display = 'block';
                }
            });
        }
        showNotification(error.message || 'Validation failed', 'error');
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
}

function closeUserModal() {
    document.getElementById('userModal').classList.add('hidden');
    currentUserId = null;
    document.getElementById('userForm').reset();
    selectedProjects.clear();
    document.getElementById('user-project-access-container').style.display = 'none';
}

function addUserRow(user) {
    const tbody = document.querySelector('table tbody');
    const emptyRow = tbody.querySelector('tr td[colspan]');
    
    if (emptyRow) {
        emptyRow.closest('tr').remove();
    }
    
    const roleClass = user.role === 'super_admin' ? 'bg-purple-100 text-purple-800' :
                     user.role === 'admin' ? 'bg-blue-100 text-blue-800' :
                     'bg-gray-100 text-gray-800';
    
    const row = document.createElement('tr');
    row.setAttribute('data-user-id', user.id);
    row.className = 'border-t';
    row.innerHTML = `
        <td class="px-4 py-2">${user.name}</td>
        <td class="px-4 py-2">${user.email}</td>
        <td class="px-4 py-2">${user.company_name || '-'}</td>
        <td class="px-4 py-2">
            <span class="px-2 py-1 rounded text-xs font-semibold ${roleClass}">
                ${user.role ? user.role.charAt(0).toUpperCase() + user.role.slice(1).replace('_', ' ') : ''}
            </span>
        </td>
        <td class="px-4 py-2 whitespace-nowrap">
            <div class="d-flex gap-1 text-nowrap">
                <button onclick="openEditUserModal(${user.id})" class="btn btn-sm btn-outline-warning" title="Edit">
                    <i class="bi bi-pencil"></i>
                </button>
                ${currentAuthUserId !== user.id ? `
                    <button onclick="showDeleteUserConfirmation(${user.id}, '${(user.name || '').replace(/'/g, "\\'")}')" class="btn btn-sm btn-outline-danger" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                ` : `
                    <button class="btn btn-sm btn-outline-danger" disabled title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                `}
            </div>
        </td>
    `;
    
    tbody.insertBefore(row, tbody.firstChild);
}

function updateUserRow(user) {
    const row = document.querySelector(`tr[data-user-id="${user.id}"]`);
    if (row) {
        const roleClass = user.role === 'super_admin' ? 'bg-purple-100 text-purple-800' :
                         user.role === 'admin' ? 'bg-blue-100 text-blue-800' :
                         'bg-gray-100 text-gray-800';
        
        row.innerHTML = `
            <td class="px-4 py-2">${user.name}</td>
            <td class="px-4 py-2">${user.email}</td>
            <td class="px-4 py-2">${user.company_name || '-'}</td>
            <td class="px-4 py-2">
                <span class="px-2 py-1 rounded text-xs font-semibold ${roleClass}">
                    ${user.role ? user.role.charAt(0).toUpperCase() + user.role.slice(1).replace('_', ' ') : ''}
                </span>
            </td>
            <td class="px-4 py-2 whitespace-nowrap">
                <div class="d-flex gap-1 text-nowrap">
                    <button onclick="openEditUserModal(${user.id})" class="btn btn-sm btn-outline-warning" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </button>
                    ${currentAuthUserId !== user.id ? `
                        <button onclick="showDeleteUserConfirmation(${user.id}, '${(user.name || '').replace(/'/g, "\\'")}')" class="btn btn-sm btn-outline-danger" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    ` : `
                        <button class="btn btn-sm btn-outline-danger" disabled title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    `}
                </div>
            </td>
        `;
    }
}

function showDeleteUserConfirmation(userId, userName) {
    deleteUserId = userId;
    document.getElementById('delete-user-name').textContent = userName;
    document.getElementById('deleteUserConfirmationModal').classList.remove('hidden');
}

function closeDeleteUserConfirmation() {
    document.getElementById('deleteUserConfirmationModal').classList.add('hidden');
    deleteUserId = null;
}

function confirmDeleteUser() {
    if (!deleteUserId) return;
    
    const userIdToDelete = deleteUserId;
    const row = document.querySelector(`tr[data-user-id="${userIdToDelete}"]`);
    const deleteBtn = event.target;
    
    deleteBtn.disabled = true;
    deleteBtn.textContent = 'Deleting...';
    
    fetch(`/admin/users/${userIdToDelete}`, {
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
            closeDeleteUserConfirmation();
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
                                <td colspan="5" class="px-4 py-6 text-center text-gray-500">No users found.</td>
                            </tr>
                        `;
                    }
                }, 300);
            }
        } else {
            showNotification(data.message || 'Failed to delete user', 'error');
            deleteBtn.disabled = false;
            deleteBtn.textContent = 'Delete';
        }
    })
    .catch(error => {
        console.error('Error deleting user:', error);
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
        if (!document.getElementById('userModal').classList.contains('hidden')) {
            closeUserModal();
        }
        if (!document.getElementById('deleteUserConfirmationModal').classList.contains('hidden')) {
            closeDeleteUserConfirmation();
        }
    }
});

document.getElementById('userModal').addEventListener('click', function(e) {
    if (e.target === this) closeUserModal();
});

document.getElementById('deleteUserConfirmationModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteUserConfirmation();
});
</script>
@endpush
@endsection
