@extends('admin.layout')

@section('title', 'Worker Payment Report')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-3xl font-bold text-gray-900">Worker Payment Report</h1>
    <a href="{{ route('admin.reports.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">Back to Reports</a>
</div>

<div class="bg-white shadow-lg rounded-lg p-6 mb-6">
    <form method="GET" action="{{ route('admin.reports.worker-payment') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
            <input type="date" name="start_date" value="{{ $startDate }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
            <input type="date" name="end_date" value="{{ $endDate }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Project</label>
            <select name="project_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                <option value="">All Projects</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}" {{ (int)$projectId === $project->id ? 'selected' : '' }}>{{ $project->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Worker</label>
            <select name="worker_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                <option value="">All Workers</option>
                @foreach($workers as $w)
                    <option value="{{ $w->id }}" {{ (string)$workerId === (string)$w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Wage Type</label>
            <select name="wage_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                <option value="">All</option>
                <option value="daily" {{ $wageType === 'daily' ? 'selected' : '' }}>Daily</option>
                <option value="monthly" {{ $wageType === 'monthly' ? 'selected' : '' }}>Monthly</option>
            </select>
        </div>
        <div class="md:col-span-5">
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">Apply</button>
            <a href="{{ route('admin.reports.worker-payment') }}" class="ml-2 border border-gray-300 px-4 py-2 rounded-lg text-gray-700 hover:bg-gray-50">Reset</a>
        </div>
    </form>
</div>

<div class="bg-white shadow-lg rounded-lg p-6 mb-6">
    <h2 class="text-2xl font-bold text-red-600">Total Worker Payments: Rs. {{ number_format($totalPayments, 2) }}</h2>
    <p class="text-sm text-gray-500 mt-1">From {{ date('M d, Y', strtotime($startDate)) }} to {{ date('M d, Y', strtotime($endDate)) }}</p>
</div>

<div class="bg-white shadow-lg rounded-lg overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Summary by Worker</h2>
    </div>
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Worker</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Wage Type</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($totalByWorker as $row)
                <tr>
                    <td class="px-6 py-3 text-sm font-medium">{{ $row->name }}</td>
                    <td class="px-6 py-3 text-sm">{{ ucfirst($row->wage_type) }}</td>
                    <td class="px-6 py-3 text-sm text-right font-semibold text-red-600">Rs. {{ number_format($row->total, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="px-6 py-6 text-center text-gray-500">No payments in this period.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="bg-white shadow-lg rounded-lg overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Payment Details</h2>
    </div>
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Worker</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Project</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($payments as $p)
                <tr>
                    <td class="px-6 py-3 text-sm">{{ $p->date?->format('M d, Y') }}</td>
                    <td class="px-6 py-3 text-sm font-medium">{{ $p->worker?->name ?? 'N/A' }}</td>
                    <td class="px-6 py-3 text-sm">{{ $p->project?->name ?? '—' }}</td>
                    <td class="px-6 py-3 text-sm">{{ $p->workerPayment ? ucfirst($p->workerPayment->wage_type) : ($p->expenseType?->name ?? 'Worker') }}</td>
                    <td class="px-6 py-3 text-sm text-gray-500">{{ \Illuminate\Support\Str::limit($p->description, 50) }}</td>
                    <td class="px-6 py-3 text-sm text-right font-semibold">Rs. {{ number_format($p->amount, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-6 py-6 text-center text-gray-500">No records.</td></tr>
            @endforelse
        </tbody>
    </table>
    <x-pagination :paginator="$payments" wrapper-class="px-6 py-4 border-t border-gray-200" :show-info="true" />
</div>
@endsection
