@extends('admin.layout')

@section('title', 'Worker Payments')

@section('content')
<div class="mb-6 flex flex-wrap justify-between items-center gap-3">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Worker Payments</h1>
        <p class="text-sm text-gray-500 mt-1">Daily / monthly wages calculated from attendance</p>
    </div>
    <button type="button" onclick="openPaymentModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">
        <i class="bi bi-plus-lg me-1"></i> Record Payment
    </button>
</div>

@if(session('success'))
<div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">{{ session('success') }}</div>
@endif
<div id="paymentFlash" class="hidden mb-4 px-4 py-3 rounded-lg text-sm border"></div>

<div class="mb-4 bg-white shadow-lg rounded-lg p-4">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Worker</label>
            <select name="worker_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg" onchange="this.form.submit()">
                <option value="">All</option>
                @foreach($workers as $w)
                    <option value="{{ $w->id }}" {{ request('worker_id') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Project</label>
            <select name="project_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg" onchange="this.form.submit()">
                <option value="">All</option>
                @foreach($projects as $p)
                    <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Wage Type</label>
            <select name="wage_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg" onchange="this.form.submit()">
                <option value="">All</option>
                <option value="daily" {{ request('wage_type') === 'daily' ? 'selected' : '' }}>Daily</option>
                <option value="monthly" {{ request('wage_type') === 'monthly' ? 'selected' : '' }}>Monthly</option>
            </select>
        </div>
        <div class="text-sm">
            <span class="text-gray-500">Total paid (filtered):</span>
            <div class="text-xl font-bold text-red-600">Rs. {{ number_format($totalPaid, 2) }}</div>
        </div>
    </form>
</div>

<div class="bg-white shadow-lg rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Worker</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Period</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Days</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Net</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Paid</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expense</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($payments as $payment)
                    <tr>
                        <td class="px-4 py-3 text-sm">{{ $payment->payment_date->format('M d, Y') }}</td>
                        <td class="px-4 py-3 text-sm font-medium">{{ $payment->worker?->name }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            {{ $payment->period_start->format('M d') }} – {{ $payment->period_end->format('M d, Y') }}
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $payment->wage_type === 'daily' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                {{ ucfirst($payment->wage_type) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-600">
                            P:{{ $payment->present_days }} H:{{ $payment->half_days }}
                        </td>
                        <td class="px-4 py-3 text-sm text-right">{{ number_format($payment->net_amount, 2) }}</td>
                        <td class="px-4 py-3 text-sm text-right font-semibold text-red-600">{{ number_format($payment->paid_amount, 2) }}</td>
                        <td class="px-4 py-3 text-sm">
                            @if($payment->expense_id)
                                <a href="{{ route('admin.expenses.show', $payment->expense_id) }}" target="_blank" rel="noopener noreferrer" class="text-indigo-600 hover:underline">#{{ $payment->expense_id }}</a>
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="d-flex gap-1">
                                <button type="button" onclick="openViewPaymentModal({{ $payment->id }})" class="btn btn-outline-primary btn-sm" title="View"><i class="bi bi-eye"></i></button>
                                <form action="{{ route('admin.worker-payments.destroy', $payment) }}" method="POST" onsubmit="return confirm('Delete payment and linked expense?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-6 py-8 text-center text-gray-500">No payments yet. <button type="button" onclick="openPaymentModal()" class="text-indigo-600 hover:underline">Record one</button></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<x-pagination :paginator="$payments" wrapper-class="mt-4" />

{{-- Payment details modal --}}
<div id="viewPaymentModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full max-h-[90vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-5 border-b">
            <h3 class="text-xl font-semibold text-gray-900">Payment Details</h3>
            <button type="button" onclick="closeViewPaymentModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-5" id="view-payment-content">
            <div class="flex justify-center py-8 text-gray-400 text-sm">Loading…</div>
        </div>
        <div class="p-4 border-t flex justify-end">
            <button type="button" onclick="closeViewPaymentModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Close</button>
        </div>
    </div>
</div>

{{-- Record Payment Modal --}}
<div id="paymentModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full max-h-[90vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-5 border-b">
            <h3 class="text-xl font-semibold text-gray-900">Record Worker Payment</h3>
            <button type="button" onclick="closePaymentModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-5">
            <form id="paymentForm" novalidate onsubmit="submitPaymentForm(event)">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Worker <span class="text-red-500">*</span></label>
                        <select name="worker_id" id="pay-worker-id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                onchange="onWorkerChange()">
                            <option value="">Select worker</option>
                        </select>
                        <div class="field-error text-red-600 text-sm mt-1" data-field="worker_id" style="display:none;"></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Period Start <span class="text-red-500">*</span></label>
                        <input type="date" name="period_start" id="pay-period-start" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg" onchange="onPeriodChange()">
                        <div class="field-error text-red-600 text-sm mt-1" data-field="period_start" style="display:none;"></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Period End <span class="text-red-500">*</span></label>
                        <input type="date" name="period_end" id="pay-period-end" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg" onchange="onPeriodChange()">
                        <div class="field-error text-red-600 text-sm mt-1" data-field="period_end" style="display:none;"></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Project</label>
                        <select name="project_id" id="pay-project-id" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <option value="">Optional</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Payment Date <span class="text-red-500">*</span></label>
                        <input type="date" name="payment_date" id="pay-payment-date" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        <div class="field-error text-red-600 text-sm mt-1" data-field="payment_date" style="display:none;"></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bonus</label>
                        <input type="number" step="0.01" min="0" name="bonus" id="pay-bonus" value="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg" onchange="recalculatePayment()">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Deduction</label>
                        <input type="number" step="0.01" min="0" name="deduction" id="pay-deduction" value="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg" onchange="recalculatePayment()">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                        <input type="text" name="payment_method" id="pay-method" placeholder="Cash / Bank / ..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Paid Amount</label>
                        <input type="number" step="0.01" min="0" name="paid_amount" id="pay-paid-amount"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Auto = due">
                        <div class="field-error text-red-600 text-sm mt-1" data-field="paid_amount" style="display:none;"></div>
                        <p class="text-xs text-gray-500 mt-1">Defaults to due amount (net − already paid for this period)</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea name="notes" id="pay-notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg"></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="inline-flex items-center gap-2">
                            <input type="checkbox" name="create_expense" id="pay-create-expense" value="1" checked class="rounded border-gray-300">
                            <span class="text-sm text-gray-700">Create expense entry (company ledger)</span>
                        </label>
                    </div>
                </div>

                <div id="pay-history-box" class="mt-5 hidden">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-semibold text-gray-900 text-sm">Previous payments</h4>
                        <span id="pay-history-scope" class="text-xs text-gray-500"></span>
                    </div>
                    <div id="pay-history-table-wrap" class="border border-gray-200 rounded-lg overflow-hidden max-h-48 overflow-y-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 sticky top-0">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Paid on</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Period</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500">Net</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500">Paid</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Status</th>
                                </tr>
                            </thead>
                            <tbody id="pay-history-body" class="divide-y divide-gray-100 bg-white"></tbody>
                        </table>
                    </div>
                    <p id="pay-history-empty" class="hidden text-sm text-gray-500 py-3 text-center">No previous payments for this worker.</p>
                </div>

                <div id="pay-calc-box" class="mt-5 p-4 bg-gray-50 rounded-lg border border-gray-200 hidden">
                    <h4 class="font-semibold text-gray-900 mb-2 text-sm">Calculation from attendance</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-sm" id="pay-calc-details"></div>
                    <div class="mt-3 space-y-1 text-sm">
                        <p class="font-semibold text-gray-800" id="pay-calc-net"></p>
                        <p class="text-amber-700" id="pay-calc-already"></p>
                        <p class="text-lg font-bold text-indigo-700" id="pay-calc-due"></p>
                    </div>
                    <p class="text-xs text-gray-500 mt-1" id="pay-calc-hint"></p>
                </div>

                <div class="flex flex-wrap justify-end gap-3 mt-6">
                    <button type="button" onclick="recalculatePayment(true)" class="px-4 py-2 border border-indigo-300 text-indigo-700 rounded-lg hover:bg-indigo-50">Calculate</button>
                    <button type="button" onclick="closePaymentModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" id="pay-submit-btn" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Save Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
    const CREATE_URL = @json(route('admin.worker-payments.create'));
    const CALC_URL = @json(route('admin.worker-payments.calculate'));
    const STORE_URL = @json(route('admin.worker-payments.store'));
    let paidManuallyEdited = false;
    let periodFiltered = false;

    function money(n) {
        return Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function showFlash(msg, isError) {
        const el = document.getElementById('paymentFlash');
        if (!el) return;
        el.textContent = msg;
        el.classList.remove('hidden', 'bg-emerald-50', 'border-emerald-200', 'text-emerald-800', 'bg-rose-50', 'border-rose-200', 'text-rose-800');
        el.classList.add(isError ? 'bg-rose-50' : 'bg-emerald-50', isError ? 'border-rose-200' : 'border-emerald-200', isError ? 'text-rose-800' : 'text-emerald-800');
        clearTimeout(showFlash._t);
        showFlash._t = setTimeout(() => el.classList.add('hidden'), 3000);
    }

    function clearErrors() {
        document.querySelectorAll('#paymentForm .field-error').forEach(el => {
            el.style.display = 'none';
            el.textContent = '';
        });
        document.querySelectorAll('#paymentForm .field-invalid').forEach(el => {
            el.classList.remove('field-invalid', 'border-red-500');
        });
    }

    function setFieldError(field, message) {
        const el = document.querySelector(`#paymentForm .field-error[data-field="${field}"]`);
        if (el) {
            el.textContent = message;
            el.style.display = 'block';
        }
        const input = document.querySelector(`#paymentForm [name="${field}"]`);
        if (input) {
            input.classList.add('field-invalid', 'border-red-500');
        }
    }

    function showErrors(errors) {
        clearErrors();
        Object.keys(errors || {}).forEach(field => {
            const msg = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
            setFieldError(field, msg);
        });
    }

    function validatePaymentForm() {
        clearErrors();
        const required = [
            { field: 'worker_id', message: 'Please select a worker.' },
            { field: 'period_start', message: 'Period start date is required.' },
            { field: 'period_end', message: 'Period end date is required.' },
            { field: 'payment_date', message: 'Payment date is required.' },
        ];
        let ok = true;
        required.forEach(({ field, message }) => {
            const input = document.querySelector(`#paymentForm [name="${field}"]`);
            if (!input || !String(input.value || '').trim()) {
                setFieldError(field, message);
                ok = false;
            }
        });

        const start = document.getElementById('pay-period-start').value;
        const end = document.getElementById('pay-period-end').value;
        if (start && end && end < start) {
            setFieldError('period_end', 'Period end must be on or after period start.');
            ok = false;
        }

        return ok;
    }

    function todayStr() {
        const d = new Date();
        return d.toISOString().slice(0, 10);
    }

    function monthStartStr() {
        const d = new Date();
        return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-01';
    }

    function hideHistory() {
        document.getElementById('pay-history-box').classList.add('hidden');
        document.getElementById('pay-calc-box').classList.add('hidden');
    }

    function renderHistory(payments, scope) {
        const box = document.getElementById('pay-history-box');
        const body = document.getElementById('pay-history-body');
        const empty = document.getElementById('pay-history-empty');
        const wrap = document.getElementById('pay-history-table-wrap');
        const scopeEl = document.getElementById('pay-history-scope');
        box.classList.remove('hidden');
        scopeEl.textContent = scope === 'period' ? 'Filtered by selected period' : 'Showing all payments';
        body.innerHTML = '';

        if (!payments || !payments.length) {
            empty.classList.remove('hidden');
            wrap.classList.add('hidden');
            return;
        }

        empty.classList.add('hidden');
        wrap.classList.remove('hidden');

        payments.forEach(p => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="px-3 py-2 whitespace-nowrap">${p.payment_date || '—'}</td>
                <td class="px-3 py-2 text-gray-600">${p.period_label || '—'}</td>
                <td class="px-3 py-2 text-right">${money(p.net_amount)}</td>
                <td class="px-3 py-2 text-right font-medium text-red-600">${money(p.paid_amount)}</td>
                <td class="px-3 py-2"><span class="text-xs capitalize">${p.status || ''}</span></td>
            `;
            body.appendChild(tr);
        });
    }

    window.onWorkerChange = function () {
        periodFiltered = false;
        paidManuallyEdited = false;
        recalculatePayment(false);
    };

    window.onPeriodChange = function () {
        periodFiltered = true;
        paidManuallyEdited = false;
        recalculatePayment(true);
    };

    window.openPaymentModal = function () {
        const modal = document.getElementById('paymentModal');
        const form = document.getElementById('paymentForm');
        form.reset();
        paidManuallyEdited = false;
        periodFiltered = false;
        clearErrors();
        document.getElementById('pay-create-expense').checked = true;
        document.getElementById('pay-period-start').value = monthStartStr();
        document.getElementById('pay-period-end').value = todayStr();
        document.getElementById('pay-payment-date').value = todayStr();
        document.getElementById('pay-bonus').value = 0;
        document.getElementById('pay-deduction').value = 0;
        hideHistory();
        modal.classList.remove('hidden');

        fetch(CREATE_URL, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
            .then(r => r.json())
            .then(data => {
                const wSel = document.getElementById('pay-worker-id');
                wSel.innerHTML = '<option value="">Select worker</option>';
                (data.workers || []).forEach(w => {
                    const opt = document.createElement('option');
                    opt.value = w.id;
                    opt.textContent = w.label || w.name;
                    opt.dataset.wageType = w.wage_type || '';
                    opt.dataset.project = w.project_id || '';
                    wSel.appendChild(opt);
                });

                const pSel = document.getElementById('pay-project-id');
                pSel.innerHTML = '<option value="">Optional</option>';
                (data.projects || []).forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p.id;
                    opt.textContent = p.name;
                    pSel.appendChild(opt);
                });
            })
            .catch(() => showFlash('Failed to load form data', true));
    };

    window.closePaymentModal = function () {
        document.getElementById('paymentModal').classList.add('hidden');
    };

    window.recalculatePayment = async function (filterPeriod) {
        if (typeof filterPeriod === 'undefined') {
            filterPeriod = periodFiltered;
        }
        const workerId = document.getElementById('pay-worker-id').value;
        const start = document.getElementById('pay-period-start').value;
        const end = document.getElementById('pay-period-end').value;
        if (!workerId || !start || !end) {
            hideHistory();
            return;
        }

        const opt = document.getElementById('pay-worker-id').selectedOptions[0];
        if (opt?.dataset.project && !document.getElementById('pay-project-id').value) {
            document.getElementById('pay-project-id').value = opt.dataset.project;
        }

        const body = new FormData();
        body.append('_token', CSRF);
        body.append('worker_id', workerId);
        body.append('period_start', start);
        body.append('period_end', end);
        body.append('bonus', document.getElementById('pay-bonus').value || 0);
        body.append('deduction', document.getElementById('pay-deduction').value || 0);
        body.append('filter_period', filterPeriod ? '1' : '0');

        try {
            const res = await fetch(CALC_URL, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body
            });
            const data = await res.json();
            if (!data.success) return;

            const c = data.calculation;
            const already = Number(data.already_paid || 0);
            const due = Number(data.due_amount != null ? data.due_amount : (c.net_amount - already));

            renderHistory(data.previous_payments || [], data.list_scope || (filterPeriod ? 'period' : 'all'));

            document.getElementById('pay-calc-box').classList.remove('hidden');
            document.getElementById('pay-calc-details').innerHTML = `
                <div>Wage: <strong>${c.wage_type}</strong></div>
                <div>Present: <strong>${c.present_days}</strong></div>
                <div>Half days: <strong>${c.half_days}</strong></div>
                <div>Absent: <strong>${c.absent_days}</strong></div>
                <div>Leave: <strong>${c.leave_days}</strong></div>
                <div>Day credits: <strong>${c.day_credits}</strong></div>
                <div>OT hours: <strong>${c.overtime_hours}</strong></div>
                <div>Base: <strong>Rs. ${money(c.calculated_amount)}</strong></div>
                <div>OT amt: <strong>Rs. ${money(c.overtime_amount)}</strong></div>
            `;
            document.getElementById('pay-calc-net').textContent = 'Net amount: Rs. ' + money(c.net_amount);
            document.getElementById('pay-calc-already').textContent = 'Already paid (this period): Rs. ' + money(already);

            const dueEl = document.getElementById('pay-calc-due');
            dueEl.classList.remove('text-indigo-700', 'text-red-600', 'text-emerald-700');
            if (due < 0) {
                dueEl.classList.add('text-red-600');
                dueEl.textContent = 'Balance: Rs. -' + money(Math.abs(due)) + ' (overpaid)';
            } else if (due === 0) {
                dueEl.classList.add('text-emerald-700');
                dueEl.textContent = 'Due amount: Rs. 0.00 (fully paid)';
            } else {
                dueEl.classList.add('text-indigo-700');
                dueEl.textContent = 'Due amount: Rs. ' + money(due);
            }
            document.getElementById('pay-calc-hint').textContent = c.wage_type === 'daily'
                ? 'Daily: (present + half×0.5) × daily wage + overtime − previous payments'
                : 'Monthly: (monthly salary ÷ period days) × day credits + overtime − previous payments';

            const paid = document.getElementById('pay-paid-amount');
            if (!paidManuallyEdited || paid.value === '') {
                paid.value = due > 0 ? due : 0;
            }
        } catch (e) {
            console.error(e);
        }
    };

    window.submitPaymentForm = async function (e) {
        e.preventDefault();
        if (!validatePaymentForm()) {
            const firstErr = document.querySelector('#paymentForm .field-invalid');
            if (firstErr) firstErr.focus();
            return;
        }

        const btn = document.getElementById('pay-submit-btn');
        const original = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Saving…';

        const fd = new FormData(e.target);
        if (!document.getElementById('pay-create-expense').checked) {
            fd.delete('create_expense');
        } else {
            fd.set('create_expense', '1');
        }

        try {
            const res = await fetch(STORE_URL, {
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
                if (data.errors) showErrors(data.errors);
                throw new Error(data.message || 'Validation failed');
            }
            closePaymentModal();
            showFlash(data.message || 'Payment saved');
            window.location.reload();
        } catch (err) {
            if (!document.querySelector('#paymentForm .field-error[style*="block"]')) {
                showFlash(err.message || 'Save failed', true);
            }
        } finally {
            btn.disabled = false;
            btn.textContent = original;
        }
    };

    document.getElementById('pay-paid-amount')?.addEventListener('input', () => {
        paidManuallyEdited = true;
    });

    document.getElementById('paymentForm')?.addEventListener('input', function (e) {
        const name = e.target?.name;
        if (!name) return;
        const err = document.querySelector(`#paymentForm .field-error[data-field="${name}"]`);
        if (err) {
            err.style.display = 'none';
            err.textContent = '';
        }
        e.target.classList.remove('field-invalid', 'border-red-500');
    });
    document.getElementById('paymentForm')?.addEventListener('change', function (e) {
        const name = e.target?.name;
        if (!name) return;
        const err = document.querySelector(`#paymentForm .field-error[data-field="${name}"]`);
        if (err) {
            err.style.display = 'none';
            err.textContent = '';
        }
        e.target.classList.remove('field-invalid', 'border-red-500');
    });

    document.getElementById('paymentModal')?.addEventListener('click', function (e) {
        if (e.target === this) closePaymentModal();
    });

    function esc(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    window.openViewPaymentModal = function (id) {
        const modal = document.getElementById('viewPaymentModal');
        const content = document.getElementById('view-payment-content');
        content.innerHTML = '<div class="flex justify-center py-8 text-gray-400 text-sm">Loading…</div>';
        modal.classList.remove('hidden');

        fetch(`/admin/worker-payments/${id}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
            .then(r => {
                if (!r.ok) throw new Error('Failed');
                return r.json();
            })
            .then(data => {
                const p = data.payment;
                const expense = p.expense_url
                    ? `<a href="${esc(p.expense_url)}" target="_blank" rel="noopener noreferrer" class="text-indigo-600 hover:underline">Expense #${esc(p.expense_id)}</a>`
                    : '—';
                content.innerHTML = `
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div><dt class="text-gray-500">Worker</dt><dd class="font-medium text-lg text-gray-900">${esc(p.worker || '—')}</dd></div>
                        <div><dt class="text-gray-500">Project</dt><dd class="font-medium">${esc(p.project || '—')}</dd></div>
                        <div><dt class="text-gray-500">Wage Type</dt><dd class="font-medium">${esc(p.wage_type)}</dd></div>
                        <div><dt class="text-gray-500">Period</dt><dd class="font-medium">${esc(p.period)}</dd></div>
                        <div><dt class="text-gray-500">Present / Half / Absent / Leave</dt><dd class="font-medium">${esc(p.present_days)} / ${esc(p.half_days)} / ${esc(p.absent_days)} / ${esc(p.leave_days)}</dd></div>
                        <div><dt class="text-gray-500">Overtime Hours</dt><dd class="font-medium">${esc(p.overtime_hours)}</dd></div>
                        <div><dt class="text-gray-500">Calculated</dt><dd class="font-medium">Rs. ${money(p.calculated_amount)}</dd></div>
                        <div><dt class="text-gray-500">OT Amount</dt><dd class="font-medium">Rs. ${money(p.overtime_amount)}</dd></div>
                        <div><dt class="text-gray-500">Bonus</dt><dd class="font-medium">Rs. ${money(p.bonus)}</dd></div>
                        <div><dt class="text-gray-500">Deduction</dt><dd class="font-medium">Rs. ${money(p.deduction)}</dd></div>
                        <div><dt class="text-gray-500">Net Amount</dt><dd class="font-medium text-indigo-700">Rs. ${money(p.net_amount)}</dd></div>
                        <div><dt class="text-gray-500">Paid Amount</dt><dd class="font-medium text-red-600 text-lg">Rs. ${money(p.paid_amount)}</dd></div>
                        <div><dt class="text-gray-500">Payment Date</dt><dd class="font-medium">${esc(p.payment_date || '—')}</dd></div>
                        <div><dt class="text-gray-500">Method</dt><dd class="font-medium">${esc(p.payment_method || '—')}</dd></div>
                        <div><dt class="text-gray-500">Status</dt><dd class="font-medium">${esc(p.status)}</dd></div>
                        <div><dt class="text-gray-500">Linked Expense</dt><dd class="font-medium">${expense}</dd></div>
                        <div class="md:col-span-2"><dt class="text-gray-500">Notes</dt><dd class="font-medium">${esc(p.notes || '—')}</dd></div>
                    </dl>`;
            })
            .catch(() => {
                content.innerHTML = '<p class="text-red-600 text-sm text-center py-6">Failed to load payment details.</p>';
            });
    };

    window.closeViewPaymentModal = function () {
        document.getElementById('viewPaymentModal').classList.add('hidden');
    };

    document.getElementById('viewPaymentModal')?.addEventListener('click', function (e) {
        if (e.target === this) closeViewPaymentModal();
    });

    const openViewId = @json(request('view'));
    if (openViewId) {
        openViewPaymentModal(openViewId);
    }
})();
</script>
@endsection
