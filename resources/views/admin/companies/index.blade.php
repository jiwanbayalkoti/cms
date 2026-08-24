@extends('admin.layout')

@section('title', 'Companies')

@section('content')
<div class="flex justify-between mb-2">
    <h1 class="text-2xl font-bold">Companies</h1>
    <div class="flex flex-wrap justify-end gap-2">
      <button onclick="openTrialPeriodModal('overall', '{{ route('admin.companies.trial-period.update-all') }}', 'Common default', {{ $commonTrialDays }})" class="bg-amber-600 text-white px-4 py-2 rounded hover:bg-amber-700 transition">
        Common Trial: {{ $commonTrialDays }} days
      </button>
      <button onclick="openCreateCompanyModal()" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">New Company</button>
    </div>
  </div>
  <p class="text-sm text-gray-600 mb-4">Click a company card to open its dashboard and ERP modules.</p>

  <div id="companiesContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($companies as $company)
      @php
        $isActiveCompany = (int) session('active_company_id') === (int) $company->id;
      @endphp
      <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-all duration-200 p-6 company-card group cursor-pointer border-2 {{ $isActiveCompany ? 'border-indigo-500 ring-2 ring-indigo-100' : 'border-transparent hover:border-indigo-400' }}"
           data-company-id="{{ $company->id }}"
           onclick="handleCompanyCardClick(event, {{ $company->id }})"
           title="Click to enter {{ $company->name }}">
        <form id="enter-company-{{ $company->id }}" method="POST" action="{{ route('admin.companies.switch') }}" class="hidden">
          @csrf
          <input type="hidden" name="company_id" value="{{ $company->id }}">
          <input type="hidden" name="redirect_to" value="dashboard">
        </form>
        @php
          $isLocked = $company->isLockedForPayment();
          $isTrialing = $company->isOnTrial();
          $trialDaysLeft = $company->trialDaysRemaining();
          $paymentDaysLeft = $company->paymentDaysRemaining();
        @endphp
        <div class="flex items-start justify-between mb-4">
          <div class="flex-1">
            <div class="flex items-start justify-between gap-2">
              <h3 class="text-lg font-semibold text-gray-900 mb-1 group-hover:text-indigo-600">{{ $company->name }}</h3>
              <svg class="h-5 w-5 flex-shrink-0 text-gray-300 group-hover:text-indigo-500 transition-colors company-card-enter-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
              </svg>
            </div>
            @if($isActiveCompany)
              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700 mb-2">Currently active</span>
            @endif
            <div class="flex flex-wrap gap-2 mb-2">
              @if($company->trial_days_override)
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-sky-100 text-sky-700">
                  Custom {{ $company->trial_days_override }} days
                </span>
              @endif
              @if($company->adminPermissionRole && $company->adminPermissionRole->modules->isNotEmpty())
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">
                  Admin modules assigned
                </span>
              @else
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-800">
                  Admin modules pending
                </span>
              @endif
              @if($isLocked)
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                  Locked{{ $company->lock_reason ? ' - ' . str_replace('_', ' ', $company->lock_reason) : '' }}
                </span>
              @elseif($isTrialing)
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                  Trial{{ $trialDaysLeft !== null ? ' - ' . max(0, $trialDaysLeft) . ' day(s) left' : '' }}
                </span>
              @else
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                  Active{{ $company->paid_until ? ' - ' . max(0, $paymentDaysLeft) . ' day(s) left' : '' }}
                </span>
              @endif
            </div>
            @php($cardLogoUrl = $company->getLogoUrl())
            @if($cardLogoUrl)
              <img src="{{ $cardLogoUrl }}" alt="{{ $company->name }} Logo" class="h-12 w-auto mt-2">
            @endif
            @if($isTrialing)
              @include('admin.partials.trial-timeline', ['company' => $company, 'compact' => true])
            @endif
            @if($company->hasPaymentTimeline())
              @include('admin.partials.payment-timeline', ['company' => $company, 'compact' => true])
            @endif
          </div>
        </div>
        
        <div class="space-y-2 mb-4">
          @if($company->email)
            <div class="flex items-center text-sm text-gray-600">
              <i class="bi bi-envelope me-2"></i>
              <span class="truncate">{{ $company->email }}</span>
            </div>
          @endif
          
          @if($company->phone)
            <div class="flex items-center text-sm text-gray-600">
              <i class="bi bi-telephone me-2"></i>
              <span>{{ $company->phone }}</span>
            </div>
          @endif
          
          @if($company->address)
            <div class="flex items-start text-sm text-gray-600">
              <i class="bi bi-geo-alt me-2 mt-0.5"></i>
              <span class="flex-1">{{ Str::limit($company->address, 50) }}</span>
            </div>
          @endif
        </div>
        
        <div class="flex items-center justify-end gap-1 pt-4 border-t company-card-actions">
          @if($isLocked)
            <form method="POST" action="{{ route('admin.companies.unlock', $company) }}" class="inline">
              @csrf
              <button type="submit" class="btn btn-sm btn-outline-success" title="Unlock">
                <i class="bi bi-unlock"></i>
              </button>
            </form>
          @elseif($isTrialing)
            <form method="POST" action="{{ route('admin.companies.activate', $company) }}" class="inline" onsubmit="return confirm('Activate this company and end the trial period?')">
              @csrf
              <button type="submit" class="btn btn-sm btn-outline-success" title="Activate">
                <i class="bi bi-check-circle"></i>
              </button>
            </form>
          @else
            <form method="POST" action="{{ route('admin.companies.lock', $company) }}" class="inline lock-company-form" data-company-name="{{ $company->name }}">
              @csrf
              <button type="submit" class="btn btn-sm btn-outline-secondary" title="Lock">
                <i class="bi bi-lock"></i>
              </button>
            </form>
          @endif
          @if($company->subscription_status !== 'active')
            <button type="button" onclick="openTrialPeriodModal('company', '{{ route('admin.companies.trial-period.update', $company) }}', '{{ addslashes($company->name) }}', {{ $company->trial_days_override ?: $company->trialTotalDays() }})" class="btn btn-sm btn-outline-info" title="Update Trial Period">
              <i class="bi bi-clock-history"></i>
            </button>
          @endif
          <button type="button" onclick="openAdminAccessModal({{ $company->id }}, '{{ addslashes($company->name) }}')" class="btn btn-sm btn-outline-indigo" title="Assign company admin modules" style="border-color:#4f46e5;color:#4f46e5;">
            <i class="bi bi-shield-lock"></i>
          </button>
          <button onclick="openViewCompanyModal({{ $company->id }})" class="btn btn-sm btn-outline-primary" title="View">
            <i class="bi bi-eye"></i>
          </button>
          <button onclick="openEditCompanyModal({{ $company->id }})" class="btn btn-sm btn-outline-warning" title="Edit">
            <i class="bi bi-pencil"></i>
          </button>
          <form method="POST" action="{{ route('admin.companies.backup', $company) }}" class="inline backup-company-form" data-company-name="{{ $company->name }}">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-success" title="Run backup now">
              <i class="bi bi-cloud-upload"></i>
            </button>
          </form>
          <button type="button" onclick="openCompanyBackupsModal({{ $company->id }}, '{{ addslashes($company->name) }}')" class="btn btn-sm btn-outline-secondary" title="View saved backups">
            <i class="bi bi-archive"></i>
          </button>
          <button onclick="showDeleteConfirmation({{ $company->id }}, '{{ addslashes($company->name) }}')" class="btn btn-sm btn-outline-danger" title="Delete">
            <i class="bi bi-trash"></i>
          </button>
        </div>
      </div>
    @empty
      <div class="col-span-full bg-white rounded-lg shadow p-8 text-center">
        <p class="text-gray-500 text-lg">No companies found.</p>
        <button onclick="openCreateCompanyModal()" class="mt-4 bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">
          Create First Company
        </button>
      </div>
    @endforelse
  </div>
  
  <div class="mt-6">
    <x-pagination :paginator="$companies" wrapper-class="mt-4" />
  </div>

<!-- Assign company admin modules -->
<div id="adminAccessModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-6 border-b">
            <div>
                <h3 class="text-xl font-semibold text-gray-900">Company admin modules</h3>
                <p class="text-sm text-gray-500 mt-1" id="admin-access-company-name"></p>
            </div>
            <button type="button" onclick="closeAdminAccessModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form id="adminAccessForm" class="flex-1 overflow-y-auto p-6" onsubmit="submitAdminAccess(event)">
            <p class="text-sm text-gray-600 mb-3">Tick modules this company admin can use. They can then give only these modules to their staff/users.</p>
            <div class="flex flex-wrap gap-2 mb-3">
                <button type="button" class="px-3 py-1.5 text-sm rounded border border-indigo-300 text-indigo-700 bg-indigo-50 hover:bg-indigo-100" onclick="toggleAdminPackAll(true)">Select all modules</button>
                <button type="button" class="px-3 py-1.5 text-sm rounded border border-gray-300 text-gray-700 hover:bg-gray-50" onclick="toggleAdminPackAll(false)">Clear all</button>
            </div>
            <div id="admin-access-admins" class="text-sm text-gray-600 mb-4"></div>
            <div id="admin-access-matrix"></div>
        </form>
        <div class="flex justify-end gap-2 p-4 border-t bg-gray-50">
            <button type="button" onclick="closeAdminAccessModal()" class="px-4 py-2 rounded border">Cancel</button>
            <button type="submit" form="adminAccessForm" id="admin-access-save" class="px-4 py-2 rounded bg-indigo-600 text-white">Save modules</button>
        </div>
    </div>
</div>

<!-- Create/Edit Company Modal -->
<div id="createCompanyModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-6 border-b">
            <h3 class="text-xl font-semibold text-gray-900" id="modal-title">Create New Company</h3>
            <button onclick="closeCreateCompanyModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form id="createCompanyForm" enctype="multipart/form-data" class="p-6">
            @csrf
            <input type="hidden" name="_method" id="form-method" value="POST">
            <input type="hidden" name="company_id" id="company-id" value="">
            <input type="hidden" name="clear_logo" id="clear-logo-flag" value="0">
            <input type="hidden" name="clear_favicon" id="clear-favicon-flag" value="0">
            <div id="form-errors" class="mb-4 hidden">
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                    <ul class="list-disc list-inside text-sm" id="error-list"></ul>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="company-name" class="w-full border rounded px-3 py-2" required>
                <div class="field-error text-red-600 text-sm mt-1" data-field="name" style="display: none;"></div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Address</label>
                <input type="text" name="address" id="company-address" class="w-full border rounded px-3 py-2">
                <div class="field-error text-red-600 text-sm mt-1" data-field="address" style="display: none;"></div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Logo</label>
                <div id="logo-preview" class="mb-2 hidden">
                    <img id="logo-preview-img" src="" alt="Company Logo" class="h-16 rounded shadow">
                </div>
                <p id="logo-remove-pending" class="text-amber-800 text-sm mb-2 hidden">Current logo will be removed when you save.</p>
                <div class="flex flex-wrap items-center gap-2">
                    <input type="file" name="logo" id="company-logo" accept="image/*" class="flex-1 min-w-[12rem] border rounded px-3 py-2" onchange="previewLogo(this)">
                    <button type="button" onclick="clearLogoFileSelection()" class="text-sm px-3 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-50 whitespace-nowrap" title="Clear the file you chose (does not remove saved logo until you use Remove current)">Clear file</button>
                    <button type="button" id="btn-remove-stored-logo" onclick="markRemoveStoredLogo()" class="hidden text-sm px-3 py-2 rounded border border-amber-300 text-amber-900 bg-amber-50 hover:bg-amber-100 whitespace-nowrap">Remove current logo</button>
                </div>
                <div class="field-error text-red-600 text-sm mt-1" data-field="logo" style="display: none;"></div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Favicon</label>
                <div id="favicon-preview" class="mb-2 hidden">
                    <img id="favicon-preview-img" src="" alt="Company Favicon" class="h-8 w-8 rounded shadow">
                </div>
                <p id="favicon-remove-pending" class="text-amber-800 text-sm mb-2 hidden">Current favicon will be replaced with a generated default when you save.</p>
                <div class="flex flex-wrap items-center gap-2">
                    <input type="file" name="favicon" id="company-favicon" accept="image/*" class="flex-1 min-w-[12rem] border rounded px-3 py-2" onchange="previewFavicon(this)">
                    <button type="button" onclick="clearFaviconFileSelection()" class="text-sm px-3 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-50 whitespace-nowrap" title="Clear the file you chose">Clear file</button>
                    <button type="button" id="btn-remove-stored-favicon" onclick="markRemoveStoredFavicon()" class="hidden text-sm px-3 py-2 rounded border border-amber-300 text-amber-900 bg-amber-50 hover:bg-amber-100 whitespace-nowrap">Reset to default</button>
                </div>
                <p class="text-gray-500 text-xs mt-1">Upload a favicon (32x32 recommended). If not provided, a default favicon will be generated.</p>
                <div class="field-error text-red-600 text-sm mt-1" data-field="favicon" style="display: none;"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Email</label>
                    <input type="email" name="email" id="company-email" class="w-full border rounded px-3 py-2">
                    <div class="field-error text-red-600 text-sm mt-1" data-field="email" style="display: none;"></div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Phone</label>
                    <input type="text" name="phone" id="company-phone" class="w-full border rounded px-3 py-2">
                    <div class="field-error text-red-600 text-sm mt-1" data-field="phone" style="display: none;"></div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Website</label>
                    <input type="url" name="website" id="company-website" class="w-full border rounded px-3 py-2">
                    <div class="field-error text-red-600 text-sm mt-1" data-field="website" style="display: none;"></div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">PAN No. <span class="text-gray-500 font-normal">(प्यान नं.)</span></label>
                    <input type="text" name="tax_number" id="company-tax-number" class="w-full border rounded px-3 py-2" placeholder="e.g. 606961234" autocomplete="off">
                    <p class="text-gray-500 text-xs mt-1">Letterhead माथि दायाँ देखाइन्छ।</p>
                    <div class="field-error text-red-600 text-sm mt-1" data-field="tax_number" style="display: none;"></div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium mb-1">City</label>
                    <input type="text" name="city" id="company-city" class="w-full border rounded px-3 py-2">
                    <div class="field-error text-red-600 text-sm mt-1" data-field="city" style="display: none;"></div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">State</label>
                    <input type="text" name="state" id="company-state" class="w-full border rounded px-3 py-2">
                    <div class="field-error text-red-600 text-sm mt-1" data-field="state" style="display: none;"></div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Country</label>
                    <input type="text" name="country" id="company-country" class="w-full border rounded px-3 py-2">
                    <div class="field-error text-red-600 text-sm mt-1" data-field="country" style="display: none;"></div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">ZIP</label>
                    <input type="text" name="zip" id="company-zip" class="w-full border rounded px-3 py-2">
                    <div class="field-error text-red-600 text-sm mt-1" data-field="zip" style="display: none;"></div>
                </div>
            </div>

            <div class="flex space-x-2 mt-6">
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition" id="submit-btn">
                    Save
                </button>
                <button type="button" onclick="closeCreateCompanyModal()" class="px-4 py-2 rounded border hover:bg-gray-50 transition">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- View Company Modal -->
<div id="viewCompanyModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-6 border-b">
            <h3 class="text-xl font-semibold text-gray-900" id="view-modal-title">Company Details</h3>
            <button onclick="closeViewCompanyModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div id="view-company-content" class="p-6">
            <!-- Loading state -->
            <div id="view-loading" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                <p class="mt-2 text-gray-600">Loading company details...</p>
            </div>
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>

<!-- Company Backups Modal -->
<div id="companyBackupsModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full max-h-[90vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-6 border-b">
            <div>
                <h3 class="text-xl font-semibold text-gray-900">Company Backups</h3>
                <p class="text-sm text-gray-500 mt-1" id="company-backups-name"></p>
                <p class="text-xs text-gray-400 mt-1">Saved under <code class="bg-gray-100 px-1 rounded">company_backups/</code> in monthly folders.</p>
            </div>
            <button type="button" onclick="closeCompanyBackupsModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="p-6 overflow-y-auto flex-1" id="company-backups-content">
            <p class="text-sm text-gray-500">Loading backups...</p>
        </div>
        <div class="p-4 border-t flex justify-end gap-2">
            <button type="button" onclick="closeCompanyBackupsModal()" class="px-4 py-2 rounded border border-gray-300 text-gray-700">Close</button>
            <button type="button" id="company-backups-run-btn" class="px-4 py-2 rounded bg-green-600 text-white hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed">Run Backup Now</button>
        </div>
    </div>
</div>

<!-- Backup Confirmation Modal -->
<div id="backupConfirmationModal" class="fixed inset-0 bg-black bg-opacity-50 z-[60] hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-2xl max-w-md w-full" onclick="event.stopPropagation()">
        <div class="p-6">
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-green-100 rounded-full">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 text-center mb-2">Run Company Backup</h3>
            <div class="text-gray-700 text-sm mb-6 space-y-3">
                <p class="text-center">
                    Create a full backup for <span class="font-semibold text-gray-900" id="backup-confirm-company-name"></span>?
                </p>
                <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg p-3 text-xs">
                    Full backup includes <strong>SQL dump</strong> (<code class="bg-white px-1 rounded">dump.sql</code>), JSON copy, and all uploaded files. Saved under <code class="bg-white px-1 rounded">company_backups/</code>. Large companies may take a minute.
                </div>
            </div>
            <div class="flex space-x-3">
                <button type="button" onclick="closeBackupConfirmation()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium">Cancel</button>
                <button type="button" onclick="confirmCompanyBackup()" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium">Start Backup</button>
            </div>
        </div>
    </div>
</div>

<!-- Backup Progress Modal -->
<div id="backupProgressModal" class="fixed inset-0 bg-black bg-opacity-50 z-[70] hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-2xl max-w-md w-full" onclick="event.stopPropagation()">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 text-center mb-1">Creating backup...</h3>
            <p class="text-sm text-gray-500 text-center mb-4" id="backup-progress-company"></p>
            <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                <div id="backup-progress-bar" class="bg-green-600 h-3 rounded-full transition-all duration-300 ease-out" style="width: 0%"></div>
            </div>
            <p id="backup-progress-percent" class="text-xs text-gray-500 text-right mt-1">0%</p>
            <p id="backup-progress-status" class="text-sm text-gray-600 mt-3 text-center">Starting backup...</p>
            <button type="button" id="backup-progress-close-btn" onclick="closeBackupProgressModal()" class="hidden w-full mt-4 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium">Close</button>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteConfirmationModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-2xl max-w-md w-full" onclick="event.stopPropagation()">
        <div class="p-6">
            <!-- Icon -->
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            
            <!-- Title -->
            <h3 class="text-xl font-semibold text-gray-900 text-center mb-2">Delete Company</h3>
            
            <!-- Message -->
            <div class="text-gray-700 text-sm mb-6 space-y-3">
                <p class="text-center">
                    You are about to permanently delete <span class="font-semibold text-gray-900" id="delete-company-name"></span>.
                </p>
                <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-3">
                    <p class="font-semibold mb-1">Warning: all company data will be deleted.</p>
                    <p>This includes users, projects, income, expenses, staff, reports, bills, invoices, loans, settings, and all related records from every table. This action cannot be undone.</p>
                </div>
            </div>
            
            <!-- Buttons -->
            <form id="delete-company-form" method="POST" class="space-y-4">
                @csrf
                <label class="flex items-start gap-2">
                    <input type="checkbox" id="confirm-delete-all-data" name="confirm_delete_all" value="1" class="mt-1 rounded border-red-300 text-red-600 focus:ring-red-500">
                    <span>I understand that all data linked to this company will be permanently deleted.</span>
                </label>
                <div class="flex space-x-3">
                    <button type="button" onclick="closeDeleteConfirmation()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors font-medium">
                        Cancel
                    </button>
                    <button type="submit" id="delete-company-submit-btn" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                        Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Trial Period Modal -->
<div id="trialPeriodModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-2xl max-w-md w-full" onclick="event.stopPropagation()">
        <div class="p-6">
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-amber-100 rounded-full">
                <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 text-center mb-2" id="trial-period-title">Update Trial Period</h3>
            <p class="text-gray-600 text-sm text-center mb-4" id="trial-period-description"></p>

            <form id="trial-period-form" method="POST">
                @csrf
                <label for="trial-days-input" class="block text-sm font-medium text-gray-700 mb-2">Trial period days</label>
                <input type="number" name="trial_days" id="trial-days-input" min="1" max="365" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500">
                <p class="text-xs text-gray-500 mt-2" id="trial-period-help"></p>

                <div class="flex space-x-3 mt-6">
                    <button type="button" onclick="closeTrialPeriodModal()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors font-medium">
                        Cancel
                    </button>
                    <button type="submit" id="trial-period-submit-btn" class="flex-1 px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors font-medium">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Lock Confirmation Modal -->
<div id="lockConfirmationModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-2xl max-w-md w-full" onclick="event.stopPropagation()">
        <div class="p-6">
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-amber-100 rounded-full">
                <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11V7a4 4 0 10-8 0v4m-1 0h10a2 2 0 012 2v6a2 2 0 01-2 2H3a2 2 0 01-2-2v-6a2 2 0 012-2z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 text-center mb-2">Lock Company</h3>
            <div class="text-gray-700 text-sm mb-6 space-y-3">
                <p class="text-center">
                    Are you sure you want to lock <span class="font-semibold text-gray-900" id="lock-company-name"></span>?
                </p>
                <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-lg p-3">
                    <p>This company will be blocked until payment is resolved. Users from this company will be redirected to the payment required page.</p>
                </div>
            </div>
            <div class="flex space-x-3">
                <button type="button" onclick="closeLockConfirmation()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors font-medium">
                    Cancel
                </button>
                <button type="button" id="lock-company-confirm-btn" onclick="confirmLockCompany()" class="flex-1 px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors font-medium">
                    Lock Company
                </button>
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
const commonTrialDays = {{ $commonTrialDays }};
let currentCompanyId = null;
let deleteCompanyId = null;
let backupsCompanyId = null;
let pendingBackupCompanyId = null;
let pendingBackupCompanyName = null;
let backupProgressInterval = null;
let backupInProgress = false;
let pendingLockCompanyForm = null;
window.editModalStoredLogoUrl = '';
window.editModalStoredFaviconUrl = '';

function handleCompanyCardClick(event, companyId) {
    if (event.target.closest('.company-card-actions')) {
        return;
    }
    const form = document.getElementById('enter-company-' + companyId);
    if (form) {
        form.submit();
    }
}

function openCompanyBackupsModal(companyId, companyName) {
    backupsCompanyId = companyId;
    document.getElementById('company-backups-name').textContent = companyName || '';
    document.getElementById('companyBackupsModal').classList.remove('hidden');
    loadCompanyBackups(companyId);
}

function closeCompanyBackupsModal() {
    document.getElementById('companyBackupsModal').classList.add('hidden');
    backupsCompanyId = null;
}

function loadCompanyBackups(companyId) {
    const content = document.getElementById('company-backups-content');
    content.innerHTML = '<p class="text-sm text-gray-500">Loading backups...</p>';

    fetch(`/admin/companies/${companyId}/backups`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        const backups = data.backups || [];
        if (!backups.length) {
            content.innerHTML = '<p class="text-sm text-gray-500">No backups yet for this company. Use "Run Backup Now" to create one.</p>';
            return;
        }

        let html = '<div class="overflow-x-auto"><table class="min-w-full text-sm"><thead><tr class="border-b text-left text-gray-500">';
        html += '<th class="py-2 pr-4">Month</th><th class="py-2 pr-4">File</th><th class="py-2 pr-4">Type</th><th class="py-2 pr-4">Size</th><th class="py-2 pr-4">Created</th><th class="py-2">Download</th></tr></thead><tbody>';
        backups.forEach(function(backup) {
            const downloadUrl = `/admin/companies/${companyId}/backups/${encodeURIComponent(backup.month)}/${encodeURIComponent(backup.filename)}`;
            html += `<tr class="border-b border-gray-100">
                <td class="py-2 pr-4">${escapeHtml(backup.month)}</td>
                <td class="py-2 pr-4 font-mono text-xs">${escapeHtml(backup.filename)}</td>
                <td class="py-2 pr-4">${escapeHtml(backup.trigger)}</td>
                <td class="py-2 pr-4">${escapeHtml(backup.size_human)}</td>
                <td class="py-2 pr-4">${escapeHtml(backup.created_at)}</td>
                <td class="py-2"><a href="${downloadUrl}" class="text-indigo-600 hover:text-indigo-800">Download</a></td>
            </tr>`;
        });
        html += '</tbody></table></div>';
        content.innerHTML = html;
    })
    .catch(function() {
        content.innerHTML = '<p class="text-sm text-red-600">Failed to load backups.</p>';
    });
}

document.getElementById('company-backups-run-btn').addEventListener('click', function() {
    if (!backupsCompanyId || backupInProgress) {
        return;
    }
    const companyName = document.getElementById('company-backups-name').textContent || '';
    openBackupConfirmation(backupsCompanyId, companyName);
});

document.getElementById('companiesContainer').addEventListener('submit', function(e) {
    const form = e.target.closest('.backup-company-form');
    if (!form) {
        return;
    }
    e.preventDefault();
    if (backupInProgress) {
        return;
    }
    const companyId = form.action.match(/companies\/(\d+)\/backup/)?.[1];
    if (!companyId) {
        return;
    }
    openBackupConfirmation(companyId, form.dataset.companyName || '');
});

function openBackupConfirmation(companyId, companyName) {
    pendingBackupCompanyId = companyId;
    pendingBackupCompanyName = companyName || '';
    document.getElementById('backup-confirm-company-name').textContent = companyName || 'this company';
    document.getElementById('backupConfirmationModal').classList.remove('hidden');
}

function closeBackupConfirmation() {
    document.getElementById('backupConfirmationModal').classList.add('hidden');
    pendingBackupCompanyId = null;
    pendingBackupCompanyName = null;
}

function confirmCompanyBackup() {
    if (!pendingBackupCompanyId || backupInProgress) {
        return;
    }
    const companyId = pendingBackupCompanyId;
    const companyName = pendingBackupCompanyName || '';
    closeBackupConfirmation();
    runCompanyBackup(companyId, companyName);
}

function setBackupButtonsDisabled(disabled) {
    document.querySelectorAll('.backup-company-form button, #company-backups-run-btn').forEach(function(btn) {
        btn.disabled = disabled;
    });
}

function startBackupProgress(companyName) {
    const bar = document.getElementById('backup-progress-bar');
    const status = document.getElementById('backup-progress-status');
    const percent = document.getElementById('backup-progress-percent');
    const closeBtn = document.getElementById('backup-progress-close-btn');

    document.getElementById('backup-progress-company').textContent = companyName || '';
    bar.style.width = '0%';
    bar.classList.remove('bg-red-600');
    bar.classList.add('bg-green-600');
    status.classList.remove('text-red-600');
    status.classList.add('text-gray-600');
    percent.textContent = '0%';
    status.textContent = 'Starting backup...';
    closeBtn.classList.add('hidden');
    document.getElementById('backupProgressModal').classList.remove('hidden');

    if (backupProgressInterval) {
        clearInterval(backupProgressInterval);
    }

    const stages = [
        { at: 8, text: 'Scanning company data...' },
        { at: 25, text: 'Building SQL dump...' },
        { at: 45, text: 'Exporting table JSON...' },
        { at: 65, text: 'Collecting uploaded files...' },
        { at: 82, text: 'Creating ZIP archive...' },
        { at: 90, text: 'Saving backup to folder...' },
    ];

    let progress = 0;
    backupProgressInterval = setInterval(function() {
        if (progress < 90) {
            progress += Math.random() * 4 + 1;
            progress = Math.min(90, progress);
            bar.style.width = progress + '%';
            percent.textContent = Math.round(progress) + '%';
            const stage = stages.filter(function(s) { return progress >= s.at; }).pop();
            if (stage) {
                status.textContent = stage.text;
            }
        }
    }, 350);
}

function finishBackupProgress(success, message) {
    if (backupProgressInterval) {
        clearInterval(backupProgressInterval);
        backupProgressInterval = null;
    }

    const bar = document.getElementById('backup-progress-bar');
    const status = document.getElementById('backup-progress-status');
    const percent = document.getElementById('backup-progress-percent');
    const closeBtn = document.getElementById('backup-progress-close-btn');

    if (success) {
        bar.style.width = '100%';
        percent.textContent = '100%';
        status.textContent = message || 'Backup completed successfully!';
        setTimeout(function() {
            closeBackupProgressModal();
        }, 1500);
        return;
    }

    bar.classList.remove('bg-green-600');
    bar.classList.add('bg-red-600');
    status.classList.remove('text-gray-600');
    status.classList.add('text-red-600');
    status.textContent = message || 'Backup failed.';
    closeBtn.classList.remove('hidden');
}

function closeBackupProgressModal() {
    document.getElementById('backupProgressModal').classList.add('hidden');
    if (backupProgressInterval) {
        clearInterval(backupProgressInterval);
        backupProgressInterval = null;
    }
}

function runCompanyBackup(companyId, companyName) {
    if (backupInProgress) {
        return;
    }

    backupInProgress = true;
    setBackupButtonsDisabled(true);
    startBackupProgress(companyName);

    fetch(`/admin/companies/${companyId}/backup`, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
        },
    })
    .then(function(response) {
        return response.json().then(function(data) {
            return { ok: response.ok, data: data };
        });
    })
    .then(function(result) {
        if (!result.ok || !result.data.success) {
            throw new Error(result.data.message || 'Backup failed');
        }
        finishBackupProgress(true, result.data.message || 'Backup saved successfully.');
        showNotification(result.data.message || 'Backup saved successfully.', 'success');
        if (backupsCompanyId === Number(companyId)) {
            loadCompanyBackups(companyId);
        }
    })
    .catch(function(error) {
        finishBackupProgress(false, error.message || 'Backup failed.');
        showNotification(error.message || 'Backup failed.', 'error');
    })
    .finally(function() {
        backupInProgress = false;
        setBackupButtonsDisabled(false);
    });
}

function openTrialPeriodModal(scope, actionUrl, companyName, currentDays) {
    const modal = document.getElementById('trialPeriodModal');
    const form = document.getElementById('trial-period-form');
    const title = document.getElementById('trial-period-title');
    const description = document.getElementById('trial-period-description');
    const input = document.getElementById('trial-days-input');
    const help = document.getElementById('trial-period-help');
    const submitBtn = document.getElementById('trial-period-submit-btn');

    form.action = actionUrl;
    title.textContent = scope === 'overall' ? 'Update Overall Trial Period' : 'Update Company Trial Period';
    description.textContent = scope === 'overall'
        ? 'This becomes the common default for new registered companies and companies without a manual override.'
        : `This creates/updates a manual trial override only for ${companyName || 'this company'}.`;
    help.textContent = scope === 'overall'
        ? 'Companies with manual overrides will keep their own trial period. New registrations will use this common default.'
        : 'Use this when a specific company requests extra days. Other companies will keep the common default.';
    input.value = currentDays || commonTrialDays;
    submitBtn.disabled = false;
    submitBtn.textContent = 'Update';
    modal.classList.remove('hidden');
    input.focus();
}

function closeTrialPeriodModal() {
    const modal = document.getElementById('trialPeriodModal');
    const form = document.getElementById('trial-period-form');
    const submitBtn = document.getElementById('trial-period-submit-btn');

    modal.classList.add('hidden');
    form.reset();
    form.action = '';
    submitBtn.disabled = false;
    submitBtn.textContent = 'Update';
}

document.getElementById('trial-period-form').addEventListener('submit', function() {
    const submitBtn = document.getElementById('trial-period-submit-btn');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Updating...';
});

document.getElementById('trialPeriodModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeTrialPeriodModal();
    }
});

function resetCompanyAssetFlags() {
    document.getElementById('clear-logo-flag').value = '0';
    document.getElementById('clear-favicon-flag').value = '0';
    document.getElementById('logo-remove-pending').classList.add('hidden');
    document.getElementById('favicon-remove-pending').classList.add('hidden');
    document.getElementById('btn-remove-stored-logo').classList.add('hidden');
    document.getElementById('btn-remove-stored-favicon').classList.add('hidden');
}

function openCreateCompanyModal() {
    currentCompanyId = null;
    window.editModalStoredLogoUrl = '';
    window.editModalStoredFaviconUrl = '';
    document.getElementById('modal-title').textContent = 'Create New Company';
    document.getElementById('form-method').value = 'POST';
    document.getElementById('company-id').value = '';
    document.getElementById('createCompanyModal').classList.remove('hidden');
    document.getElementById('createCompanyForm').reset();
    resetCompanyAssetFlags();
    document.getElementById('form-errors').classList.add('hidden');
    document.getElementById('logo-preview').classList.add('hidden');
    document.getElementById('favicon-preview').classList.add('hidden');
    document.querySelectorAll('.field-error').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
    const submitBtn = document.getElementById('submit-btn');
    submitBtn.disabled = false;
    submitBtn.textContent = 'Save';
}

function openEditCompanyModal(companyId) {
    currentCompanyId = companyId;
    window.editModalStoredLogoUrl = '';
    window.editModalStoredFaviconUrl = '';
    document.getElementById('modal-title').textContent = 'Edit Company';
    document.getElementById('form-method').value = 'PUT';
    document.getElementById('company-id').value = companyId;
    resetCompanyAssetFlags();
    
    // Fetch company data
    fetch(`/admin/companies/${companyId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.company) {
            const company = data.company;
            document.getElementById('company-name').value = company.name || '';
            document.getElementById('company-address').value = company.address || '';
            document.getElementById('company-email').value = company.email || '';
            document.getElementById('company-phone').value = company.phone || '';
            document.getElementById('company-website').value = company.website || '';
            document.getElementById('company-tax-number').value = company.tax_number || '';
            document.getElementById('company-city').value = company.city || '';
            document.getElementById('company-state').value = company.state || '';
            document.getElementById('company-country').value = company.country || '';
            document.getElementById('company-zip').value = company.zip || '';
            
            // Show logo preview if exists
            if (company.logo_url) {
                document.getElementById('logo-preview-img').src = company.logo_url;
                document.getElementById('logo-preview').classList.remove('hidden');
            } else {
                document.getElementById('logo-preview').classList.add('hidden');
            }
            
            // Show favicon preview if exists
            if (company.favicon_url) {
                document.getElementById('favicon-preview-img').src = company.favicon_url;
                document.getElementById('favicon-preview').classList.remove('hidden');
            } else {
                document.getElementById('favicon-preview').classList.add('hidden');
            }
            
            document.getElementById('createCompanyModal').classList.remove('hidden');
            document.getElementById('form-errors').classList.add('hidden');
            document.querySelectorAll('.field-error').forEach(el => {
                el.style.display = 'none';
                el.textContent = '';
            });
            const submitBtn = document.getElementById('submit-btn');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Update';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Failed to load company data', 'error');
    });
}

function closeCreateCompanyModal() {
    document.getElementById('createCompanyModal').classList.add('hidden');
    document.getElementById('createCompanyForm').reset();
    resetCompanyAssetFlags();
    window.editModalStoredLogoUrl = '';
    window.editModalStoredFaviconUrl = '';
    document.getElementById('form-errors').classList.add('hidden');
    document.getElementById('logo-preview').classList.add('hidden');
    document.getElementById('favicon-preview').classList.add('hidden');
    document.querySelectorAll('.field-error').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
    // Re-enable submit button
    const submitBtn = document.getElementById('submit-btn');
    submitBtn.disabled = false;
    submitBtn.textContent = 'Save';
    currentCompanyId = null;
}

function previewLogo(input) {
    document.getElementById('clear-logo-flag').value = '0';
    document.getElementById('logo-remove-pending').classList.add('hidden');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('logo-preview-img').src = e.target.result;
            document.getElementById('logo-preview').classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function previewFavicon(input) {
    document.getElementById('clear-favicon-flag').value = '0';
    document.getElementById('favicon-remove-pending').classList.add('hidden');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('favicon-preview-img').src = e.target.result;
            document.getElementById('favicon-preview').classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function clearLogoFileSelection() {
    const input = document.getElementById('company-logo');
    input.value = '';
    const removePending = document.getElementById('clear-logo-flag').value === '1';
    if (removePending) {
        document.getElementById('logo-preview').classList.add('hidden');
        return;
    }
    if (window.editModalStoredLogoUrl) {
        document.getElementById('logo-preview-img').src = window.editModalStoredLogoUrl;
        document.getElementById('logo-preview').classList.remove('hidden');
    } else {
        document.getElementById('logo-preview').classList.add('hidden');
    }
}

function clearFaviconFileSelection() {
    const input = document.getElementById('company-favicon');
    input.value = '';
    const removePending = document.getElementById('clear-favicon-flag').value === '1';
    if (removePending) {
        document.getElementById('favicon-preview').classList.add('hidden');
        return;
    }
    if (window.editModalStoredFaviconUrl) {
        document.getElementById('favicon-preview-img').src = window.editModalStoredFaviconUrl;
        document.getElementById('favicon-preview').classList.remove('hidden');
    } else {
        document.getElementById('favicon-preview').classList.add('hidden');
    }
}

function markRemoveStoredLogo() {
    document.getElementById('clear-logo-flag').value = '1';
    document.getElementById('company-logo').value = '';
    document.getElementById('logo-preview').classList.add('hidden');
    document.getElementById('logo-remove-pending').classList.remove('hidden');
}

function markRemoveStoredFavicon() {
    document.getElementById('clear-favicon-flag').value = '1';
    document.getElementById('company-favicon').value = '';
    document.getElementById('favicon-preview').classList.add('hidden');
    document.getElementById('favicon-remove-pending').classList.remove('hidden');
}

document.getElementById('createCompanyForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const formData = new FormData(form);
    const submitBtn = document.getElementById('submit-btn');
    const originalText = submitBtn.textContent;
    const isEdit = currentCompanyId !== null;
    const url = isEdit ? `/admin/companies/${currentCompanyId}` : '{{ route("admin.companies.store") }}';
    const method = isEdit ? 'POST' : 'POST';
    
    // Hide previous errors
    document.getElementById('form-errors').classList.add('hidden');
    document.querySelectorAll('.field-error').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
    
    submitBtn.disabled = true;
    submitBtn.textContent = isEdit ? 'Updating...' : 'Saving...';
    
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
                throw new Error('Server returned HTML instead of JSON');
            });
        }
    })
    .then(data => {
        if (data.success) {
            // Re-enable button before closing modal
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
            
            closeCreateCompanyModal();
            
            if (isEdit) {
                // Update existing row
                updateCompanyRow(data.company);
                showSuccessMessage('Company updated successfully!');
            } else {
                // Add new row to table
                addCompanyRow(data.company);
                showSuccessMessage('Company created successfully!');
            }
        } else {
            // Handle validation errors
            if (data.errors) {
                const errorList = document.getElementById('error-list');
                errorList.innerHTML = '';
                document.getElementById('form-errors').classList.remove('hidden');
                
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
                showNotification(data.message || (isEdit ? 'Failed to update company' : 'Failed to create company'), 'error');
            }
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred: ' + error.message, 'error');
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeCreateCompanyModal();
    }
});

// Close modal when clicking outside
document.getElementById('createCompanyModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCreateCompanyModal();
    }
});

// Function to show notification message
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

// Function to show success message (backward compatibility)
function showSuccessMessage(message) {
    showNotification(message, 'success');
}

// Function to add new company row to table
function addCompanyRow(company) {
    const container = document.getElementById('companiesContainer');
    
    // Check if container is empty
    const emptyDiv = container.querySelector('.col-span-full');
    if (emptyDiv) {
        container.innerHTML = '';
    }
    
    const card = document.createElement('div');
    card.className = 'bg-white rounded-lg shadow-md hover:shadow-lg transition-all duration-200 p-6 company-card group cursor-pointer border-2 border-transparent hover:border-indigo-400';
    card.setAttribute('data-company-id', company.id);
    card.setAttribute('title', 'Click to enter ' + (company.name || ''));
    card.setAttribute('onclick', 'handleCompanyCardClick(event, ' + company.id + ')');
    
    const enterFormHtml = `
      <form id="enter-company-${company.id}" method="POST" action="{{ route('admin.companies.switch') }}" class="hidden">
        <input type="hidden" name="_token" value="${escapeHtml(csrfToken)}">
        <input type="hidden" name="company_id" value="${company.id}">
        <input type="hidden" name="redirect_to" value="dashboard">
      </form>`;
    
    const logoHtml = company.logo_url ? `<img src="${escapeHtml(company.logo_url)}" alt="${escapeHtml(company.name || '')} Logo" class="h-12 w-auto mt-2">` : '';
    const emailHtml = company.email ? `
      <div class="flex items-center text-sm text-gray-600">
        <i class="bi bi-envelope me-2"></i>
        <span class="truncate">${escapeHtml(company.email)}</span>
      </div>
    ` : '';
    const phoneHtml = company.phone ? `
      <div class="flex items-center text-sm text-gray-600">
        <i class="bi bi-telephone me-2"></i>
        <span>${escapeHtml(company.phone)}</span>
      </div>
    ` : '';
    const addressHtml = company.address ? `
      <div class="flex items-start text-sm text-gray-600">
        <i class="bi bi-geo-alt me-2 mt-0.5"></i>
        <span class="flex-1">${escapeHtml(company.address.length > 50 ? company.address.substring(0, 50) + '...' : company.address)}</span>
      </div>
    ` : '';
    const isLocked = company.subscription_status === 'locked' || !!company.locked_at;
    const statusHtml = isLocked
      ? `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">Locked${company.lock_reason ? ' - ' + escapeHtml(String(company.lock_reason).replaceAll('_', ' ')) : ''}</span>`
      : (company.subscription_status === 'trialing'
          ? `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">Trial - ${commonTrialDays} day(s)</span>`
          : '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">Active</span>');
    const isActive = company.subscription_status === 'active' && !isLocked;
    const isTrialing = company.subscription_status === 'trialing';
    const activateButtonHtml = isTrialing
      ? `<form method="POST" action="/admin/companies/${company.id}/activate" class="inline" onsubmit="return confirm('Activate this company and end the trial period?')">
          <input type="hidden" name="_token" value="${escapeHtml(csrfToken)}">
          <button type="submit" class="btn btn-sm btn-outline-success" title="Activate"><i class="bi bi-check-circle"></i></button>
        </form>`
      : '';
    const lockButtonHtml = isLocked
      ? `<form method="POST" action="/admin/companies/${company.id}/unlock" class="inline">
          <input type="hidden" name="_token" value="${escapeHtml(csrfToken)}">
          <button type="submit" class="btn btn-sm btn-outline-success" title="Unlock"><i class="bi bi-unlock"></i></button>
        </form>`
      : (isTrialing ? activateButtonHtml
      : `<form method="POST" action="/admin/companies/${company.id}/lock" class="inline lock-company-form" data-company-name="${escapeHtml(company.name || '')}">
          <input type="hidden" name="_token" value="${escapeHtml(csrfToken)}">
          <button type="submit" class="btn btn-sm btn-outline-secondary" title="Lock"><i class="bi bi-lock"></i></button>
        </form>`);
    const trialButtonHtml = isActive ? '' : `
          <button type="button" onclick="openTrialPeriodModal('company', '/admin/companies/${company.id}/trial-period', '${escapeHtml((company.name || '').replace(/'/g, "\\'"))}', commonTrialDays)" class="btn btn-sm btn-outline-info" title="Update Trial Period">
            <i class="bi bi-clock-history"></i>
          </button>`;
    
    card.innerHTML = enterFormHtml + `
        <div class="flex items-start justify-between mb-4">
          <div class="flex-1">
            <div class="flex items-start justify-between gap-2">
              <h3 class="text-lg font-semibold text-gray-900 mb-1">${escapeHtml(company.name || '')}</h3>
              <svg class="h-5 w-5 flex-shrink-0 text-gray-300 group-hover:text-indigo-500 transition-colors company-card-enter-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
              </svg>
            </div>
            <div class="flex flex-wrap gap-2 mb-2">${statusHtml}</div>
            ${logoHtml}
          </div>
        </div>
        
        <div class="space-y-2 mb-4">
          ${emailHtml}
          ${phoneHtml}
          ${addressHtml}
        </div>
        
        <div class="flex items-center justify-end gap-1 pt-4 border-t company-card-actions">
          ${lockButtonHtml}
          ${trialButtonHtml}
          <button onclick="openViewCompanyModal(${company.id})" class="btn btn-sm btn-outline-primary" title="View">
            <i class="bi bi-eye"></i>
          </button>
          <button onclick="openEditCompanyModal(${company.id})" class="btn btn-sm btn-outline-warning" title="Edit">
            <i class="bi bi-pencil"></i>
          </button>
          <form method="POST" action="/admin/companies/${company.id}/backup" class="inline backup-company-form" data-company-name="${escapeHtml(company.name || '')}">
            <input type="hidden" name="_token" value="${escapeHtml(csrfToken)}">
            <button type="submit" class="btn btn-sm btn-outline-success" title="Run backup now">
              <i class="bi bi-cloud-upload"></i>
            </button>
          </form>
          <button type="button" onclick="openCompanyBackupsModal(${company.id}, ${JSON.stringify(company.name || '')})" class="btn btn-sm btn-outline-secondary" title="View saved backups">
            <i class="bi bi-archive"></i>
          </button>
          <button onclick="showDeleteConfirmation(${company.id}, ${JSON.stringify(escapeHtml(company.name || ''))})" class="btn btn-sm btn-outline-danger" title="Delete">
            <i class="bi bi-trash"></i>
          </button>
        </div>
    `;
    
    // Insert at the beginning of container
    container.insertBefore(card, container.firstChild);
    
    // Add fade-in animation
    card.style.opacity = '0';
    card.style.transform = 'translateY(-10px)';
    setTimeout(() => {
        card.style.transition = 'all 0.3s ease';
        card.style.opacity = '1';
        card.style.transform = 'translateY(0)';
    }, 10);
}

// Function to update existing company row
function updateCompanyRow(company) {
    const card = document.querySelector(`div[data-company-id="${company.id}"]`);
    if (card) {
        // Update card content
        const nameEl = card.querySelector('h3');
        if (nameEl) nameEl.textContent = company.name || '';
        
        const logoContainer = card.querySelector('.flex-1');
        if (logoContainer) {
            let logoImg = logoContainer.querySelector('img');
            if (company.logo_url) {
                if (!logoImg) {
                    logoImg = document.createElement('img');
                    logoImg.className = 'h-12 w-auto mt-2';
                    logoImg.alt = (company.name || '') + ' Logo';
                    logoContainer.appendChild(logoImg);
                }
                logoImg.src = company.logo_url;
            } else if (logoImg) {
                logoImg.remove();
            }
        }
        
        // Update email
        const emailContainer = card.querySelector('.space-y-2');
        if (emailContainer) {
            let emailDiv = emailContainer.querySelector('.bi-envelope')?.closest('div');
            if (company.email) {
                if (!emailDiv) {
                    emailDiv = document.createElement('div');
                    emailDiv.className = 'flex items-center text-sm text-gray-600';
                    emailDiv.innerHTML = '<i class="bi bi-envelope me-2"></i><span class="truncate"></span>';
                    emailContainer.insertBefore(emailDiv, emailContainer.firstChild);
                }
                emailDiv.querySelector('span').textContent = company.email;
            } else if (emailDiv) {
                emailDiv.remove();
            }
        }
        
        // Update phone
        if (emailContainer) {
            let phoneDiv = emailContainer.querySelector('.bi-telephone')?.closest('div');
            if (company.phone) {
                if (!phoneDiv) {
                    phoneDiv = document.createElement('div');
                    phoneDiv.className = 'flex items-center text-sm text-gray-600';
                    phoneDiv.innerHTML = '<i class="bi bi-telephone me-2"></i><span></span>';
                    emailContainer.appendChild(phoneDiv);
                }
                phoneDiv.querySelector('span').textContent = company.phone;
            } else if (phoneDiv) {
                phoneDiv.remove();
            }
        }
        
        // Update address
        if (emailContainer) {
            let addressDiv = emailContainer.querySelector('.bi-geo-alt')?.closest('div');
            if (company.address) {
                const addressText = company.address.length > 50 ? company.address.substring(0, 50) + '...' : company.address;
                if (!addressDiv) {
                    addressDiv = document.createElement('div');
                    addressDiv.className = 'flex items-start text-sm text-gray-600';
                    addressDiv.innerHTML = '<i class="bi bi-geo-alt me-2 mt-0.5"></i><span class="flex-1"></span>';
                    emailContainer.appendChild(addressDiv);
                }
                addressDiv.querySelector('span').textContent = addressText;
            } else if (addressDiv) {
                addressDiv.remove();
            }
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

// Function to escape HTML to prevent XSS
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// View Company Functions
function openViewCompanyModal(companyId) {
    // Ensure companyId is a number and not undefined
    if (!companyId || isNaN(companyId)) {
        console.error('Invalid company ID:', companyId);
        showNotification('Invalid company ID', 'error');
        return;
    }
    
    // Clear any previous content
    const modal = document.getElementById('viewCompanyModal');
    const content = document.getElementById('view-company-content');
    
    modal.classList.remove('hidden');
    content.innerHTML = '<div id="view-loading" class="text-center py-8"><div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div><p class="mt-2 text-gray-600">Loading company details...</p></div>';
    
    // Use the companyId parameter directly, ensure it's a number
    const companyIdNum = parseInt(companyId, 10);
    console.log('Loading company details for ID:', companyIdNum);
    
    fetch(`/admin/companies/${companyIdNum}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        cache: 'no-cache' // Prevent caching
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Failed to load company details');
        }
        return response.json();
    })
    .then(data => {
        if (data.company) {
            const company = data.company;
            console.log('Loaded company:', company.id, company.name);
            
            // Verify the loaded company matches the requested ID
            if (company.id != companyIdNum) {
                console.warn('Company ID mismatch. Requested:', companyIdNum, 'Received:', company.id);
            }
            
            document.getElementById('view-modal-title').textContent = company.name || 'Company Details';
            
            let html = `
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-1 flex items-start justify-center">
            `;
            
            if (company.logo_url) {
                html += `<img src="${escapeHtml(company.logo_url)}" alt="Logo" class="h-32 rounded shadow bg-white p-2">`;
            } else {
                html += `<div class="h-32 w-32 flex items-center justify-center rounded bg-gray-100 text-gray-600 font-semibold text-center">${escapeHtml(company.name || 'N/A')}</div>`;
            }
            
            html += `
                    </div>
                    <div class="md:col-span-2">
                        <!-- Project Count Card -->
                        <div class="mb-6 bg-gradient-to-br from-indigo-50 to-blue-50 rounded-lg p-6 border border-indigo-100 shadow-sm cursor-pointer hover:shadow-md transition-all duration-200 hover:from-indigo-100 hover:to-blue-100" onclick="viewCompanyProjects(${company.id})" title="Click to view projects">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="bg-indigo-600 rounded-full p-3 shadow-lg">
                                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-sm text-gray-600 font-medium mb-1">Total Projects</div>
                                        <div class="text-3xl font-bold text-indigo-700 flex items-center gap-2">
                                            ${company.project_count || 0}
                                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </div>
                                        <div class="text-xs text-indigo-600 mt-1">Click to view all projects</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs text-gray-500 mb-1">Active Company</div>
                                    <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                                        Active
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Company Details Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="text-sm text-gray-500 mb-1">Name</div>
                            <div class="font-medium">${escapeHtml(company.name || '—')}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 mb-1">Address</div>
                            <div class="font-medium">${escapeHtml(company.address || '—')}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 mb-1">Email</div>
                            <div class="font-medium">${escapeHtml(company.email || '—')}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 mb-1">Phone</div>
                            <div class="font-medium">${escapeHtml(company.phone || '—')}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 mb-1">Website</div>
                            <div class="font-medium">${company.website ? `<a href="${escapeHtml(company.website)}" target="_blank" class="text-indigo-600 hover:underline">${escapeHtml(company.website)}</a>` : '—'}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 mb-1">PAN No.</div>
                            <div class="font-medium">${escapeHtml(company.tax_number || '—')}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 mb-1">City</div>
                            <div class="font-medium">${escapeHtml(company.city || '—')}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 mb-1">State</div>
                            <div class="font-medium">${escapeHtml(company.state || '—')}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 mb-1">Country</div>
                            <div class="font-medium">${escapeHtml(company.country || '—')}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 mb-1">ZIP</div>
                            <div class="font-medium">${escapeHtml(company.zip || '—')}</div>
                        </div>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex space-x-3 justify-end">
                    <button onclick="closeViewCompanyModal(); openEditCompanyModal(${company.id})" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">
                        Edit
                    </button>
                    <button onclick="closeViewCompanyModal()" class="px-4 py-2 rounded border hover:bg-gray-50 transition">
                        Close
                    </button>
                </div>
            `;
            
            document.getElementById('view-company-content').innerHTML = html;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('view-company-content').innerHTML = `
            <div class="text-center py-8">
                <p class="text-red-600">Failed to load company details.</p>
                <button onclick="closeViewCompanyModal()" class="mt-4 px-4 py-2 rounded border hover:bg-gray-50 transition">
                    Close
                </button>
  </div>
        `;
    });
}

function closeViewCompanyModal() {
    const modal = document.getElementById('viewCompanyModal');
    const content = document.getElementById('view-company-content');
    const title = document.getElementById('view-modal-title');
    
    modal.classList.add('hidden');
    // Clear content when closing
    content.innerHTML = '<div id="view-loading" class="text-center py-8"><div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div><p class="mt-2 text-gray-600">Loading company details...</p></div>';
    title.textContent = 'Company Details';
}

// Function to view company projects
function viewCompanyProjects(companyId) {
    // Close the company modal first
    closeViewCompanyModal();
    
    // Check if user is super admin (only super admin can switch companies)
    // For regular admins, they can only see their own company's projects
    const userRole = '{{ Auth::user()->role ?? "" }}';
    const projectsUrl = '{{ route("admin.projects.index") }}';
    
    if (userRole === 'super_admin') {
        // For super admin, switch company context first using form data, then navigate via AJAX
        const formData = new FormData();
        formData.append('company_id', companyId);
        formData.append('redirect_to', 'projects');
        formData.append('_token', '{{ csrf_token() }}');
        
        fetch('{{ route("admin.companies.switch") }}', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Navigate to projects page via AJAX (no page refresh)
                if (typeof window.loadPageViaAjax === 'function') {
                    window.loadPageViaAjax(projectsUrl);
                } else if (typeof loadPageViaAjax === 'function') {
                    loadPageViaAjax(projectsUrl);
                } else {
                    // Fallback: use window.location if loadPageViaAjax is not available
                    window.location.href = projectsUrl;
                }
            } else {
                console.error('Error switching company:', data.error);
                showNotification(data.error || 'Failed to switch company', 'error');
            }
        })
        .catch(error => {
            console.error('Error switching company:', error);
            // Fallback: try AJAX navigation (might work if session is already set)
            if (typeof window.loadPageViaAjax === 'function') {
                window.loadPageViaAjax(projectsUrl);
            } else if (typeof loadPageViaAjax === 'function') {
                loadPageViaAjax(projectsUrl);
            } else {
                window.location.href = projectsUrl;
            }
        });
    } else {
        // For regular admins, navigate to projects via AJAX (they'll see their company's projects)
        if (typeof window.loadPageViaAjax === 'function') {
            window.loadPageViaAjax(projectsUrl);
        } else if (typeof loadPageViaAjax === 'function') {
            loadPageViaAjax(projectsUrl);
        } else {
            window.location.href = projectsUrl;
        }
    }
}

// Close view modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !document.getElementById('viewCompanyModal').classList.contains('hidden')) {
        closeViewCompanyModal();
    }
});

// Close view modal when clicking outside
document.getElementById('viewCompanyModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeViewCompanyModal();
    }
});

// Lock Confirmation Functions
function showLockConfirmation(form) {
    pendingLockCompanyForm = form;
    const companyName = form.getAttribute('data-company-name') || 'this company';
    document.getElementById('lock-company-name').textContent = companyName;
    const confirmBtn = document.getElementById('lock-company-confirm-btn');
    confirmBtn.disabled = false;
    confirmBtn.textContent = 'Lock Company';
    document.getElementById('lockConfirmationModal').classList.remove('hidden');
}

function closeLockConfirmation() {
    document.getElementById('lockConfirmationModal').classList.add('hidden');
    pendingLockCompanyForm = null;
    const confirmBtn = document.getElementById('lock-company-confirm-btn');
    if (confirmBtn) {
        confirmBtn.disabled = false;
        confirmBtn.textContent = 'Lock Company';
    }
}

function confirmLockCompany() {
    if (!pendingLockCompanyForm) return;

    const confirmBtn = document.getElementById('lock-company-confirm-btn');
    confirmBtn.disabled = true;
    confirmBtn.textContent = 'Locking...';
    pendingLockCompanyForm.submit();
}

document.addEventListener('submit', function(e) {
    const form = e.target;
    if (form && form.classList && form.classList.contains('lock-company-form')) {
        e.preventDefault();
        showLockConfirmation(form);
    }
});

document.getElementById('lockConfirmationModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeLockConfirmation();
    }
});

// Delete Confirmation Functions
function resetDeleteSubmitButton() {
    const submitBtn = document.getElementById('delete-company-submit-btn');
    const confirmCheckbox = document.getElementById('confirm-delete-all-data');
    if (confirmCheckbox) {
        confirmCheckbox.checked = false;
    }
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Delete';
    }
}

function showDeleteConfirmation(companyId, companyName) {
    resetDeleteSubmitButton();
    deleteCompanyId = companyId;
    document.getElementById('delete-company-name').textContent = companyName;
    document.getElementById('delete-company-form').action = `/admin/companies/${companyId}/delete`;
    document.getElementById('deleteConfirmationModal').classList.remove('hidden');
}

function closeDeleteConfirmation() {
    document.getElementById('deleteConfirmationModal').classList.add('hidden');
    deleteCompanyId = null;
    resetDeleteSubmitButton();
}

// Handle delete form submission
document.getElementById('delete-company-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const submitBtn = document.getElementById('delete-company-submit-btn');
    const confirmCheckbox = document.getElementById('confirm-delete-all-data');
    if (!confirmCheckbox || !confirmCheckbox.checked) {
        showNotification('Please confirm that all company data will be permanently deleted.', 'error');
        return;
    }

    const originalText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Deleting...';
    
    const companyIdToDelete = deleteCompanyId;
    const formData = new FormData(form);
    formData.set('confirm_delete_all', '1');
    
    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => {
        const contentType = response.headers.get('content-type') || '';
        if (contentType.includes('application/json')) {
            return response.json().then(data => {
                if (!response.ok && !data.message) {
                    throw new Error('Failed to delete company (' + response.status + ')');
                }
                return data;
            });
        }
        if (response.ok) {
            return { success: true };
        }
        throw new Error('Server returned HTML instead of JSON');
    })
    .then(data => {
        if (data.success) {
            closeDeleteConfirmation();
            
            // Remove card from container using the stored company ID
            const card = document.querySelector(`div[data-company-id="${companyIdToDelete}"]`);
            
            if (card) {
                card.style.transition = 'all 0.3s ease';
                card.style.opacity = '0';
                card.style.transform = 'scale(0.8)';
                setTimeout(() => {
                    card.remove();
                    
                    // Check if container is empty
                    const container = document.getElementById('companiesContainer');
                    if (container && container.children.length === 0) {
                        container.innerHTML = `
                            <div class="col-span-full bg-white rounded-lg shadow p-8 text-center">
                                <p class="text-gray-500 text-lg">No companies found.</p>
                                <button onclick="openCreateCompanyModal()" class="mt-4 bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">
                                    Create First Company
                                </button>
  </div>
                        `;
                    }
                }, 300);
            } else {
                console.warn('Card not found for company ID:', companyIdToDelete);
                // If card not found, reload the page
                window.location.reload();
            }
            
            showNotification(data.message || 'Company and all related data deleted successfully!', 'success');
        } else {
            showNotification(data.message || 'Failed to delete company', 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while deleting the company: ' + error.message, 'error');
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
});

document.getElementById('confirm-delete-all-data').addEventListener('change', function() {
    const submitBtn = document.getElementById('delete-company-submit-btn');
    if (submitBtn) {
        submitBtn.disabled = !this.checked;
    }
});

// Close delete modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !document.getElementById('trialPeriodModal').classList.contains('hidden')) {
        closeTrialPeriodModal();
    }
    if (e.key === 'Escape' && !document.getElementById('lockConfirmationModal').classList.contains('hidden')) {
        closeLockConfirmation();
    }
    if (e.key === 'Escape' && !document.getElementById('deleteConfirmationModal').classList.contains('hidden')) {
        closeDeleteConfirmation();
    }
});

// Close delete modal when clicking outside
document.getElementById('deleteConfirmationModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteConfirmation();
    }
});

let adminAccessCompanyId = null;

function closeAdminAccessModal() {
    document.getElementById('adminAccessModal').classList.add('hidden');
    adminAccessCompanyId = null;
}

function openAdminAccessModal(companyId, companyName) {
    adminAccessCompanyId = companyId;
    document.getElementById('admin-access-company-name').textContent = companyName || '';
    document.getElementById('admin-access-matrix').innerHTML = '<p class="text-sm text-gray-500">Loading...</p>';
    document.getElementById('admin-access-admins').textContent = '';
    document.getElementById('adminAccessModal').classList.remove('hidden');

    fetch(`/admin/companies/${companyId}/admin-access`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        const admins = (data.admins || []);
        document.getElementById('admin-access-admins').textContent = admins.length
            ? 'Company admin: ' + admins.map(a => a.name + ' (' + a.email + ')').join(', ')
            : 'No company admin user yet. Create an Admin user after saving these modules.';
        renderAdminAccessMatrix(data.grouped_modules || {}, data.matrix || {});
    })
    .catch(() => {
        document.getElementById('admin-access-matrix').innerHTML = '<p class="text-sm text-red-600">Failed to load modules.</p>';
    });
}

function renderAdminAccessMatrix(groups, matrix) {
    let html = '';
    Object.keys(groups).forEach(groupKey => {
        const group = groups[groupKey];
        html += `<div class="mb-4 border rounded-lg overflow-hidden">
            <div class="px-3 py-2 bg-gray-50 text-xs font-semibold uppercase text-gray-500 flex items-center justify-between">
                <span>${group.label || groupKey}</span>
                <label class="normal-case font-medium text-gray-600 flex items-center gap-1">
                    <input type="checkbox" class="admin-pack-group-all rounded" data-group="${groupKey}" onchange="toggleAdminPackGroup('${groupKey}', this.checked)">
                    Select all
                </label>
            </div>
            <table class="min-w-full text-sm">
                <thead><tr class="text-gray-500">
                    <th class="text-left px-3 py-2">Module</th>
                    <th class="text-center w-16">All</th>
                    <th class="text-center w-16">View</th>
                    <th class="text-center w-16">Create</th>
                    <th class="text-center w-16">Edit</th>
                    <th class="text-center w-16">Delete</th>
                </tr></thead><tbody>`;
        Object.keys(group.modules || {}).forEach(moduleKey => {
            const module = group.modules[moduleKey];
            const row = matrix[moduleKey] || {};
            const allChecked = row.view && row.create && row.update && row.delete ? 'checked' : '';
            html += `<tr class="border-t">
                <td class="px-3 py-2">${module.label || moduleKey}</td>
                <td class="text-center"><input type="checkbox" class="admin-pack-row-all rounded" data-module="${moduleKey}" data-group="${groupKey}" title="Select all for this module" ${allChecked} onchange="toggleAdminPackRow('${moduleKey}', this.checked)"></td>`;
            ['view','create','update','delete'].forEach(action => {
                const checked = row[action] ? 'checked' : '';
                html += `<td class="text-center"><input type="checkbox" class="admin-pack-box rounded" data-module="${moduleKey}" data-group="${groupKey}" data-action="${action}" value="1" ${checked} onchange="syncAdminPackRow('${moduleKey}')"></td>`;
            });
            html += '</tr>';
        });
        html += '</tbody></table></div>';
    });
    document.getElementById('admin-access-matrix').innerHTML = html || '<p class="text-sm text-gray-500">No modules configured.</p>';
}

function toggleAdminPackAll(on) {
    document.querySelectorAll('.admin-pack-box, .admin-pack-row-all, .admin-pack-group-all').forEach(el => el.checked = on);
}
function toggleAdminPackGroup(group, on) {
    document.querySelectorAll('.admin-pack-box[data-group="' + group + '"], .admin-pack-row-all[data-group="' + group + '"]').forEach(el => el.checked = on);
}
function toggleAdminPackRow(module, on) {
    document.querySelectorAll('.admin-pack-box[data-module="' + module + '"]').forEach(el => el.checked = on);
    syncAdminPackGroup(module);
}
function syncAdminPackRow(module) {
    const boxes = Array.from(document.querySelectorAll('.admin-pack-box[data-module="' + module + '"]'));
    const all = document.querySelector('.admin-pack-row-all[data-module="' + module + '"]');
    if (all) all.checked = boxes.length > 0 && boxes.every(b => b.checked);
    syncAdminPackGroup(module);
}
function syncAdminPackGroup(module) {
    const sample = document.querySelector('.admin-pack-box[data-module="' + module + '"]');
    if (!sample) return;
    const group = sample.dataset.group;
    const boxes = Array.from(document.querySelectorAll('.admin-pack-box[data-group="' + group + '"]'));
    const groupAll = document.querySelector('.admin-pack-group-all[data-group="' + group + '"]');
    if (groupAll) groupAll.checked = boxes.length > 0 && boxes.every(b => b.checked);
}

function submitAdminAccess(e) {
    e.preventDefault();
    if (!adminAccessCompanyId) return;
    const btn = document.getElementById('admin-access-save');
    btn.disabled = true;
    const formData = new FormData();
    document.querySelectorAll('.admin-pack-box:checked').forEach(box => {
        formData.append(`permissions[${box.dataset.module}][${box.dataset.action}]`, '1');
    });
    fetch(`/admin/companies/${adminAccessCompanyId}/admin-access`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            closeAdminAccessModal();
            window.location.reload();
        } else {
            alert(data.message || 'Save failed');
        }
    })
    .catch(() => alert('Save failed'))
    .finally(() => { btn.disabled = false; });
}

document.getElementById('adminAccessModal').addEventListener('click', function(e) {
    if (e.target === this) closeAdminAccessModal();
});
</script>
@endpush
@endsection
