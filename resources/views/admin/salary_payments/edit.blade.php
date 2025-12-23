@extends('admin.layout')

@section('title', 'Edit Salary Payment')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Edit Salary Payment</h1>
    <p class="mt-2 text-gray-600">Update salary payment details</p>
</div>

<div class="bg-white shadow-lg rounded-lg p-6">
    <form action="{{ route('admin.salary-payments.update', $salaryPayment) }}" method="POST"
          data-validate="true"
          data-validation-route="{{ route('admin.salary-payments.validate.edit', $salaryPayment) }}"
          id="salaryPaymentForm">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label for="staff_id" class="block text-sm font-medium text-gray-700 mb-2">Staff Member <span class="text-red-500">*</span></label>
                <select name="staff_id" id="staff_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('staff_id') border-red-500 @enderror">
                    <option value="">Select Staff Member</option>
                    @foreach($staff as $member)
                        <option value="{{ $member->id }}" 
                                data-salary="{{ $member->salary }}"
                                {{ old('staff_id', $salaryPayment->staff_id) == $member->id ? 'selected' : '' }}>
                            {{ $member->name }} - {{ $member->position ? $member->position->name : 'N/A' }} (Rs. {{ number_format($member->salary, 2) }})
                        </option>
                    @endforeach
                </select>
                <div class="field-error text-red-600 text-sm mt-1" data-field="staff_id" style="display: none;"></div>
                @error('staff_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="project_id" class="block text-sm font-medium text-gray-700 mb-2">Project</label>
                <select name="project_id" id="project_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">None</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" {{ old('project_id', $salaryPayment->project_id) == $project->id ? 'selected' : '' }}>
                            {{ $project->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="payment_month" class="block text-sm font-medium text-gray-700 mb-2">Payment Month <span class="text-red-500">*</span></label>
                <input type="month" name="payment_month" id="payment_month" value="{{ old('payment_month', $salaryPayment->payment_month->format('Y-m')) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('payment_month') border-red-500 @enderror">
                <div class="field-error text-red-600 text-sm mt-1" data-field="payment_month" style="display: none;"></div>
                @error('payment_month')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="payment_date" class="block text-sm font-medium text-gray-700 mb-2">Payment Date <span class="text-red-500">*</span></label>
                <input type="date" name="payment_date" id="payment_date" value="{{ old('payment_date', $salaryPayment->payment_date->format('Y-m-d')) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('payment_date') border-red-500 @enderror">
                <div class="field-error text-red-600 text-sm mt-1" data-field="payment_date" style="display: none;"></div>
                @error('payment_date')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="border-t border-gray-200 pt-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Salary Calculation</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
                <div>
                    <label for="base_salary" class="block text-sm font-medium text-gray-700 mb-2">Base Salary <span class="text-red-500">*</span></label>
                    <input type="number" name="base_salary" id="base_salary" step="0.01" min="0" value="{{ old('base_salary', $salaryPayment->base_salary) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('base_salary') border-red-500 @enderror">
                    <div class="field-error text-red-600 text-sm mt-1" data-field="base_salary" style="display: none;"></div>
                    @error('base_salary')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="working_days" class="block text-sm font-medium text-gray-700 mb-2">Working Days</label>
                    <input type="number" name="working_days" id="working_days" min="1" value="{{ old('working_days', $salaryPayment->working_days) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           placeholder="Leave empty for full month">
                    <small class="text-gray-500">For partial month calculation</small>
                </div>

                <div>
                    <label for="total_days" class="block text-sm font-medium text-gray-700 mb-2">Total Days in Month</label>
                    <input type="number" name="total_days" id="total_days" min="1" value="{{ old('total_days', $salaryPayment->total_days) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           placeholder="Auto-calculated">
                    <small class="text-gray-500">Auto-filled based on month</small>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
                <div>
                    <label for="overtime_amount" class="block text-sm font-medium text-gray-700 mb-2">Overtime Amount</label>
                    <input type="number" name="overtime_amount" id="overtime_amount" step="0.01" min="0" value="{{ old('overtime_amount', $salaryPayment->overtime_amount) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label for="bonus_amount" class="block text-sm font-medium text-gray-700 mb-2">Bonus Amount</label>
                    <input type="number" name="bonus_amount" id="bonus_amount" step="0.01" min="0" value="{{ old('bonus_amount', $salaryPayment->bonus_amount) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label for="allowance_amount" class="block text-sm font-medium text-gray-700 mb-2">Allowance Amount</label>
                    <input type="number" name="allowance_amount" id="allowance_amount" step="0.01" min="0" value="{{ old('allowance_amount', $salaryPayment->allowance_amount) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                <div>
                    <label for="deduction_amount" class="block text-sm font-medium text-gray-700 mb-2">Deduction Amount</label>
                    <input type="number" name="deduction_amount" id="deduction_amount" step="0.01" min="0" value="{{ old('deduction_amount', $salaryPayment->deduction_amount) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label for="advance_deduction" class="block text-sm font-medium text-gray-700 mb-2">Advance Deduction</label>
                    <input type="number" name="advance_deduction" id="advance_deduction" step="0.01" min="0" value="{{ old('advance_deduction', $salaryPayment->advance_deduction) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-sm font-semibold text-gray-900 mb-3">Salary Calculation Summary</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Gross Amount</label>
                        <div class="text-lg font-semibold text-gray-900" id="gross_amount_display">Rs. {{ number_format($salaryPayment->gross_amount, 2) }}</div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Total Deductions</label>
                        <div class="text-lg font-semibold text-red-600" id="total_deductions_display">Rs. {{ number_format($salaryPayment->deduction_amount + $salaryPayment->advance_deduction, 2) }}</div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tax Amount</label>
                        <div class="text-lg font-semibold text-orange-600" id="tax_amount_display">Rs. {{ number_format($salaryPayment->tax_amount ?? 0, 2) }}</div>
                        <div class="text-xs text-gray-500 mt-1" id="tax_info">
                            Annual: Rs. {{ number_format($salaryPayment->taxable_income ?? 0, 2) }}
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Net Payable Amount</label>
                        <div class="text-xl font-bold text-indigo-600" id="net_amount_display">Rs. {{ number_format($salaryPayment->net_amount, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="border-t border-gray-200 pt-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment Details</h3>
            
            <!-- Payment Amount Section -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                @if($salaryPayment->status === 'partial')
                <div class="bg-yellow-50 border border-yellow-300 rounded-lg p-3 mb-3">
                    <p class="text-sm font-semibold text-yellow-800">
                        <strong>Partial Payment Status:</strong> Rs. {{ number_format($salaryPayment->paid_amount, 2) }} paid out of Rs. {{ number_format($salaryPayment->net_amount, 2) }}
                    </p>
                    <p class="text-sm text-yellow-700 mt-1">
                        <strong>Remaining Balance:</strong> Rs. {{ number_format($salaryPayment->balance_amount, 2) }} 
                        ({{ number_format(($salaryPayment->balance_amount / $salaryPayment->net_amount) * 100, 1) }}%)
                    </p>
                    <p class="text-xs text-yellow-600 mt-2">
                        💡 You can pay the remaining balance or add another partial payment below.
                    </p>
                </div>
                @endif
                <label class="block text-sm font-medium text-gray-700 mb-2">Payment Amount (Optional - for partial payments)</label>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="payment_percentage" class="block text-xs text-gray-600 mb-1">Payment Percentage (%)</label>
                        <input type="number" name="payment_percentage" id="payment_percentage" step="0.01" min="0" max="100" value="{{ old('payment_percentage') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                               placeholder="e.g., 25 for 25%">
                        <p class="text-xs text-gray-500 mt-1">Enter percentage (0-100)</p>
                    </div>
                    <div>
                        <label for="payment_amount" class="block text-xs text-gray-600 mb-1">Additional Payment Amount (Rs.)</label>
                        <input type="number" name="payment_amount" id="payment_amount" step="0.01" min="0" value="{{ old('payment_amount') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                               placeholder="e.g., 5000">
                        <p class="text-xs text-gray-500 mt-1">Enter additional amount to pay (will be added to existing Rs. {{ number_format($salaryPayment->paid_amount, 2) }})</p>
                    </div>
                    <div class="flex items-end">
                        <div class="w-full bg-white p-3 rounded-lg border border-gray-300">
                            <p class="text-xs text-gray-600 mb-1">Total Payment</p>
                            <p class="text-lg font-semibold text-indigo-600" id="calculated_payment_display">Rs. {{ number_format($salaryPayment->paid_amount, 2) }}</p>
                            <p class="text-xs text-gray-500 mt-1">
                                <span class="text-gray-600">Existing Paid: </span>
                                <span class="font-semibold text-blue-600" id="existing_paid_display">Rs. {{ number_format($salaryPayment->paid_amount, 2) }}</span>
                            </p>
                            <p class="text-xs text-gray-500 mt-1">Balance: <span id="calculated_balance_display" class="font-semibold text-red-600">Rs. {{ number_format($salaryPayment->balance_amount, 2) }}</span></p>
                            <p class="text-xs text-gray-500 mt-1">Net Amount: <span id="net_amount_reference" class="font-semibold text-gray-700">Rs. {{ number_format($salaryPayment->net_amount, 2) }}</span></p>
                            <div id="payment_status_indicator" class="mt-2 text-xs font-semibold" style="display: none;">
                                <span class="px-2 py-1 rounded" id="status_badge"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <p class="text-xs text-gray-600 mt-2">
                    <strong>Note:</strong> Leave empty for full payment. Enter percentage (e.g., 25) or amount to pay only a portion now. 
                    The remaining balance can be paid later using the "Record Payment" feature.
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status <span class="text-red-500">*</span></label>
                    <select name="status" id="status"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('status') border-red-500 @enderror">
                        <option value="pending" {{ old('status', $salaryPayment->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="partial" {{ old('status', $salaryPayment->status) == 'partial' ? 'selected' : '' }}>Partial</option>
                        <option value="paid" {{ old('status', $salaryPayment->status) == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="cancelled" {{ old('status', $salaryPayment->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    <div class="field-error text-red-600 text-sm mt-1" data-field="status" style="display: none;"></div>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @if($salaryPayment->expense_id)
                        <p class="mt-1 text-sm text-green-600">✓ Linked to expense record</p>
                    @endif
                    <p class="text-xs text-gray-500 mt-1">Status will auto-update based on payment amount</p>
                </div>

                <div>
                    <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                    <select name="payment_method" id="payment_method"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select Payment Method</option>
                        @foreach($paymentModes as $paymentMode)
                            <option value="{{ $paymentMode->name }}" {{ old('payment_method', $salaryPayment->payment_method) == $paymentMode->name ? 'selected' : '' }}>
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
                            <option value="{{ $account->id }}" {{ old('bank_account_id', $salaryPayment->bank_account_id) == $account->id ? 'selected' : '' }}>
                                {{ $account->account_name }} - {{ $account->bank_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="transaction_reference" class="block text-sm font-medium text-gray-700 mb-2">Transaction Reference</label>
                    <input type="text" name="transaction_reference" id="transaction_reference" value="{{ old('transaction_reference', $salaryPayment->transaction_reference) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           placeholder="Transaction ID, Cheque Number, etc.">
                </div>

                <div class="md:col-span-2">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                    <textarea name="notes" id="notes" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                              placeholder="Additional notes...">{{ old('notes', $salaryPayment->notes) }}</textarea>
                </div>
            </div>
            
            <!-- Hidden fields for paid_amount and balance_amount -->
            <input type="hidden" name="paid_amount" id="paid_amount" value="{{ old('paid_amount', $salaryPayment->paid_amount) }}">
            <input type="hidden" name="balance_amount" id="balance_amount" value="{{ old('balance_amount', $salaryPayment->balance_amount) }}">
        </div>

        <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
            <a href="{{ route('admin.salary-payments.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                Update Salary Payment
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const staffSelect = document.getElementById('staff_id');
    const baseSalaryInput = document.getElementById('base_salary');
    const paymentMonthInput = document.getElementById('payment_month');
    const workingDaysInput = document.getElementById('working_days');
    const totalDaysInput = document.getElementById('total_days');
    const overtimeInput = document.getElementById('overtime_amount');
    const bonusInput = document.getElementById('bonus_amount');
    const allowanceInput = document.getElementById('allowance_amount');
    const deductionInput = document.getElementById('deduction_amount');
    const advanceDeductionInput = document.getElementById('advance_deduction');
    const paymentPercentageInput = document.getElementById('payment_percentage');
    const paymentAmountInput = document.getElementById('payment_amount');
    const statusSelect = document.getElementById('status');
    const paidAmountInput = document.getElementById('paid_amount');
    const balanceAmountInput = document.getElementById('balance_amount');

    // Initialize payment fields based on existing data
    @if($salaryPayment->net_amount > 0)
        const existingPaidAmount = {{ $salaryPayment->paid_amount }};
        const existingNetAmount = {{ $salaryPayment->net_amount }};
        const existingBalanceAmount = {{ $salaryPayment->balance_amount }};
        
        // If partial payment, show remaining balance as default
        @if($salaryPayment->status === 'partial')
            // Pre-fill with remaining balance for easy payment
            paymentAmountInput.value = existingBalanceAmount.toFixed(2);
            if (existingNetAmount > 0) {
                const remainingPercentage = (existingBalanceAmount / existingNetAmount) * 100;
                paymentPercentageInput.value = remainingPercentage.toFixed(2);
            }
        @else
            // If fully paid or pending, show current paid amount
            if (existingPaidAmount > 0) {
                const existingPercentage = (existingPaidAmount / existingNetAmount) * 100;
                paymentPercentageInput.value = existingPercentage.toFixed(2);
                paymentAmountInput.value = existingPaidAmount.toFixed(2);
            }
        @endif
    @endif

    // Auto-fill base salary when staff is selected
    // Auto-fill base salary and assessment type when staff is selected
    staffSelect.addEventListener('change', function() {
        const staffId = this.value;
        const selectedOption = this.options[this.selectedIndex];
        const salary = selectedOption.getAttribute('data-salary');
        
        if (salary && !baseSalaryInput.value) {
            baseSalaryInput.value = salary;
        }
        
        // Fetch staff details (marriage status, assessment type)
        if (staffId) {
            fetch(`{{ route('admin.staff.details', ':id') }}`.replace(':id', staffId))
                .then(response => response.json())
                .then(data => {
                    // Auto-set assessment type based on marriage status
                    const assessmentTypeSelect = document.getElementById('assessment_type');
                    if (assessmentTypeSelect && data.assessment_type) {
                        assessmentTypeSelect.value = data.assessment_type;
                    }
                    
                    // Recalculate amounts with new assessment type
                    calculateAmounts();
                })
                .catch(error => {
                    console.error('Error fetching staff details:', error);
                });
        }
    });

    // Store original payment month
    const originalPaymentMonth = paymentMonthInput.value;
    const currentPaymentId = {{ $salaryPayment->id }};

    // Auto-calculate total days when month is selected
    paymentMonthInput.addEventListener('change', function() {
        const monthValue = this.value;
        if (monthValue) {
            const [year, month] = monthValue.split('-');
            const daysInMonth = new Date(year, month, 0).getDate();
            if (!totalDaysInput.value) {
                totalDaysInput.value = daysInMonth;
            }
            calculateAmounts();
            
            // Check if month changed to a different month
            if (monthValue !== originalPaymentMonth) {
                checkExistingPaymentForNewMonth();
            }
        }
    });

    // Check for existing payment when month is changed in edit form
    function checkExistingPaymentForNewMonth() {
        const staffId = staffSelect.value;
        const paymentMonth = paymentMonthInput.value;
        
        if (!staffId || !paymentMonth) {
            return;
        }

        // Show loading indicator
        showNotification('Checking for existing payment in selected month...', 'info');

        fetch('{{ route("admin.salary-payments.check-existing") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                staff_id: staffId,
                payment_month: paymentMonth,
                exclude_id: currentPaymentId // Exclude current payment from check
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            // Remove loading message
            const loading = document.querySelector('.payment-notification');
            if (loading) loading.remove();

            if (data.exists) {
                if (data.is_paid) {
                    // Already fully paid for this month
                    if (confirm('This staff member already has a fully paid salary for the selected month. Would you like to edit that payment instead?')) {
                        window.location.href = `/admin/salary-payments/${data.data.id}/edit`;
                    } else {
                        // Revert to original month
                        paymentMonthInput.value = originalPaymentMonth;
                        const [year, month] = originalPaymentMonth.split('-');
                        const daysInMonth = new Date(year, month, 0).getDate();
                        if (!totalDaysInput.value) {
                            totalDaysInput.value = daysInMonth;
                        }
                        calculateAmounts();
                    }
                } else if (data.is_partial) {
                    // Partial payment exists for this month
                    const paidAmount = parseFloat(data.data.paid_amount) || 0;
                    const balanceAmount = parseFloat(data.data.balance_amount) || 0;
                    
                    if (confirm(`This staff member has a partial payment (Rs. ${paidAmount.toFixed(2)} paid, Rs. ${balanceAmount.toFixed(2)} remaining) for the selected month. Would you like to edit that payment instead?`)) {
                        window.location.href = `/admin/salary-payments/${data.data.id}/edit`;
                    } else {
                        // Revert to original month
                        paymentMonthInput.value = originalPaymentMonth;
                        const [year, month] = originalPaymentMonth.split('-');
                        const daysInMonth = new Date(year, month, 0).getDate();
                        if (!totalDaysInput.value) {
                            totalDaysInput.value = daysInMonth;
                        }
                        calculateAmounts();
                    }
                }
            } else {
                // No existing payment for this month - allow change
                showNotification('No existing payment found for selected month. You can create a new payment.', 'info');
            }
        })
        .catch(error => {
            console.error('Error checking existing payment:', error);
            const loading = document.querySelector('.payment-notification');
            if (loading) loading.remove();
            showNotification('Error checking for existing payment. Please try again.', 'warning');
            // Revert to original month on error
            paymentMonthInput.value = originalPaymentMonth;
        });
    }

    // Show notification function
    function showNotification(message, type = 'info') {
        // Remove existing notification
        const existing = document.querySelector('.payment-notification');
        if (existing) {
            existing.remove();
        }
        
        const notification = document.createElement('div');
        notification.className = `payment-notification p-4 mb-4 rounded-lg ${
            type === 'info' ? 'bg-blue-50 border border-blue-200 text-blue-800' :
            type === 'warning' ? 'bg-yellow-50 border border-yellow-200 text-yellow-800' :
            'bg-green-50 border border-green-200 text-green-800'
        }`;
        notification.innerHTML = `<p class="font-medium">${message}</p>`;
        
        const form = document.getElementById('salaryPaymentForm');
        if (form && form.parentNode) {
            form.parentNode.insertBefore(notification, form);
        }
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }

    // Calculate amounts when any input changes
    [baseSalaryInput, workingDaysInput, totalDaysInput, overtimeInput, bonusInput, allowanceInput, deductionInput, advanceDeductionInput].forEach(input => {
        input.addEventListener('input', function() {
            calculateAmounts();
            calculatePayment();
        });
    });

    // Calculate payment when percentage or amount changes
    paymentPercentageInput.addEventListener('input', function() {
        if (this.value) {
            paymentAmountInput.value = '';
        }
        calculatePayment();
    });

    paymentAmountInput.addEventListener('input', function() {
        if (this.value) {
            paymentPercentageInput.value = '';
        }
        calculatePayment();
    });

    function calculateAmounts() {
        let baseSalary = parseFloat(baseSalaryInput.value) || 0;
        const workingDays = parseFloat(workingDaysInput.value);
        const totalDays = parseFloat(totalDaysInput.value);
        
        // If partial month, calculate prorated salary
        if (workingDays && totalDays) {
            baseSalary = (baseSalary / totalDays) * workingDays;
        }

        const overtime = parseFloat(overtimeInput.value) || 0;
        const bonus = parseFloat(bonusInput.value) || 0;
        const allowance = parseFloat(allowanceInput.value) || 0;
        const deduction = parseFloat(deductionInput.value) || 0;
        const advanceDeduction = parseFloat(advanceDeductionInput.value) || 0;

        const grossAmount = baseSalary + overtime + bonus + allowance;
        const totalDeductions = deduction + advanceDeduction;
        
        // Calculate tax
        const assessmentType = document.getElementById('assessment_type') ? document.getElementById('assessment_type').value : '{{ $salaryPayment->assessment_type ?? "single" }}';
        const taxResult = calculateTax(grossAmount, workingDays, totalDays, assessmentType);
        const taxAmount = taxResult.monthlyTax;
        const annualTaxableIncome = taxResult.annualTaxableIncome;
        
        const netAmount = grossAmount - totalDeductions - taxAmount;

        document.getElementById('gross_amount_display').textContent = 'Rs. ' + grossAmount.toFixed(2);
        document.getElementById('total_deductions_display').textContent = 'Rs. ' + totalDeductions.toFixed(2);
        const taxDisplay = document.getElementById('tax_amount_display');
        const taxInfo = document.getElementById('tax_info');
        if (taxDisplay) {
            taxDisplay.textContent = 'Rs. ' + taxAmount.toFixed(2);
        }
        if (taxInfo) {
            taxInfo.textContent = 'Annual: Rs. ' + annualTaxableIncome.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }
        document.getElementById('net_amount_display').textContent = 'Rs. ' + netAmount.toFixed(2);
        
        // Recalculate payment after amounts change
        calculatePayment();
    }

    // Nepal Tax Calculation (FY 2080/81) - same as create form
    function calculateTax(monthlyGross, workingDays, totalDays, assessmentType) {
        // Calculate full month gross for annualization
        let fullMonthGross = monthlyGross;
        if (workingDays && totalDays && totalDays > 0) {
            fullMonthGross = (monthlyGross / workingDays) * totalDays;
        }
        
        // Annual taxable income
        const annualTaxableIncome = fullMonthGross * 12;
        
        // Tax brackets
        const brackets = assessmentType === 'couple' ? [
            {min: 0, max: 600000, rate: 0.01},
            {min: 600000, max: 800000, rate: 0.10},
            {min: 800000, max: 1100000, rate: 0.20},
            {min: 1100000, max: 2000000, rate: 0.30},
            {min: 2000000, max: 5000000, rate: 0.36},
            {min: 5000000, max: Infinity, rate: 0.39}
        ] : [
            {min: 0, max: 500000, rate: 0.01},
            {min: 500000, max: 700000, rate: 0.10},
            {min: 700000, max: 1000000, rate: 0.20},
            {min: 1000000, max: 2000000, rate: 0.30},
            {min: 2000000, max: 5000000, rate: 0.36},
            {min: 5000000, max: Infinity, rate: 0.39}
        ];
        
        let totalTax = 0;
        let remainingIncome = annualTaxableIncome;
        
        for (const bracket of brackets) {
            if (remainingIncome <= 0) break;
            
            const bracketRange = bracket.max - bracket.min;
            const incomeInBracket = Math.min(remainingIncome, bracketRange);
            
            if (incomeInBracket > 0) {
                totalTax += incomeInBracket * bracket.rate;
                remainingIncome -= incomeInBracket;
            }
        }
        
        // Monthly tax
        let monthlyTax = totalTax / 12;
        
        // Prorate if partial month
        if (workingDays && totalDays && totalDays > 0) {
            monthlyTax = (monthlyTax / totalDays) * workingDays;
        }
        
        return {
            annualTaxableIncome: annualTaxableIncome,
            annualTax: totalTax,
            monthlyTax: monthlyTax
        };
    }

    function calculatePayment() {
        const netAmount = parseFloat(document.getElementById('net_amount_display').textContent.replace('Rs. ', '').replace(',', '')) || 0;
        
        // Get existing paid amount (from initial load)
        const existingPaidAmount = {{ $salaryPayment->paid_amount ?? 0 }};
        
        let totalPaidAmount = existingPaidAmount; // Start with existing paid amount
        let paymentPercentage = parseFloat(paymentPercentageInput.value) || 0;
        let paymentAmount = parseFloat(paymentAmountInput.value) || 0;

        // Calculate additional payment based on percentage or amount
        if (paymentPercentage > 0 && paymentPercentage <= 100) {
            // Percentage represents total percentage of net amount
            const totalPaidFromPercentage = (netAmount * paymentPercentage) / 100;
            totalPaidAmount = Math.min(totalPaidFromPercentage, netAmount);
            // Update payment amount field to show additional amount needed
            const additionalAmount = totalPaidAmount - existingPaidAmount;
            if (additionalAmount > 0) {
                paymentAmountInput.value = additionalAmount.toFixed(2);
            } else {
                paymentAmountInput.value = '';
            }
        } else if (paymentAmount > 0) {
            // Payment amount represents ADDITIONAL payment to add to existing
            const additionalPayment = Math.min(paymentAmount, netAmount - existingPaidAmount);
            totalPaidAmount = existingPaidAmount + additionalPayment;
            
            // Don't allow more than net amount
            totalPaidAmount = Math.min(totalPaidAmount, netAmount);
            
            if (netAmount > 0) {
                paymentPercentage = (totalPaidAmount / netAmount) * 100;
                paymentPercentageInput.value = paymentPercentage.toFixed(2);
            }
        } else {
            // If no payment entered, use existing paid amount
            totalPaidAmount = existingPaidAmount;
            
            // If status is 'paid', set full amount
            if (statusSelect.value === 'paid') {
                totalPaidAmount = netAmount;
            }
        }

        const balanceAmount = netAmount - totalPaidAmount;

        // Update display - show TOTAL paid amount (existing + new)
        document.getElementById('calculated_payment_display').textContent = 'Rs. ' + totalPaidAmount.toFixed(2);
        document.getElementById('existing_paid_display').textContent = 'Rs. ' + existingPaidAmount.toFixed(2);
        document.getElementById('calculated_balance_display').textContent = 'Rs. ' + balanceAmount.toFixed(2);

        // Update hidden fields - store TOTAL paid amount
        paidAmountInput.value = totalPaidAmount.toFixed(2);
        balanceAmountInput.value = balanceAmount.toFixed(2);

        // Auto-update status based on payment
        const statusIndicator = document.getElementById('payment_status_indicator');
        const statusBadge = document.getElementById('status_badge');
        
        if (netAmount <= 0) {
            statusSelect.value = 'pending';
            if (statusIndicator) statusIndicator.style.display = 'none';
        } else if (totalPaidAmount >= netAmount - 0.01) { // Allow small rounding differences
            // Fully paid - match with net amount
            statusSelect.value = 'paid';
            totalPaidAmount = netAmount; // Ensure exact match
            balanceAmount = 0;
            paidAmountInput.value = totalPaidAmount.toFixed(2);
            balanceAmountInput.value = 0;
            
            // Clear payment amount field since fully paid
            paymentAmountInput.value = '';
            paymentPercentageInput.value = '100';
            
            // Show success indicator
            if (statusIndicator) {
                statusIndicator.style.display = 'block';
                statusBadge.textContent = '✓ Fully Paid';
                statusBadge.className = 'px-2 py-1 rounded bg-green-100 text-green-800';
            }
            
            // Update display
            document.getElementById('calculated_payment_display').textContent = 'Rs. ' + totalPaidAmount.toFixed(2);
            document.getElementById('calculated_balance_display').textContent = 'Rs. 0.00';
            document.getElementById('calculated_balance_display').className = 'font-semibold text-green-600';
        } else if (totalPaidAmount > 0) {
            statusSelect.value = 'partial';
            
            // Show partial indicator
            if (statusIndicator) {
                statusIndicator.style.display = 'block';
                const paidPercentage = ((totalPaidAmount / netAmount) * 100).toFixed(1);
                statusBadge.textContent = `${paidPercentage}% Paid (Partial)`;
                statusBadge.className = 'px-2 py-1 rounded bg-yellow-100 text-yellow-800';
            }
            
            // Update display
            document.getElementById('calculated_balance_display').className = 'font-semibold text-red-600';
        } else {
            statusSelect.value = 'pending';
            if (statusIndicator) statusIndicator.style.display = 'none';
            document.getElementById('calculated_balance_display').className = 'font-semibold text-red-600';
        }
        
        // Update net amount reference
        const netAmountRef = document.getElementById('net_amount_reference');
        if (netAmountRef) {
            netAmountRef.textContent = 'Rs. ' + netAmount.toFixed(2);
        }
    }

    // Update payment when status changes manually
    statusSelect.addEventListener('change', function() {
        if (this.value === 'paid') {
            const netAmount = parseFloat(document.getElementById('net_amount_display').textContent.replace('Rs. ', '').replace(',', '')) || 0;
            paymentAmountInput.value = netAmount.toFixed(2);
            paymentPercentageInput.value = '100';
            calculatePayment();
        } else if (this.value === 'pending') {
            paymentAmountInput.value = '';
            paymentPercentageInput.value = '';
            calculatePayment();
        }
    });

    // Initial calculation
    calculateAmounts();
});
</script>
@endsection

