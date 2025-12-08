@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
    <p class="mt-2 text-gray-600">Welcome to the admin panel, {{ Auth::user()->name }}!</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <!-- Stats Card 1 -->
    <div class="bg-white overflow-hidden shadow-lg rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Staff</dt>
                        <dd class="text-lg font-semibold text-gray-900">{{ $totalStaff }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Card 2 -->
    <div class="bg-white overflow-hidden shadow-lg rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Income (This Month)</dt>
                        <dd class="text-lg font-semibold text-green-600">${{ number_format($totalIncome, 2) }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Card 3 -->
    <div class="bg-white overflow-hidden shadow-lg rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Expenses (This Month)</dt>
                        <dd class="text-lg font-semibold text-red-600">${{ number_format($totalExpenses, 2) }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Card 4 -->
    <div class="bg-white overflow-hidden shadow-lg rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Net Balance (This Month)</dt>
                        <dd class="text-lg font-semibold {{ $balance >= 0 ? 'text-green-600' : 'text-red-600' }}">${{ number_format($balance, 2) }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Transactions Section -->
<div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Recent Income -->
    <div class="bg-white shadow-lg rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Recent Income</h2>
        </div>
        <div class="p-6">
            @if($recentIncomes->count() > 0)
                <div class="space-y-4">
                    @foreach($recentIncomes as $income)
                        <div class="flex justify-between items-center pb-3 border-b border-gray-100 last:border-0">
                            <div>
                                <p class="font-medium text-gray-900">{{ $income->source }}</p>
                                <p class="text-sm text-gray-500">{{ $income->category->name }} • {{ $income->date->format('M d, Y') }}</p>
                            </div>
                            <p class="font-semibold text-green-600">${{ number_format($income->amount, 2) }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-500">No recent income records</p>
            @endif
            <a href="{{ route('admin.incomes.index') }}" class="mt-4 block text-sm text-indigo-600 hover:text-indigo-900">View all income →</a>
        </div>
    </div>

    <!-- Recent Expenses -->
    <div class="bg-white shadow-lg rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Recent Expenses</h2>
        </div>
        <div class="p-6">
            @if($recentExpenses->count() > 0)
                <div class="space-y-4">
                    @foreach($recentExpenses as $expense)
                        <div class="flex justify-between items-center pb-3 border-b border-gray-100 last:border-0">
                            <div>
                                <p class="font-medium text-gray-900">{{ $expense->item_name ?? ucfirst($expense->expense_type) }}</p>
                                <p class="text-sm text-gray-500">{{ $expense->category->name }} • {{ $expense->date->format('M d, Y') }}</p>
                            </div>
                            <p class="font-semibold text-red-600">${{ number_format($expense->amount, 2) }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-500">No recent expense records</p>
            @endif
            <a href="{{ route('admin.expenses.index') }}" class="mt-4 block text-sm text-indigo-600 hover:text-indigo-900">View all expenses →</a>
        </div>
    </div>
</div>

<!-- Charts Section -->
<!-- Income vs Expenses Over Time Chart - Full Width -->
<div class="mt-8 bg-white shadow-lg rounded-lg">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Income vs Expenses (Last 12 Months)</h2>
    </div>
    <div class="p-6">
        <canvas id="incomeExpenseChart" height="300"></canvas>
    </div>
</div>

<!-- Category Breakdown Charts -->
<div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Income by Category Chart -->
    <div class="bg-white shadow-lg rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Income by Category (This Month)</h2>
        </div>
        <div class="p-6">
            @if(count($incomeByCategory) > 0)
                <canvas id="incomeCategoryChart" height="300"></canvas>
            @else
                <p class="text-sm text-gray-500 text-center py-8">No income data available for this month</p>
            @endif
        </div>
    </div>

    <!-- Expense by Category Chart -->
    <div class="bg-white shadow-lg rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Expenses by Category (This Month)</h2>
        </div>
        <div class="p-6">
            @if(count($expenseByCategory) > 0)
                <canvas id="expenseCategoryChart" height="300"></canvas>
            @else
                <p class="text-sm text-gray-500 text-center py-8">No expense data available for this month</p>
            @endif
        </div>
    </div>
</div>

<!-- Quick Actions Section -->
<div class="mt-8 bg-white shadow-lg rounded-lg">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Quick Actions</h2>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('admin.reports.index') }}" class="block p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition duration-200">
                <h3 class="font-semibold text-gray-900">View Reports</h3>
                <p class="text-sm text-gray-600 mt-1">Generate and view financial reports</p>
            </a>
            <a href="{{ route('admin.incomes.create') }}" class="block p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition duration-200">
                <h3 class="font-semibold text-gray-900">Add Income</h3>
                <p class="text-sm text-gray-600 mt-1">Record a new income source</p>
            </a>
            <a href="{{ route('admin.expenses.create') }}" class="block p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition duration-200">
                <h3 class="font-semibold text-gray-900">Add Expense</h3>
                <p class="text-sm text-gray-600 mt-1">Record a new expense</p>
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Income vs Expenses Line Chart
    const incomeExpenseCtx = document.getElementById('incomeExpenseChart');
    if (incomeExpenseCtx) {
        new Chart(incomeExpenseCtx, {
            type: 'line',
            data: {
                labels: @json($monthlyData['labels']),
                datasets: [
                    {
                        label: 'Income',
                        data: @json($monthlyData['income']),
                        borderColor: 'rgb(34, 197, 94)',
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Expenses',
                        data: @json($monthlyData['expenses']),
                        borderColor: 'rgb(239, 68, 68)',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': $' + context.parsed.y.toLocaleString('en-US', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString('en-US', {
                                    minimumFractionDigits: 0,
                                    maximumFractionDigits: 0
                                });
                            }
                        }
                    }
                }
            }
        });
    }

    // Income by Category Pie Chart
    const incomeCategoryCtx = document.getElementById('incomeCategoryChart');
    if (incomeCategoryCtx && @json(count($incomeByCategory) > 0)) {
        const incomeData = @json($incomeByCategory);
        const colors = generateColors(incomeData.length);
        
        new Chart(incomeCategoryCtx, {
            type: 'doughnut',
            data: {
                labels: incomeData.map(item => item.label),
                datasets: [{
                    data: incomeData.map(item => item.value),
                    backgroundColor: colors.background,
                    borderColor: colors.border,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return label + ': $' + value.toLocaleString('en-US', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                }) + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    // Expense by Category Pie Chart
    const expenseCategoryCtx = document.getElementById('expenseCategoryChart');
    if (expenseCategoryCtx && @json(count($expenseByCategory) > 0)) {
        const expenseData = @json($expenseByCategory);
        const colors = generateColors(expenseData.length);
        
        new Chart(expenseCategoryCtx, {
            type: 'doughnut',
            data: {
                labels: expenseData.map(item => item.label),
                datasets: [{
                    data: expenseData.map(item => item.value),
                    backgroundColor: colors.background,
                    borderColor: colors.border,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return label + ': $' + value.toLocaleString('en-US', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                }) + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    // Generate color palette for charts
    function generateColors(count) {
        const colorPalettes = [
            ['rgba(59, 130, 246, 0.8)', 'rgba(147, 51, 234, 0.8)', 'rgba(236, 72, 153, 0.8)', 'rgba(251, 146, 60, 0.8)', 'rgba(34, 197, 94, 0.8)', 'rgba(234, 179, 8, 0.8)', 'rgba(239, 68, 68, 0.8)', 'rgba(168, 85, 247, 0.8)'],
            ['rgba(59, 130, 246, 1)', 'rgba(147, 51, 234, 1)', 'rgba(236, 72, 153, 1)', 'rgba(251, 146, 60, 1)', 'rgba(34, 197, 94, 1)', 'rgba(234, 179, 8, 1)', 'rgba(239, 68, 68, 1)', 'rgba(168, 85, 247, 1)']
        ];
        
        const background = [];
        const border = [];
        
        for (let i = 0; i < count; i++) {
            const colorIndex = i % colorPalettes[0].length;
            background.push(colorPalettes[0][colorIndex]);
            border.push(colorPalettes[1][colorIndex]);
        }
        
        return { background, border };
    }
});
</script>
@endpush
@endsection

