@extends('admin.layout')

@section('title', 'Worker Payment Details')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-3xl font-bold text-gray-900">Payment Details</h1>
    <a href="{{ route('admin.worker-payments.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">Back</a>
</div>

<div class="bg-white shadow-lg rounded-lg p-6 max-w-3xl">
    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
        <div><dt class="text-gray-500">Worker</dt><dd class="font-medium text-lg">{{ $workerPayment->worker?->name }}</dd></div>
        <div><dt class="text-gray-500">Project</dt><dd class="font-medium">{{ $workerPayment->project?->name ?? '—' }}</dd></div>
        <div><dt class="text-gray-500">Wage Type</dt><dd class="font-medium">{{ ucfirst($workerPayment->wage_type) }}</dd></div>
        <div><dt class="text-gray-500">Period</dt><dd class="font-medium">{{ $workerPayment->period_start->format('M d, Y') }} – {{ $workerPayment->period_end->format('M d, Y') }}</dd></div>
        <div><dt class="text-gray-500">Present / Half / Absent / Leave</dt>
            <dd class="font-medium">{{ $workerPayment->present_days }} / {{ $workerPayment->half_days }} / {{ $workerPayment->absent_days }} / {{ $workerPayment->leave_days }}</dd></div>
        <div><dt class="text-gray-500">Overtime Hours</dt><dd class="font-medium">{{ $workerPayment->overtime_hours }}</dd></div>
        <div><dt class="text-gray-500">Calculated</dt><dd class="font-medium">Rs. {{ number_format($workerPayment->calculated_amount, 2) }}</dd></div>
        <div><dt class="text-gray-500">OT Amount</dt><dd class="font-medium">Rs. {{ number_format($workerPayment->overtime_amount, 2) }}</dd></div>
        <div><dt class="text-gray-500">Bonus</dt><dd class="font-medium">Rs. {{ number_format($workerPayment->bonus, 2) }}</dd></div>
        <div><dt class="text-gray-500">Deduction</dt><dd class="font-medium">Rs. {{ number_format($workerPayment->deduction, 2) }}</dd></div>
        <div><dt class="text-gray-500">Net Amount</dt><dd class="font-medium text-indigo-700">Rs. {{ number_format($workerPayment->net_amount, 2) }}</dd></div>
        <div><dt class="text-gray-500">Paid Amount</dt><dd class="font-medium text-red-600 text-lg">Rs. {{ number_format($workerPayment->paid_amount, 2) }}</dd></div>
        <div><dt class="text-gray-500">Payment Date</dt><dd class="font-medium">{{ $workerPayment->payment_date->format('M d, Y') }}</dd></div>
        <div><dt class="text-gray-500">Status</dt><dd class="font-medium">{{ ucfirst($workerPayment->status) }}</dd></div>
        <div><dt class="text-gray-500">Linked Expense</dt>
            <dd class="font-medium">
                @if($workerPayment->expense_id)
                    <a href="{{ route('admin.expenses.show', $workerPayment->expense_id) }}" target="_blank" rel="noopener noreferrer" class="text-indigo-600 hover:underline">Expense #{{ $workerPayment->expense_id }}</a>
                @else — @endif
            </dd>
        </div>
        <div class="md:col-span-2"><dt class="text-gray-500">Notes</dt><dd class="font-medium">{{ $workerPayment->notes ?? '—' }}</dd></div>
    </dl>
</div>
@endsection
