@extends('admin.layout')

@section('title', 'Workers')

@section('content')
<div class="mb-6 flex flex-wrap justify-between items-center gap-3">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Workers</h1>
        <p class="text-sm text-gray-500 mt-1">Company workers — daily or monthly wages</p>
    </div>
    <button type="button" onclick="openCreateWorkerModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition duration-200">
        <i class="bi bi-plus-lg me-1"></i> <span class="worker-btn-text">Add Worker</span>
    </button>
</div>

<div id="workerFlash" class="hidden mb-4 px-4 py-3 rounded-lg text-sm border"></div>

<div class="mb-4 bg-white shadow-lg rounded-lg p-4">
    <form method="GET" action="{{ route('admin.workers.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Name, phone..."
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Project</label>
            <select name="project_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Projects</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>{{ $project->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Wage Type</label>
            <select name="wage_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All</option>
                <option value="daily" {{ request('wage_type') === 'daily' ? 'selected' : '' }}>Daily</option>
                <option value="monthly" {{ request('wage_type') === 'monthly' ? 'selected' : '' }}>Monthly</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="flex-1 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">Filter</button>
            <a href="{{ route('admin.workers.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Reset</a>
        </div>
    </form>
</div>

<div class="bg-white shadow-lg rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">SN</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Project</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Wage</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody id="workersTableBody" class="bg-white divide-y divide-gray-200">
                @forelse($workers as $worker)
                    <tr data-worker-id="{{ $worker->id }}">
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ ($workers->currentPage() - 1) * $workers->perPage() + $loop->iteration }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 worker-name">{{ $worker->name }}</div>
                            <div class="text-xs text-gray-500 worker-email">{{ $worker->email ?: '' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 worker-phone">{{ $worker->phone ?? '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 worker-project">{{ $worker->project?->name ?? '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm worker-wage">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $worker->wage_type === 'daily' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                {{ ucfirst($worker->wage_type) }}
                            </span>
                            <div class="text-xs text-gray-600 mt-1">
                                Rs. {{ number_format($worker->wage_type === 'daily' ? (float)$worker->daily_wage : (float)$worker->monthly_salary, 2) }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap worker-status">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $worker->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $worker->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="d-flex gap-1 text-nowrap">
                                <button type="button" onclick="openViewWorkerModal({{ $worker->id }})" class="btn btn-outline-primary btn-sm" title="View"><i class="bi bi-eye"></i></button>
                                <button type="button" onclick="openEditWorkerModal({{ $worker->id }})" class="btn btn-outline-warning btn-sm" title="Edit"><i class="bi bi-pencil"></i></button>
                                <button type="button" onclick="openDeleteWorkerModal({{ $worker->id }}, {{ json_encode($worker->name) }})" class="btn btn-outline-danger btn-sm" title="Delete"><i class="bi bi-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr id="workersEmptyRow">
                        <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">
                            No workers found. <button type="button" onclick="openCreateWorkerModal()" class="text-indigo-600 hover:text-indigo-900">Add one now</button>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<x-pagination :paginator="$workers" wrapper-class="mt-4" />

{{-- Create / Edit Modal --}}
<div id="workerModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full max-h-[90vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-5 border-b">
            <h3 class="text-xl font-semibold text-gray-900" id="worker-modal-title">Add Worker</h3>
            <button type="button" onclick="closeWorkerModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-5">
            <form id="workerForm" novalidate onsubmit="submitWorkerForm(event)">
                @csrf
                <input type="hidden" name="_method" id="worker-method" value="POST">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="worker-name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <div class="field-error text-red-600 text-sm mt-1" data-field="name" style="display:none;"></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input type="text" name="phone" id="worker-phone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <div class="field-error text-red-600 text-sm mt-1" data-field="phone" style="display:none;"></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" id="worker-email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <div class="field-error text-red-600 text-sm mt-1" data-field="email" style="display:none;"></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Project</label>
                        <select name="project_id" id="worker-project-id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Optional</option>
                        </select>
                        <div class="field-error text-red-600 text-sm mt-1" data-field="project_id" style="display:none;"></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Join Date</label>
                        <input type="date" name="join_date" id="worker-join-date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <div class="field-error text-red-600 text-sm mt-1" data-field="join_date" style="display:none;"></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Wage Type <span class="text-red-500">*</span></label>
                        <select name="wage_type" id="worker-wage-type" required onchange="toggleWorkerWageFields()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="daily">Daily</option>
                            <option value="monthly">Monthly</option>
                        </select>
                        <div class="field-error text-red-600 text-sm mt-1" data-field="wage_type" style="display:none;"></div>
                    </div>
                    <div id="worker-daily-wrap">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Daily Wage (Rs.) <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" min="0" name="daily_wage" id="worker-daily-wage" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <div class="field-error text-red-600 text-sm mt-1" data-field="daily_wage" style="display:none;"></div>
                    </div>
                    <div id="worker-monthly-wrap" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Monthly Salary (Rs.) <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" min="0" name="monthly_salary" id="worker-monthly-salary" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <div class="field-error text-red-600 text-sm mt-1" data-field="monthly_salary" style="display:none;"></div>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                        <textarea name="address" id="worker-address" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea name="notes" id="worker-notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="inline-flex items-center gap-2">
                            <input type="checkbox" name="is_active" id="worker-is-active" value="1" checked class="rounded border-gray-300">
                            <span class="text-sm text-gray-700">Active</span>
                        </label>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeWorkerModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" id="worker-submit-btn" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Save Worker</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- View Modal --}}
<div id="viewWorkerModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-lg w-full max-h-[90vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-5 border-b">
            <h3 class="text-xl font-semibold text-gray-900">Worker Details</h3>
            <button type="button" onclick="closeViewWorkerModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-5" id="view-worker-content">
            <div class="flex justify-center py-8 text-gray-400 text-sm">Loading…</div>
        </div>
        <div class="p-4 border-t flex justify-end gap-2">
            <button type="button" onclick="closeViewWorkerModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Close</button>
            <button type="button" id="view-worker-edit-btn" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600">Edit</button>
        </div>
    </div>
</div>

{{-- Delete Modal --}}
<div id="deleteWorkerModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-2xl max-w-md w-full p-6" onclick="event.stopPropagation()">
        <div class="flex items-center justify-center w-14 h-14 mx-auto mb-4 bg-red-100 rounded-full">
            <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">Delete Worker</h3>
        <p class="text-gray-600 text-center mb-6 text-sm">
            Delete <span class="font-semibold" id="delete-worker-name"></span>? This cannot be undone.
        </p>
        <div class="flex gap-3">
            <button type="button" onclick="closeDeleteWorkerModal()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
            <button type="button" onclick="confirmDeleteWorker()" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Delete</button>
        </div>
    </div>
</div>

<script>
(function () {
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
    let currentWorkerId = null;
    let deleteWorkerId = null;

    function showFlash(msg, isError) {
        const el = document.getElementById('workerFlash');
        if (!el) return;
        el.textContent = msg;
        el.classList.remove('hidden', 'bg-emerald-50', 'border-emerald-200', 'text-emerald-800', 'bg-rose-50', 'border-rose-200', 'text-rose-800');
        el.classList.add(isError ? 'bg-rose-50' : 'bg-emerald-50', isError ? 'border-rose-200' : 'border-emerald-200', isError ? 'text-rose-800' : 'text-emerald-800');
        clearTimeout(showFlash._t);
        showFlash._t = setTimeout(() => el.classList.add('hidden'), 3000);
    }

    function clearFieldErrors() {
        document.querySelectorAll('#workerForm .field-error').forEach(el => {
            el.style.display = 'none';
            el.textContent = '';
        });
        document.querySelectorAll('#workerForm .field-invalid').forEach(el => {
            el.classList.remove('field-invalid', 'border-red-500');
        });
    }

    function setFieldError(field, message) {
        const el = document.querySelector(`#workerForm .field-error[data-field="${field}"]`);
        if (el) {
            el.textContent = message;
            el.style.display = 'block';
        }
        const input = document.querySelector(`#workerForm [name="${field}"]`);
        if (input) {
            input.classList.add('field-invalid', 'border-red-500');
        }
    }

    function showFieldErrors(errors) {
        clearFieldErrors();
        Object.keys(errors || {}).forEach(field => {
            const msg = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
            setFieldError(field, msg);
        });
    }

    function validateWorkerForm() {
        clearFieldErrors();
        let ok = true;
        const name = document.getElementById('worker-name').value.trim();
        if (!name) {
            setFieldError('name', 'Worker name is required.');
            ok = false;
        }
        const wageType = document.getElementById('worker-wage-type').value;
        if (!wageType) {
            setFieldError('wage_type', 'Please select a wage type.');
            ok = false;
        }
        if (wageType === 'daily') {
            const daily = document.getElementById('worker-daily-wage').value;
            if (daily === '' || Number(daily) < 0) {
                setFieldError('daily_wage', 'Daily wage is required.');
                ok = false;
            }
        }
        if (wageType === 'monthly') {
            const monthly = document.getElementById('worker-monthly-salary').value;
            if (monthly === '' || Number(monthly) < 0) {
                setFieldError('monthly_salary', 'Monthly salary is required.');
                ok = false;
            }
        }
        const email = document.getElementById('worker-email').value.trim();
        if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            setFieldError('email', 'Please enter a valid email address.');
            ok = false;
        }
        return ok;
    }

    function fillProjects(projects, selectedId) {
        const sel = document.getElementById('worker-project-id');
        sel.innerHTML = '<option value="">Optional</option>';
        (projects || []).forEach(p => {
            const opt = document.createElement('option');
            opt.value = p.id;
            opt.textContent = p.name;
            if (selectedId && String(p.id) === String(selectedId)) opt.selected = true;
            sel.appendChild(opt);
        });
    }

    window.toggleWorkerWageFields = function () {
        const type = document.getElementById('worker-wage-type').value;
        document.getElementById('worker-daily-wrap').classList.toggle('hidden', type !== 'daily');
        document.getElementById('worker-monthly-wrap').classList.toggle('hidden', type !== 'monthly');
    };

    window.openCreateWorkerModal = function () {
        currentWorkerId = null;
        const modal = document.getElementById('workerModal');
        document.getElementById('worker-modal-title').textContent = 'Add Worker';
        document.getElementById('worker-method').value = 'POST';
        document.getElementById('worker-submit-btn').textContent = 'Save Worker';
        document.getElementById('workerForm').reset();
        document.getElementById('worker-is-active').checked = true;
        document.getElementById('worker-wage-type').value = 'daily';
        toggleWorkerWageFields();
        clearFieldErrors();
        modal.classList.remove('hidden');

        fetch('{{ route('admin.workers.create') }}', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
            .then(r => r.json())
            .then(data => fillProjects(data.projects))
            .catch(() => showFlash('Failed to load form data', true));
    };

    window.openEditWorkerModal = function (id) {
        currentWorkerId = id;
        closeViewWorkerModal();
        const modal = document.getElementById('workerModal');
        document.getElementById('worker-modal-title').textContent = 'Edit Worker';
        document.getElementById('worker-method').value = 'PUT';
        document.getElementById('worker-submit-btn').textContent = 'Update Worker';
        clearFieldErrors();
        modal.classList.remove('hidden');

        fetch(`/admin/workers/${id}/edit`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
            .then(r => r.json())
            .then(data => {
                const w = data.worker;
                fillProjects(data.projects, w.project_id);
                document.getElementById('worker-name').value = w.name || '';
                document.getElementById('worker-phone').value = w.phone || '';
                document.getElementById('worker-email').value = w.email || '';
                document.getElementById('worker-join-date').value = w.join_date || '';
                document.getElementById('worker-wage-type').value = w.wage_type || 'daily';
                document.getElementById('worker-daily-wage').value = w.daily_wage ?? '';
                document.getElementById('worker-monthly-salary').value = w.monthly_salary ?? '';
                document.getElementById('worker-address').value = w.address || '';
                document.getElementById('worker-notes').value = w.notes || '';
                document.getElementById('worker-is-active').checked = !!w.is_active;
                toggleWorkerWageFields();
            })
            .catch(() => showFlash('Failed to load worker', true));
    };

    window.closeWorkerModal = function () {
        document.getElementById('workerModal').classList.add('hidden');
        currentWorkerId = null;
    };

    window.submitWorkerForm = async function (e) {
        e.preventDefault();
        if (!validateWorkerForm()) {
            const firstErr = document.querySelector('#workerForm .field-invalid');
            if (firstErr) firstErr.focus();
            return;
        }

        const form = e.target;
        const btn = document.getElementById('worker-submit-btn');
        const original = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Saving…';

        const fd = new FormData(form);
        if (!document.getElementById('worker-is-active').checked) {
            fd.delete('is_active');
        }
        const url = currentWorkerId ? `/admin/workers/${currentWorkerId}` : '{{ route('admin.workers.store') }}';

        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                },
                body: fd,
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) {
                if (data.errors) showFieldErrors(data.errors);
                throw new Error(data.message || 'Validation failed');
            }
            closeWorkerModal();
            showFlash(data.message || 'Saved');
            // Reload to keep pagination / SN correct
            window.location.reload();
        } catch (err) {
            if (!document.querySelector('#workerForm .field-error[style*="block"]')) {
                showFlash(err.message || 'Save failed', true);
            }
        } finally {
            btn.disabled = false;
            btn.textContent = original;
        }
    };

    document.getElementById('workerForm')?.addEventListener('input', function (e) {
        const name = e.target?.name;
        if (!name) return;
        const err = document.querySelector(`#workerForm .field-error[data-field="${name}"]`);
        if (err) {
            err.style.display = 'none';
            err.textContent = '';
        }
        e.target.classList.remove('field-invalid', 'border-red-500');
    });
    document.getElementById('workerForm')?.addEventListener('change', function (e) {
        const name = e.target?.name;
        if (!name) return;
        const err = document.querySelector(`#workerForm .field-error[data-field="${name}"]`);
        if (err) {
            err.style.display = 'none';
            err.textContent = '';
        }
        e.target.classList.remove('field-invalid', 'border-red-500');
    });

    window.openViewWorkerModal = function (id) {
        const modal = document.getElementById('viewWorkerModal');
        const content = document.getElementById('view-worker-content');
        content.innerHTML = '<div class="flex justify-center py-8 text-gray-400 text-sm">Loading…</div>';
        modal.classList.remove('hidden');
        document.getElementById('view-worker-edit-btn').onclick = () => openEditWorkerModal(id);

        fetch(`/admin/workers/${id}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
            .then(r => r.json())
            .then(data => {
                const w = data.worker;
                content.innerHTML = `
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div class="sm:col-span-2"><dt class="text-gray-500">Name</dt><dd class="font-semibold text-lg text-gray-900">${esc(w.name)}</dd></div>
                        <div><dt class="text-gray-500">Phone</dt><dd class="font-medium">${esc(w.phone || '—')}</dd></div>
                        <div><dt class="text-gray-500">Email</dt><dd class="font-medium">${esc(w.email || '—')}</dd></div>
                        <div><dt class="text-gray-500">Project</dt><dd class="font-medium">${esc(w.project_name || '—')}</dd></div>
                        <div><dt class="text-gray-500">Join Date</dt><dd class="font-medium">${esc(w.join_date_formatted || '—')}</dd></div>
                        <div><dt class="text-gray-500">Wage</dt><dd class="font-medium">${esc(w.wage_label || '')}</dd></div>
                        <div><dt class="text-gray-500">Status</dt><dd class="font-medium">${w.is_active ? 'Active' : 'Inactive'}</dd></div>
                        <div class="sm:col-span-2"><dt class="text-gray-500">Address</dt><dd class="font-medium">${esc(w.address || '—')}</dd></div>
                        <div class="sm:col-span-2"><dt class="text-gray-500">Notes</dt><dd class="font-medium">${esc(w.notes || '—')}</dd></div>
                    </dl>`;
            })
            .catch(() => {
                content.innerHTML = '<p class="text-red-600 text-sm text-center py-6">Failed to load details.</p>';
            });
    };

    window.closeViewWorkerModal = function () {
        document.getElementById('viewWorkerModal').classList.add('hidden');
    };

    window.openDeleteWorkerModal = function (id, name) {
        deleteWorkerId = id;
        document.getElementById('delete-worker-name').textContent = name;
        document.getElementById('deleteWorkerModal').classList.remove('hidden');
    };

    window.closeDeleteWorkerModal = function () {
        deleteWorkerId = null;
        document.getElementById('deleteWorkerModal').classList.add('hidden');
    };

    window.confirmDeleteWorker = async function () {
        if (!deleteWorkerId) return;
        try {
            const res = await fetch(`/admin/workers/${deleteWorkerId}`, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                },
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(data.message || 'Delete failed');
            closeDeleteWorkerModal();
            showFlash(data.message || 'Deleted');
            window.location.reload();
        } catch (e) {
            showFlash(e.message || 'Delete failed', true);
        }
    };

    function esc(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    // Close modals on backdrop click
    ['workerModal', 'viewWorkerModal', 'deleteWorkerModal'].forEach(id => {
        document.getElementById(id)?.addEventListener('click', function (e) {
            if (e.target === this) {
                if (id === 'workerModal') closeWorkerModal();
                if (id === 'viewWorkerModal') closeViewWorkerModal();
                if (id === 'deleteWorkerModal') closeDeleteWorkerModal();
            }
        });
    });
})();
</script>
@endsection
