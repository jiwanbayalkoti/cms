@extends('admin.layout')

@section('title', 'Sub-contractors')

@section('content')
<script>window.SC_WORK_TYPE_PRESETS = @json($workTypeOptions);</script>
<div class="mb-6 flex justify-between items-center flex-wrap gap-3">
    <h1 class="text-3xl font-bold text-gray-900">Sub-contractors</h1>
    <button type="button" onclick="openCreateSubcontractorModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition duration-200 inline-flex items-center gap-2">
        <i class="bi bi-plus-lg"></i>
        <span>Add Sub-contractor</span>
    </button>
</div>

@if(session('success'))
    <div class="mb-4 rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3">{{ session('error') }}</div>
@endif

<div class="mb-4 bg-white shadow-lg rounded-lg p-4">
    <form id="subcontractorFilterForm" method="GET" action="{{ route('admin.subcontractors.index') }}" class="flex flex-wrap gap-4 items-end">
        <div class="flex-1 min-w-[200px]">
            <label for="keyword" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
            <input type="text" name="keyword" id="keyword" value="{{ request('keyword') }}" placeholder="Name, phone, email..."
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div class="w-40">
            <label for="is_active" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select name="is_active" id="is_active" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All</option>
                <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        <button type="button" id="subcontractorFilterResetBtn" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Reset</button>
    </form>
</div>

<div id="subcontractorListWrap">
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12" title="Payments">Pay</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SN</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[140px]">Work types</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PAN</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-nowrap">Actions</th>
                </tr>
            </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                @forelse($subcontractors as $row)
                    <tr data-subcontractor-id="{{ $row->id }}">
                        <td class="px-3 py-4 align-middle">
                            <button type="button" onclick="toggleSubcontractorPaymentsRow({{ $row->id }})" class="p-1.5 rounded-lg border border-gray-200 hover:bg-gray-100 text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500" title="Show / hide payments" aria-expanded="false" id="sc-toggle-btn-{{ $row->id }}">
                                <i class="bi bi-chevron-down transition-transform duration-200 inline-block" id="sc-toggle-icon-{{ $row->id }}"></i>
                            </button>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ ($subcontractors->currentPage() - 1) * $subcontractors->perPage() + $loop->iteration }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $row->name }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 max-w-xs">
                            @if(!empty($row->work_types) && is_array($row->work_types))
                                <div class="flex flex-wrap gap-1">
                                    @foreach(array_slice($row->work_types, 0, 5) as $wt)
                                        <span class="inline-flex px-1.5 py-0.5 text-xs rounded bg-indigo-50 text-indigo-800 border border-indigo-100">{{ $wt }}</span>
                                    @endforeach
                                    @if(count($row->work_types) > 5)
                                        <span class="text-xs text-gray-500">+{{ count($row->work_types) - 5 }}</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $row->contact_person ?? '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $row->phone ?? '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $row->pan_number ?? '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $row->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $row->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex gap-1 flex-nowrap">
                                <button type="button" onclick="openSubcontractorPaymentModal({{ $row->id }})" class="btn btn-outline-primary btn-sm" title="View & payment">
                                    <i class="bi bi-cash-coin"></i>
                                </button>
                                <button type="button" onclick="openEditSubcontractorModal({{ $row->id }})" class="btn btn-outline-warning btn-sm" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" onclick="showDeleteSubcontractorConfirmation({{ $row->id }}, '{{ addslashes($row->name) }}')" class="btn btn-outline-danger btn-sm" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr id="sc-payments-expand-{{ $row->id }}" class="hidden bg-slate-50/90">
                        <td colspan="9" class="px-6 py-0 border-t border-gray-100">
                            <div id="sc-payments-inline-{{ $row->id }}" class="py-4 pl-1 text-sm text-gray-500">
                                <span class="text-gray-400">Open the toggle to load payments.</span>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-6 py-8 text-center text-sm text-gray-500">
                            No sub-contractors yet. <button type="button" onclick="openCreateSubcontractorModal()" class="text-indigo-600 hover:text-indigo-900">Add one</button>.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <x-pagination :paginator="$subcontractors" wrapper-class="mt-4" />
</div>

<!-- Delete confirmation -->
<div id="deleteSubcontractorConfirmationModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-2xl max-w-md w-full" onclick="event.stopPropagation()">
        <div class="p-6">
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 text-center mb-2">Delete Sub-contractor</h3>
            <p class="text-gray-600 text-center mb-6">
                Are you sure you want to delete <span class="font-semibold text-gray-900" id="delete-subcontractor-name"></span>? This cannot be undone.
            </p>
            <div class="flex space-x-3">
                <button type="button" onclick="closeDeleteSubcontractorConfirmation()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors font-medium">
                    Cancel
                </button>
                <button type="button" onclick="confirmDeleteSubcontractor()" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium">
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Create / Edit modal -->
<div id="subcontractorModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full max-h-[90vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-6 border-b">
            <h3 class="text-xl font-semibold text-gray-900" id="subcontractor-modal-title">Add Sub-contractor</h3>
            <button type="button" onclick="closeSubcontractorModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6">
            <form id="subcontractorForm">
                @csrf
                <input type="hidden" name="_method" id="subcontractor-method" value="POST">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label for="sc-name" class="block text-sm font-medium text-gray-700 mb-2">Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="sc-name"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <div class="field-error text-red-600 text-sm mt-1" data-field="name" style="display: none;"></div>
                    </div>
                    <div>
                        <label for="sc-contact-person" class="block text-sm font-medium text-gray-700 mb-2">Contact person</label>
                        <input type="text" name="contact_person" id="sc-contact-person"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <div class="field-error text-red-600 text-sm mt-1" data-field="contact_person" style="display: none;"></div>
                    </div>
                    <div>
                        <label for="sc-phone" class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                        <input type="text" name="phone" id="sc-phone"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <div class="field-error text-red-600 text-sm mt-1" data-field="phone" style="display: none;"></div>
                    </div>
                    <div>
                        <label for="sc-email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" name="email" id="sc-email"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <div class="field-error text-red-600 text-sm mt-1" data-field="email" style="display: none;"></div>
                    </div>
                    <div>
                        <label for="sc-pan" class="block text-sm font-medium text-gray-700 mb-2">PAN / VAT</label>
                        <input type="text" name="pan_number" id="sc-pan"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <div class="field-error text-red-600 text-sm mt-1" data-field="pan_number" style="display: none;"></div>
                    </div>
                    <div class="md:col-span-2">
                        <label for="sc-address" class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                        <textarea name="address" id="sc-address" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                        <div class="field-error text-red-600 text-sm mt-1" data-field="address" style="display: none;"></div>
                    </div>
                    <div class="md:col-span-2">
                        <label for="sc-notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                        <textarea name="notes" id="sc-notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                        <div class="field-error text-red-600 text-sm mt-1" data-field="notes" style="display: none;"></div>
                    </div>
                    <div class="md:col-span-2">
                        <span class="block text-sm font-medium text-gray-700 mb-2">Work type <span class="text-gray-500 font-normal">(multiple)</span></span>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 p-3 border border-gray-200 rounded-lg bg-gray-50">
                            @foreach($workTypeOptions as $opt)
                                <label class="inline-flex items-center gap-2 text-sm text-gray-800 cursor-pointer">
                                    <input type="checkbox" name="work_types[]" value="{{ $opt }}" class="sc-work-type-cb rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span>{{ $opt }}</span>
                                </label>
                            @endforeach
                        </div>
                        <label for="sc-work-types-custom" class="block text-sm font-medium text-gray-700 mt-3 mb-1">Other work types</label>
                        <textarea name="work_types_custom" id="sc-work-types-custom" rows="2" placeholder="Add more types separated by comma or new line (e.g. Glass work, Aluminium)"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"></textarea>
                        <p class="mt-1 text-xs text-gray-500">Choose from the list above and/or type additional categories here.</p>
                        <div class="field-error text-red-600 text-sm mt-1" data-field="work_types" style="display: none;"></div>
                        <div class="field-error text-red-600 text-sm mt-1" data-field="work_types_custom" style="display: none;"></div>
                    </div>
                    <div class="md:col-span-2">
                        <label class="inline-flex items-center gap-2">
                            <input type="checkbox" name="is_active" id="sc-is-active" value="1" checked class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700">Active</span>
                        </label>
                        <div class="field-error text-red-600 text-sm mt-1" data-field="is_active" style="display: none;"></div>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" onclick="closeSubcontractorModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700" id="subcontractor-submit-btn">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View & Record payment (from list) -->
<div id="subcontractorPaymentModal" class="fixed inset-0 bg-black bg-opacity-50 z-[60] hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[92vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-4 border-b bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-900 pr-4" id="sc-payment-modal-title">View & payment</h3>
            <button type="button" onclick="closeSubcontractorPaymentModal()" class="text-gray-400 hover:text-gray-600 shrink-0">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div id="sc-payment-modal-body" class="flex-1 overflow-y-auto p-6">
            <div class="flex justify-center py-16 text-gray-500 text-sm">Loading…</div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const scCsrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
let currentSubcontractorId = null;
let deleteSubcontractorId = null;
let currentPaymentModalScId = null;
/** Per sub-contractor id: true after inline payment list loaded successfully */
var scPaymentsInlineLoaded = {};
var scFilterDebounceTimer = null;
var scFilterAbortController = null;

function escapeHtmlSc(s) {
    if (s == null || s === '') return '';
    return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function toggleSubcontractorPaymentsRow(id) {
    var expand = document.getElementById('sc-payments-expand-' + id);
    var icon = document.getElementById('sc-toggle-icon-' + id);
    var btn = document.getElementById('sc-toggle-btn-' + id);
    if (!expand) return;
    var willOpen = expand.classList.contains('hidden');
    if (willOpen) {
        expand.classList.remove('hidden');
        if (icon) icon.classList.add('rotate-180');
        if (btn) btn.setAttribute('aria-expanded', 'true');
        if (!scPaymentsInlineLoaded[id]) {
            loadSubcontractorPaymentsInline(id, false);
        }
    } else {
        expand.classList.add('hidden');
        if (icon) icon.classList.remove('rotate-180');
        if (btn) btn.setAttribute('aria-expanded', 'false');
    }
}

function loadSubcontractorPaymentsInline(id, force) {
    if (!force && scPaymentsInlineLoaded[id]) return;
    var container = document.getElementById('sc-payments-inline-' + id);
    if (!container) return;
    if (force) scPaymentsInlineLoaded[id] = false;
    container.innerHTML = '<div class="flex items-center gap-2 text-gray-500 py-2"><svg class="animate-spin h-4 w-4 text-indigo-600 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Loading payments…</div>';
    fetch('/admin/subcontractors/' + id + '/payment-data', {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        credentials: 'same-origin'
    })
    .then(function(r) {
        if (!r.ok) throw new Error();
        return r.json();
    })
    .then(function(data) {
        scPaymentsInlineLoaded[id] = true;
        renderSubcontractorPaymentsInline(container, data);
    })
    .catch(function() {
        scPaymentsInlineLoaded[id] = false;
        container.innerHTML = '<p class="text-red-600 text-sm py-2">Could not load payments.</p>';
    });
}

function renderSubcontractorPaymentsInline(container, data) {
    var rows = (data.recent_expenses || []).map(function(ex) {
        return '<tr><td class="px-3 py-2 text-sm">' + escapeHtmlSc(ex.date_display) + '</td><td class="px-3 py-2 text-sm">' + escapeHtmlSc(ex.project || '—') + '</td><td class="px-3 py-2 text-sm">' + escapeHtmlSc(ex.category_line) + '</td><td class="px-3 py-2 text-sm font-medium text-gray-900">' + escapeHtmlSc(ex.amount) + '</td></tr>';
    }).join('');
    if (!rows) {
        rows = '<tr><td colspan="4" class="px-3 py-6 text-center text-sm text-gray-500">No payments yet.</td></tr>';
    }
    var total = data.payments_total != null && data.payments_total !== '' ? escapeHtmlSc(String(data.payments_total)) : '0.00';
    var fullUrl = (data.urls && data.urls.full_show) ? escapeHtmlSc(data.urls.full_show) : '';
    var linkLine = fullUrl
        ? '<p class="mt-2 text-xs text-gray-500"><a href="' + fullUrl + '" class="text-indigo-600 hover:underline" target="_blank" rel="noopener">Open full page</a> for full paginated history.</p>'
        : '';
    container.innerHTML =
        '<div class="flex flex-wrap justify-between items-center gap-2 mb-2">' +
            '<span class="font-semibold text-gray-800">Payments (this sub-contractor)</span>' +
            '<span class="text-sm font-semibold text-gray-900">Total: <span class="text-indigo-700">' + total + '</span></span>' +
        '</div>' +
        '<div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm">' +
            '<table class="min-w-full divide-y divide-gray-200">' +
                '<thead class="bg-gray-50"><tr><th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th><th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Project</th><th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Category</th><th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Amount</th></tr></thead>' +
                '<tbody class="divide-y divide-gray-200">' + rows + '</tbody>' +
            '</table>' +
        '</div>' +
        linkLine;
}

function showNotification(message, type = 'success') {
    const notificationDiv = document.createElement('div');
    notificationDiv.className = 'fixed top-4 right-4 px-6 py-4 rounded-lg shadow-2xl z-[100] transition-all duration-300 flex items-center gap-3 min-w-[300px] max-w-[500px]';
    notificationDiv.className += type === 'success' ? ' bg-green-500 text-white' : (type === 'error' ? ' bg-red-500 text-white' : ' bg-blue-500 text-white');
    notificationDiv.innerHTML = '<span>' + message + '</span><button type="button" onclick="this.parentElement.remove()" class="ml-2 text-white hover:text-gray-200"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>';
    document.body.appendChild(notificationDiv);
    setTimeout(() => { notificationDiv.style.opacity = '0'; setTimeout(() => notificationDiv.remove(), 300); }, 3000);
}

function serializeSubcontractorFilters() {
    var form = document.getElementById('subcontractorFilterForm');
    var params = new URLSearchParams();
    if (!form) return params;
    var keyword = (form.querySelector('#keyword')?.value || '').trim();
    var isActive = form.querySelector('#is_active')?.value || '';
    if (keyword !== '') params.set('keyword', keyword);
    if (isActive !== '') params.set('is_active', isActive);
    return params;
}

function syncSubcontractorFilterInputsFromUrl(urlString) {
    var form = document.getElementById('subcontractorFilterForm');
    if (!form) return;
    var u = new URL(urlString, window.location.origin);
    var keyword = u.searchParams.get('keyword') || '';
    var isActive = u.searchParams.get('is_active') || '';
    var keywordEl = form.querySelector('#keyword');
    var activeEl = form.querySelector('#is_active');
    if (keywordEl) keywordEl.value = keyword;
    if (activeEl) activeEl.value = isActive;
}

function loadSubcontractorListAjax(url, pushHistory) {
    var listWrap = document.getElementById('subcontractorListWrap');
    if (!listWrap) return;
    if (!url) {
        var base = '{{ route('admin.subcontractors.index') }}';
        var params = serializeSubcontractorFilters();
        url = base + (params.toString() ? ('?' + params.toString()) : '');
    }
    if (scFilterAbortController) {
        scFilterAbortController.abort();
    }
    scFilterAbortController = new AbortController();
    listWrap.style.opacity = '0.55';
    fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
        credentials: 'same-origin',
        signal: scFilterAbortController.signal
    })
    .then(function(r) {
        if (!r.ok) throw new Error('Failed to load');
        return r.text();
    })
    .then(function(html) {
        var parser = new DOMParser();
        var doc = parser.parseFromString(html, 'text/html');
        var incoming = doc.getElementById('subcontractorListWrap');
        if (!incoming) throw new Error('List container missing');
        listWrap.innerHTML = incoming.innerHTML;
        listWrap.style.opacity = '1';
        scPaymentsInlineLoaded = {};
        if (pushHistory) {
            window.history.replaceState({}, '', url);
        }
    })
    .catch(function(err) {
        if (err && err.name === 'AbortError') return;
        listWrap.style.opacity = '1';
        showNotification('Failed to load list', 'error');
    });
}

function debounceSubcontractorFilterReload() {
    if (scFilterDebounceTimer) {
        clearTimeout(scFilterDebounceTimer);
    }
    scFilterDebounceTimer = setTimeout(function() {
        loadSubcontractorListAjax(null, true);
    }, 300);
}

function normalizeClientWorkTypes(w) {
    if (w == null || w === '') return [];
    if (Array.isArray(w)) return w.map(function(x) { return String(x).trim(); }).filter(Boolean);
    if (typeof w === 'object') {
        try { return Object.values(w).map(function(x) { return String(x).trim(); }).filter(Boolean); } catch (e) { return []; }
    }
    return [];
}

function resetWorkTypeFields() {
    document.querySelectorAll('.sc-work-type-cb').forEach(function(cb) { cb.checked = false; });
    var customEl = document.getElementById('sc-work-types-custom');
    if (customEl) customEl.value = '';
}

/** Apply saved work types to checkboxes + "Other" field (must be defined — was missing and broke edit). */
function applyWorkTypesFromRecord(workTypes) {
    var presets = window.SC_WORK_TYPE_PRESETS || [];
    var list = normalizeClientWorkTypes(workTypes);
    document.querySelectorAll('.sc-work-type-cb').forEach(function(cb) {
        var v = cb.value;
        cb.checked = list.some(function(t) {
            return t === v || String(t).toLowerCase() === String(v).toLowerCase();
        });
    });
    var customParts = [];
    list.forEach(function(t) {
        var isPreset = presets.some(function(p) {
            return p === t || String(p).toLowerCase() === String(t).toLowerCase();
        });
        if (!isPreset) customParts.push(t);
    });
    var customEl = document.getElementById('sc-work-types-custom');
    if (customEl) customEl.value = customParts.join(', ');
}

function openCreateSubcontractorModal() {
    currentSubcontractorId = null;
    document.getElementById('subcontractorModal').classList.remove('hidden');
    if (typeof jQuery !== 'undefined' && jQuery.fn.validate) {
        initSubcontractorFormJqueryValidate();
    }
    document.getElementById('subcontractor-modal-title').textContent = 'Add Sub-contractor';
    document.getElementById('subcontractor-method').value = 'POST';
    document.getElementById('subcontractor-submit-btn').textContent = 'Save';
    if (typeof jQuery !== 'undefined' && jQuery('#subcontractorForm').data('validator')) {
        jQuery('#subcontractorForm').validate().resetForm();
    } else {
        document.getElementById('subcontractorForm').reset();
    }
    document.getElementById('sc-is-active').checked = true;
    resetWorkTypeFields();
    document.querySelectorAll('#subcontractorForm .field-error').forEach(el => { el.style.display = 'none'; el.textContent = ''; });
}

function openEditSubcontractorModal(id) {
    currentSubcontractorId = id;
    document.getElementById('subcontractorModal').classList.remove('hidden');
    if (typeof jQuery !== 'undefined' && jQuery.fn.validate) {
        initSubcontractorFormJqueryValidate();
    }
    document.getElementById('subcontractor-modal-title').textContent = 'Edit Sub-contractor';
    document.getElementById('subcontractor-submit-btn').textContent = 'Update';
    resetWorkTypeFields();
    document.querySelectorAll('#subcontractorForm .field-error').forEach(el => { el.style.display = 'none'; el.textContent = ''; });
    if (typeof jQuery !== 'undefined' && jQuery('#subcontractorForm').data('validator')) {
        jQuery('#subcontractorForm').validate().resetForm();
    }
    document.getElementById('subcontractor-method').value = 'PUT';

    fetch('/admin/subcontractors/' + id + '/edit', {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        credentials: 'same-origin',
    })
    .then(function(r) {
        if (!r.ok) throw new Error('Bad response');
        return r.json();
    })
    .then(function(data) {
        var s = data.subcontractor;
        if (!s) throw new Error('No data');
        document.getElementById('sc-name').value = s.name || '';
        document.getElementById('sc-contact-person').value = s.contact_person || '';
        document.getElementById('sc-phone').value = s.phone || '';
        document.getElementById('sc-email').value = s.email || '';
        document.getElementById('sc-pan').value = s.pan_number || '';
        document.getElementById('sc-address').value = s.address || '';
        document.getElementById('sc-notes').value = s.notes || '';
        document.getElementById('sc-is-active').checked = !!s.is_active;
        applyWorkTypesFromRecord(s.work_types);
    })
    .catch(function() { showNotification('Failed to load sub-contractor', 'error'); });
}

function closeSubcontractorModal() {
    document.getElementById('subcontractorModal').classList.add('hidden');
    currentSubcontractorId = null;
    document.getElementById('subcontractorForm').reset();
}

function submitSubcontractorFormAjax(form) {
    document.querySelectorAll('#subcontractorForm .field-error').forEach(function(el) {
        el.style.display = 'none';
        el.textContent = '';
    });
    const btn = document.getElementById('subcontractor-submit-btn');
    const orig = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Saving...';

    const fd = new FormData(form);
    const url = currentSubcontractorId ? '/admin/subcontractors/' + currentSubcontractorId : '/admin/subcontractors';
    if (currentSubcontractorId) fd.append('_method', 'PUT');

    fetch(url, {
        method: 'POST',
        body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': scCsrfToken, 'Accept': 'application/json' }
    })
    .then(async response => {
        const data = await response.json();
        if (response.ok && data.success) {
            showNotification(data.message, 'success');
            closeSubcontractorModal();
            window.location.reload();
            return;
        }
        if (data.errors) {
            Object.keys(data.errors).forEach(field => {
                const el = document.querySelector('#subcontractorForm .field-error[data-field="' + field + '"]');
                if (el) { el.textContent = data.errors[field][0]; el.style.display = 'block'; }
            });
        }
        showNotification(data.message || 'Validation failed', 'error');
        btn.disabled = false;
        btn.textContent = orig;
    })
    .catch(() => {
        showNotification('An error occurred while saving', 'error');
        btn.disabled = false;
        btn.textContent = orig;
    });
}

function showDeleteSubcontractorConfirmation(id, name) {
    deleteSubcontractorId = id;
    document.getElementById('delete-subcontractor-name').textContent = name;
    document.getElementById('deleteSubcontractorConfirmationModal').classList.remove('hidden');
}

function closeDeleteSubcontractorConfirmation() {
    deleteSubcontractorId = null;
    document.getElementById('deleteSubcontractorConfirmationModal').classList.add('hidden');
}

function confirmDeleteSubcontractor() {
    if (!deleteSubcontractorId) return;
    const id = deleteSubcontractorId;
    fetch('/admin/subcontractors/' + id, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': scCsrfToken,
            'Accept': 'application/json',
        },
    })
    .then(async response => {
        const data = await response.json().catch(() => ({}));
        if (response.ok && data.success) {
            closeDeleteSubcontractorConfirmation();
            showNotification(data.message, 'success');
            window.location.reload();
            return;
        }
        showNotification(data.message || 'Could not delete', 'error');
    })
    .catch(() => showNotification('An error occurred while deleting', 'error'));
}

function renderSubcontractorPaymentModal(data) {
    var s = data.subcontractor;
    var u = data.urls || {};
    var today = new Date().toISOString().slice(0, 10);

    var workTypesHtml = '';
    if (s.work_types && s.work_types.length) {
        workTypesHtml = '<div class="flex flex-wrap gap-1 mt-1">' + s.work_types.map(function(w) {
            return '<span class="inline-flex px-1.5 py-0.5 text-xs rounded bg-indigo-50 text-indigo-800 border border-indigo-100">' + escapeHtmlSc(w) + '</span>';
        }).join('') + '</div>';
    } else {
        workTypesHtml = '<span class="text-gray-400">—</span>';
    }

    var projOpts = (data.projects || []).map(function(p) {
        return '<option value="' + p.id + '">' + escapeHtmlSc(p.name) + '</option>';
    }).join('');

    var historyBody = (data.recent_expenses || []).map(function(ex) {
        return '<tr><td class="px-3 py-2 text-sm">' + escapeHtmlSc(ex.date_display) + '</td><td class="px-3 py-2 text-sm">' + escapeHtmlSc(ex.project || '—') + '</td><td class="px-3 py-2 text-sm">' + escapeHtmlSc(ex.category_line) + '</td><td class="px-3 py-2 text-sm font-medium">' + escapeHtmlSc(ex.amount) + '</td></tr>';
    }).join('');
    if (!historyBody) {
        historyBody = '<tr><td colspan="4" class="px-3 py-6 text-center text-sm text-gray-500">No payments yet. Use the form below.</td></tr>';
    }

    var paymentsTotalDisplay = data.payments_total != null && data.payments_total !== ''
        ? escapeHtmlSc(String(data.payments_total))
        : '0.00';

    document.getElementById('sc-payment-modal-title').textContent = 'View & payment — ' + (s.name || '');

    document.getElementById('sc-payment-modal-body').innerHTML =
        '<div class="space-y-6">' +
          '<div class="flex flex-wrap gap-2 text-sm">' +
            '<a href="' + escapeHtmlSc(u.full_show || '#') + '" target="_blank" rel="noopener" class="px-3 py-1.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Full page</a>' +
            '<a href="' + escapeHtmlSc(u.expenses || '#') + '" class="px-3 py-1.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Expenses</a>' +
            '<button type="button" onclick="closeSubcontractorPaymentModal();openEditSubcontractorModal(' + s.id + ')" class="px-3 py-1.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Edit profile</button>' +
          '</div>' +
          '<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">' +
            '<div class="lg:col-span-1 rounded-lg border border-gray-200 p-4 bg-gray-50">' +
              '<h4 class="font-semibold text-gray-900 mb-3">Profile</h4>' +
              '<dl class="space-y-2 text-sm">' +
                '<div><dt class="text-gray-500">Contact</dt><dd class="font-medium text-gray-900">' + escapeHtmlSc(s.contact_person || '—') + '</dd></div>' +
                '<div><dt class="text-gray-500">Phone</dt><dd>' + escapeHtmlSc(s.phone || '—') + '</dd></div>' +
                '<div><dt class="text-gray-500">Email</dt><dd>' + escapeHtmlSc(s.email || '—') + '</dd></div>' +
                '<div><dt class="text-gray-500">PAN / VAT</dt><dd>' + escapeHtmlSc(s.pan_number || '—') + '</dd></div>' +
                '<div><dt class="text-gray-500">Work type</dt><dd>' + workTypesHtml + '</dd></div>' +
                '<div><dt class="text-gray-500">Address</dt><dd class="whitespace-pre-line">' + escapeHtmlSc(s.address || '—') + '</dd></div>' +
                '<div><dt class="text-gray-500">Notes</dt><dd class="whitespace-pre-line text-gray-700">' + escapeHtmlSc(s.notes || '—') + '</dd></div>' +
                '<div><dt class="text-gray-500">Status</dt><dd><span class="px-2 py-0.5 text-xs rounded-full ' + (s.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800') + '">' + (s.is_active ? 'Active' : 'Inactive') + '</span></dd></div>' +
              '</dl>' +
            '</div>' +
            '<div class="lg:col-span-2 rounded-lg border border-gray-200 p-4">' +
              '<h4 class="font-semibold text-gray-900 mb-2">Record payment</h4>' +
              '<p class="text-sm text-gray-600 mb-4">Creates an expense linked to this sub-contractor. Category and expense type are set automatically (first active expense category; first expense type if any).</p>' +
              '<form id="scPayPaymentForm">' +
                '<input type="hidden" name="_token" value="' + escapeHtmlSc(scCsrfToken) + '">' +
                '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">' +
                  '<div><label class="block text-sm font-medium text-gray-700 mb-1">Project <span class="text-red-500">*</span></label>' +
                    '<select name="project_id" id="sc-pay-project-id" class="w-full px-3 py-2 border border-gray-300 rounded-lg"><option value="">Select project</option>' + projOpts + '</select></div>' +
                  '<div><label class="block text-sm font-medium text-gray-700 mb-1">Date <span class="text-red-500">*</span></label>' +
                    '<input type="date" name="date" id="sc-pay-date" value="' + today + '" class="w-full px-3 py-2 border border-gray-300 rounded-lg"></div>' +
                  '<div><label class="block text-sm font-medium text-gray-700 mb-1">Amount <span class="text-red-500">*</span></label>' +
                    '<input type="number" name="amount" id="sc-pay-amount" step="0.01" min="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg"></div>' +
                  '<div class="md:col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1" for="sc-pay-payment-method">Payment method</label>' +
                    '<select name="payment_method" id="sc-pay-payment-method" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">' +
                    '<option value="">Select payment method</option>' +
                    '<option value="Cash">Cash</option>' +
                    '<option value="Bank Transfer">Bank Transfer</option>' +
                    '<option value="Cheque">Cheque</option>' +
                    '<option value="Credit Card">Credit Card</option>' +
                    '<option value="Other">Other</option>' +
                    '</select></div>' +
                  '<div class="md:col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1">Description</label>' +
                    '<input type="text" name="description" class="w-full px-3 py-2 border border-gray-300 rounded-lg"></div>' +
                  '<div class="md:col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>' +
                    '<textarea name="notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg"></textarea></div>' +
                '</div>' +
                '<button type="submit" class="mt-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Save payment</button>' +
              '</form>' +
            '</div>' +
          '</div>' +
          '<div class="rounded-lg border border-gray-200 overflow-hidden">' +
            '<div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex flex-wrap justify-between items-center gap-2">' +
              '<span class="font-semibold text-gray-900">Recent payments (expenses)</span>' +
              '<span class="text-sm font-semibold text-gray-900">Total: <span class="text-indigo-700">' + paymentsTotalDisplay + '</span></span>' +
            '</div>' +
            '<div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200">' +
              '<thead class="bg-gray-50"><tr><th class="px-3 py-2 text-left text-xs text-gray-500">Date</th><th class="px-3 py-2 text-left text-xs text-gray-500">Project</th><th class="px-3 py-2 text-left text-xs text-gray-500">Category</th><th class="px-3 py-2 text-left text-xs text-gray-500">Amount</th></tr></thead>' +
              '<tbody class="divide-y divide-gray-200">' + historyBody + '</tbody>' +
            '</table></div>' +
          '</div>' +
        '</div>';
    if (typeof jQuery !== 'undefined' && jQuery.fn.validate) {
        initScPayPaymentFormJqueryValidate();
    }
}

function submitScPaymentFormAjax(form) {
    var id = currentPaymentModalScId;
    if (!id) return false;
    if (!form) form = document.getElementById('scPayPaymentForm');
    if (!form) return false;
    var btn = form.querySelector('button[type="submit"]');
    var orig = btn ? btn.textContent : '';
    if (btn) { btn.disabled = true; btn.textContent = 'Saving…'; }
    var fd = new FormData(form);
    fetch('/admin/subcontractors/' + id + '/payments', {
        method: 'POST',
        body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': scCsrfToken, 'Accept': 'application/json' }
    })
    .then(async function(response) {
        var data = await response.json().catch(function() { return {}; });
        if (response.ok && data.success) {
            showNotification(data.message, 'success');
            scPaymentsInlineLoaded[id] = false;
            var expandRow = document.getElementById('sc-payments-expand-' + id);
            if (expandRow && !expandRow.classList.contains('hidden')) {
                loadSubcontractorPaymentsInline(id, true);
            }
            openSubcontractorPaymentModal(id);
            return;
        }
        var msg = data.message || 'Validation failed';
        if (data.errors) {
            var keys = Object.keys(data.errors);
            if (keys.length && data.errors[keys[0]] && data.errors[keys[0]][0]) {
                msg = data.errors[keys[0]][0];
            }
        }
        showNotification(msg, 'error');
        if (btn) { btn.disabled = false; btn.textContent = orig; }
    })
    .catch(function() {
        showNotification('Could not save payment', 'error');
        if (btn) { btn.disabled = false; btn.textContent = orig; }
    });
    return false;
}

function openSubcontractorPaymentModal(id) {
    currentPaymentModalScId = id;
    document.getElementById('subcontractorPaymentModal').classList.remove('hidden');
    document.getElementById('sc-payment-modal-body').innerHTML = '<div class="flex justify-center py-16 text-gray-500 text-sm">Loading…</div>';
    document.getElementById('sc-payment-modal-title').textContent = 'View & payment';
    fetch('/admin/subcontractors/' + id + '/payment-data', {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        credentials: 'same-origin'
    })
    .then(function(r) {
        if (!r.ok) throw new Error();
        return r.json();
    })
    .then(function(data) {
        renderSubcontractorPaymentModal(data);
    })
    .catch(function() {
        var showUrl = '{{ url('/admin/subcontractors') }}/' + id;
        document.getElementById('sc-payment-modal-body').innerHTML = '<p class="text-red-600 text-center py-8">Failed to load. <a href="' + showUrl + '" class="underline text-indigo-600">Open full page</a></p>';
        showNotification('Failed to load sub-contractor', 'error');
    });
}

function closeSubcontractorPaymentModal() {
    if (typeof jQuery !== 'undefined') {
        var $f = jQuery('#scPayPaymentForm');
        if ($f.length && $f.data('validator')) {
            $f.data('validator').destroy();
        }
    }
    document.getElementById('subcontractorPaymentModal').classList.add('hidden');
    currentPaymentModalScId = null;
}

function scJqFieldKeySubcontractor(element) {
    var name = element.name || (element.getAttribute && element.getAttribute('name')) || '';
    if (name.indexOf('work_types') === 0) return 'work_types';
    return name.replace(/\[\]$/, '');
}

function initSubcontractorFormJqueryValidate() {
    if (typeof jQuery === 'undefined' || !jQuery.fn.validate) return;
    var $form = jQuery('#subcontractorForm');
    if (!$form.length) return;
    if ($form.data('validator')) {
        $form.data('validator').destroy();
    }
    $form.validate({
        rules: {
            name: { required: true, maxlength: 255 },
            email: { email: true, maxlength: 255 },
            contact_person: { maxlength: 255 },
            phone: { maxlength: 50 },
            pan_number: { maxlength: 50 },
            work_types_custom: { maxlength: 2000 }
        },
        messages: {
            name: { required: 'Please enter a name.' }
        },
        ignore: ':hidden',
        errorElement: 'span',
        errorClass: 'jq-sc-error text-red-600 text-sm mt-1 block',
        errorPlacement: function(error, element) {
            var key = scJqFieldKeySubcontractor(element[0]);
            var $box = $form.find('.field-error[data-field="' + key + '"]');
            if ($box.length) {
                $box.text(error.text()).show();
                error.remove();
            } else {
                error.insertAfter(element);
            }
        },
        highlight: function(element) {
            jQuery(element).addClass('border-red-500');
        },
        unhighlight: function(element) {
            var el = element;
            jQuery(el).removeClass('border-red-500');
            var key = scJqFieldKeySubcontractor(el);
            var $box = $form.find('.field-error[data-field="' + key + '"]');
            if ($box.length) {
                $box.hide().text('');
            }
        },
        submitHandler: function(form) {
            submitSubcontractorFormAjax(form);
            return false;
        }
    });
}

function initScPayPaymentFormJqueryValidate() {
    if (typeof jQuery === 'undefined' || !jQuery.fn.validate) return;
    var $form = jQuery('#scPayPaymentForm');
    if (!$form.length) return;
    if ($form.data('validator')) {
        $form.data('validator').destroy();
    }
    $form.validate({
        ignore: 'input[type="hidden"]',
        rules: {
            project_id: { required: true },
            date: { required: true },
            amount: { required: true, number: true, min: 0.01 }
        },
        messages: {
            project_id: { required: 'Please select a project.' },
            date: { required: 'Please choose a date.' },
            amount: { required: 'Please enter an amount.', min: 'Amount must be at least 0.01.' }
        },
        errorElement: 'span',
        errorClass: 'text-red-600 text-sm mt-1 block',
        errorPlacement: function(error, element) {
            error.insertAfter(element);
        },
        highlight: function(element) {
            jQuery(element).addClass('border-red-500');
        },
        unhighlight: function(element) {
            jQuery(element).removeClass('border-red-500');
        },
        submitHandler: function(form) {
            submitScPaymentFormAjax(form);
            return false;
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const params = new URLSearchParams(window.location.search);
    const editId = params.get('edit');
    if (editId) {
        openEditSubcontractorModal(parseInt(editId, 10));
        params.delete('edit');
        const q = params.toString();
        window.history.replaceState({}, '', window.location.pathname + (q ? '?' + q : ''));
    }

    var filterForm = document.getElementById('subcontractorFilterForm');
    if (filterForm) {
        var keywordInput = filterForm.querySelector('#keyword');
        var activeInput = filterForm.querySelector('#is_active');
        var resetBtn = document.getElementById('subcontractorFilterResetBtn');

        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            loadSubcontractorListAjax(null, true);
        });

        if (keywordInput) {
            keywordInput.addEventListener('keyup', debounceSubcontractorFilterReload);
        }
        if (activeInput) {
            activeInput.addEventListener('change', function() {
                loadSubcontractorListAjax(null, true);
            });
        }
        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                if (keywordInput) keywordInput.value = '';
                if (activeInput) activeInput.value = '';
                loadSubcontractorListAjax('{{ route('admin.subcontractors.index') }}', true);
            });
        }
    }
});

document.addEventListener('click', function(e) {
    var link = e.target.closest('#subcontractorListWrap .pagination a[href]');
    if (!link) return;
    e.preventDefault();
    syncSubcontractorFilterInputsFromUrl(link.href);
    loadSubcontractorListAjax(link.href, true);
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        if (!document.getElementById('subcontractorPaymentModal').classList.contains('hidden')) {
            closeSubcontractorPaymentModal();
            return;
        }
        if (!document.getElementById('subcontractorModal').classList.contains('hidden')) closeSubcontractorModal();
        if (!document.getElementById('deleteSubcontractorConfirmationModal').classList.contains('hidden')) closeDeleteSubcontractorConfirmation();
    }
});

document.getElementById('subcontractorModal').addEventListener('click', function(e) { if (e.target === this) closeSubcontractorModal(); });
document.getElementById('deleteSubcontractorConfirmationModal').addEventListener('click', function(e) { if (e.target === this) closeDeleteSubcontractorConfirmation(); });
document.getElementById('subcontractorPaymentModal').addEventListener('click', function(e) { if (e.target === this) closeSubcontractorPaymentModal(); });
</script>
@endpush
@endsection
