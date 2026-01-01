@extends('admin.layout')

@section('title', 'Financial Summary Report')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-3xl font-bold text-gray-900">Financial Summary Report</h1>
    <a href="{{ route('admin.reports.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition duration-200">
        Back to Reports
    </a>
</div>

<!-- Date Filter -->
<div class="bg-white shadow-lg rounded-lg p-6 mb-6">
    <form method="GET" action="{{ route('admin.reports.financial-summary') }}" id="financialSummaryFilterForm" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
        <div>
            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
            <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" required onchange="applyFinancialFilters()"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
            <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" required onchange="applyFinancialFilters()"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label for="project_id" class="block text-sm font-medium text-gray-700 mb-2">Project</label>
            <select name="project_id" id="project_id" onchange="applyFinancialFilters()"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Projects</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}" {{ (int)$projectId === $project->id ? 'selected' : '' }}>
                        {{ $project->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="flex md:block">
            <button type="button" onclick="applyFinancialFilters()" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg transition duration-200">
                Filter
            </button>
        </div>
    </form>
</div>

<!-- Loading Indicator -->
<div id="financial-loading" class="hidden mb-6 bg-white shadow-lg rounded-lg p-8 text-center">
    <svg class="animate-spin h-8 w-8 text-indigo-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
    <p class="mt-2 text-gray-600">Loading financial data...</p>
</div>

<!-- Summary Cards -->
<div id="summary-cards" class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white shadow-lg rounded-lg p-6">
        <h3 class="text-sm font-medium text-gray-500 mb-2">Total Income</h3>
        <p class="text-3xl font-bold text-green-600" id="total-income">${{ number_format($totalIncome, 2) }}</p>
    </div>
    <div class="bg-white shadow-lg rounded-lg p-6">
        <h3 class="text-sm font-medium text-gray-500 mb-2">Total Expenses</h3>
        <p class="text-3xl font-bold text-red-600" id="total-expenses">${{ number_format($totalExpenses, 2) }}</p>
    </div>
    <div class="bg-white shadow-lg rounded-lg p-6">
        <h3 class="text-sm font-medium text-gray-500 mb-2">Net Balance</h3>
        <p class="text-3xl font-bold {{ $netBalance >= 0 ? 'text-green-600' : 'text-red-600' }}" id="net-balance">${{ number_format($netBalance, 2) }}</p>
    </div>
</div>

<!-- Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Monthly Trend Chart -->
    <div class="bg-white shadow-lg rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Monthly Trend (Last 6 Months)</h2>
        <div style="height: 250px;">
            <canvas id="monthlyTrendChart"></canvas>
        </div>
    </div>

    <!-- Income by Category Chart -->
    <div class="bg-white shadow-lg rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Income by Category</h2>
        <div style="height: 250px;">
            <canvas id="incomeCategoryChart"></canvas>
        </div>
    </div>
</div>

<!-- Expenses Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Expenses by Category Chart -->
    <div class="bg-white shadow-lg rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Expenses by Category</h2>
        <div style="height: 250px;">
            <canvas id="expenseCategoryChart"></canvas>
        </div>
    </div>

    <!-- Expenses by Type Chart -->
    <div class="bg-white shadow-lg rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Expenses by Type</h2>
        <div style="height: 250px;">
            <canvas id="expenseTypeChart"></canvas>
        </div>
    </div>
</div>

<!-- Data Tables -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Income by Category Table -->
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Income by Category</h2>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200" id="income-category-tbody">
                @forelse($incomeByCategory as $item)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-green-600">${{ number_format($item->total, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="px-6 py-4 text-center text-sm text-gray-500">No data available</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <!-- Expenses by Category Table -->
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Expenses by Category</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200" id="expense-category-tbody">
                @forelse($expensesByCategory as $item)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-red-600">${{ number_format($item->total, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="px-6 py-4 text-center text-sm text-gray-500">No data available</td>
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
    // Chart instances
    let monthlyTrendChart = null;
    let incomeCategoryChart = null;
    let expenseCategoryChart = null;
    let expenseTypeChart = null;
    let isLoadingFinancial = false;

    // Initialize charts with initial data
    function initializeCharts(data) {
        // Destroy existing charts if they exist
        if (monthlyTrendChart) monthlyTrendChart.destroy();
        if (incomeCategoryChart) incomeCategoryChart.destroy();
        if (expenseCategoryChart) expenseCategoryChart.destroy();
        if (expenseTypeChart) expenseTypeChart.destroy();

        // Monthly Trend Chart
        const monthlyTrendCtx = document.getElementById('monthlyTrendChart').getContext('2d');
        monthlyTrendChart = new Chart(monthlyTrendCtx, {
        type: 'line',
        data: {
            labels: data.monthlyTrend ? data.monthlyTrend.map(t => t.month) : {!! json_encode(array_column($monthlyTrend, 'month')) !!},
            datasets: [{
                label: 'Income',
                data: data.monthlyTrend ? data.monthlyTrend.map(t => t.income) : {!! json_encode(array_column($monthlyTrend, 'income')) !!},
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                tension: 0.4
            }, {
                label: 'Expenses',
                data: data.monthlyTrend ? data.monthlyTrend.map(t => t.expenses) : {!! json_encode(array_column($monthlyTrend, 'expenses')) !!},
                borderColor: 'rgb(239, 68, 68)',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Income by Category Chart
    const incomeCategoryCtx = document.getElementById('incomeCategoryChart').getContext('2d');
    incomeCategoryChart = new Chart(incomeCategoryCtx, {
        type: 'pie',
        data: {
            labels: data.incomeByCategory ? data.incomeByCategory.map(c => c.name) : {!! json_encode($incomeByCategory->pluck('name')) !!},
            datasets: [{
                data: data.incomeByCategory ? data.incomeByCategory.map(c => c.total) : {!! json_encode($incomeByCategory->pluck('total')) !!},
                backgroundColor: [
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(34, 197, 94, 0.8)',
                    'rgba(251, 191, 36, 0.8)',
                    'rgba(239, 68, 68, 0.8)',
                    'rgba(168, 85, 247, 0.8)',
                    'rgba(236, 72, 153, 0.8)',
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Expenses by Category Chart
    const expenseCategoryCtx = document.getElementById('expenseCategoryChart').getContext('2d');
    expenseCategoryChart = new Chart(expenseCategoryCtx, {
        type: 'pie',
        data: {
            labels: data.expensesByCategory ? data.expensesByCategory.map(c => c.name) : {!! json_encode($expensesByCategory->pluck('name')) !!},
            datasets: [{
                data: data.expensesByCategory ? data.expensesByCategory.map(c => c.total) : {!! json_encode($expensesByCategory->pluck('total')) !!},
                backgroundColor: [
                    'rgba(239, 68, 68, 0.8)',
                    'rgba(251, 191, 36, 0.8)',
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(168, 85, 247, 0.8)',
                    'rgba(236, 72, 153, 0.8)',
                    'rgba(34, 197, 94, 0.8)',
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Expenses by Type Chart
    const expenseTypeCtx = document.getElementById('expenseTypeChart').getContext('2d');
    expenseTypeChart = new Chart(expenseTypeCtx, {
        type: 'bar',
        data: {
            labels: data.expensesByType ? data.expensesByType.map(t => t.expense_type ? t.expense_type.charAt(0).toUpperCase() + t.expense_type.slice(1) : '') : {!! json_encode($expensesByType->pluck('expense_type')->map(fn($t) => ucfirst($t))) !!},
            datasets: [{
                label: 'Amount',
                data: data.expensesByType ? data.expensesByType.map(t => t.total) : {!! json_encode($expensesByType->pluck('total')) !!},
                backgroundColor: [
                    'rgba(239, 68, 68, 0.8)',
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(251, 191, 36, 0.8)',
                    'rgba(168, 85, 247, 0.8)',
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    }

    // Initialize with current data
    initializeCharts({
        monthlyTrend: {!! json_encode($monthlyTrend) !!},
        incomeByCategory: {!! json_encode($incomeByCategory) !!},
        expensesByCategory: {!! json_encode($expensesByCategory) !!},
        expensesByType: {!! json_encode($expensesByType) !!}
    });

    // Apply filters function
    function applyFinancialFilters() {
        if (isLoadingFinancial) return;
        
        isLoadingFinancial = true;
        
        const form = document.getElementById('financialSummaryFilterForm');
        const formData = new FormData(form);
        const params = new URLSearchParams();
        
        // Add form values to params
        for (const [key, value] of formData.entries()) {
            if (value) {
                params.append(key, value);
            }
        }
        
        // Show loading state
        document.getElementById('financial-loading').classList.remove('hidden');
        document.getElementById('summary-cards').style.opacity = '0.5';
        document.querySelectorAll('.grid.grid-cols-1.lg\\:grid-cols-2').forEach(el => {
            el.style.opacity = '0.5';
        });
        
        // Fetch filtered data via AJAX
        fetch(`{{ route('admin.reports.financial-summary') }}?${params.toString()}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            // Update summary cards
            document.getElementById('total-income').textContent = '$' + parseFloat(data.totalIncome).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('total-expenses').textContent = '$' + parseFloat(data.totalExpenses).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            
            const netBalance = parseFloat(data.netBalance);
            const netBalanceEl = document.getElementById('net-balance');
            netBalanceEl.textContent = '$' + netBalance.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            netBalanceEl.className = 'text-3xl font-bold ' + (netBalance >= 0 ? 'text-green-600' : 'text-red-600');
            
            // Update tables
            updateIncomeCategoryTable(data.incomeByCategory);
            updateExpenseCategoryTable(data.expensesByCategory);
            
            // Update charts
            initializeCharts(data);
            
            // Update URL
            const newURL = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
            window.history.pushState({path: newURL}, '', newURL);
            
            // Hide loading state
            document.getElementById('financial-loading').classList.add('hidden');
            document.getElementById('summary-cards').style.opacity = '1';
            document.querySelectorAll('.grid.grid-cols-1.lg\\:grid-cols-2').forEach(el => {
                el.style.opacity = '1';
            });
            
            isLoadingFinancial = false;
        })
        .catch(error => {
            console.error('Error loading financial data:', error);
            document.getElementById('financial-loading').classList.add('hidden');
            document.getElementById('summary-cards').style.opacity = '1';
            document.querySelectorAll('.grid.grid-cols-1.lg\\:grid-cols-2').forEach(el => {
                el.style.opacity = '1';
            });
            alert('Failed to load financial data. Please try again.');
            isLoadingFinancial = false;
        });
    }

    function updateIncomeCategoryTable(data) {
        const tbody = document.getElementById('income-category-tbody');
        if (!data || data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="2" class="px-6 py-4 text-center text-sm text-gray-500">No data available</td></tr>';
            return;
        }
        
        tbody.innerHTML = data.map(item => `
            <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.name}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-green-600">$${parseFloat(item.total).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
            </tr>
        `).join('');
    }

    function updateExpenseCategoryTable(data) {
        const tbody = document.getElementById('expense-category-tbody');
        if (!data || data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="2" class="px-6 py-4 text-center text-sm text-gray-500">No data available</td></tr>';
            return;
        }
        
        tbody.innerHTML = data.map(item => `
            <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.name}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-red-600">$${parseFloat(item.total).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
            </tr>
        `).join('');
    }

    // Handle browser back/forward buttons
    window.addEventListener('popstate', function(e) {
        if (e.state && e.state.path) {
            applyFinancialFilters();
        }
    });
</script>
@endsection

