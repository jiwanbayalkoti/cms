@extends('admin.layout')

@section('title', 'Balance Sheet Report')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-3xl font-bold text-gray-900">Balance Sheet Report</h1>
    <a href="{{ route('admin.reports.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition duration-200">
        Back to Reports
    </a>
</div>

<!-- Date Filter -->
<div class="bg-white shadow-lg rounded-lg p-6 mb-6">
    <form method="GET" action="{{ route('admin.reports.balance-sheet') }}" class="flex items-end gap-4">
        <div class="flex-1">
            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
            <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div class="flex-1">
            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
            <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg transition duration-200">
            Filter
        </button>
    </form>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <div class="bg-white shadow-lg rounded-lg p-6">
        <h3 class="text-sm font-medium text-gray-500 mb-2">Total Credits (Income)</h3>
        <p class="text-3xl font-bold text-green-600">${{ number_format($totalCredits, 2) }}</p>
        <p class="text-xs text-gray-500 mt-2">Period: {{ date('M d, Y', strtotime($startDate)) }} to {{ date('M d, Y', strtotime($endDate)) }}</p>
    </div>
    <div class="bg-white shadow-lg rounded-lg p-6">
        <h3 class="text-sm font-medium text-gray-500 mb-2">Total Debits (Expenses)</h3>
        <p class="text-3xl font-bold text-red-600">${{ number_format($totalDebits, 2) }}</p>
        <p class="text-xs text-gray-500 mt-2">Period: {{ date('M d, Y', strtotime($startDate)) }} to {{ date('M d, Y', strtotime($endDate)) }}</p>
    </div>
</div>

<!-- Net Balance Card -->
<div class="bg-white shadow-lg rounded-lg p-6 mb-6">
    <h3 class="text-sm font-medium text-gray-500 mb-2">Net Profit / Loss</h3>
    <p class="text-4xl font-bold {{ $netProfit >= 0 ? 'text-green-600' : 'text-red-600' }}">
        {{ $netProfit >= 0 ? '+' : '' }}${{ number_format($netProfit, 2) }}
    </p>
    <p class="text-sm text-gray-600 mt-2">
        @if($netProfit > 0)
            <span class="text-green-600">✓ Operating at a profit</span>
        @elseif($netProfit < 0)
            <span class="text-red-600">⚠ Operating at a loss</span>
        @else
            <span class="text-gray-600">⊘ Break-even</span>
        @endif
    </p>
</div>

<!-- Balance Sheet Table -->
<div class="bg-white shadow-lg rounded-lg overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-indigo-500 to-purple-600">
        <h2 class="text-2xl font-bold text-white">Balance Sheet</h2>
        <p class="text-indigo-100 mt-1">Period: {{ date('M d, Y', strtotime($startDate)) }} - {{ date('M d, Y', strtotime($endDate)) }}</p>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-4 text-left text-sm font-bold text-gray-900 uppercase w-1/2">Debit (Expenses)</th>
                    <th class="px-6 py-4 text-left text-sm font-bold text-gray-900 uppercase w-1/2">Credit (Income)</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <tr class="bg-red-50">
                    <td class="px-6 py-4">
                        <div class="text-lg font-bold text-red-700">Total Expenses: ${{ number_format($totalDebits, 2) }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-lg font-bold text-green-700">Total Income: ${{ number_format($totalCredits, 2) }}</div>
                    </td>
                </tr>
                <tr>
                    <td class="px-6 py-4 align-top">
                        <div class="font-semibold text-gray-900 mb-2">Expense Breakdown:</div>
                        <div class="space-y-1">
                            @forelse($debitByCategory as $item)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">{{ $item->name }}:</span>
                                    <span class="font-medium text-red-600">${{ number_format($item->total, 2) }}</span>
                                </div>
                            @empty
                                <div class="text-sm text-gray-500">No expenses recorded</div>
                            @endforelse
                        </div>
                    </td>
                    <td class="px-6 py-4 align-top">
                        <div class="font-semibold text-gray-900 mb-2">Income Breakdown:</div>
                        <div class="space-y-1">
                            @forelse($creditByCategory as $item)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">{{ $item->name }}:</span>
                                    <span class="font-medium text-green-600">${{ number_format($item->total, 2) }}</span>
                                </div>
                            @empty
                                <div class="text-sm text-gray-500">No income recorded</div>
                            @endforelse
                        </div>
                    </td>
                </tr>
                <tr class="bg-gray-50 border-t-2 border-gray-300">
                    <td class="px-6 py-4">
                        <div class="text-lg font-bold text-gray-900">Net: ${{ number_format($totalDebits, 2) }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-lg font-bold text-gray-900">Net: ${{ number_format($totalCredits, 2) }}</div>
                    </td>
                </tr>
                <tr class="bg-indigo-50">
                    <td colspan="2" class="px-6 py-4 text-center">
                        <div class="text-2xl font-bold {{ $netProfit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            Net Profit/Loss: {{ $netProfit >= 0 ? '+' : '' }}${{ number_format($netProfit, 2) }}
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Detailed Transactions -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Debit Transactions -->
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-red-50">
            <h2 class="text-xl font-semibold text-red-900">Debit Transactions (Expenses)</h2>
            <p class="text-sm text-red-600 mt-1">{{ $debitRecords->count() }} transaction(s)</p>
        </div>
        <div class="max-h-96 overflow-y-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-red-50 sticky top-0">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-red-700 uppercase">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-red-700 uppercase">Description</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-red-700 uppercase">Amount</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($debitRecords as $debit)
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-900">{{ $debit->date->format('M d, Y') }}</td>
                            <td class="px-4 py-3 text-xs text-gray-900">
                                <div class="font-medium">{{ $debit->item_name ?? ucfirst($debit->expense_type) }}</div>
                                <div class="text-xs text-gray-500">{{ $debit->category->name }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-xs text-right font-semibold text-red-600">${{ number_format($debit->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-4 text-center text-xs text-gray-500">No debit transactions</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-red-50 sticky bottom-0 border-t-2 border-red-200">
                    <tr>
                        <td colspan="2" class="px-4 py-3 text-sm font-bold text-red-900">Total Debits:</td>
                        <td class="px-4 py-3 text-right text-sm font-bold text-red-900">${{ number_format($totalDebits, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Credit Transactions -->
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-green-50">
            <h2 class="text-xl font-semibold text-green-900">Credit Transactions (Income)</h2>
            <p class="text-sm text-green-600 mt-1">{{ $creditRecords->count() }} transaction(s)</p>
        </div>
        <div class="max-h-96 overflow-y-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-green-50 sticky top-0">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-green-700 uppercase">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-green-700 uppercase">Description</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-green-700 uppercase">Amount</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($creditRecords as $credit)
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-900">{{ $credit->date->format('M d, Y') }}</td>
                            <td class="px-4 py-3 text-xs text-gray-900">
                                <div class="font-medium">{{ $credit->source }}</div>
                                <div class="text-xs text-gray-500">{{ $credit->category->name }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-xs text-right font-semibold text-green-600">${{ number_format($credit->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-4 text-center text-xs text-gray-500">No credit transactions</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-green-50 sticky bottom-0 border-t-2 border-green-200">
                    <tr>
                        <td colspan="2" class="px-4 py-3 text-sm font-bold text-green-900">Total Credits:</td>
                        <td class="px-4 py-3 text-right text-sm font-bold text-green-900">${{ number_format($totalCredits, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection

