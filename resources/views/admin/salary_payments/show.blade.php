@extends('admin.layout')

@section('title', 'Salary Payment Details')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-3xl font-bold text-gray-900">Salary Payment Details</h1>
    <div class="space-x-2">
        <a href="{{ route('admin.salary-payments.edit', $salaryPayment) }}" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg transition duration-200">
            Edit
        </a>
        <a href="{{ route('admin.salary-payments.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition duration-200">
            Back to List
        </a>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Basic Information -->
    <div class="bg-white shadow-lg rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Basic Information</h2>
        <dl class="space-y-4">
            <div>
                <dt class="text-sm font-medium text-gray-500">Staff Member</dt>
                <dd class="mt-1 text-sm font-semibold text-gray-900">{{ $salaryPayment->staff->name }}</dd>
                @if($salaryPayment->staff->position)
                    <dd class="text-sm text-gray-600">{{ $salaryPayment->staff->position->name }}</dd>
                @endif
            </div>
            
            @if($salaryPayment->project)
            <div>
                <dt class="text-sm font-medium text-gray-500">Project</dt>
                <dd class="mt-1 text-sm text-gray-900">
                    <a href="{{ route('admin.projects.show', $salaryPayment->project) }}" class="text-indigo-600 hover:text-indigo-900">
                        {{ $salaryPayment->project->name }}
                    </a>
                </dd>
            </div>
            @endif

            <div>
                <dt class="text-sm font-medium text-gray-500">Payment Month</dt>
                <dd class="mt-1 text-sm font-semibold text-gray-900">{{ $salaryPayment->payment_month_name }}</dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500">Payment Date</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $salaryPayment->payment_date->format('M d, Y') }}</dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500">Status</dt>
                <dd class="mt-1">
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full
                        @if($salaryPayment->status === 'paid') bg-green-100 text-green-800
                        @elseif($salaryPayment->status === 'partial') bg-blue-100 text-blue-800
                        @elseif($salaryPayment->status === 'pending') bg-yellow-100 text-yellow-800
                        @else bg-red-100 text-red-800
                        @endif">
                        {{ ucfirst($salaryPayment->status) }}
                    </span>
                </dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500">Payment Progress</dt>
                <dd class="mt-1">
                    <div class="flex items-center space-x-2">
                        <div class="flex-1 bg-gray-200 rounded-full h-2">
                            @php
                                $percentage = $salaryPayment->net_amount > 0 ? ($salaryPayment->paid_amount / $salaryPayment->net_amount) * 100 : 0;
                            @endphp
                            <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ min($percentage, 100) }}%"></div>
                        </div>
                        <span class="text-sm font-semibold text-gray-900">{{ number_format($percentage, 1) }}%</span>
                    </div>
                    <div class="mt-1 text-xs text-gray-600">
                        Paid: Rs. {{ number_format($salaryPayment->paid_amount, 2) }} / 
                        Total: Rs. {{ number_format($salaryPayment->net_amount, 2) }}
                    </div>
                    @if($salaryPayment->balance_amount > 0)
                        <div class="mt-1 text-xs text-red-600 font-semibold">
                            Balance: Rs. {{ number_format($salaryPayment->balance_amount, 2) }}
                        </div>
                    @endif
                </dd>
            </div>

            @if($salaryPayment->expense_id)
            <div>
                <dt class="text-sm font-medium text-gray-500">Linked Expense</dt>
                <dd class="mt-1">
                    <a href="{{ route('admin.expenses.show', $salaryPayment->expense_id) }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                        View Expense Record →
                    </a>
                </dd>
            </div>
            @endif
        </dl>
    </div>

    <!-- Payment Details -->
    <div class="bg-white shadow-lg rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Payment Details</h2>
        <dl class="space-y-4">
            @if($salaryPayment->payment_method)
            <div>
                <dt class="text-sm font-medium text-gray-500">Payment Method</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $salaryPayment->payment_method }}</dd>
            </div>
            @endif

            @if($salaryPayment->bankAccount)
            <div>
                <dt class="text-sm font-medium text-gray-500">Bank Account</dt>
                <dd class="mt-1 text-sm text-gray-900">
                    {{ $salaryPayment->bankAccount->account_name }} - {{ $salaryPayment->bankAccount->bank_name }}
                </dd>
            </div>
            @endif

            @if($salaryPayment->transaction_reference)
            <div>
                <dt class="text-sm font-medium text-gray-500">Transaction Reference</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $salaryPayment->transaction_reference }}</dd>
            </div>
            @endif

            @if($salaryPayment->notes)
            <div>
                <dt class="text-sm font-medium text-gray-500">Notes</dt>
                <dd class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ $salaryPayment->notes }}</dd>
            </div>
            @endif

            <div>
                <dt class="text-sm font-medium text-gray-500">Created By</dt>
                <dd class="mt-1 text-sm text-gray-900">
                    {{ $salaryPayment->creator ? $salaryPayment->creator->name : 'System' }}
                    <span class="text-gray-500">on {{ $salaryPayment->created_at->format('M d, Y') }}</span>
                </dd>
            </div>

            @if($salaryPayment->updated_by && $salaryPayment->updated_at != $salaryPayment->created_at)
            <div>
                <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                <dd class="mt-1 text-sm text-gray-900">
                    {{ $salaryPayment->updater ? $salaryPayment->updater->name : 'System' }}
                    <span class="text-gray-500">on {{ $salaryPayment->updated_at->format('M d, Y') }}</span>
                </dd>
            </div>
            @endif
        </dl>
    </div>
</div>

<!-- Salary Calculation Breakdown -->
<div class="mt-6 bg-white shadow-lg rounded-lg p-6">
    <h2 class="text-xl font-semibold text-gray-900 mb-4">Salary Calculation Breakdown</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Earnings -->
        <div>
            <h3 class="text-lg font-semibold text-gray-800 mb-3">Earnings</h3>
            <dl class="space-y-3">
                <div class="flex justify-between items-center pb-2 border-b border-gray-200">
                    <dt class="text-sm font-medium text-gray-600">Base Salary</dt>
                    <dd class="text-sm font-semibold text-gray-900">
                        @if($salaryPayment->working_days && $salaryPayment->total_days)
                            Rs. {{ number_format($salaryPayment->base_salary, 2) }} 
                            <span class="text-xs text-gray-500">({{ $salaryPayment->working_days }}/{{ $salaryPayment->total_days }} days)</span>
                        @else
                            Rs. {{ number_format($salaryPayment->base_salary, 2) }}
                        @endif
                    </dd>
                </div>
                
                @if($salaryPayment->overtime_amount > 0)
                <div class="flex justify-between items-center pb-2 border-b border-gray-200">
                    <dt class="text-sm font-medium text-gray-600">Overtime</dt>
                    <dd class="text-sm font-semibold text-green-600">+ Rs. {{ number_format($salaryPayment->overtime_amount, 2) }}</dd>
                </div>
                @endif

                @if($salaryPayment->bonus_amount > 0)
                <div class="flex justify-between items-center pb-2 border-b border-gray-200">
                    <dt class="text-sm font-medium text-gray-600">Bonus</dt>
                    <dd class="text-sm font-semibold text-green-600">+ Rs. {{ number_format($salaryPayment->bonus_amount, 2) }}</dd>
                </div>
                @endif

                @if($salaryPayment->allowance_amount > 0)
                <div class="flex justify-between items-center pb-2 border-b border-gray-200">
                    <dt class="text-sm font-medium text-gray-600">Allowance</dt>
                    <dd class="text-sm font-semibold text-green-600">+ Rs. {{ number_format($salaryPayment->allowance_amount, 2) }}</dd>
                </div>
                @endif

                <div class="flex justify-between items-center pt-2 border-t-2 border-gray-300">
                    <dt class="text-base font-semibold text-gray-900">Gross Amount</dt>
                    <dd class="text-base font-bold text-gray-900">Rs. {{ number_format($salaryPayment->gross_amount, 2) }}</dd>
                </div>
            </dl>
        </div>

        <!-- Deductions -->
        <div>
            <h3 class="text-lg font-semibold text-gray-800 mb-3">Deductions</h3>
            <dl class="space-y-3">
                @if($salaryPayment->deduction_amount > 0)
                <div class="flex justify-between items-center pb-2 border-b border-gray-200">
                    <dt class="text-sm font-medium text-gray-600">Deductions</dt>
                    <dd class="text-sm font-semibold text-red-600">- Rs. {{ number_format($salaryPayment->deduction_amount, 2) }}</dd>
                </div>
                @endif

                @if($salaryPayment->advance_deduction > 0)
                <div class="flex justify-between items-center pb-2 border-b border-gray-200">
                    <dt class="text-sm font-medium text-gray-600">Advance Deduction</dt>
                    <dd class="text-sm font-semibold text-red-600">- Rs. {{ number_format($salaryPayment->advance_deduction, 2) }}</dd>
                </div>
                @endif

                @if($salaryPayment->deduction_amount == 0 && $salaryPayment->advance_deduction == 0)
                <div class="text-sm text-gray-500 italic">No deductions</div>
                @endif

                <div class="flex justify-between items-center pt-2 border-t-2 border-gray-300">
                    <dt class="text-base font-semibold text-gray-900">Total Deductions</dt>
                    <dd class="text-base font-bold text-red-600">
                        Rs. {{ number_format($salaryPayment->deduction_amount + $salaryPayment->advance_deduction, 2) }}
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Net Amount -->
    <div class="mt-6 pt-6 border-t-2 border-indigo-300 bg-indigo-50 rounded-lg p-4">
        <div class="flex justify-between items-center">
            <dt class="text-xl font-bold text-gray-900">Net Amount Payable</dt>
            <dd class="text-2xl font-bold text-indigo-600">Rs. {{ number_format($salaryPayment->net_amount, 2) }}</dd>
        </div>
        @if($salaryPayment->paid_amount > 0)
            <div class="mt-2 flex justify-between items-center text-sm">
                <span class="text-gray-600">Paid Amount:</span>
                <span class="font-semibold text-green-600">Rs. {{ number_format($salaryPayment->paid_amount, 2) }}</span>
            </div>
            @if($salaryPayment->balance_amount > 0)
                <div class="mt-1 flex justify-between items-center text-sm">
                    <span class="text-gray-600">Balance:</span>
                    <span class="font-semibold text-red-600">Rs. {{ number_format($salaryPayment->balance_amount, 2) }}</span>
                </div>
            @endif
        @endif
    </div>
</div>

@if($salaryPayment->balance_amount > 0)
<!-- Record Payment Form -->
<div class="mt-6 bg-white shadow-lg rounded-lg p-6">
    <h2 class="text-xl font-semibold text-gray-900 mb-4">Record Payment</h2>
    <form action="{{ route('admin.salary-payments.record-payment', $salaryPayment) }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="payment_amount" class="block text-sm font-medium text-gray-700 mb-2">Payment Amount <span class="text-red-500">*</span></label>
                <input type="number" name="payment_amount" id="payment_amount" step="0.01" min="0.01" max="{{ $salaryPayment->balance_amount }}" 
                       value="{{ old('payment_amount') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('payment_amount') border-red-500 @enderror"
                       placeholder="Max: Rs. {{ number_format($salaryPayment->balance_amount, 2) }}">
                @error('payment_amount')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="payment_date" class="block text-sm font-medium text-gray-700 mb-2">Payment Date <span class="text-red-500">*</span></label>
                <input type="date" name="payment_date" id="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('payment_date') border-red-500 @enderror">
                @error('payment_date')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                <select name="payment_method" id="payment_method"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Select Payment Method</option>
                    @foreach($paymentModes as $paymentMode)
                        <option value="{{ $paymentMode->name }}" {{ old('payment_method') == $paymentMode->name ? 'selected' : '' }}>
                            {{ $paymentMode->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="bank_account_id" class="block text-sm font-medium text-gray-700 mb-2">Bank Account</label>
                <select name="bank_account_id" id="bank_account_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">None</option>
                    @foreach($bankAccounts as $account)
                        <option value="{{ $account->id }}" {{ old('bank_account_id') == $account->id ? 'selected' : '' }}>
                            {{ $account->account_name }} - {{ $account->bank_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="transaction_reference" class="block text-sm font-medium text-gray-700 mb-2">Transaction Reference</label>
                <input type="text" name="transaction_reference" id="transaction_reference" value="{{ old('transaction_reference') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                       placeholder="Transaction ID, Cheque Number, etc.">
            </div>

            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                <textarea name="notes" id="notes" rows="2"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                          placeholder="Additional notes...">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                Record Payment
            </button>
        </div>
    </form>
</div>
@endif

<!-- Payment History -->
@if($salaryPayment->transactions->count() > 0)
<div class="mt-6 bg-white shadow-lg rounded-lg p-6">
    <h2 class="text-xl font-semibold text-gray-900 mb-4">Payment History</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bank Account</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recorded By</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($salaryPayment->transactions as $transaction)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $transaction->payment_date->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                            Rs. {{ number_format($transaction->amount, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $transaction->payment_method ?: '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $transaction->bankAccount ? $transaction->bankAccount->account_name : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $transaction->transaction_reference ?: '-' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $transaction->notes ?: '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $transaction->creator ? $transaction->creator->name : 'System' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50">
                <tr>
                    <td class="px-6 py-4 text-sm font-semibold text-gray-900" colspan="1">Total Paid:</td>
                    <td class="px-6 py-4 text-sm font-bold text-indigo-600" colspan="6">
                        Rs. {{ number_format($salaryPayment->transactions->sum('amount'), 2) }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endif
@endsection

