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
@endsection

