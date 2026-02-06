@extends('admin.layout')

@section('title', 'Staff Payment Report')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-3xl font-bold text-gray-900">Staff Payment Report</h1>
    <a href="{{ route('admin.reports.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition duration-200">
        Back to Reports
    </a>
</div>

<!-- Date Filter -->
<div class="bg-white shadow-lg rounded-lg p-6 mb-6">
    <form id="staffPaymentFilterForm" method="GET" action="{{ route('admin.reports.staff-payment') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
            <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" onchange="applyStaffPaymentFilters()">
        </div>
        <div>
            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
            <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" onchange="applyStaffPaymentFilters()">
        </div>
        <div>
            <label for="project_id" class="block text-sm font-medium text-gray-700 mb-2">Project</label>
            <select name="project_id" id="project_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" onchange="applyStaffPaymentFilters()">
                <option value="">All Projects</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}" {{ (int)$projectId === $project->id ? 'selected' : '' }}>
                        {{ $project->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="staff_id" class="block text-sm font-medium text-gray-700 mb-2">Staff Member</label>
            <select name="staff_id" id="staff_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" onchange="applyStaffPaymentFilters()">
                <option value="">All Staff</option>
                @foreach($staff as $member)
                    <option value="{{ $member->id }}" {{ $staffId == $member->id ? 'selected' : '' }}>
                        {{ $member->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end">
            <button type="button" onclick="resetStaffPaymentFilters()" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-gray-700 hover:bg-gray-50 transition duration-200">Reset</button>
        </div>
    </form>
</div>

<div id="staff-payment-report-content">
<!-- Summary Card -->
<div class="bg-white shadow-lg rounded-lg p-6 mb-6">
    <h2 class="text-2xl font-bold text-red-600" id="sp-total-text">Total Payments: ${{ number_format($totalPayments, 2) }}</h2>
    <p class="text-sm text-gray-500 mt-1" id="sp-date-range">From {{ date('M d, Y', strtotime($startDate)) }} to {{ date('M d, Y', strtotime($endDate)) }}</p>
</div>

<!-- Total by Staff Chart -->
<div class="bg-white shadow-lg rounded-lg p-6 mb-6">
    <h2 class="text-xl font-semibold text-gray-900 mb-4">Total Payments by Staff</h2>
    <div style="height: 300px;">
        <canvas id="staffPaymentChart"></canvas>
    </div>
</div>

<!-- Total by Staff Table -->
<div class="bg-white shadow-lg rounded-lg overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Summary by Staff</h2>
    </div>
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Staff Member</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Payments</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200" id="sp-total-by-staff-tbody">
            @forelse($totalByStaff as $item)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $item->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-red-600">${{ number_format($item->total, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="px-6 py-4 text-center text-sm text-gray-500">No payment records found</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Payment Records Table -->
<div class="bg-white shadow-lg rounded-lg overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <h2 class="text-xl font-semibold text-gray-900">Payment Records</h2>
        <span class="text-sm text-gray-500" id="sp-record-count">{{ $payments->count() }} record(s)</span>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Staff Member</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200" id="sp-payments-tbody">
            @forelse($payments as $payment)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $payment->date->format('M d, Y') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ ($payment->expenseType && strtolower($payment->expenseType->name) === 'salary') ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ $payment->expenseType ? ucfirst($payment->expenseType->name) : 'Salary' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        @if($payment->staff)
                            <a href="{{ route('admin.staff.show', $payment->staff) }}" class="text-indigo-600 hover:text-indigo-900">{{ $payment->staff->name }}</a>
                        @else
                            N/A
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ Str::limit($payment->description, 40) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-red-600">${{ number_format($payment->amount, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No payment records found</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function() {
    var baseUrl = '{{ route("admin.reports.staff-payment") }}';
    var defaultStart = '{{ date("Y-01-01") }}';
    var defaultEnd = '{{ date("Y-m-d") }}';
    var staffPaymentChartInstance = null;

    function numFmt(n) {
        if (n == null || isNaN(n)) return '0.00';
        return Number(n).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function dateFmt(str) {
        if (!str) return '-';
        var d = new Date(str);
        if (isNaN(d.getTime())) return '-';
        var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        return months[d.getMonth()] + ' ' + ('0' + d.getDate()).slice(-2) + ', ' + d.getFullYear();
    }

    window.applyStaffPaymentFilters = function() {
        var form = document.getElementById('staffPaymentFilterForm');
        var q = new URLSearchParams(new FormData(form));
        var url = baseUrl + (q.toString() ? '?' + q.toString() : '');
        var content = document.getElementById('staff-payment-report-content');
        if (content) content.classList.add('opacity-60', 'pointer-events-none');

        fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                updateStaffPaymentReport(data);
            })
            .catch(function(err) {
                console.error(err);
                if (content) content.classList.remove('opacity-60', 'pointer-events-none');
            })
            .then(function() {
                if (content) content.classList.remove('opacity-60', 'pointer-events-none');
            });
    };

    window.resetStaffPaymentFilters = function() {
        document.getElementById('start_date').value = defaultStart;
        document.getElementById('end_date').value = defaultEnd;
        document.getElementById('project_id').value = '';
        document.getElementById('staff_id').value = '';
        applyStaffPaymentFilters();
    };

    function updateStaffPaymentReport(data) {
        var total = data.totalPayments != null ? data.totalPayments : 0;
        document.getElementById('sp-total-text').textContent = 'Total Payments: $' + numFmt(total);
        document.getElementById('sp-date-range').textContent = 'From ' + dateFmt(data.start_date) + ' to ' + dateFmt(data.end_date);

        var totalByStaff = data.totalByStaff || [];
        var tbody = document.getElementById('sp-total-by-staff-tbody');
        if (tbody) {
            var rows = totalByStaff.map(function(item) {
                return '<tr><td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">' + (item.name || '') + '</td><td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-red-600">$' + numFmt(item.total) + '</td></tr>';
            });
            tbody.innerHTML = rows.length ? rows.join('') : '<tr><td colspan="2" class="px-6 py-4 text-center text-sm text-gray-500">No payment records found</td></tr>';
        }

        var staffShowBase = '{{ url("admin/staff") }}';
        var payments = data.payments || [];
        var paymentsTbody = document.getElementById('sp-payments-tbody');
        if (paymentsTbody) {
            var rows = payments.map(function(p) {
                var typeClass = (p.expense_type === 'salary') ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800';
                var staffCell = p.staff_id ? '<a href="' + staffShowBase + '/' + p.staff_id + '" class="text-indigo-600 hover:text-indigo-900">' + (p.staff_name || 'N/A') + '</a>' : (p.staff_name || 'N/A');
                return '<tr><td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' + (p.date_formatted || '-') + '</td><td class="px-6 py-4 whitespace-nowrap"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ' + typeClass + '">' + (p.expense_type_label || 'Salary') + '</span></td><td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">' + staffCell + '</td><td class="px-6 py-4 text-sm text-gray-500">' + (p.description || '') + '</td><td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-red-600">$' + numFmt(p.amount) + '</td></tr>';
            });
            paymentsTbody.innerHTML = rows.length ? rows.join('') : '<tr><td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No payment records found</td></tr>';
        }
        document.getElementById('sp-record-count').textContent = payments.length + ' record(s)';

        var labels = totalByStaff.map(function(x) { return x.name; });
        var values = totalByStaff.map(function(x) { return Number(x.total); });
        var canvas = document.getElementById('staffPaymentChart');
        if (canvas && typeof Chart !== 'undefined') {
            if (staffPaymentChartInstance) staffPaymentChartInstance.destroy();
            staffPaymentChartInstance = new Chart(canvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{ label: 'Total Payments', data: values, backgroundColor: 'rgba(239, 68, 68, 0.8)' }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true } }
                }
            });
        }
    }

    // Initial chart on page load
    (function() {
        var labels = {!! json_encode($totalByStaff->pluck('name')) !!};
        var values = {!! json_encode($totalByStaff->pluck('total')) !!};
        var canvas = document.getElementById('staffPaymentChart');
        if (canvas && typeof Chart !== 'undefined') {
            staffPaymentChartInstance = new Chart(canvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{ label: 'Total Payments', data: values, backgroundColor: 'rgba(239, 68, 68, 0.8)' }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true } }
                }
            });
        }
    })();
})();
</script>
@endsection

