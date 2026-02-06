@extends('admin.layout')

@section('title', 'Income Report')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-3xl font-bold text-gray-900">Income Report</h1>
    <a href="{{ route('admin.reports.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition duration-200">
        Back to Reports
    </a>
</div>

<!-- Date Filter -->
<div class="bg-white shadow-lg rounded-lg p-6 mb-6">
    <form method="GET" action="{{ route('admin.reports.income') }}" id="incomeReportFilterForm" class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
            <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
            <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label for="project_id" class="block text-sm font-medium text-gray-700 mb-2">Project</label>
            <select name="project_id" id="project_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Projects</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}" {{ (int)$projectId === $project->id ? 'selected' : '' }}>
                        {{ $project->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
            <select name="category_id" id="category_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ $categoryId == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end">
            <button type="button" onclick="resetIncomeFilters()" class="w-full bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200">
                Reset
            </button>
        </div>
    </form>
</div>

<!-- Summary Card -->
<div class="bg-white shadow-lg rounded-lg p-6 mb-6" id="income-summary-card">
    <h2 class="text-2xl font-bold text-green-600">Total Income: $<span id="income-total">{{ number_format($totalIncome, 2) }}</span></h2>
    <p class="text-sm text-gray-500 mt-1">From <span id="income-date-range">{{ date('M d, Y', strtotime($startDate)) }} to {{ date('M d, Y', strtotime($endDate)) }}</span></p>
</div>

<!-- Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Income by Category Chart -->
    <div class="bg-white shadow-lg rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Income by Category</h2>
        <div style="height: 300px;">
            <canvas id="incomeCategoryChart"></canvas>
        </div>
    </div>

    <!-- Income by Source Chart -->
    <div class="bg-white shadow-lg rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Top Income Sources</h2>
        <div style="height: 300px;">
            <canvas id="incomeSourceChart"></canvas>
        </div>
    </div>
</div>

<!-- Income Records Table -->
<div class="bg-white shadow-lg rounded-lg overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <h2 class="text-xl font-semibold text-gray-900">Income Records</h2>
        <span class="text-sm text-gray-500" id="income-record-count">{{ $incomes->count() }} record(s)</span>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Source</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200" id="income-tbody">
            @forelse($incomes as $income)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $income->date->format('M d, Y') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $income->source }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $income->category->name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ Str::limit($income->description, 40) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-green-600">${{ number_format($income->amount, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No income records found</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot class="bg-gray-50 border-t-2 border-gray-200" id="income-table-tfoot" style="{{ $incomes->count() > 0 ? '' : 'display:none' }}">
            <tr>
                <td colspan="4" class="px-6 py-3 text-right text-sm font-semibold text-gray-900">Total</td>
                <td class="px-6 py-3 text-right text-sm font-bold text-green-600" id="income-table-total">${{ number_format($totalIncome, 2) }}</td>
            </tr>
        </tfoot>
    </table>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let incomeCategoryChart = null;
    let incomeSourceChart = null;
    const incomeReportUrl = '{{ route("admin.reports.income") }}';

    function applyIncomeFilters() {
        var form = document.getElementById('incomeReportFilterForm');
        if (!form) return;
        var params = new URLSearchParams(new FormData(form));
        var loadingEl = document.getElementById('income-summary-card');
        if (loadingEl) loadingEl.style.opacity = '0.6';
        fetch(incomeReportUrl + '?' + params.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            document.getElementById('income-total').textContent = parseFloat(data.totalIncome).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            var startStr = new Date(data.startDate).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            var endStr = new Date(data.endDate).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            document.getElementById('income-date-range').textContent = startStr + ' to ' + endStr;
            document.getElementById('income-record-count').textContent = data.recordCount + ' record(s)';
            var tfoot = document.getElementById('income-table-tfoot');
            var totalCell = document.getElementById('income-table-total');
            if (tfoot && totalCell) {
                totalCell.textContent = '$' + parseFloat(data.totalIncome).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                tfoot.style.display = data.incomeRows.length > 0 ? '' : 'none';
            }
            var tbody = document.getElementById('income-tbody');
            if (data.incomeRows.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No income records found</td></tr>';
            } else {
                tbody.innerHTML = data.incomeRows.map(function(row) {
                    return '<tr><td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' + row.date + '</td>' +
                        '<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">' + escapeHtml(row.source) + '</td>' +
                        '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">' + escapeHtml(row.category) + '</td>' +
                        '<td class="px-6 py-4 text-sm text-gray-500">' + escapeHtml(row.description) + '</td>' +
                        '<td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-green-600">$' + row.amount + '</td></tr>';
                }).join('');
            }
            if (incomeCategoryChart) incomeCategoryChart.destroy();
            incomeCategoryChart = new Chart(document.getElementById('incomeCategoryChart').getContext('2d'), {
                type: 'pie',
                data: {
                    labels: data.incomeByCategory.map(function(c) { return c.name; }),
                    datasets: [{
                        data: data.incomeByCategory.map(function(c) { return parseFloat(c.total); }),
                        backgroundColor: ['rgba(34, 197, 94, 0.8)', 'rgba(59, 130, 246, 0.8)', 'rgba(251, 191, 36, 0.8)', 'rgba(168, 85, 247, 0.8)', 'rgba(236, 72, 153, 0.8)']
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
            if (incomeSourceChart) incomeSourceChart.destroy();
            incomeSourceChart = new Chart(document.getElementById('incomeSourceChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: data.incomeBySource.map(function(s) { return s.source; }),
                    datasets: [{ label: 'Amount', data: data.incomeBySource.map(function(s) { return parseFloat(s.total); }), backgroundColor: 'rgba(34, 197, 94, 0.8)' }]
                },
                options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y', scales: { x: { beginAtZero: true } } }
            });
            if (loadingEl) loadingEl.style.opacity = '1';
        })
        .catch(function() { if (loadingEl) loadingEl.style.opacity = '1'; });
    }

    function resetIncomeFilters() {
        var y = new Date().getFullYear();
        document.getElementById('start_date').value = y + '-01-01';
        document.getElementById('end_date').value = new Date().toISOString().slice(0, 10);
        document.getElementById('project_id').value = '';
        document.getElementById('category_id').value = '';
        applyIncomeFilters();
    }

    function escapeHtml(text) {
        if (!text) return '';
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    document.addEventListener('DOMContentLoaded', function() {
        var form = document.getElementById('incomeReportFilterForm');
        if (form) {
            form.querySelectorAll('input, select').forEach(function(el) {
                el.addEventListener('change', function() { applyIncomeFilters(); });
            });
        }
        incomeCategoryChart = new Chart(document.getElementById('incomeCategoryChart').getContext('2d'), {
            type: 'pie',
            data: {
                labels: {!! json_encode($incomeByCategory->pluck('name')) !!},
                datasets: [{
                    data: {!! json_encode($incomeByCategory->pluck('total')) !!},
                    backgroundColor: ['rgba(34, 197, 94, 0.8)', 'rgba(59, 130, 246, 0.8)', 'rgba(251, 191, 36, 0.8)', 'rgba(168, 85, 247, 0.8)', 'rgba(236, 72, 153, 0.8)']
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
        incomeSourceChart = new Chart(document.getElementById('incomeSourceChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($incomeBySource->pluck('source')) !!},
                datasets: [{ label: 'Amount', data: {!! json_encode($incomeBySource->pluck('total')) !!}, backgroundColor: 'rgba(34, 197, 94, 0.8)' }]
            },
            options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y', scales: { x: { beginAtZero: true } } }
        });
    });
</script>
@endsection

