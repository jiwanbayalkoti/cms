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
    <form id="balanceSheetFilterForm" method="GET" action="{{ route('admin.reports.balance-sheet') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 md:gap-4 items-end">
        <div>
            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
            <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" onchange="applyBalanceSheetFilters()">
        </div>
        <div>
            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
            <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" onchange="applyBalanceSheetFilters()">
        </div>
        <div>
            <label for="project_id" class="block text-sm font-medium text-gray-700 mb-2">Project</label>
            <select name="project_id" id="project_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" onchange="applyBalanceSheetFilters()">
                <option value="">All Projects</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}" {{ (int)$projectId === $project->id ? 'selected' : '' }}>
                        {{ $project->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="flex md:block">
            <button type="button" onclick="resetBalanceSheetFilters()" class="w-full border border-gray-300 rounded-lg px-6 py-2 text-gray-700 hover:bg-gray-50 transition duration-200">Reset</button>
        </div>
    </form>
</div>

<div id="balance-sheet-report-content">
<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 mb-6">
    <div class="bg-white shadow-lg rounded-lg p-6 w-full max-w-full overflow-hidden break-words text-left">
        <h3 class="text-sm font-medium text-gray-500 mb-2">Total Credits (Income)</h3>
        <p class="text-3xl font-bold text-green-600" id="bs-total-credits">${{ number_format($totalCredits, 2) }}</p>
        <p class="text-xs text-gray-500 mt-2" id="bs-period-credits">Period: {{ date('M d, Y', strtotime($startDate)) }} to {{ date('M d, Y', strtotime($endDate)) }}</p>
    </div>
    <div class="bg-white shadow-lg rounded-lg p-6 w-full max-w-full overflow-hidden break-words text-left">
        <h3 class="text-sm font-medium text-gray-500 mb-2">Total Debits (Expenses)</h3>
        <p class="text-3xl font-bold text-red-600" id="bs-total-debits">${{ number_format($totalDebits, 2) }}</p>
        <p class="text-xs text-gray-500 mt-2" id="bs-period-debits">Period: {{ date('M d, Y', strtotime($startDate)) }} to {{ date('M d, Y', strtotime($endDate)) }}</p>
    </div>
</div>

<!-- Net Balance Card -->
<div class="bg-white shadow-lg rounded-lg p-6 mb-6 w-full max-w-full overflow-hidden break-words text-left">
    <h3 class="text-sm font-medium text-gray-500 mb-2">Net Profit / Loss</h3>
    <p class="text-4xl font-bold" id="bs-net-profit">{{ $netProfit >= 0 ? '+' : '' }}${{ number_format($netProfit, 2) }}</p>
    <p class="text-sm text-gray-600 mt-2" id="bs-net-message">
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
        <p class="text-indigo-100 mt-1" id="bs-table-period">Period: {{ date('M d, Y', strtotime($startDate)) }} - {{ date('M d, Y', strtotime($endDate)) }}</p>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-4 text-left text-sm font-bold text-gray-900 uppercase w-1/2">Debit (Expenses)</th>
                    <th class="px-6 py-4 text-left text-sm font-bold text-gray-900 uppercase w-1/2">Credit (Income)</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200" id="bs-main-tbody">
                <tr class="bg-red-50">
                    <td class="px-6 py-4">
                        <div class="text-lg font-bold text-red-700" id="bs-row-total-expenses">Total Expenses: ${{ number_format($totalDebits, 2) }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-lg font-bold text-green-700" id="bs-row-total-income">Total Income: ${{ number_format($totalCredits, 2) }}</div>
                    </td>
                </tr>
                <tr>
                    <td class="px-6 py-4 align-top">
                        <div class="font-semibold text-gray-900 mb-2">Expense Breakdown:</div>
                        <div class="space-y-1" id="bs-debit-breakdown">
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
                        <div class="space-y-1" id="bs-credit-breakdown">
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
                        <div class="text-lg font-bold text-gray-900" id="bs-net-debits">Net: ${{ number_format($totalDebits, 2) }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-lg font-bold text-gray-900" id="bs-net-credits">Net: ${{ number_format($totalCredits, 2) }}</div>
                    </td>
                </tr>
                <tr class="bg-indigo-50">
                    <td colspan="2" class="px-6 py-4 text-center">
                        <div class="text-2xl font-bold" id="bs-final-net">{{ $netProfit >= 0 ? '+' : '' }}${{ number_format($netProfit, 2) }}</div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Detailed Transactions -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-red-50">
            <h2 class="text-xl font-semibold text-red-900">Debit Transactions (Expenses)</h2>
            <p class="text-sm text-red-600 mt-1" id="bs-debit-count">{{ $debitRecords->count() }} transaction(s)</p>
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
                <tbody class="bg-white divide-y divide-gray-200" id="bs-debit-tbody">
                    @forelse($debitRecords as $debit)
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-900">{{ $debit->date->format('M d, Y') }}</td>
                            <td class="px-4 py-3 text-xs text-gray-900">
                                <div class="font-medium">{{ $debit->item_name ?? ucfirst($debit->expense_type ?? 'Expense') }}</div>
                                <div class="text-xs text-gray-500">{{ $debit->category->name ?? '' }}</div>
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
                        <td class="px-4 py-3 text-right text-sm font-bold text-red-900" id="bs-debit-foot-total">${{ number_format($totalDebits, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-green-50">
            <h2 class="text-xl font-semibold text-green-900">Credit Transactions (Income)</h2>
            <p class="text-sm text-green-600 mt-1" id="bs-credit-count">{{ $creditRecords->count() }} transaction(s)</p>
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
                <tbody class="bg-white divide-y divide-gray-200" id="bs-credit-tbody">
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
                        <td class="px-4 py-3 text-right text-sm font-bold text-green-900" id="bs-credit-foot-total">${{ number_format($totalCredits, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
</div>

<script>
(function() {
    var baseUrl = '{{ route("admin.reports.balance-sheet") }}';
    var defaultStart = '{{ date("Y-01-01") }}';
    var defaultEnd = '{{ date("Y-m-d") }}';

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

    window.applyBalanceSheetFilters = function() {
        var form = document.getElementById('balanceSheetFilterForm');
        var q = new URLSearchParams(new FormData(form));
        var url = baseUrl + (q.toString() ? '?' + q.toString() : '');
        var content = document.getElementById('balance-sheet-report-content');
        if (content) content.classList.add('opacity-60', 'pointer-events-none');

        fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                updateBalanceSheetReport(data);
            })
            .catch(function(err) {
                console.error(err);
                if (content) content.classList.remove('opacity-60', 'pointer-events-none');
            })
            .then(function() {
                if (content) content.classList.remove('opacity-60', 'pointer-events-none');
            });
    };

    window.resetBalanceSheetFilters = function() {
        document.getElementById('start_date').value = defaultStart;
        document.getElementById('end_date').value = defaultEnd;
        document.getElementById('project_id').value = '';
        applyBalanceSheetFilters();
    };

    function updateBalanceSheetReport(data) {
        var credits = data.totalCredits != null ? data.totalCredits : 0;
        var debits = data.totalDebits != null ? data.totalDebits : 0;
        var net = data.netProfit != null ? data.netProfit : (credits - debits);
        var periodStr = 'Period: ' + dateFmt(data.start_date) + ' - ' + dateFmt(data.end_date);

        document.getElementById('bs-total-credits').textContent = '$' + numFmt(credits);
        document.getElementById('bs-total-debits').textContent = '$' + numFmt(debits);
        document.getElementById('bs-period-credits').textContent = 'Period: ' + dateFmt(data.start_date) + ' to ' + dateFmt(data.end_date);
        document.getElementById('bs-period-debits').textContent = 'Period: ' + dateFmt(data.start_date) + ' to ' + dateFmt(data.end_date);
        document.getElementById('bs-table-period').textContent = periodStr;

        var netEl = document.getElementById('bs-net-profit');
        netEl.textContent = (net >= 0 ? '+' : '') + '$' + numFmt(net);
        netEl.className = 'text-4xl font-bold ' + (net >= 0 ? 'text-green-600' : 'text-red-600');

        var msgEl = document.getElementById('bs-net-message');
        if (net > 0) msgEl.innerHTML = '<span class="text-green-600">✓ Operating at a profit</span>';
        else if (net < 0) msgEl.innerHTML = '<span class="text-red-600">⚠ Operating at a loss</span>';
        else msgEl.innerHTML = '<span class="text-gray-600">⊘ Break-even</span>';

        document.getElementById('bs-row-total-expenses').textContent = 'Total Expenses: $' + numFmt(debits);
        document.getElementById('bs-row-total-income').textContent = 'Total Income: $' + numFmt(credits);
        document.getElementById('bs-net-debits').textContent = 'Net: $' + numFmt(debits);
        document.getElementById('bs-net-credits').textContent = 'Net: $' + numFmt(credits);
        var finalNet = document.getElementById('bs-final-net');
        finalNet.textContent = 'Net Profit/Loss: ' + (net >= 0 ? '+' : '') + '$' + numFmt(net);
        finalNet.className = 'text-2xl font-bold ' + (net >= 0 ? 'text-green-600' : 'text-red-600');

        var debitCat = data.debitByCategory || [];
        document.getElementById('bs-debit-breakdown').innerHTML = debitCat.length
            ? debitCat.map(function(item) { return '<div class="flex justify-between text-sm"><span class="text-gray-600">' + (item.name || '') + ':</span><span class="font-medium text-red-600">$' + numFmt(item.total) + '</span></div>'; }).join('')
            : '<div class="text-sm text-gray-500">No expenses recorded</div>';

        var creditCat = data.creditByCategory || [];
        document.getElementById('bs-credit-breakdown').innerHTML = creditCat.length
            ? creditCat.map(function(item) { return '<div class="flex justify-between text-sm"><span class="text-gray-600">' + (item.name || '') + ':</span><span class="font-medium text-green-600">$' + numFmt(item.total) + '</span></div>'; }).join('')
            : '<div class="text-sm text-gray-500">No income recorded</div>';

        var debitRecords = data.debitRecords || [];
        document.getElementById('bs-debit-count').textContent = debitRecords.length + ' transaction(s)';
        document.getElementById('bs-debit-foot-total').textContent = '$' + numFmt(debits);
        var dtbody = document.getElementById('bs-debit-tbody');
        dtbody.innerHTML = debitRecords.length
            ? debitRecords.map(function(r) { return '<tr><td class="px-4 py-3 whitespace-nowrap text-xs text-gray-900">' + (r.date_formatted || '-') + '</td><td class="px-4 py-3 text-xs text-gray-900"><div class="font-medium">' + (r.description || '') + '</div><div class="text-xs text-gray-500">' + (r.category_name || '') + '</div></td><td class="px-4 py-3 whitespace-nowrap text-xs text-right font-semibold text-red-600">$' + numFmt(r.amount) + '</td></tr>'; }).join('')
            : '<tr><td colspan="3" class="px-4 py-4 text-center text-xs text-gray-500">No debit transactions</td></tr>';

        var creditRecords = data.creditRecords || [];
        document.getElementById('bs-credit-count').textContent = creditRecords.length + ' transaction(s)';
        document.getElementById('bs-credit-foot-total').textContent = '$' + numFmt(credits);
        var ctbody = document.getElementById('bs-credit-tbody');
        ctbody.innerHTML = creditRecords.length
            ? creditRecords.map(function(r) { return '<tr><td class="px-4 py-3 whitespace-nowrap text-xs text-gray-900">' + (r.date_formatted || '-') + '</td><td class="px-4 py-3 text-xs text-gray-900"><div class="font-medium">' + (r.description || '') + '</div><div class="text-xs text-gray-500">' + (r.category_name || '') + '</div></td><td class="px-4 py-3 whitespace-nowrap text-xs text-right font-semibold text-green-600">$' + numFmt(r.amount) + '</td></tr>'; }).join('')
            : '<tr><td colspan="3" class="px-4 py-4 text-center text-xs text-gray-500">No credit transactions</td></tr>';
    }
})();
</script>
@endsection

