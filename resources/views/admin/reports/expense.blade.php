@extends('admin.layout')

@section('title', 'Expense Report')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-3xl font-bold text-gray-900">Expense Report</h1>
    <a href="{{ route('admin.reports.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition duration-200">
        Back to Reports
    </a>
</div>

<!-- Date Filter -->
<div class="bg-white shadow-lg rounded-lg p-6 mb-6">
    <form method="GET" action="{{ route('admin.reports.expense') }}" id="expenseReportFilterForm" class="grid grid-cols-1 md:grid-cols-6 gap-4">
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
        <div>
            <label for="expense_type" class="block text-sm font-medium text-gray-700 mb-2">Expense Type</label>
            <select name="expense_type" id="expense_type"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Types</option>
                <option value="purchase" {{ $expenseType == 'purchase' ? 'selected' : '' }}>Purchase</option>
                <option value="salary" {{ $expenseType == 'salary' ? 'selected' : '' }}>Salary</option>
                <option value="advance" {{ $expenseType == 'advance' ? 'selected' : '' }}>Advance</option>
                <option value="rent" {{ $expenseType == 'rent' ? 'selected' : '' }}>Rent</option>
            </select>
        </div>
        <div class="flex items-end">
            <button type="button" onclick="resetExpenseFilters()" class="w-full bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200">
                Reset
            </button>
        </div>
    </form>
</div>

<!-- Summary Card -->
<div class="bg-white shadow-lg rounded-lg p-6 mb-6" id="expense-summary-card">
    <h2 class="text-2xl font-bold text-red-600">Total Expenses: $<span id="expense-total">{{ number_format($totalExpenses, 2) }}</span></h2>
    <p class="text-sm text-gray-500 mt-1">From <span id="expense-date-range">{{ date('M d, Y', strtotime($startDate)) }} to {{ date('M d, Y', strtotime($endDate)) }}</span></p>
</div>

<!-- Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Expenses by Category Chart -->
    <div class="bg-white shadow-lg rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Expenses by Category</h2>
        <div style="height: 300px;">
            <canvas id="expenseCategoryChart"></canvas>
        </div>
    </div>

    <!-- Expenses by Type Chart -->
    <div class="bg-white shadow-lg rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Expenses by Type</h2>
        <div style="height: 300px;">
            <canvas id="expenseTypeChart"></canvas>
        </div>
    </div>
</div>

<!-- Expense Records Table -->
<div class="bg-white shadow-lg rounded-lg overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <h2 class="text-xl font-semibold text-gray-900">Expense Records</h2>
        <span class="text-sm text-gray-500" id="expense-record-count">{{ $expenses->count() }} record(s)</span>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item/Description</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Staff</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200" id="expense-tbody">
            @forelse($expenses as $expense)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $expense->date->format('M d, Y') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $typeClass = $expense->expenseType && in_array(strtolower($expense->expenseType->code ?? ''), ['salary']) ? 'bg-blue-100 text-blue-800' :
                                (($expense->expenseType && in_array(strtolower($expense->expenseType->code ?? ''), ['advance'])) ? 'bg-yellow-100 text-yellow-800' :
                                (($expense->expenseType && in_array(strtolower($expense->expenseType->code ?? ''), ['rent'])) ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800'));
                        @endphp
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $typeClass }}">
                            {{ $expense->expenseType ? $expense->expenseType->name : 'Other' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $expense->item_name ?? Str::limit($expense->description, 30) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $expense->category->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $expense->staff ? $expense->staff->name : 'N/A' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-red-600">${{ number_format($expense->amount, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No expense records found</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot class="bg-gray-50 border-t-2 border-gray-200" id="expense-table-tfoot" style="{{ $expenses->count() > 0 ? '' : 'display:none' }}">
            <tr>
                <td colspan="5" class="px-6 py-3 text-right text-sm font-semibold text-gray-900">Total</td>
                <td class="px-6 py-3 text-right text-sm font-bold text-red-600" id="expense-table-total">${{ number_format($totalExpenses, 2) }}</td>
            </tr>
        </tfoot>
    </table>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    var expenseCategoryChart = null;
    var expenseTypeChart = null;
    var expenseReportUrl = '{{ route("admin.reports.expense") }}';

    function applyExpenseFilters() {
        var form = document.getElementById('expenseReportFilterForm');
        if (!form) return;
        var params = new URLSearchParams(new FormData(form));
        var loadingEl = document.getElementById('expense-summary-card');
        if (loadingEl) loadingEl.style.opacity = '0.6';
        fetch(expenseReportUrl + '?' + params.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            document.getElementById('expense-total').textContent = parseFloat(data.totalExpenses).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            var startStr = new Date(data.startDate).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            var endStr = new Date(data.endDate).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            document.getElementById('expense-date-range').textContent = startStr + ' to ' + endStr;
            document.getElementById('expense-record-count').textContent = data.recordCount + ' record(s)';
            var tfoot = document.getElementById('expense-table-tfoot');
            var totalCell = document.getElementById('expense-table-total');
            if (tfoot && totalCell) {
                totalCell.textContent = '$' + parseFloat(data.totalExpenses).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                tfoot.style.display = data.expenseRows.length > 0 ? '' : 'none';
            }
            var tbody = document.getElementById('expense-tbody');
            if (data.expenseRows.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No expense records found</td></tr>';
            } else {
                tbody.innerHTML = data.expenseRows.map(function(row) {
                    return '<tr><td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' + row.date + '</td>' +
                        '<td class="px-6 py-4 whitespace-nowrap"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ' + row.type_class + '">' + escapeHtml(row.expense_type) + '</span></td>' +
                        '<td class="px-6 py-4 text-sm text-gray-900">' + escapeHtml(row.item) + '</td>' +
                        '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">' + escapeHtml(row.category) + '</td>' +
                        '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">' + escapeHtml(row.staff) + '</td>' +
                        '<td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-red-600">$' + row.amount + '</td></tr>';
                }).join('');
            }
            if (expenseCategoryChart) expenseCategoryChart.destroy();
            expenseCategoryChart = new Chart(document.getElementById('expenseCategoryChart').getContext('2d'), {
                type: 'pie',
                data: {
                    labels: data.expensesByCategory.map(function(c) { return c.name; }),
                    datasets: [{
                        data: data.expensesByCategory.map(function(c) { return parseFloat(c.total); }),
                        backgroundColor: ['rgba(239, 68, 68, 0.8)', 'rgba(251, 191, 36, 0.8)', 'rgba(59, 130, 246, 0.8)', 'rgba(168, 85, 247, 0.8)', 'rgba(236, 72, 153, 0.8)']
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
            if (expenseTypeChart) expenseTypeChart.destroy();
            expenseTypeChart = new Chart(document.getElementById('expenseTypeChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: data.expensesByType.map(function(t) { return t.expense_type; }),
                    datasets: [{
                        label: 'Amount',
                        data: data.expensesByType.map(function(t) { return parseFloat(t.total); }),
                        backgroundColor: ['rgba(239, 68, 68, 0.8)', 'rgba(59, 130, 246, 0.8)', 'rgba(251, 191, 36, 0.8)', 'rgba(168, 85, 247, 0.8)']
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
            });
            if (loadingEl) loadingEl.style.opacity = '1';
        })
        .catch(function() { if (loadingEl) loadingEl.style.opacity = '1'; });
    }

    function resetExpenseFilters() {
        var y = new Date().getFullYear();
        document.getElementById('start_date').value = y + '-01-01';
        document.getElementById('end_date').value = new Date().toISOString().slice(0, 10);
        document.getElementById('project_id').value = '';
        document.getElementById('category_id').value = '';
        document.getElementById('expense_type').value = '';
        applyExpenseFilters();
    }

    function escapeHtml(text) {
        if (!text) return '';
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    document.addEventListener('DOMContentLoaded', function() {
        var form = document.getElementById('expenseReportFilterForm');
        if (form) {
            form.querySelectorAll('input, select').forEach(function(el) {
                el.addEventListener('change', function() { applyExpenseFilters(); });
            });
        }
        expenseCategoryChart = new Chart(document.getElementById('expenseCategoryChart').getContext('2d'), {
            type: 'pie',
            data: {
                labels: {!! json_encode($expensesByCategory->pluck('name')) !!},
                datasets: [{
                    data: {!! json_encode($expensesByCategory->pluck('total')) !!},
                    backgroundColor: ['rgba(239, 68, 68, 0.8)', 'rgba(251, 191, 36, 0.8)', 'rgba(59, 130, 246, 0.8)', 'rgba(168, 85, 247, 0.8)', 'rgba(236, 72, 153, 0.8)']
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
        expenseTypeChart = new Chart(document.getElementById('expenseTypeChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($expensesByType->pluck('expense_type')->map(fn($t) => ucfirst($t))) !!},
                datasets: [{
                    label: 'Amount',
                    data: {!! json_encode($expensesByType->pluck('total')) !!},
                    backgroundColor: ['rgba(239, 68, 68, 0.8)', 'rgba(59, 130, 246, 0.8)', 'rgba(251, 191, 36, 0.8)', 'rgba(168, 85, 247, 0.8)']
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
        });
    });
</script>
@endsection

