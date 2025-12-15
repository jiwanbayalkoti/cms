@extends('admin.layout')

@section('title', 'Reports')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Reports</h1>
    <p class="mt-2 text-gray-600">Generate and view financial reports</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
    <!-- Financial Summary Report -->
    <a href="{{ route('admin.reports.financial-summary') }}" class="bg-white shadow-lg rounded-lg p-6 hover:shadow-xl transition duration-200 w-full max-w-full overflow-hidden break-words text-left">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="h-12 w-12 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-semibold text-gray-900">Financial Summary</h3>
                <p class="text-sm text-gray-500 mt-1">Income vs Expenses overview</p>
            </div>
        </div>
    </a>

    <!-- Income Report -->
    <a href="{{ route('admin.reports.income') }}" class="bg-white shadow-lg rounded-lg p-6 hover:shadow-xl transition duration-200 w-full max-w-full overflow-hidden break-words text-left">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="h-12 w-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-semibold text-gray-900">Income Report</h3>
                <p class="text-sm text-gray-500 mt-1">Detailed income analysis</p>
            </div>
        </div>
    </a>

    <!-- Expense Report -->
    <a href="{{ route('admin.reports.expense') }}" class="bg-white shadow-lg rounded-lg p-6 hover:shadow-xl transition duration-200 w-full max-w-full overflow-hidden break-words text-left">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="h-12 w-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-semibold text-gray-900">Expense Report</h3>
                <p class="text-sm text-gray-500 mt-1">Detailed expense analysis</p>
            </div>
        </div>
    </a>

    <!-- Project Material Report -->
    <a href="{{ route('admin.reports.project-materials') }}" class="bg-white shadow-lg rounded-lg p-6 hover:shadow-xl transition duration-200 w-full max-w-full overflow-hidden break-words text-left">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="h-12 w-12 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a2 2 0 012-2h2a2 2 0 012 2v2m2 4H7a2 2 0 01-2-2V7a2 2 0 012-2h3l2-2h3l2 2h3a2 2 0 012 2v12a2 2 0 01-2 2z" />
                </svg>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-semibold text-gray-900">Project Materials</h3>
                <p class="text-sm text-gray-500 mt-1">Consumption & supplier insights</p>
            </div>
        </div>
    </a>

    <!-- Staff Payment Report -->
    <a href="{{ route('admin.reports.staff-payment') }}" class="bg-white shadow-lg rounded-lg p-6 hover:shadow-xl transition duration-200 w-full max-w-full overflow-hidden break-words text-left">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="h-12 w-12 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-semibold text-gray-900">Staff Payments</h3>
                <p class="text-sm text-gray-500 mt-1">Salary & advance payments</p>
            </div>
        </div>
    </a>

    <!-- Balance Sheet Report -->
    <a href="{{ route('admin.reports.balance-sheet') }}" class="bg-white shadow-lg rounded-lg p-6 hover:shadow-xl transition duration-200 w-full max-w-full overflow-hidden break-words text-left">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="h-12 w-12 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-semibold text-gray-900">Balance Sheet</h3>
                <p class="text-sm text-gray-500 mt-1">Debit & Credit report</p>
            </div>
        </div>
    </a>
</div>
@endsection

