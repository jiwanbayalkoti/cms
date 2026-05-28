@extends('admin.layout')

@section('title', 'Tax Invoices (कर विजक)')

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin-tax-invoice-print.css') }}?v=1">
<style>
    .tax-invoice-print-preview { max-height: 460px; }
    .tax-invoice-print-preview .vat-sheet { box-shadow: none; }
</style>
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h1 class="h3 mb-0">Tax Invoices (कर विजक)</h1>
        <p class="text-muted mb-0">{{ $company->name }} — VAT bills with company design</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.tax-invoices.settings') }}" class="btn btn-outline-secondary">
            <i class="bi bi-palette me-1"></i>Design settings
        </a>
        <button type="button" class="btn btn-success" id="taxInvoiceCreateBtn">
            <i class="bi bi-plus-circle me-1"></i>New Tax Invoice
        </button>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<form method="GET" action="{{ route('admin.tax-invoices.index') }}" id="taxInvoiceFilterForm" class="row g-2 mb-3" onsubmit="return false;">
    <div class="col-md-4">
        <input type="text" name="q" id="tax_invoice_filter_q" class="form-control"
               placeholder="Search invoice no. or buyer..." value="{{ request('q') }}"
               autocomplete="off">
    </div>
    <div class="col-md-2">
        <select name="year" id="tax_invoice_filter_year" class="form-select">
            <option value="">All years</option>
            @foreach($filterYears ?? [] as $y)
                <option value="{{ $y }}" @selected((string) request('year') === (string) $y)>{{ $y }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <select name="status" id="tax_invoice_filter_status" class="form-select">
            <option value="">All status</option>
            @foreach(['draft','issued','cancelled'] as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <button type="button" class="btn btn-outline-secondary w-100" id="taxInvoiceFilterClear">
            <i class="bi bi-x-circle me-1"></i>Clear
        </button>
    </div>
</form>

<div class="card position-relative" id="taxInvoicesListCard">
    <div id="taxInvoicesListLoading" class="position-absolute top-0 start-0 w-100 h-100 d-none align-items-center justify-content-center bg-white bg-opacity-75" style="z-index: 2;">
        <div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
    </div>
    <div id="taxInvoicesListInner">
        @include('admin.tax_invoices.partials.index-list')
    </div>
</div>
@endsection

@push('modals')
<div class="modal fade" id="taxInvoiceCrudModal" tabindex="-1" aria-labelledby="taxInvoiceCrudModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="taxInvoiceCrudModalTitle">New Tax Invoice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="taxInvoiceCrudModalBody">
                <div class="text-center text-muted py-5">Loading form…</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="taxInvoiceCrudSaveBtn">
                    <i class="bi bi-check-circle me-1"></i>Save
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="taxInvoiceViewModal" tabindex="-1" aria-labelledby="taxInvoiceViewModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="taxInvoiceViewModalTitle">Tax Invoice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="taxInvoiceViewModalBody">
                <div class="text-center text-muted py-5">Loading…</div>
            </div>
            <div class="modal-footer">
                <a href="#" class="btn btn-primary d-none" id="taxInvoiceViewPrintBtn" target="_blank">
                    <i class="bi bi-printer me-1"></i>Print / PDF
                </a>
                <button type="button" class="btn btn-outline-warning d-none" id="taxInvoiceViewEditBtn">
                    <i class="bi bi-pencil me-1"></i>Edit
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
(function() {
    const createUrl = @json(route('admin.tax-invoices.create'));
    const editUrlTemplate = @json(route('admin.tax-invoices.edit', ['tax_invoice' => '__ID__']));
    const showUrlTemplate = @json(route('admin.tax-invoices.show', ['tax_invoice' => '__ID__']));
    const printUrlTemplate = @json(route('admin.tax-invoices.print', ['tax_invoice' => '__ID__']));
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    let taxInvoiceModalInstance = null;
    let taxInvoiceViewModalInstance = null;
    let taxInvoiceFormLoading = false;
    let taxInvoiceViewLoading = false;
    let taxInvoiceViewCurrentId = null;

    function editUrl(id) {
        return editUrlTemplate.replace('__ID__', String(id));
    }

    function showUrl(id) {
        return showUrlTemplate.replace('__ID__', String(id));
    }

    function printUrl(id) {
        return printUrlTemplate.replace('__ID__', String(id));
    }

    window.openTaxInvoicePrint = function(invoiceId) {
        if (!invoiceId) return;

        let frame = document.getElementById('taxInvoicePrintFrame');
        if (!frame) {
            frame = document.createElement('iframe');
            frame.id = 'taxInvoicePrintFrame';
            frame.setAttribute('title', 'Tax invoice print');
            frame.style.cssText = 'position:fixed;width:0;height:0;border:0;visibility:hidden;';
            document.body.appendChild(frame);
        }

        const url = printUrl(invoiceId) + '?embed=1';

        frame.onload = function() {
            frame.onload = null;
            try {
                const win = frame.contentWindow;
                if (!win) {
                    throw new Error('Print frame unavailable');
                }
                win.focus();
                win.print();
            } catch (err) {
                if (typeof showNotification === 'function') {
                    showNotification('Could not open print dialog. Try again.', 'error');
                }
            }
        };

        frame.src = url;
    };

    function ensureModalOnBody() {
        const modalEl = document.getElementById('taxInvoiceCrudModal');
        if (modalEl && modalEl.parentElement !== document.body) {
            document.body.appendChild(modalEl);
        }
        return modalEl;
    }

    function showFormErrors(messages) {
        const errorsEl = document.getElementById('taxInvoiceFormErrors');
        if (!errorsEl) return;
        if (!messages || !messages.length) {
            errorsEl.classList.add('d-none');
            errorsEl.innerHTML = '';
            return;
        }
        errorsEl.innerHTML = '<ul class="mb-0">' + messages.map(function(m) {
            return '<li>' + String(m).replace(/</g, '&lt;') + '</li>';
        }).join('') + '</ul>';
        errorsEl.classList.remove('d-none');
    }

    window.initTaxInvoiceForm = function(root) {
        root = root || document.getElementById('taxInvoiceCrudModalBody');
        if (!root) return;

        let taxItemIndex = root.querySelectorAll('.tax-item-row').length;

        function recalcPreview() {
            let subtotal = 0;
            root.querySelectorAll('.tax-item-row').forEach(function(row) {
                const q = parseFloat(row.querySelector('.qty-input')?.value) || 0;
                const r = parseFloat(row.querySelector('.rate-input')?.value) || 0;
                const amt = Math.round(q * r * 100) / 100;
                subtotal += amt;
                const cell = row.querySelector('.line-amt');
                if (cell) cell.textContent = amt.toFixed(2);
            });
            const discPct = parseFloat(root.querySelector('#discount_percent')?.value) || 0;
            let discAmt = parseFloat(root.querySelector('#discount_amount')?.value) || 0;
            if (discPct > 0) discAmt = Math.round(subtotal * discPct / 100 * 100) / 100;
            discAmt = Math.min(discAmt, subtotal);
            const taxable = Math.round((subtotal - discAmt) * 100) / 100;
            const vatPct = parseFloat(root.querySelector('#vat_percent')?.value) || 13;
            const vat = Math.round(taxable * vatPct / 100 * 100) / 100;
            const grand = Math.round((taxable + vat) * 100) / 100;
            const subEl = root.querySelector('#preview_subtotal');
            const taxEl = root.querySelector('#preview_taxable');
            const vatEl = root.querySelector('#preview_vat');
            const grandEl = root.querySelector('#preview_grand');
            if (subEl) subEl.textContent = subtotal.toFixed(2);
            if (taxEl) taxEl.textContent = taxable.toFixed(2);
            if (vatEl) vatEl.textContent = vat.toFixed(2);
            if (grandEl) grandEl.textContent = grand.toFixed(2);
        }

        function bindCalc() {
            root.querySelectorAll('.qty-input, .rate-input, #discount_percent, #discount_amount, #vat_percent').forEach(function(el) {
                el.removeEventListener('input', recalcPreview);
                el.addEventListener('input', recalcPreview);
            });
            recalcPreview();
        }

        function addTaxItemRow() {
            const tbody = root.querySelector('#taxItemsBody');
            if (!tbody) return;
            const tr = document.createElement('tr');
            tr.className = 'tax-item-row';
            tr.innerHTML = `
                <td><input type="text" name="items[${taxItemIndex}][hs_code]" class="form-control form-control-sm"></td>
                <td><textarea name="items[${taxItemIndex}][description]" class="form-control form-control-sm tax-desc-input" rows="4" required></textarea></td>
                <td><input type="number" step="0.0001" min="0.0001" name="items[${taxItemIndex}][quantity]" class="form-control form-control-sm qty-input" value="1" required></td>
                <td><input type="text" name="items[${taxItemIndex}][unit]" class="form-control form-control-sm"></td>
                <td><input type="number" step="0.01" min="0" name="items[${taxItemIndex}][unit_price]" class="form-control form-control-sm rate-input" required></td>
                <td class="line-amt text-end align-middle">0.00</td>
                <td><button type="button" class="btn btn-sm btn-outline-danger tax-item-remove-btn" title="Remove row"><i class="bi bi-trash"></i></button></td>
            `;
            tbody.appendChild(tr);
            taxItemIndex++;
            bindCalc();
        }

        const addBtn = root.querySelector('#taxInvoiceAddRowBtn');
        if (addBtn) {
            addBtn.addEventListener('click', addTaxItemRow);
        }

        root.querySelectorAll('.tax-item-remove-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const rows = root.querySelectorAll('.tax-item-row');
                if (rows.length <= 1) return;
                btn.closest('tr')?.remove();
                recalcPreview();
            });
        });

        const cust = root.querySelector('#tax_invoice_customer');
        if (cust) {
            cust.addEventListener('change', function() {
                const opt = this.options[this.selectedIndex];
                if (!opt.value) return;
                const nameEl = root.querySelector('#buyer_name');
                const addrEl = root.querySelector('#buyer_address');
                const panEl = root.querySelector('#buyer_pan');
                const phoneEl = root.querySelector('#buyer_phone');
                if (nameEl) nameEl.value = opt.dataset.name || '';
                if (addrEl) addrEl.value = opt.dataset.address || '';
                if (panEl) panEl.value = opt.dataset.pan || '';
                if (phoneEl) phoneEl.value = opt.dataset.phone || '';
            });
        }

        const deleteBtn = root.querySelector('#taxInvoiceDeleteBtn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function() {
                if (!confirm('Delete this tax invoice?')) return;
                submitTaxInvoiceDelete();
            });
        }

        bindCalc();
    };

    window.openTaxInvoiceModal = async function(mode, invoiceId) {
        if (taxInvoiceFormLoading) return;
        const modalEl = ensureModalOnBody();
        const bodyEl = document.getElementById('taxInvoiceCrudModalBody');
        const titleEl = document.getElementById('taxInvoiceCrudModalTitle');
        const saveBtn = document.getElementById('taxInvoiceCrudSaveBtn');
        if (!modalEl || !bodyEl || !titleEl || !saveBtn) return;

        const url = mode === 'edit' && invoiceId ? editUrl(invoiceId) : createUrl;
        titleEl.textContent = mode === 'edit' ? 'Edit Tax Invoice' : 'New Tax Invoice (कर विजक)';
        saveBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i>' + (mode === 'edit' ? 'Update' : 'Save');
        bodyEl.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>';
        showFormErrors([]);

        taxInvoiceFormLoading = true;
        try {
            const resp = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
            });
            if (resp.status === 403) {
                const data = await resp.json().catch(function() { return {}; });
                if (typeof showNotification === 'function') {
                    showNotification(data.message || 'This invoice cannot be edited.', 'error');
                }
                return;
            }
            if (!resp.ok) throw new Error('Failed to load form');
            bodyEl.innerHTML = await resp.text();
            initTaxInvoiceForm(bodyEl);
            taxInvoiceModalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
            taxInvoiceModalInstance.show();
        } catch (err) {
            bodyEl.innerHTML = '<div class="alert alert-danger mb-0">Could not load the form. Please refresh and try again.</div>';
            if (typeof showNotification === 'function') {
                showNotification('Could not load tax invoice form.', 'error');
            }
        } finally {
            taxInvoiceFormLoading = false;
        }
    };

    async function submitTaxInvoiceCrudForm() {
        const bodyEl = document.getElementById('taxInvoiceCrudModalBody');
        const form = bodyEl?.querySelector('#taxInvoiceCrudForm');
        const saveBtn = document.getElementById('taxInvoiceCrudSaveBtn');
        if (!form || !saveBtn) return;

        saveBtn.disabled = true;
        showFormErrors([]);

        const formData = new FormData(form);
        const methodInput = form.querySelector('input[name="_method"]');
        if (methodInput && methodInput.value === 'PUT') {
            formData.append('_method', 'PUT');
        }

        try {
            const resp = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: formData,
            });

            const data = await resp.json().catch(function() { return {}; });

            if (!resp.ok) {
                if (data.errors) {
                    showFormErrors(Object.values(data.errors).flat());
                } else {
                    showFormErrors([data.message || 'Failed to save tax invoice.']);
                }
                return;
            }

            taxInvoiceModalInstance?.hide();
            if (typeof showNotification === 'function') {
                showNotification(data.message || 'Saved.', 'success');
            }
            if (typeof loadTaxInvoices === 'function') {
                loadTaxInvoices(1, true);
            }
        } catch (err) {
            showFormErrors(['Request failed. Please try again.']);
        } finally {
            saveBtn.disabled = false;
        }
    }

    async function submitTaxInvoiceDelete() {
        const bodyEl = document.getElementById('taxInvoiceCrudModalBody');
        const form = bodyEl?.querySelector('#taxInvoiceCrudForm');
        if (!form) return;
        const invoiceId = form.dataset.invoiceId;
        if (!invoiceId) return;

        const destroyUrl = @json(route('admin.tax-invoices.destroy', ['tax_invoice' => '__ID__'])).replace('__ID__', invoiceId);

        try {
            const resp = await fetch(destroyUrl, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({ _method: 'DELETE', _token: csrfToken }),
            });
            const data = await resp.json().catch(function() { return {}; });
            if (!resp.ok) {
                showFormErrors([data.message || 'Failed to delete.']);
                return;
            }
            taxInvoiceModalInstance?.hide();
            if (typeof showNotification === 'function') {
                showNotification(data.message || 'Deleted.', 'success');
            }
            if (typeof loadTaxInvoices === 'function') {
                loadTaxInvoices(1, true);
            }
        } catch (err) {
            showFormErrors(['Delete request failed.']);
        }
    }

    document.getElementById('taxInvoiceCreateBtn')?.addEventListener('click', function() {
        openTaxInvoiceModal('create');
    });

    document.getElementById('taxInvoiceCrudSaveBtn')?.addEventListener('click', submitTaxInvoiceCrudForm);

    function ensureViewModalOnBody() {
        const modalEl = document.getElementById('taxInvoiceViewModal');
        if (modalEl && modalEl.parentElement !== document.body) {
            document.body.appendChild(modalEl);
        }
        return modalEl;
    }

    window.openTaxInvoiceViewModal = async function(invoiceId, isEditable) {
        if (taxInvoiceViewLoading || !invoiceId) return;
        const modalEl = ensureViewModalOnBody();
        const bodyEl = document.getElementById('taxInvoiceViewModalBody');
        const titleEl = document.getElementById('taxInvoiceViewModalTitle');
        const printBtn = document.getElementById('taxInvoiceViewPrintBtn');
        const editBtn = document.getElementById('taxInvoiceViewEditBtn');
        if (!modalEl || !bodyEl) return;

        taxInvoiceViewCurrentId = invoiceId;
        titleEl.textContent = 'Tax Invoice';
        bodyEl.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>';

        if (printBtn) {
            printBtn.classList.add('d-none');
            printBtn.onclick = null;
        }
        taxInvoiceViewLoading = true;
        try {
            const resp = await fetch(showUrl(invoiceId), {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
            });
            if (!resp.ok) throw new Error('Failed to load');
            bodyEl.innerHTML = await resp.text();
            const root = bodyEl.querySelector('.tax-invoice-view-detail');
            const status = root?.dataset.status || '';
            const canEdit = root
                ? root.dataset.editable === '1'
                : (isEditable === true || isEditable === '1' || isEditable === 1);
            const canPrint = status === 'issued';
            if (printBtn) {
                if (canPrint) {
                    printBtn.href = printUrl(invoiceId);
                    printBtn.classList.remove('d-none');
                    printBtn.onclick = function(ev) {
                        ev.preventDefault();
                        openTaxInvoicePrint(invoiceId);
                    };
                } else {
                    printBtn.classList.add('d-none');
                }
            }
            if (editBtn) {
                if (canEdit) {
                    editBtn.classList.remove('d-none');
                    editBtn.dataset.invoiceId = root?.dataset.invoiceId || invoiceId;
                } else {
                    editBtn.classList.add('d-none');
                }
            }
            const numEl = bodyEl.querySelector('.h5');
            if (numEl) titleEl.textContent = 'Tax Invoice ' + numEl.textContent.replace('#', '').trim();
            taxInvoiceViewModalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
            taxInvoiceViewModalInstance.show();
        } catch (err) {
            bodyEl.innerHTML = '<div class="alert alert-danger mb-0">Could not load invoice details.</div>';
            if (typeof showNotification === 'function') {
                showNotification('Could not load invoice.', 'error');
            }
        } finally {
            taxInvoiceViewLoading = false;
        }
    };

    document.getElementById('taxInvoiceViewEditBtn')?.addEventListener('click', function() {
        const id = this.dataset.invoiceId || taxInvoiceViewCurrentId;
        taxInvoiceViewModalInstance?.hide();
        if (id) openTaxInvoiceModal('edit', id);
    });

    document.addEventListener('click', function(e) {
        const viewBtn = e.target.closest('.tax-invoice-view-btn');
        if (viewBtn) {
            e.preventDefault();
            openTaxInvoiceViewModal(viewBtn.dataset.id, viewBtn.dataset.editable);
            return;
        }
        const editBtn = e.target.closest('.tax-invoice-edit-btn');
        if (editBtn) {
            e.preventDefault();
            openTaxInvoiceModal('edit', editBtn.dataset.id);
        }
    });

    @if(request('modal') === 'create')
    openTaxInvoiceModal('create');
    @elseif(request('edit'))
    openTaxInvoiceModal('edit', {{ (int) request('edit') }});
    @elseif(request('view'))
    openTaxInvoiceViewModal({{ (int) request('view') }});
    @endif
})();

(function() {
    const listInner = document.getElementById('taxInvoicesListInner');
    const loadingEl = document.getElementById('taxInvoicesListLoading');
    const qInput = document.getElementById('tax_invoice_filter_q');
    const yearSelect = document.getElementById('tax_invoice_filter_year');
    const statusSelect = document.getElementById('tax_invoice_filter_status');
    const clearBtn = document.getElementById('taxInvoiceFilterClear');
    const indexUrl = @json(route('admin.tax-invoices.index'));
    let filterTimer = null;
    let listAbort = null;
    let isLoading = false;

    function setLoading(on) {
        isLoading = on;
        if (loadingEl) {
            loadingEl.classList.toggle('d-none', !on);
            loadingEl.classList.toggle('d-flex', on);
        }
        if (listInner) {
            listInner.style.opacity = on ? '0.55' : '1';
        }
    }

    function buildUrl(page) {
        const params = new URLSearchParams();
        const q = (qInput && qInput.value) ? qInput.value.trim() : '';
        const year = yearSelect ? yearSelect.value : '';
        const status = statusSelect ? statusSelect.value : '';
        if (q) params.set('q', q);
        if (year) params.set('year', year);
        if (status) params.set('status', status);
        if (page && page > 1) params.set('page', String(page));
        const qs = params.toString();
        return qs ? indexUrl + '?' + qs : indexUrl;
    }

    function bindPagination() {
        if (!listInner) return;
        listInner.querySelectorAll('.pagination a').forEach(function(anchor) {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                if (isLoading) return;
                let page = 1;
                try {
                    const u = new URL(anchor.href, window.location.origin);
                    page = parseInt(u.searchParams.get('page') || '1', 10) || 1;
                } catch (err) {
                    page = 1;
                }
                loadTaxInvoices(page, true);
            });
        });
    }

    window.loadTaxInvoices = function(page, pushHistory) {
        if (!listInner) return;
        if (listAbort) listAbort.abort();
        listAbort = new AbortController();
        const url = buildUrl(page || 1);
        setLoading(true);

        fetch(url, {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
            signal: listAbort.signal,
        })
            .then(function(res) {
                if (!res.ok) throw new Error('Failed to load');
                return res.text();
            })
            .then(function(html) {
                listInner.innerHTML = html;
                bindPagination();
                if (pushHistory !== false) {
                    window.history.replaceState({}, '', url);
                }
                setLoading(false);
            })
            .catch(function(err) {
                if (err && err.name === 'AbortError') return;
                setLoading(false);
                if (typeof showNotification === 'function') {
                    showNotification('Failed to load invoices.', 'error');
                }
            });
    };

    function scheduleFilter() {
        clearTimeout(filterTimer);
        filterTimer = setTimeout(function() {
            loadTaxInvoices(1, true);
        }, 300);
    }

    if (qInput) {
        qInput.addEventListener('input', scheduleFilter);
    }
    if (yearSelect) {
        yearSelect.addEventListener('change', function() {
            loadTaxInvoices(1, true);
        });
    }
    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            loadTaxInvoices(1, true);
        });
    }
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            if (qInput) qInput.value = '';
            if (yearSelect) yearSelect.value = '';
            if (statusSelect) statusSelect.value = '';
            loadTaxInvoices(1, true);
        });
    }

    bindPagination();

    const statusCsrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

    function applyTaxInvoiceStatusSelectStyle(sel, status) {
        if (!sel) return;
        sel.classList.remove('border-success', 'border-warning', 'border-secondary');
        if (status === 'issued') sel.classList.add('border-success');
        else if (status === 'cancelled') sel.classList.add('border-secondary');
        else sel.classList.add('border-warning');
        sel.dataset.current = status;
    }

    document.addEventListener('change', function(e) {
        const sel = e.target.closest('.tax-invoice-status-select');
        if (!sel || !listInner?.contains(sel)) return;

        const url = sel.dataset.url;
        const previous = sel.dataset.current || sel.value;
        const newStatus = sel.value;

        if (newStatus === previous) return;

        sel.disabled = true;

        fetch(url, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': statusCsrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ status: newStatus }),
        })
            .then(function(res) {
                return res.json().then(function(data) {
                    return { ok: res.ok, data: data };
                });
            })
            .then(function(result) {
                if (!result.ok) {
                    sel.value = previous;
                    applyTaxInvoiceStatusSelectStyle(sel, previous);
                    if (typeof showNotification === 'function') {
                        showNotification(result.data.message || 'Could not update status.', 'error');
                    }
                    return;
                }
                applyTaxInvoiceStatusSelectStyle(sel, newStatus);
                if (typeof showNotification === 'function') {
                    showNotification(result.data.message || 'Status updated.', 'success');
                }
            })
            .catch(function() {
                sel.value = previous;
                applyTaxInvoiceStatusSelectStyle(sel, previous);
                if (typeof showNotification === 'function') {
                    showNotification('Could not update status.', 'error');
                }
            })
            .finally(function() {
                sel.disabled = false;
            });
    });

    window.addEventListener('popstate', function() {
        const params = new URLSearchParams(window.location.search);
        if (qInput) qInput.value = params.get('q') || '';
        if (yearSelect) yearSelect.value = params.get('year') || '';
        if (statusSelect) statusSelect.value = params.get('status') || '';
        const page = parseInt(params.get('page') || '1', 10) || 1;
        loadTaxInvoices(page, false);
    });
})();
</script>
@endpush
