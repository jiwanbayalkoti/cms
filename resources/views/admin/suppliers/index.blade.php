@extends('admin.layout')

@section('title', 'Suppliers')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Suppliers</h1>
    <button onclick="openCreateSupplierModal()" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> <span class="supplier-btn-text">Add Supplier</span>
    </button>
</div>

<div class="card">
    <div class="card-header">
        <strong>Supplier List</strong>
    </div>
    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead id="suppliers-thead">
                @php
                    $ssc = $sortColumn ?? request('sort', 'name');
                    $ssd = $sortDir ?? request('sort_dir', 'asc');
                    $supSortUrl = function (string $col) use ($ssc, $ssd) {
                        $nextDir = ($ssc === $col && $ssd === 'asc') ? 'desc' : 'asc';
                        return route('admin.suppliers.index', array_merge(request()->query(), ['sort' => $col, 'sort_dir' => $nextDir]));
                    };
                    $supSortIcon = function (string $col) use ($ssc, $ssd) {
                        $active = $ssc === $col;
                        $icon = $active ? ($ssd === 'asc' ? 'bi-sort-up' : 'bi-sort-down') : 'bi-arrow-down-up';
                        $cls = $active ? 'text-primary' : 'text-secondary';
                        return '<i class="bi '.$icon.' ms-1 '.$cls.'" aria-hidden="true"></i>';
                    };
                @endphp
                <tr>
                    <th>SN</th>
                    <th>
                        <button type="button" onclick="navigateSupplierSort(@js($supSortUrl('name')))" class="btn btn-link btn-sm btn-keep-text p-0 text-decoration-none text-dark fw-semibold">
                            Name {!! $supSortIcon('name') !!}
                        </button>
                    </th>
                    <th>
                        <button type="button" onclick="navigateSupplierSort(@js($supSortUrl('contact')))" class="btn btn-link btn-sm btn-keep-text p-0 text-decoration-none text-dark fw-semibold">
                            Contact {!! $supSortIcon('contact') !!}
                        </button>
                    </th>
                    <th>
                        <button type="button" onclick="navigateSupplierSort(@js($supSortUrl('email')))" class="btn btn-link btn-sm btn-keep-text p-0 text-decoration-none text-dark fw-semibold">
                            Email {!! $supSortIcon('email') !!}
                        </button>
                    </th>
                    <th>
                        <button type="button" onclick="navigateSupplierSort(@js($supSortUrl('is_active')))" class="btn btn-link btn-sm btn-keep-text p-0 text-decoration-none text-dark fw-semibold">
                            Status {!! $supSortIcon('is_active') !!}
                        </button>
                    </th>
                    <th class="text-end text-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suppliers as $supplier)
                    <tr data-supplier-id="{{ $supplier->id }}">
                        <td>{{ ($suppliers->currentPage() - 1) * $suppliers->perPage() + $loop->iteration }}</td>
                        <td>{{ $supplier->name }}</td>
                        <td>{{ $supplier->contact }}</td>
                        <td>{{ $supplier->email }}</td>
                        <td>
                            <span class="badge {{ $supplier->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1 justify-content-end text-nowrap supplier-actions">
                                <button type="button" onclick="toggleSupplierPayments({{ $supplier->id }}, event)" class="btn btn-sm btn-outline-info" title="Toggle payments">
                                    <i class="bi bi-chevron-down" id="supplier-payments-icon-{{ $supplier->id }}"></i>
                                </button>
                                <button onclick="openViewSupplierModal({{ $supplier->id }})" class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button type="button" onclick="openSupplierPaymentModal({{ $supplier->id }}, {{ json_encode($supplier->name) }})" class="btn btn-sm btn-outline-success" title="Add payment (Advance)">
                                    <i class="bi bi-cash-coin"></i>
                                </button>
                                <button onclick="openEditSupplierModal({{ $supplier->id }})" class="btn btn-sm btn-outline-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button onclick="showDeleteSupplierConfirmation({{ $supplier->id }}, '{{ addslashes($supplier->name) }}')" class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr id="supplier-payments-row-{{ $supplier->id }}" class="d-none bg-light">
                        <td colspan="6" class="p-3">
                            @php($payments = ($supplierPayments[$supplier->id] ?? collect()))
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong class="mb-0">Payment History</strong>
                                <span class="text-muted small" id="supplier-payments-count-{{ $supplier->id }}">{{ $payments->count() }} records</span>
                            </div>
                            @if($payments->isEmpty())
                                <div class="text-muted" id="supplier-payments-empty-{{ $supplier->id }}">No payments recorded yet.</div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0" id="supplier-payments-table-{{ $supplier->id }}">
                                        <thead class="table-secondary">
                                            <tr>
                                                <th>
                                                    <button type="button" class="btn btn-link btn-sm btn-keep-text p-0 text-decoration-none text-dark fw-semibold" onclick="sortSupplierPayments({{ $supplier->id }}, 'date', event)">
                                                        Date <i class="bi bi-arrow-down-up ms-1 text-secondary" id="supplier-payments-sort-icon-{{ $supplier->id }}-date"></i>
                                                    </button>
                                                </th>
                                                <th>
                                                    <button type="button" class="btn btn-link btn-sm btn-keep-text p-0 text-decoration-none text-dark fw-semibold" onclick="sortSupplierPayments({{ $supplier->id }}, 'type', event)">
                                                        Type <i class="bi bi-arrow-down-up ms-1 text-secondary" id="supplier-payments-sort-icon-{{ $supplier->id }}-type"></i>
                                                    </button>
                                                </th>
                                                <th>
                                                    <button type="button" class="btn btn-link btn-sm btn-keep-text p-0 text-decoration-none text-dark fw-semibold" onclick="sortSupplierPayments({{ $supplier->id }}, 'project', event)">
                                                        Project <i class="bi bi-arrow-down-up ms-1 text-secondary" id="supplier-payments-sort-icon-{{ $supplier->id }}-project"></i>
                                                    </button>
                                                </th>
                                                <th class="text-end">
                                                    <button type="button" class="btn btn-link btn-sm btn-keep-text p-0 text-decoration-none text-dark fw-semibold" onclick="sortSupplierPayments({{ $supplier->id }}, 'amount', event)">
                                                        Amount <i class="bi bi-arrow-down-up ms-1 text-secondary" id="supplier-payments-sort-icon-{{ $supplier->id }}-amount"></i>
                                                    </button>
                                                </th>
                                                <th>
                                                    <button type="button" class="btn btn-link btn-sm btn-keep-text p-0 text-decoration-none text-dark fw-semibold" onclick="sortSupplierPayments({{ $supplier->id }}, 'method', event)">
                                                        Method <i class="bi bi-arrow-down-up ms-1 text-secondary" id="supplier-payments-sort-icon-{{ $supplier->id }}-method"></i>
                                                    </button>
                                                </th>
                                                <th>Reference</th>
                                            </tr>
                                        </thead>
                                        <tbody id="supplier-payments-body-{{ $supplier->id }}">
                                            @foreach($payments as $p)
                                                <tr>
                                                    <td>{{ data_get($p, 'payment_date') ?: 'N/A' }}</td>
                                                    <td>{{ data_get($p, 'payment_label') ?: 'N/A' }}</td>
                                                    <td>{{ data_get($p, 'project_name') ?: 'N/A' }}</td>
                                                    <td class="text-end">Rs. {{ number_format((float) (data_get($p, 'amount') ?: 0), 2) }}</td>
                                                    <td>{{ data_get($p, 'payment_method_label') ?: 'N/A' }}</td>
                                                    <td>{{ data_get($p, 'transaction_reference') ?: '—' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-light">
                                                <td colspan="3" class="text-end"><strong>Total</strong></td>
                                                <td class="text-end"><strong id="supplier-payments-total-{{ $supplier->id }}">Rs. {{ number_format((float) $payments->sum('amount'), 2) }}</strong></td>
                                                <td colspan="2"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-3">No suppliers found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pagination :paginator="$suppliers" wrapper-class="card-footer" />
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteSupplierConfirmationModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-2xl max-w-md w-full" onclick="event.stopPropagation()">
        <div class="p-6">
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 text-center mb-2">Delete Supplier</h3>
            <p class="text-gray-600 text-center mb-6">
                Are you sure you want to delete <span class="font-semibold text-gray-900" id="delete-supplier-name"></span>? This action cannot be undone.
            </p>
            <div class="flex space-x-3">
                <button onclick="closeDeleteSupplierConfirmation()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors font-medium">
                    Cancel
                </button>
                <button onclick="confirmDeleteSupplier()" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium">
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Modal -->
<div id="supplierModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-6 border-b">
            <h3 class="text-xl font-semibold text-gray-900" id="supplier-modal-title">Add Supplier</h3>
            <button onclick="closeSupplierModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6">
            <form id="supplierForm" onsubmit="submitSupplierForm(event)" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_method" id="supplier-method" value="POST">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="supplier-name" class="form-label">Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="supplier-name" required
                               class="form-control">
                        <div class="field-error text-red-600 text-sm mt-1" data-field="name" style="display: none;"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="supplier-contact" class="form-label">Contact</label>
                        <input type="text" name="contact" id="supplier-contact"
                               class="form-control">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="supplier-email" class="form-label">Email</label>
                        <input type="email" name="email" id="supplier-email"
                               class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="is_active" id="supplier-is-active" value="1" checked>
                            <label class="form-check-label" for="supplier-is-active">
                                Active
                            </label>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="supplier-address" class="form-label">Address</label>
                    <textarea name="address" id="supplier-address" rows="3" class="form-control"></textarea>
                </div>

                <hr class="my-4">
                <h5 class="mb-3">Bank Details</h5>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="supplier-bank-name" class="form-label">Bank Name</label>
                        <input type="text" name="bank_name" id="supplier-bank-name" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="supplier-account-holder-name" class="form-label">Account Holder Name</label>
                        <input type="text" name="account_holder_name" id="supplier-account-holder-name" class="form-control">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="supplier-account-number" class="form-label">Account Number</label>
                        <input type="text" name="account_number" id="supplier-account-number" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="supplier-branch-name" class="form-label">Branch Name</label>
                        <input type="text" name="branch_name" id="supplier-branch-name" class="form-control">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="supplier-branch-address" class="form-label">Branch Address</label>
                    <input type="text" name="branch_address" id="supplier-branch-address" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="supplier-qr-code-image" class="form-label">QR Code Image</label>
                    <input type="file" name="qr_code_image" id="supplier-qr-code-image" accept="image/*" onchange="previewQRImage(this)">
                    <small class="text-muted">Upload a QR code image (JPEG, PNG, JPG, GIF, SVG - Max: 2MB)</small>
                    <div id="qr-preview" class="mt-2" style="display: none;">
                        <img id="qr-preview-img" src="" alt="QR Code Preview" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                    </div>
                </div>
                
                <div class="d-flex justify-content-end">
                    <button type="button" onclick="closeSupplierModal()" class="btn btn-secondary me-2">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="supplier-submit-btn">Save Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Modal -->
<div id="viewSupplierModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-6 border-b">
            <h3 class="text-xl font-semibold text-gray-900">Supplier Details</h3>
            <button onclick="closeViewSupplierModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6" id="view-supplier-content">
            <div class="flex items-center justify-center py-12">
                <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Add payment (Advance Payment → Expense) -->
<div id="supplierPaymentModal" class="fixed inset-0 bg-black bg-opacity-50 z-[60] hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <div class="sticky top-0 bg-white z-10 border-b px-6 py-4 flex justify-between items-center">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Supplier payment</h2>
                <p class="text-sm text-gray-500 mb-0">Recorded as an advance payment and linked to <strong>Expenses</strong> (Advance Payments category).</p>
            </div>
            <button type="button" onclick="closeSupplierPaymentModal()" class="text-gray-500 hover:text-gray-700">
                <i class="bi bi-x-lg text-2xl"></i>
            </button>
        </div>
        <div class="p-6">
            <form id="supplierPaymentForm">
                @csrf
                <input type="hidden" name="supplier_id" id="sp_supplier_id" value="">
                <div class="mb-4 p-3 bg-amber-50 border border-amber-100 rounded-lg">
                    <span class="text-sm text-gray-600">Supplier:</span>
                    <span class="font-semibold text-gray-900" id="sp_supplier_label">—</span>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Payment type <span class="text-danger">*</span></label>
                        <select name="payment_type" id="sp_payment_type" class="form-select" required>
                            <option value="">Loading…</option>
                        </select>
                        <div class="field-error-sp text-danger small mt-1" data-field="payment_type" style="display: none;"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Project</label>
                        <select name="project_id" id="sp_project_id" class="form-select">
                            <option value="">None</option>
                        </select>
                        <div class="field-error-sp text-danger small mt-1" data-field="project_id" style="display: none;"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Amount <span class="text-danger">*</span></label>
                        <input type="number" name="amount" id="sp_amount" step="0.01" min="0.01" class="form-control" placeholder="0.00" required>
                        <div class="field-error-sp text-danger small mt-1" data-field="amount" style="display: none;"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Payment date <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" id="sp_payment_date" class="form-control" required>
                        <div class="field-error-sp text-danger small mt-1" data-field="payment_date" style="display: none;"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Bank account</label>
                        <select name="bank_account_id" id="sp_bank_account_id" class="form-select">
                            <option value="">None</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Payment method</label>
                        <select name="payment_method" id="sp_payment_method" class="form-select">
                            <option value="">Select method</option>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                            <option value="online_payment">Online Payment</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Transaction reference</label>
                    <input type="text" name="transaction_reference" id="sp_transaction_reference" class="form-control" placeholder="Cheque no., transfer ref…">
                </div>
                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" id="sp_notes" rows="2" class="form-control"></textarea>
                </div>
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <a href="{{ route('admin.advance-payments.index') }}" target="_blank" class="text-sm text-indigo-600">Open advance payments list</a>
                    <div>
                        <button type="button" onclick="closeSupplierPaymentModal()" class="btn btn-secondary me-2">Cancel</button>
                        <button type="submit" class="btn btn-success" id="supplierPaymentSubmitBtn">Save payment</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .supplier-actions {
        flex-wrap: nowrap;
        align-items: center;
    }
    @media (max-width: 768px) {
        .supplier-btn-text {
            display: none;
        }
        .supplier-actions {
            gap: 0.25rem !important;
        }
    }
</style>

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
const supplierPaymentStoreUrl = "{{ route('admin.advance-payments.store') }}";
const supplierPaymentFormDataUrl = "{{ route('admin.advance-payments.create') }}";
let currentSupplierId = null;
let deleteSupplierId = null;
const supplierPaymentSortState = {};

function navigateSupplierSort(url) {
    if (!url) return;
    window.location.assign(url);
}

function toggleSupplierPayments(supplierId, evt = null) {
    if (evt) {
        evt.preventDefault();
        evt.stopPropagation();
    }

    const row = document.getElementById(`supplier-payments-row-${supplierId}`);
    const icon = document.getElementById(`supplier-payments-icon-${supplierId}`);
    if (!row) return;

    const isHidden = row.classList.contains('d-none');
    if (isHidden) {
        row.classList.remove('d-none');
        if (icon) icon.className = 'bi bi-chevron-up';
    } else {
        row.classList.add('d-none');
        if (icon) icon.className = 'bi bi-chevron-down';
    }
}

function updateSupplierPaymentsSortIcons(supplierId, column, direction) {
    ['date', 'type', 'project', 'amount', 'method'].forEach((col) => {
        const icon = document.getElementById(`supplier-payments-sort-icon-${supplierId}-${col}`);
        if (!icon) return;
        if (col === column) {
            icon.className = `bi ${direction === 'asc' ? 'bi-sort-up' : 'bi-sort-down'} ms-1 text-primary`;
        } else {
            icon.className = 'bi bi-arrow-down-up ms-1 text-secondary';
        }
    });
}

function sortSupplierPayments(supplierId, column, evt = null) {
    if (evt) {
        evt.preventDefault();
        evt.stopPropagation();
    }
    const bodyEl = document.getElementById(`supplier-payments-body-${supplierId}`);
    if (!bodyEl) return;

    const current = supplierPaymentSortState[supplierId] || { column: 'date', direction: 'desc' };
    const direction = (current.column === column && current.direction === 'asc') ? 'desc' : 'asc';
    supplierPaymentSortState[supplierId] = { column, direction };

    const rows = Array.from(bodyEl.querySelectorAll('tr'));
    const idxMap = { date: 0, type: 1, project: 2, amount: 3, method: 4 };
    const idx = idxMap[column] ?? 0;

    rows.sort((a, b) => {
        const av = a.children[idx]?.textContent?.trim() || '';
        const bv = b.children[idx]?.textContent?.trim() || '';
        if (column === 'amount') {
            const an = parseFloat(av.replace(/[^0-9.-]/g, '')) || 0;
            const bn = parseFloat(bv.replace(/[^0-9.-]/g, '')) || 0;
            return direction === 'asc' ? an - bn : bn - an;
        }
        if (column === 'date') {
            const ad = new Date(av).getTime() || 0;
            const bd = new Date(bv).getTime() || 0;
            return direction === 'asc' ? ad - bd : bd - ad;
        }
        return direction === 'asc' ? av.localeCompare(bv) : bv.localeCompare(av);
    });

    rows.forEach((r) => bodyEl.appendChild(r));
    updateSupplierPaymentsSortIcons(supplierId, column, direction);
}

function formatMoney(value) {
    return Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function renderPaymentTypeLabel(value) {
    return String(value || '').replaceAll('_', ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}

function addSupplierPaymentToList(payment) {
    const supplierId = payment?.supplier_id;
    if (!supplierId) return;

    const row = document.getElementById(`supplier-payments-row-${supplierId}`);
    if (!row) return;

    const countEl = document.getElementById(`supplier-payments-count-${supplierId}`);
    const emptyEl = document.getElementById(`supplier-payments-empty-${supplierId}`);
    let bodyEl = document.getElementById(`supplier-payments-body-${supplierId}`);
    let totalEl = document.getElementById(`supplier-payments-total-${supplierId}`);

    // If this supplier had no payments on initial render, build table skeleton on first insert.
    if (!bodyEl) {
        row.querySelector('td').innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-2">
                <strong class="mb-0">Payment History</strong>
                <span class="text-muted small" id="supplier-payments-count-${supplierId}">0 records</span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0" id="supplier-payments-table-${supplierId}">
                    <thead class="table-secondary">
                        <tr>
                            <th><button type="button" class="btn btn-link btn-sm btn-keep-text p-0 text-decoration-none text-dark fw-semibold" onclick="sortSupplierPayments(${supplierId}, 'date', event)">Date <i class="bi bi-arrow-down-up ms-1 text-secondary" id="supplier-payments-sort-icon-${supplierId}-date"></i></button></th>
                            <th><button type="button" class="btn btn-link btn-sm btn-keep-text p-0 text-decoration-none text-dark fw-semibold" onclick="sortSupplierPayments(${supplierId}, 'type', event)">Type <i class="bi bi-arrow-down-up ms-1 text-secondary" id="supplier-payments-sort-icon-${supplierId}-type"></i></button></th>
                            <th><button type="button" class="btn btn-link btn-sm btn-keep-text p-0 text-decoration-none text-dark fw-semibold" onclick="sortSupplierPayments(${supplierId}, 'project', event)">Project <i class="bi bi-arrow-down-up ms-1 text-secondary" id="supplier-payments-sort-icon-${supplierId}-project"></i></button></th>
                            <th class="text-end"><button type="button" class="btn btn-link btn-sm btn-keep-text p-0 text-decoration-none text-dark fw-semibold" onclick="sortSupplierPayments(${supplierId}, 'amount', event)">Amount <i class="bi bi-arrow-down-up ms-1 text-secondary" id="supplier-payments-sort-icon-${supplierId}-amount"></i></button></th>
                            <th><button type="button" class="btn btn-link btn-sm btn-keep-text p-0 text-decoration-none text-dark fw-semibold" onclick="sortSupplierPayments(${supplierId}, 'method', event)">Method <i class="bi bi-arrow-down-up ms-1 text-secondary" id="supplier-payments-sort-icon-${supplierId}-method"></i></button></th>
                            <th>Reference</th>
                        </tr>
                    </thead>
                    <tbody id="supplier-payments-body-${supplierId}"></tbody>
                    <tfoot>
                        <tr class="table-light">
                            <td colspan="3" class="text-end"><strong>Total</strong></td>
                            <td class="text-end"><strong id="supplier-payments-total-${supplierId}">Rs. 0.00</strong></td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        `;
        bodyEl = document.getElementById(`supplier-payments-body-${supplierId}`);
        totalEl = document.getElementById(`supplier-payments-total-${supplierId}`);
    }

    if (!bodyEl) return;
    if (emptyEl) emptyEl.remove();

    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td>${payment.payment_date ? String(payment.payment_date).slice(0, 10) : 'N/A'}</td>
        <td>${renderPaymentTypeLabel(payment.payment_type)}</td>
        <td>${payment.project?.name || 'N/A'}</td>
        <td class="text-end">Rs. ${formatMoney(payment.amount)}</td>
        <td>${renderPaymentTypeLabel(payment.payment_method || 'N/A')}</td>
        <td>${payment.transaction_reference || '—'}</td>
    `;
    bodyEl.prepend(tr);

    const count = bodyEl.querySelectorAll('tr').length;
    const countTarget = document.getElementById(`supplier-payments-count-${supplierId}`);
    if (countTarget) countTarget.textContent = `${count} records`;

    if (totalEl) {
        const existingTotal = parseFloat(String(totalEl.textContent || '').replace(/[^0-9.-]/g, '')) || 0;
        const added = parseFloat(payment.amount || 0) || 0;
        totalEl.textContent = `Rs. ${formatMoney(existingTotal + added)}`;
    }

    const state = supplierPaymentSortState[supplierId];
    if (state?.column) {
        sortSupplierPayments(supplierId, state.column);
    }
}

function openSupplierPaymentModal(supplierId, supplierName) {
    const modal = document.getElementById('supplierPaymentModal');
    document.getElementById('sp_supplier_id').value = supplierId;
    document.getElementById('sp_supplier_label').textContent = supplierName || '—';
    document.querySelectorAll('#supplierPaymentForm .field-error-sp').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
    document.getElementById('sp_payment_type').innerHTML = '<option value="">Loading…</option>';

    fetch(supplierPaymentFormDataUrl, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        const pt = document.getElementById('sp_payment_type');
        pt.innerHTML = '<option value="">Select payment type</option>';
        (data.paymentTypes || []).forEach(type => {
            const code = type.code || (type.name || '').toLowerCase().replace(/\s+/g, '_');
            pt.innerHTML += `<option value="${code}">${type.name}</option>`;
        });
        const preferred = (data.paymentTypes || []).find(t => (t.code || '') === 'supplier')
            || (data.paymentTypes || []).find(t => (t.code || '') === 'material_payment');
        if (preferred) {
            const c = preferred.code || (preferred.name || '').toLowerCase().replace(/\s+/g, '_');
            pt.value = c;
        }
        const proj = document.getElementById('sp_project_id');
        proj.innerHTML = '<option value="">None</option>';
        (data.projects || []).forEach(p => {
            proj.innerHTML += `<option value="${p.id}">${p.name}</option>`;
        });
        const bank = document.getElementById('sp_bank_account_id');
        bank.innerHTML = '<option value="">None</option>';
        (data.bankAccounts || []).forEach(a => {
            bank.innerHTML += `<option value="${a.id}">${a.account_name} (${a.account_type || '—'})</option>`;
        });
        document.getElementById('sp_payment_date').value = new Date().toISOString().split('T')[0];
        document.getElementById('sp_amount').value = '';
        document.getElementById('sp_payment_method').value = '';
        document.getElementById('sp_transaction_reference').value = '';
        document.getElementById('sp_notes').value = '';
        modal.classList.remove('hidden');
    })
    .catch(err => {
        console.error(err);
        showNotification('Could not load payment form. Try again.', 'error');
    });
}

function closeSupplierPaymentModal() {
    document.getElementById('supplierPaymentModal').classList.add('hidden');
}

function previewQRImage(input) {
    const previewDiv = document.getElementById('qr-preview');
    const previewImg = document.getElementById('qr-preview-img');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            previewDiv.style.display = 'block';
        }
        
        reader.readAsDataURL(input.files[0]);
    } else {
        previewDiv.style.display = 'none';
    }
}

function openCreateSupplierModal() {
    currentSupplierId = null;
    const modal = document.getElementById('supplierModal');
    const title = document.getElementById('supplier-modal-title');
    const form = document.getElementById('supplierForm');
    const methodInput = document.getElementById('supplier-method');
    const submitBtn = document.getElementById('supplier-submit-btn');
    
    modal.classList.remove('hidden');
    title.textContent = 'Add Supplier';
    methodInput.value = 'POST';
    submitBtn.textContent = 'Save Supplier';
    form.reset();
    document.getElementById('supplier-is-active').checked = true;
    document.getElementById('qr-preview').style.display = 'none';
    
    document.querySelectorAll('.field-error').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
}

function openEditSupplierModal(supplierId) {
    currentSupplierId = supplierId;
    const modal = document.getElementById('supplierModal');
    const title = document.getElementById('supplier-modal-title');
    const form = document.getElementById('supplierForm');
    const methodInput = document.getElementById('supplier-method');
    const submitBtn = document.getElementById('supplier-submit-btn');
    
    modal.classList.remove('hidden');
    title.textContent = 'Edit Supplier';
    methodInput.value = 'PUT';
    submitBtn.textContent = 'Update Supplier';
    
    document.querySelectorAll('.field-error').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
    
    fetch(`/admin/suppliers/${supplierId}/edit`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('supplier-name').value = data.supplier.name || '';
        document.getElementById('supplier-contact').value = data.supplier.contact || '';
        document.getElementById('supplier-email').value = data.supplier.email || '';
        document.getElementById('supplier-address').value = data.supplier.address || '';
        document.getElementById('supplier-bank-name').value = data.supplier.bank_name || '';
        document.getElementById('supplier-account-holder-name').value = data.supplier.account_holder_name || '';
        document.getElementById('supplier-account-number').value = data.supplier.account_number || '';
        document.getElementById('supplier-branch-name').value = data.supplier.branch_name || '';
        document.getElementById('supplier-branch-address').value = data.supplier.branch_address || '';
        document.getElementById('supplier-is-active').checked = data.supplier.is_active || false;
        
        if (data.supplier.qr_code_image) {
            document.getElementById('qr-preview-img').src = data.supplier.qr_code_image;
            document.getElementById('qr-preview').style.display = 'block';
        } else {
            document.getElementById('qr-preview').style.display = 'none';
        }
    })
    .catch(error => {
        console.error('Error loading supplier data:', error);
        showNotification('Failed to load supplier data', 'error');
    });
}

function submitSupplierForm(e) {
    e.preventDefault();
    const form = e.target;
    const submitBtn = document.getElementById('supplier-submit-btn');
    const originalText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';
    
    const formData = new FormData(form);
    const url = currentSupplierId 
        ? `/admin/suppliers/${currentSupplierId}`
        : '/admin/suppliers';
    
    if (currentSupplierId) {
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
            closeSupplierModal();
            
            if (currentSupplierId) {
                updateSupplierRow(data.supplier);
            } else {
                addSupplierRow(data.supplier);
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

function closeSupplierModal() {
    document.getElementById('supplierModal').classList.add('hidden');
    currentSupplierId = null;
    document.getElementById('supplierForm').reset();
    document.getElementById('qr-preview').style.display = 'none';
}

function addSupplierRow(supplier) {
    const tbody = document.querySelector('table tbody');
    const emptyRow = tbody.querySelector('tr td[colspan]');
    
    if (emptyRow) {
        emptyRow.closest('tr').remove();
    }
    
    const row = document.createElement('tr');
    row.setAttribute('data-supplier-id', supplier.id);
    row.innerHTML = `
        <td>${supplier.id}</td>
        <td>${supplier.name}</td>
        <td>${supplier.contact || ''}</td>
        <td>${supplier.email || ''}</td>
        <td>
            <span class="badge ${supplier.is_active ? 'bg-success' : 'bg-secondary'}">
                ${supplier.is_active ? 'Active' : 'Inactive'}
            </span>
        </td>
        <td class="text-end">
            <div class="d-inline-flex gap-1 justify-content-end text-nowrap supplier-actions">
                <button type="button" onclick="toggleSupplierPayments(${supplier.id}, event)" class="btn btn-sm btn-outline-info" title="Toggle payments">
                    <i class="bi bi-chevron-down" id="supplier-payments-icon-${supplier.id}"></i>
                </button>
                <button onclick="openViewSupplierModal(${supplier.id})" class="btn btn-sm btn-outline-primary" title="View">
                    <i class="bi bi-eye"></i>
                </button>
                <button type="button" onclick="openSupplierPaymentModal(${supplier.id}, ${JSON.stringify(supplier.name || '')})" class="btn btn-sm btn-outline-success" title="Add payment (Advance)">
                    <i class="bi bi-cash-coin"></i>
                </button>
                <button onclick="openEditSupplierModal(${supplier.id})" class="btn btn-sm btn-outline-warning" title="Edit">
                    <i class="bi bi-pencil"></i>
                </button>
                <button onclick="showDeleteSupplierConfirmation(${supplier.id}, '${(supplier.name || '').replace(/'/g, "\\'")}')" class="btn btn-sm btn-outline-danger" title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </td>
    `;
    
    tbody.insertBefore(row, tbody.firstChild);

    const paymentsRow = document.createElement('tr');
    paymentsRow.id = `supplier-payments-row-${supplier.id}`;
    paymentsRow.className = 'd-none bg-light';
    paymentsRow.innerHTML = `
        <td colspan="6" class="p-3">
            <div class="text-muted">No payments recorded yet.</div>
        </td>
    `;
    if (row.nextSibling) {
        tbody.insertBefore(paymentsRow, row.nextSibling);
    } else {
        tbody.appendChild(paymentsRow);
    }
}

function updateSupplierRow(supplier) {
    const row = document.querySelector(`tr[data-supplier-id="${supplier.id}"]`);
    if (row) {
        row.innerHTML = `
            <td>${supplier.id}</td>
            <td>${supplier.name}</td>
            <td>${supplier.contact || ''}</td>
            <td>${supplier.email || ''}</td>
            <td>
                <span class="badge ${supplier.is_active ? 'bg-success' : 'bg-secondary'}">
                    ${supplier.is_active ? 'Active' : 'Inactive'}
                </span>
            </td>
            <td class="text-end">
                <div class="d-inline-flex gap-1 justify-content-end text-nowrap supplier-actions">
                    <button type="button" onclick="toggleSupplierPayments(${supplier.id}, event)" class="btn btn-sm btn-outline-info" title="Toggle payments">
                        <i class="bi bi-chevron-down" id="supplier-payments-icon-${supplier.id}"></i>
                    </button>
                    <button onclick="openViewSupplierModal(${supplier.id})" class="btn btn-sm btn-outline-primary" title="View">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button type="button" onclick="openSupplierPaymentModal(${supplier.id}, ${JSON.stringify(supplier.name || '')})" class="btn btn-sm btn-outline-success" title="Add payment (Advance)">
                        <i class="bi bi-cash-coin"></i>
                    </button>
                    <button onclick="openEditSupplierModal(${supplier.id})" class="btn btn-sm btn-outline-warning" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button onclick="showDeleteSupplierConfirmation(${supplier.id}, '${(supplier.name || '').replace(/'/g, "\\'")}')" class="btn btn-sm btn-outline-danger" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        `;
    }
}

function openViewSupplierModal(supplierId) {
    const modal = document.getElementById('viewSupplierModal');
    const content = document.getElementById('view-supplier-content');
    
    modal.classList.remove('hidden');
    content.innerHTML = `
        <div class="flex items-center justify-center py-12">
            <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    `;
    
    fetch(`/admin/suppliers/${supplierId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const sup = data.supplier;
        const fin = data.financial || null;
        const money = (v) => Number(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        const paidWithAdvance = fin ? (Number(fin.gross_paid || 0) + Number(fin.advance_payments_total || 0)) : 0;
        const isClear = fin ? (Number(fin.net_balance || 0) <= 0) : false;
        content.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Name</dt>
                        <dd class="mt-1 text-sm text-gray-900">${sup.name || ''}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Contact</dt>
                        <dd class="mt-1 text-sm text-gray-900">${sup.contact || 'N/A'}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900">${sup.email || 'N/A'}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Address</dt>
                        <dd class="mt-1 text-sm text-gray-900">${sup.address || 'N/A'}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${sup.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                                ${sup.is_active ? 'Active' : 'Inactive'}
                            </span>
                        </dd>
                    </div>
                </div>
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Bank Details</h3>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Bank Name</dt>
                        <dd class="mt-1 text-sm text-gray-900">${sup.bank_name || 'N/A'}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Account Holder Name</dt>
                        <dd class="mt-1 text-sm text-gray-900">${sup.account_holder_name || 'N/A'}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Account Number</dt>
                        <dd class="mt-1 text-sm text-gray-900">${sup.account_number || 'N/A'}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Branch Name</dt>
                        <dd class="mt-1 text-sm text-gray-900">${sup.branch_name || 'N/A'}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Branch Address</dt>
                        <dd class="mt-1 text-sm text-gray-900">${sup.branch_address || 'N/A'}</dd>
                    </div>
                    ${sup.qr_code_image ? `
                        <div>
                            <dt class="text-sm font-medium text-gray-500 mb-2">QR Code</dt>
                            <dd class="mt-1">
                                <img src="${sup.qr_code_image}" alt="QR Code" class="img-thumbnail" style="max-width: 200px;">
                            </dd>
                        </div>
                    ` : ''}
                </div>
            </div>
            <div class="mt-4 flex justify-end gap-2 flex-wrap">
                <button type="button" onclick="closeViewSupplierModal(); openSupplierPaymentModal(${sup.id}, ${JSON.stringify(sup.name || '')})" class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700">
                    Add payment
                </button>
                <button onclick="closeViewSupplierModal(); openEditSupplierModal(${sup.id})" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                    Edit
                </button>
                <button onclick="closeViewSupplierModal()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    Close
                </button>
            </div>
        `;
    })
    .catch(error => {
        console.error('Error loading supplier:', error);
        content.innerHTML = `
            <div class="text-center py-12">
                <p class="text-red-600 mb-4">Failed to load supplier details</p>
                <button onclick="closeViewSupplierModal()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    Close
                </button>
            </div>
        `;
    });
}

function closeViewSupplierModal() {
    document.getElementById('viewSupplierModal').classList.add('hidden');
}

function showDeleteSupplierConfirmation(supplierId, supplierName) {
    deleteSupplierId = supplierId;
    document.getElementById('delete-supplier-name').textContent = supplierName;
    document.getElementById('deleteSupplierConfirmationModal').classList.remove('hidden');
}

function closeDeleteSupplierConfirmation() {
    document.getElementById('deleteSupplierConfirmationModal').classList.add('hidden');
    deleteSupplierId = null;
}

function confirmDeleteSupplier() {
    if (!deleteSupplierId) return;
    
    const supplierIdToDelete = deleteSupplierId;
    const row = document.querySelector(`tr[data-supplier-id="${supplierIdToDelete}"]`);
    const deleteBtn = event.target;
    
    deleteBtn.disabled = true;
    deleteBtn.textContent = 'Deleting...';
    
    fetch(`/admin/suppliers/${supplierIdToDelete}`, {
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
            closeDeleteSupplierConfirmation();
            showNotification(data.message, 'success');
            
            if (row) {
                const detailsRow = document.getElementById(`supplier-payments-row-${supplierIdToDelete}`);
                row.style.transition = 'opacity 0.3s, transform 0.3s';
                row.style.opacity = '0';
                row.style.transform = 'translateX(-20px)';
                if (detailsRow) {
                    detailsRow.style.transition = 'opacity 0.3s';
                    detailsRow.style.opacity = '0';
                }
                setTimeout(() => {
                    row.remove();
                    if (detailsRow) detailsRow.remove();
                    const tbody = document.querySelector('table tbody');
                    if (tbody && tbody.children.length === 0) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">No suppliers found.</td>
                            </tr>
                        `;
                    }
                }, 300);
            }
        } else {
            showNotification(data.message || 'Failed to delete supplier', 'error');
            deleteBtn.disabled = false;
            deleteBtn.textContent = 'Delete';
        }
    })
    .catch(error => {
        console.error('Error deleting supplier:', error);
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

document.getElementById('supplierPaymentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const submitBtn = document.getElementById('supplierPaymentSubmitBtn');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';
    document.querySelectorAll('#supplierPaymentForm .field-error-sp').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });

    const formData = new FormData(this);
    fetch(supplierPaymentStoreUrl, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(r => {
        if (r.status === 422) return r.json().then(j => Promise.reject({ validation: true, body: j }));
        return r.json();
    })
    .then(data => {
        if (data.success) {
            showNotification(data.message || 'Payment recorded. Expense entry created.', 'success');
            if (data.advancePayment) {
                addSupplierPaymentToList(data.advancePayment);
            }
            closeSupplierPaymentModal();
        } else if (data.errors) {
            Object.keys(data.errors).forEach(field => {
                const el = document.querySelector(`#supplierPaymentForm .field-error-sp[data-field="${field}"]`);
                if (el) {
                    el.textContent = data.errors[field][0];
                    el.style.display = 'block';
                }
            });
            showNotification('Please fix the highlighted fields.', 'error');
        } else {
            showNotification(data.message || 'Could not save payment', 'error');
        }
    })
    .catch(err => {
        if (err.validation && err.body && err.body.errors) {
            const errors = err.body.errors;
            Object.keys(errors).forEach(field => {
                const el = document.querySelector(`#supplierPaymentForm .field-error-sp[data-field="${field}"]`);
                if (el) {
                    el.textContent = errors[field][0];
                    el.style.display = 'block';
                }
            });
            showNotification('Please fix the highlighted fields.', 'error');
        } else {
            console.error(err);
            showNotification('Could not save payment.', 'error');
        }
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        if (!document.getElementById('supplierModal').classList.contains('hidden')) {
            closeSupplierModal();
        }
        if (!document.getElementById('viewSupplierModal').classList.contains('hidden')) {
            closeViewSupplierModal();
        }
        if (!document.getElementById('deleteSupplierConfirmationModal').classList.contains('hidden')) {
            closeDeleteSupplierConfirmation();
        }
        if (!document.getElementById('supplierPaymentModal').classList.contains('hidden')) {
            closeSupplierPaymentModal();
        }
    }
});

document.getElementById('supplierModal').addEventListener('click', function(e) {
    if (e.target === this) closeSupplierModal();
});

document.getElementById('viewSupplierModal').addEventListener('click', function(e) {
    if (e.target === this) closeViewSupplierModal();
});

document.getElementById('deleteSupplierConfirmationModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteSupplierConfirmation();
});

document.getElementById('supplierPaymentModal').addEventListener('click', function(e) {
    if (e.target === this) closeSupplierPaymentModal();
});
</script>
@endpush
@endsection
