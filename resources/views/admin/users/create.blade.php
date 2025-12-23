@extends('admin.layout')

@section('title', 'New User')

@section('content')
<h1 class="text-2xl font-bold mb-4">Create User</h1>

<form method="POST" action="{{ route('admin.users.store') }}" class="bg-white p-6 rounded shadow max-w-xl" id="userForm">
  @csrf

  <div class="mb-4">
    <label class="block text-sm font-medium mb-1">Name</label>
    <input type="text" name="name" value="{{ old('name') }}" class="w-full border rounded px-3 py-2 @error('name') border-red-500 @enderror">
    <div class="field-error text-red-600 text-sm mt-1" data-field="name" style="display: none;"></div>
    @error('name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
  </div>

  <div class="mb-4">
    <label class="block text-sm font-medium mb-1">Email</label>
    <input type="email" name="email" value="{{ old('email') }}" class="w-full border rounded px-3 py-2 @error('email') border-red-500 @enderror">
    <div class="field-error text-red-600 text-sm mt-1" data-field="email" style="display: none;"></div>
    @error('email')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
      <label class="block text-sm font-medium mb-1">Password</label>
      <input type="password" name="password" class="w-full border rounded px-3 py-2 @error('password') border-red-500 @enderror">
      <div class="field-error text-red-600 text-sm mt-1" data-field="password" style="display: none;"></div>
      @error('password')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
      <label class="block text-sm font-medium mb-1">Confirm Password</label>
      <input type="password" name="password_confirmation" class="w-full border rounded px-3 py-2">
      <div class="field-error text-red-600 text-sm mt-1" data-field="password_confirmation" style="display: none;"></div>
    </div>
  </div>

  <div class="mb-4 mt-4">
    <label class="block text-sm font-medium mb-1">Company</label>
    @php
      $activeCompanyId = \App\Support\CompanyContext::getActiveCompanyId();
      $defaultCompanyId = old('company_id', $activeCompanyId);
    @endphp
    <select name="company_id" id="company_id" class="w-full border rounded px-3 py-2 @error('company_id') border-red-500 @enderror">
      <option value="">None</option>
      @foreach($companies as $company)
        <option value="{{ $company->id }}" {{ $defaultCompanyId == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
      @endforeach
    </select>
    <div class="field-error text-red-600 text-sm mt-1" data-field="company_id" style="display: none;"></div>
    @error('company_id')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
  </div>

  @if($projects->count() > 0)
    <div class="mb-4">
      <label class="block text-sm font-medium mb-1">Project Access (optional)</label>
      <p class="text-xs text-gray-500 mb-2">Check one or more projects for this user. Leave all unchecked to allow all projects in the selected company.</p>
      <div id="project-checkboxes" class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-48 overflow-auto border rounded p-3 @error('project_ids') border-red-500 @enderror">
        <!-- populated by script -->
      </div>
      <div class="field-error text-red-600 text-sm mt-1" data-field="project_ids" style="display: none;"></div>
      @error('project_ids')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
      @error('project_ids.*')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
    </div>
  @endif

  <div class="mb-4">
    <label class="block text-sm font-medium mb-1">Role</label>
    <select name="role" class="w-full border rounded px-3 py-2 @error('role') border-red-500 @enderror">
      @php
        $currentUser = auth()->user();
        $roles = ['super_admin' => 'Super Admin', 'admin' => 'Admin', 'user' => 'User'];
        // Regular admin can only create regular users
        if (!$currentUser->isSuperAdmin()) {
            $roles = ['user' => 'User'];
        }
      @endphp
      @foreach($roles as $value => $label)
        <option value="{{ $value }}" {{ old('role', 'user') == $value ? 'selected' : '' }}>{{ $label }}</option>
      @endforeach
    </select>
    @if(!auth()->user()->isSuperAdmin())
      <p class="text-gray-500 text-xs mt-1">You can only create regular users.</p>
    @endif
    <div class="field-error text-red-600 text-sm mt-1" data-field="role" style="display: none;"></div>
    @error('role')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
  </div>

  <div class="mb-6">
    <label class="inline-flex items-center">
      <input type="checkbox" name="is_admin" value="1" {{ old('is_admin') ? 'checked' : '' }} class="mr-2">
      <span>Legacy is_admin (keep compatibility)</span>
    </label>
  </div>

  <div class="flex space-x-2">
    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded">Save</button>
    <a href="{{ route('admin.users.index') }}" class="px-4 py-2 rounded border">Cancel</a>
  </div>
</form>

@if($projects->count() > 0)
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      @php
        $projectOptions = $projects->map(function($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'company_id' => $p->company_id,
                'company_name' => optional($p->company)->name ?? 'No Company',
            ];
        })->values();
      @endphp
      const projects = @json($projectOptions);

      const selected = new Set(@json(old('project_ids', [])));
      const projectContainer = document.getElementById('project-checkboxes');
      const companySelect = document.getElementById('company_id') || document.querySelector('select[name="company_id"]');

      function renderProjectOptions(clearSelection = false) {
        const companyId = companySelect ? companySelect.value : '';
        if (!projectContainer) {
          console.error('Project container not found');
          return;
        }
        projectContainer.innerHTML = '';
        if (!companyId) {
          projectContainer.innerHTML = '<p class="text-xs text-gray-500">Select a company to load its projects.</p>';
          return;
        }
        if (clearSelection) {
          selected.clear();
        }
        const filtered = projects.filter(p => String(p.company_id) === String(companyId));
        if (!filtered.length) {
          projectContainer.innerHTML = '<p class="text-xs text-gray-500">No projects for this company.</p>';
          return;
        }
        filtered.forEach(p => {
          const id = `project_${p.id}`;
          const wrapper = document.createElement('label');
          wrapper.className = 'flex items-center space-x-2 text-sm bg-gray-50 hover:bg-gray-100 rounded px-2 py-1';
          wrapper.innerHTML = `
            <input type="checkbox" name="project_ids[]" value="${p.id}" id="${id}" ${selected.has(String(p.id)) ? 'checked' : ''} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            <span>${p.name}${p.company_name ? ` (${p.company_name})` : ''}</span>
          `;
          projectContainer.appendChild(wrapper);
        });
      }

      if (projectContainer && companySelect) {
        // Initial render based on selected company
        renderProjectOptions();
        
        // Listen for company changes - use both change and input events
        companySelect.addEventListener('change', function() {
          console.log('Company changed to:', this.value);
          renderProjectOptions(true);
        });
        
        // Also listen for input event in case change doesn't fire
        companySelect.addEventListener('input', function() {
          console.log('Company input changed to:', this.value);
          renderProjectOptions(true);
        });
      } else {
        console.error('Project container or company select not found', {
          projectContainer: !!projectContainer,
          companySelect: !!companySelect
        });
      }
    });
  </script>
@endif
@endsection


