@extends('admin.layout')

@section('title', 'Roles & Permissions')

@section('content')
<style>
    .role-action-btn {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 0.4rem !important;
        font-size: 0.875rem !important;
        line-height: 1.25 !important;
        width: auto !important;
        min-width: 0 !important;
        height: auto !important;
        white-space: nowrap;
    }
    .role-action-btn i.bi,
    .role-action-btn .bi {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 1rem !important;
        line-height: 1 !important;
        margin: 0 !important;
        flex-shrink: 0;
        color: inherit !important;
        overflow: visible !important;
    }
    .role-action-btn i.bi::before,
    .role-action-btn .bi::before {
        font-family: bootstrap-icons !important;
        font-style: normal !important;
        font-weight: normal !important;
        display: inline-block !important;
        line-height: 1 !important;
    }
</style>
<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold"><i class="bi bi-shield-lock me-2 text-primary"></i>Roles &amp; Permissions</h4>
            <p class="text-muted small mb-0">
                Super Admin grants modules to the company admin. Then the company admin creates staff roles from those modules only.
            </p>
        </div>
        <button type="button" class="role-action-btn bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white px-4 py-2 rounded-lg transition" data-bs-toggle="modal" data-bs-target="#createRoleModal" @if(!auth()->user()->isSuperAdmin() && empty($hasAdminPack)) disabled title="Wait until Super Admin grants company modules" @endif>
            <i class="bi bi-plus-lg"></i> <span>New Role</span>
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif
    @if(!auth()->user()->isSuperAdmin() && empty($hasAdminPack))
        <div class="alert alert-warning">Super Admin has not granted this company any modules yet. You can manage users, but you cannot open ERP modules or assign them to staff until Super Admin assigns company admin access.</div>
    @endif

    <div class="row g-3">
        <div class="col-lg-3">
            <div class="card shadow-sm">
                <div class="card-header fw-semibold">Roles</div>
                <div class="list-group list-group-flush">
                    @forelse($roles as $role)
                        @php
                            $modulesGranted = $role->modules->filter(fn($m) => $m->can_view || $m->can_create || $m->can_update || $m->can_delete)->count();
                            $totalModules = count(config('modules.modules', []));
                        @endphp
                        <a href="{{ route('admin.roles.index', ['role' => $role->id]) }}"
                           class="list-group-item list-group-item-action {{ $selected && $selected->id === $role->id ? 'active' : '' }}">
                            <div class="d-flex justify-content-between align-items-start">
                                <span>
                                    {{ $role->isAdminPack() ? 'Company Admin' : $role->name }}
                                    @if($role->isAdminPack())
                                        <span class="badge bg-primary ms-1">Admin</span>
                                    @elseif($role->isLoginRole())
                                        <span class="badge bg-info text-dark ms-1">{{ ucfirst(str_replace('_', ' ', $role->login_role)) }}</span>
                                    @endif
                                    @unless($role->is_active)<span class="badge bg-secondary ms-1">Inactive</span>@endunless
                                </span>
                                <span class="badge {{ $selected && $selected->id === $role->id ? 'bg-light text-dark' : 'bg-secondary' }}">
                                    {{ $role->users_count }} user{{ $role->users_count !== 1 ? 's' : '' }}
                                </span>
                            </div>
                            <div class="small mt-1 {{ $selected && $selected->id === $role->id ? 'text-white-50' : 'text-muted' }}">
                                <i class="bi bi-shield-check me-1"></i>{{ $modulesGranted }} / {{ $totalModules }} modules
                            </div>
                        </a>
                    @empty
                        <div class="list-group-item text-muted small">No custom roles yet. Create one to start assigning modules.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-9">
            @if($selected)
                <div class="card shadow-sm mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span class="fw-semibold">Role details</span>
                        @if(!empty($canEditSelected) && !$selected->isAdminPack() && !$selected->isLoginRole())
                        <form method="POST" action="{{ route('admin.roles.destroy', $selected) }}" onsubmit="return confirm('Delete this role? Users will keep their login type but lose this module access.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="role-action-btn border border-red-500 text-red-600 hover:bg-red-50 px-3 py-1.5 rounded-lg transition"><i class="bi bi-trash"></i> <span>Delete</span></button>
                        </form>
                        @elseif($selected->isAdminPack())
                            <span class="badge bg-dark">Granted by Super Admin</span>
                        @elseif($selected->isLoginRole())
                            <span class="badge bg-info text-dark">Login role — cannot delete</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.roles.update', $selected) }}" class="row g-2 align-items-end">
                            @csrf
                            @method('PUT')
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Name</label>
                                <input type="text" name="name" class="form-control form-control-sm" required maxlength="100" value="{{ $selected->name }}">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-semibold small">Description</label>
                                <input type="text" name="description" class="form-control form-control-sm" maxlength="500" value="{{ $selected->description }}">
                            </div>
                            <div class="col-md-2">
                                <div class="form-check mt-4">
                                    <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="roleActive" {{ $selected->is_active ? 'checked' : '' }}>
                                    <label class="form-check-label" for="roleActive">Active</label>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button class="role-action-btn w-100 border border-indigo-500 text-indigo-600 hover:bg-indigo-50 px-3 py-2 rounded-lg transition"><i class="bi bi-check2"></i> <span>Save</span></button>
                            </div>
                        </form>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.roles.permissions', $selected) }}">
                    @csrf
                    <div class="card shadow-sm mb-3">
                        <div class="card-header d-flex justify-between align-items-center flex-wrap gap-2">
                            <span class="fw-semibold">Module permissions</span>
                            @if(!empty($canEditSelected))
                            <div class="d-flex gap-2 flex-wrap">
                                <button type="button" class="role-action-btn border border-indigo-500 text-indigo-600 hover:bg-indigo-50 px-3 py-1.5 rounded-lg transition" onclick="toggleAllModules(true)">
                                    <i class="bi bi-check2-square"></i> <span>Select all modules</span>
                                </button>
                                <button type="button" class="role-action-btn border border-gray-400 text-gray-700 hover:bg-gray-100 px-3 py-1.5 rounded-lg transition" onclick="toggleAllModules(false)">
                                    <i class="bi bi-x-square"></i> <span>Clear all</span>
                                </button>
                                <button class="role-action-btn bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-lg transition"><i class="bi bi-floppy"></i> <span>Save permissions</span></button>
                            </div>
                            @endif
                        </div>
                        <div class="card-body p-0">
                            @forelse($groupedModules as $groupKey => $group)
                                <div class="border-bottom">
                                    <div class="px-3 py-2 bg-light d-flex justify-content-between align-items-center">
                                        <span class="fw-semibold small text-uppercase text-muted">{{ $group['label'] }}</span>
                                        @if(!empty($canEditSelected))
                                        <label class="small mb-0 d-flex align-items-center gap-1">
                                            <input type="checkbox" class="form-check-input perm-group-all" data-group="{{ $groupKey }}" onchange="toggleGroup('{{ $groupKey }}', this.checked)">
                                            Select all
                                        </label>
                                        @endif
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle mb-0">
                                            <thead>
                                                <tr class="small text-muted">
                                                    <th class="ps-3">Module</th>
                                                    <th class="text-center" style="width:80px">All</th>
                                                    <th class="text-center" style="width:80px">View</th>
                                                    <th class="text-center" style="width:80px">Create</th>
                                                    <th class="text-center" style="width:80px">Edit</th>
                                                    <th class="text-center" style="width:80px">Delete</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($group['modules'] as $moduleKey => $module)
                                                    @php $row = $matrix[$moduleKey] ?? []; @endphp
                                                    <tr>
                                                        <td class="ps-3">{{ $module['label'] }}</td>
                                                        <td class="text-center">
                                                            <input type="checkbox"
                                                                   class="form-check-input perm-row-all"
                                                                   data-module="{{ $moduleKey }}"
                                                                   data-group="{{ $groupKey }}"
                                                                   title="Select all for this module"
                                                                   {{ !empty($row['view']) && !empty($row['create']) && !empty($row['update']) && !empty($row['delete']) ? 'checked' : '' }}
                                                                   @if(empty($canEditSelected)) disabled @endif
                                                                   onchange="toggleModuleRow('{{ $moduleKey }}', this.checked)">
                                                        </td>
                                                        @foreach(['view','create','update','delete'] as $action)
                                                            <td class="text-center">
                                                                <input type="checkbox"
                                                                       class="form-check-input perm-box perm-{{ $action }}"
                                                                       data-module="{{ $moduleKey }}"
                                                                       data-group="{{ $groupKey }}"
                                                                       name="permissions[{{ $moduleKey }}][{{ $action }}]"
                                                                       value="1"
                                                                       {{ !empty($row[$action]) ? 'checked' : '' }}
                                                                       @if($action !== 'view') onchange="ensureView(this); syncModuleRow('{{ $moduleKey }}')" @else onchange="syncModuleRow('{{ $moduleKey }}')" @endif
                                                                       @if(empty($canEditSelected)) disabled @endif>
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @empty
                                <div class="p-4 text-muted small">No modules available to assign. Super Admin must grant company admin modules first.</div>
                            @endforelse
                        </div>
                        @if(!empty($canEditSelected))
                        <div class="card-footer text-end">
                            <button class="role-action-btn bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-lg transition"><i class="bi bi-floppy"></i> <span>Save permissions</span></button>
                        </div>
                        @endif
                    </div>
                </form>

                @if(!$selected->isAdminPack())
                <div class="card shadow-sm">
                    <div class="card-header fw-semibold">Assign users to this role</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.roles.assign-users', $selected) }}">
                            @csrf
                            <p class="small text-muted">Only User / Site Engineer. Company admin access is set by Super Admin and cannot be given from here.</p>
                            <div class="row g-2" style="max-height:280px; overflow:auto;">
                                @forelse($assignableUsers as $u)
                                    <div class="col-md-6">
                                        <label class="form-check border rounded px-3 py-2 w-100">
                                            <input class="form-check-input me-2" type="checkbox" name="user_ids[]" value="{{ $u->id }}" {{ (int) $u->permission_role_id === (int) $selected->id ? 'checked' : '' }}>
                                            <span class="fw-semibold">{{ $u->name }}</span>
                                            <span class="text-muted small"> — {{ $u->email }} ({{ str_replace('_', ' ', $u->role) }})</span>
                                            @if($u->permission_role_id && (int) $u->permission_role_id !== (int) $selected->id)
                                                <span class="badge bg-warning text-dark ms-1">other role</span>
                                            @endif
                                        </label>
                                    </div>
                                @empty
                                    <div class="col-12 text-muted">No users in this company.</div>
                                @endforelse
                            </div>
                            <div class="mt-3">
                                <button class="role-action-btn bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition"><i class="bi bi-people"></i> <span>Assign selected users</span></button>
                            </div>
                        </form>
                    </div>
                </div>
                @endif
            @else
                <div class="card shadow-sm">
                    <div class="card-body text-center text-muted py-5">
                        Create a role first, then tick the modules that role can use.
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="createRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.roles.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">New role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required maxlength="100" placeholder="e.g. HR Officer, Store Keeper">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="2" maxlength="500"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="role-action-btn border border-gray-400 text-gray-700 hover:bg-gray-100 px-3 py-2 rounded-lg transition" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i> <span>Cancel</span></button>
                    <button class="role-action-btn bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition"><i class="bi bi-plus-lg"></i> <span>Create role</span></button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function ensureView(el) {
    if (!el.checked) return;
    const module = el.dataset.module;
    const viewBox = document.querySelector('.perm-view[data-module="' + module + '"]');
    if (viewBox && !viewBox.disabled) viewBox.checked = true;
}
function toggleAllModules(on) {
    document.querySelectorAll('.perm-box, .perm-row-all, .perm-group-all').forEach(el => {
        if (!el.disabled) el.checked = on;
    });
}
function toggleGroup(group, on) {
    document.querySelectorAll('.perm-box[data-group="' + group + '"], .perm-row-all[data-group="' + group + '"]').forEach(el => {
        if (!el.disabled) el.checked = on;
    });
}
function toggleModuleRow(module, on) {
    document.querySelectorAll('.perm-box[data-module="' + module + '"]').forEach(el => {
        if (!el.disabled) el.checked = on;
    });
    syncGroupAll(module);
}
function syncModuleRow(module) {
    const boxes = Array.from(document.querySelectorAll('.perm-box[data-module="' + module + '"]'));
    const all = document.querySelector('.perm-row-all[data-module="' + module + '"]');
    if (all) all.checked = boxes.length > 0 && boxes.every(b => b.checked);
    syncGroupAll(module);
}
function syncGroupAll(module) {
    const sample = document.querySelector('.perm-box[data-module="' + module + '"]');
    if (!sample) return;
    const group = sample.dataset.group;
    const boxes = Array.from(document.querySelectorAll('.perm-box[data-group="' + group + '"]'));
    const groupAll = document.querySelector('.perm-group-all[data-group="' + group + '"]');
    if (groupAll) groupAll.checked = boxes.length > 0 && boxes.every(b => b.checked);
}
</script>
@endpush
