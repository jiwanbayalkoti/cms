@extends('admin.layout')

@section('title', 'Record Worker Payment')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-3xl font-bold text-gray-900">Record Worker Payment</h1>
    <a href="{{ route('admin.worker-payments.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">Back</a>
</div>

@if($errors->any())
<div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
    <ul class="list-disc list-inside text-sm">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

<div class="bg-white shadow-lg rounded-lg p-6 max-w-3xl">
    <form method="POST" action="{{ route('admin.worker-payments.store') }}" id="paymentForm">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Worker <span class="text-red-500">*</span></label>
                <select name="worker_id" id="worker_id" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        onchange="recalculate()">
                    <option value="">Select worker</option>
                    @foreach($workers as $w)
                        <option value="{{ $w->id }}"
                                data-wage-type="{{ $w->wage_type }}"
                                data-project="{{ $w->project_id }}"
                                {{ old('worker_id') == $w->id ? 'selected' : '' }}>
                            {{ $w->name }} ({{ ucfirst($w->wage_type) }} — Rs. {{ number_format($w->wage_type === 'daily' ? (float)$w->daily_wage : (float)$w->monthly_salary, 2) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Period Start <span class="text-red-500">*</span></label>
                <input type="date" name="period_start" id="period_start" value="{{ old('period_start', date('Y-m-01')) }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg" onchange="recalculate()">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Period End <span class="text-red-500">*</span></label>
                <input type="date" name="period_end" id="period_end" value="{{ old('period_end', date('Y-m-d')) }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg" onchange="recalculate()">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Project</label>
                <select name="project_id" id="project_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">Optional</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ old('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Payment Date <span class="text-red-500">*</span></label>
                <input type="date" name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Bonus</label>
                <input type="number" step="0.01" min="0" name="bonus" id="bonus" value="{{ old('bonus', 0) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg" onchange="recalculate()">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Deduction</label>
                <input type="number" step="0.01" min="0" name="deduction" id="deduction" value="{{ old('deduction', 0) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg" onchange="recalculate()">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                <input type="text" name="payment_method" value="{{ old('payment_method') }}" placeholder="Cash / Bank / ..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Paid Amount</label>
                <input type="number" step="0.01" min="0" name="paid_amount" id="paid_amount" value="{{ old('paid_amount') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Auto = net">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                <textarea name="notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg">{{ old('notes') }}</textarea>
            </div>

            <div class="md:col-span-2">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="create_expense" value="1" checked class="rounded border-gray-300">
                    <span class="text-sm text-gray-700">Create expense entry (company ledger)</span>
                </label>
            </div>
        </div>

        <div id="calcBox" class="mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200 hidden">
            <h3 class="font-semibold text-gray-900 mb-2">Calculation from attendance</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm" id="calcDetails"></div>
            <p class="mt-3 text-lg font-bold text-indigo-700" id="calcNet"></p>
            <p class="text-xs text-gray-500 mt-1" id="calcHint"></p>
        </div>

        <div class="mt-6 flex gap-3">
            <button type="button" onclick="recalculate()" class="px-4 py-2 border border-indigo-300 text-indigo-700 rounded-lg hover:bg-indigo-50">Calculate</button>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg">Save Payment</button>
        </div>
    </form>
</div>

<script>
async function recalculate() {
    const workerId = document.getElementById('worker_id').value;
    const start = document.getElementById('period_start').value;
    const end = document.getElementById('period_end').value;
    if (!workerId || !start || !end) return;

    const opt = document.getElementById('worker_id').selectedOptions[0];
    if (opt && opt.dataset.project && !document.getElementById('project_id').value) {
        document.getElementById('project_id').value = opt.dataset.project;
    }

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content
        || document.querySelector('input[name="_token"]')?.value;

    const body = new FormData();
    body.append('worker_id', workerId);
    body.append('period_start', start);
    body.append('period_end', end);
    body.append('bonus', document.getElementById('bonus').value || 0);
    body.append('deduction', document.getElementById('deduction').value || 0);
    body.append('_token', csrf);

    try {
        const res = await fetch('{{ route('admin.worker-payments.calculate') }}', {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body
        });
        const data = await res.json();
        if (!data.success) return;

        const c = data.calculation;
        document.getElementById('calcBox').classList.remove('hidden');
        document.getElementById('calcDetails').innerHTML = `
            <div>Wage: <strong>${c.wage_type}</strong></div>
            <div>Present: <strong>${c.present_days}</strong></div>
            <div>Half days: <strong>${c.half_days}</strong></div>
            <div>Absent: <strong>${c.absent_days}</strong></div>
            <div>Leave: <strong>${c.leave_days}</strong></div>
            <div>Day credits: <strong>${c.day_credits}</strong></div>
            <div>OT hours: <strong>${c.overtime_hours}</strong></div>
            <div>Base: <strong>Rs. ${Number(c.calculated_amount).toLocaleString(undefined,{minimumFractionDigits:2})}</strong></div>
            <div>OT amt: <strong>Rs. ${Number(c.overtime_amount).toLocaleString(undefined,{minimumFractionDigits:2})}</strong></div>
        `;
        document.getElementById('calcNet').textContent = 'Net payable: Rs. ' + Number(c.net_amount).toLocaleString(undefined,{minimumFractionDigits:2});
        document.getElementById('calcHint').textContent = c.wage_type === 'daily'
            ? 'Daily: (present + half×0.5) × daily wage + overtime'
            : 'Monthly: (monthly salary ÷ period days) × day credits + overtime';

        const paid = document.getElementById('paid_amount');
        if (!paid.value) paid.value = c.net_amount;
    } catch (e) {
        console.error(e);
    }
}
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('worker_id').value) recalculate();
});
</script>
@endsection
