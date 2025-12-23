@extends('admin.layout')

@section('title', 'Add Salary Payment')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Add Salary Payment</h1>
    <p class="mt-2 text-gray-600">Record a new salary payment for staff member</p>
</div>

<div class="bg-white shadow-lg rounded-lg p-6">
    <form action="{{ route('admin.salary-payments.store') }}" method="POST"
          data-validate="true"
          data-validation-route="{{ route('admin.salary-payments.validate') }}"
          id="salaryPaymentForm">
        @csrf
        <input type="hidden" name="payment_id" id="payment_id" value="">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label for="staff_id" class="block text-sm font-medium text-gray-700 mb-2">Staff Member <span class="text-red-500">*</span></label>
                <select name="staff_id" id="staff_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('staff_id') border-red-500 @enderror">
                    <option value="">Select Staff Member</option>
                    @foreach($staff as $member)
                        <option value="{{ $member->id }}" 
                                data-salary="{{ $member->salary }}"
                                {{ old('staff_id') == $member->id ? 'selected' : '' }}>
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
                        <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>
                            {{ $project->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="payment_month" class="block text-sm font-medium text-gray-700 mb-2">Payment Month <span class="text-red-500">*</span></label>
                <input type="month" name="payment_month" id="payment_month" value="{{ old('payment_month', date('Y-m')) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('payment_month') border-red-500 @enderror">
                <div class="field-error text-red-600 text-sm mt-1" data-field="payment_month" style="display: none;"></div>
                @error('payment_month')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="payment_date" class="block text-sm font-medium text-gray-700 mb-2">Payment Date <span class="text-red-500">*</span></label>
                <input type="date" name="payment_date" id="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}"
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
                    <input type="number" name="base_salary" id="base_salary" step="0.01" min="0" value="{{ old('base_salary') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('base_salary') border-red-500 @enderror">
                    <div class="field-error text-red-600 text-sm mt-1" data-field="base_salary" style="display: none;"></div>
                    @error('base_salary')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="working_days" class="block text-sm font-medium text-gray-700 mb-2">Working Days</label>
                    <input type="number" name="working_days" id="working_days" min="1" value="{{ old('working_days') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           placeholder="Leave empty for full month">
                    <small class="text-gray-500">For partial month calculation</small>
                </div>

                <div>
                    <label for="total_days" class="block text-sm font-medium text-gray-700 mb-2">Total Days in Month</label>
                    <input type="number" name="total_days" id="total_days" min="1" value="{{ old('total_days') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           placeholder="Auto-calculated">
                    <small class="text-gray-500">Auto-filled based on month</small>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
                <div>
                    <label for="overtime_amount" class="block text-sm font-medium text-gray-700 mb-2">Overtime Amount</label>
                    <input type="number" name="overtime_amount" id="overtime_amount" step="0.01" min="0" value="{{ old('overtime_amount', 0) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label for="bonus_amount" class="block text-sm font-medium text-gray-700 mb-2">Bonus Amount</label>
                    <input type="number" name="bonus_amount" id="bonus_amount" step="0.01" min="0" value="{{ old('bonus_amount', 0) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label for="allowance_amount" class="block text-sm font-medium text-gray-700 mb-2">Allowance Amount</label>
                    <input type="number" name="allowance_amount" id="allowance_amount" step="0.01" min="0" value="{{ old('allowance_amount', 0) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                <div>
                    <label for="deduction_amount" class="block text-sm font-medium text-gray-700 mb-2">Deduction Amount</label>
                    <input type="number" name="deduction_amount" id="deduction_amount" step="0.01" min="0" value="{{ old('deduction_amount', 0) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label for="advance_deduction" class="block text-sm font-medium text-gray-700 mb-2">Advance Deduction</label>
                    <input type="number" name="advance_deduction" id="advance_deduction" step="0.01" min="0" value="{{ old('advance_deduction', 0) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="mb-4">
                <label for="assessment_type" class="block text-sm font-medium text-gray-700 mb-2">Tax Assessment Type</label>
                <select name="assessment_type" id="assessment_type"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="single" {{ old('assessment_type') == 'single' ? 'selected' : '' }}>Single Assessment</option>
                    <option value="couple" {{ old('assessment_type') == 'couple' ? 'selected' : '' }}>Couple Assessment</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">Select tax assessment type as per Nepal Government FY 2080/81</p>
            </div>

            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                <h4 class="text-sm font-semibold text-gray-900 mb-3">Salary Calculation Summary</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Gross Amount</label>
                        <div class="text-lg font-semibold text-gray-900" id="gross_amount_display">Rs. 0.00</div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Total Deductions</label>
                        <div class="text-lg font-semibold text-red-600" id="total_deductions_display">Rs. 0.00</div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tax Amount</label>
                        <div class="text-lg font-semibold text-orange-600" id="tax_amount_display">Rs. 0.00</div>
                        <div class="text-xs text-gray-500 mt-1" id="tax_info">Annual: Rs. 0.00</div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Net Payable Amount</label>
                        <div class="text-xl font-bold text-indigo-600" id="net_amount_display">Rs. 0.00</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="border-t border-gray-200 pt-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment Details</h3>
            
            <!-- Payment Amount Section -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
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
                        <label for="payment_amount" class="block text-xs text-gray-600 mb-1">Or Payment Amount (Rs.)</label>
                        <input type="number" name="payment_amount" id="payment_amount" step="0.01" min="0" value="{{ old('payment_amount') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                               placeholder="e.g., 5000">
                        <p class="text-xs text-gray-500 mt-1">Enter exact amount</p>
                    </div>
                    <div class="flex items-end">
                        <div class="w-full bg-white p-3 rounded-lg border border-gray-300">
                            <p class="text-xs text-gray-600 mb-1">Total Payment</p>
                            <p class="text-lg font-semibold text-indigo-600" id="calculated_payment_display">Rs. 0.00</p>
                            <p class="text-xs text-gray-500 mt-1">Balance: <span id="calculated_balance_display" class="font-semibold text-red-600">Rs. 0.00</span></p>
                            <p class="text-xs text-gray-500 mt-1">Net Amount: <span id="net_amount_reference" class="font-semibold text-gray-700">Rs. 0.00</span></p>
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
                        <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="partial" {{ old('status') == 'partial' ? 'selected' : '' }}>Partial</option>
                        <option value="paid" {{ old('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    <div class="field-error text-red-600 text-sm mt-1" data-field="status" style="display: none;"></div>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Status will auto-update based on payment amount</p>
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

                <div class="md:col-span-2">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                    <textarea name="notes" id="notes" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                              placeholder="Additional notes...">{{ old('notes') }}</textarea>
                </div>
            </div>
            
            <!-- Hidden fields for paid_amount and balance_amount -->
            <input type="hidden" name="paid_amount" id="paid_amount" value="0">
            <input type="hidden" name="balance_amount" id="balance_amount" value="0">
        </div>

        <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
            <a href="{{ route('admin.salary-payments.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                Create Salary Payment
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

    // Check for existing payment when staff or month changes
    function checkExistingPayment() {
        const staffId = staffSelect.value;
        const paymentMonth = paymentMonthInput.value;
        
        console.log('Checking existing payment:', { staffId, paymentMonth });
        
        if (!staffId || !paymentMonth) {
            console.log('Missing staff or month, skipping check');
            return;
        }

        // Show loading indicator
        const loadingMsg = showNotification('Checking for existing payment...', 'info');

        fetch('{{ route("admin.salary-payments.check-existing") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                staff_id: staffId,
                payment_month: paymentMonth
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('Existing payment check response:', data);
            
            // Remove loading message
            const loading = document.querySelector('.payment-notification');
            if (loading) loading.remove();

            if (data.exists) {
                if (data.is_paid) {
                    // Already fully paid - show message and redirect to edit
                    if (confirm('This staff member already has a fully paid salary for this month. Would you like to view/edit it?')) {
                        window.location.href = `/admin/salary-payments/${data.data.id}/edit`;
                    } else {
                        // Reset form
                        staffSelect.value = '';
                        paymentMonthInput.value = '{{ date("Y-m") }}';
                        return;
                    }
                } else if (data.is_partial) {
                    // Partial payment exists - redirect to edit page
                    const paidAmount = parseFloat(data.data.paid_amount) || 0;
                    const balanceAmount = parseFloat(data.data.balance_amount) || 0;
                    if (confirm(`This staff member has a partial payment (Rs. ${paidAmount.toFixed(2)} paid, Rs. ${balanceAmount.toFixed(2)} remaining) for this month. Would you like to continue the payment?`)) {
                        window.location.href = `/admin/salary-payments/${data.data.id}/edit`;
                    } else {
                        // Reset form
                        staffSelect.value = '';
                        paymentMonthInput.value = '{{ date("Y-m") }}';
                    }
                }
            } else {
                // No existing payment - auto-fill base salary
                const selectedOption = staffSelect.options[staffSelect.selectedIndex];
                const salary = selectedOption.getAttribute('data-salary');
                if (salary && !baseSalaryInput.value) {
                    baseSalaryInput.value = salary;
                    calculateAmounts();
                }
            }
        })
        .catch(error => {
            console.error('Error checking existing payment:', error);
            const loading = document.querySelector('.payment-notification');
            if (loading) loading.remove();
            showNotification('Error checking for existing payment. Please try again.', 'warning');
        });
    }


    // Show notification
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
        form.parentNode.insertBefore(notification, form);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }

    // Auto-fill base salary and assessment type when staff is selected
    staffSelect.addEventListener('change', function() {
        const staffId = this.value;
        const selectedOption = this.options[this.selectedIndex];
        const salary = selectedOption.getAttribute('data-salary');
        
        if (salary) {
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
        
        // Check for existing payment after a short delay
        setTimeout(function() {
            checkExistingPayment();
        }, 100);
    });

    // Auto-calculate total days when month is selected
    paymentMonthInput.addEventListener('change', function() {
        const monthValue = this.value;
        if (monthValue) {
            const [year, month] = monthValue.split('-');
            const daysInMonth = new Date(year, month, 0).getDate();
            totalDaysInput.value = daysInMonth;
            calculateAmounts();
        }
        // Check for existing payment after a short delay to ensure both fields are set
        setTimeout(function() {
            checkExistingPayment();
        }, 100);
    });

    // Calculate amounts when any input changes
    const assessmentTypeInput = document.getElementById('assessment_type');
    [baseSalaryInput, workingDaysInput, totalDaysInput, overtimeInput, bonusInput, allowanceInput, deductionInput, advanceDeductionInput, assessmentTypeInput].forEach(input => {
        input.addEventListener('input', function() {
            calculateAmounts();
            calculatePayment();
        });
        input.addEventListener('change', function() {
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
        const assessmentType = document.getElementById('assessment_type').value;
        const taxResult = calculateTax(grossAmount, workingDays, totalDays, assessmentType);
        const taxAmount = taxResult.monthlyTax;
        const annualTaxableIncome = taxResult.annualTaxableIncome;
        
        const netAmount = grossAmount - totalDeductions - taxAmount;

        document.getElementById('gross_amount_display').textContent = 'Rs. ' + grossAmount.toFixed(2);
        document.getElementById('total_deductions_display').textContent = 'Rs. ' + totalDeductions.toFixed(2);
        document.getElementById('tax_amount_display').textContent = 'Rs. ' + taxAmount.toFixed(2);
        document.getElementById('tax_info').textContent = 'Annual: Rs. ' + annualTaxableIncome.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('net_amount_display').textContent = 'Rs. ' + netAmount.toFixed(2);
        
        // Recalculate payment after amounts change
        calculatePayment();
    }

    // Nepal Tax Calculation (FY 2080/81)
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
        let paidAmount = 0;
        let paymentPercentage = parseFloat(paymentPercentageInput.value) || 0;
        let paymentAmount = parseFloat(paymentAmountInput.value) || 0;

        // Calculate paid amount based on percentage or amount
        if (paymentPercentage > 0 && paymentPercentage <= 100) {
            paidAmount = (netAmount * paymentPercentage) / 100;
            paymentAmountInput.value = paidAmount.toFixed(2);
        } else if (paymentAmount > 0) {
            paidAmount = Math.min(paymentAmount, netAmount); // Don't allow more than net amount
            if (netAmount > 0) {
                paymentPercentage = (paidAmount / netAmount) * 100;
                paymentPercentageInput.value = paymentPercentage.toFixed(2);
            }
        } else {
            // If status is 'paid', set full amount
            if (statusSelect.value === 'paid') {
                paidAmount = netAmount;
            }
        }

        const balanceAmount = netAmount - paidAmount;

        // Update display
        document.getElementById('calculated_payment_display').textContent = 'Rs. ' + paidAmount.toFixed(2);
        document.getElementById('calculated_balance_display').textContent = 'Rs. ' + balanceAmount.toFixed(2);

        // Update hidden fields
        paidAmountInput.value = paidAmount.toFixed(2);
        balanceAmountInput.value = balanceAmount.toFixed(2);

        // Auto-update status based on payment
        const statusIndicator = document.getElementById('payment_status_indicator');
        const statusBadge = document.getElementById('status_badge');
        
        if (netAmount <= 0) {
            statusSelect.value = 'pending';
            if (statusIndicator) statusIndicator.style.display = 'none';
        } else if (paidAmount >= netAmount - 0.01) { // Allow small rounding differences
            // Fully paid - match with net amount
            statusSelect.value = 'paid';
            paidAmount = netAmount; // Ensure exact match
            balanceAmount = 0;
            paidAmountInput.value = paidAmount.toFixed(2);
            balanceAmountInput.value = 0;
            
            // Show success indicator
            if (statusIndicator) {
                statusIndicator.style.display = 'block';
                statusBadge.textContent = '✓ Fully Paid';
                statusBadge.className = 'px-2 py-1 rounded bg-green-100 text-green-800';
            }
            
            // Update display
            document.getElementById('calculated_payment_display').textContent = 'Rs. ' + paidAmount.toFixed(2);
            document.getElementById('calculated_balance_display').textContent = 'Rs. 0.00';
            document.getElementById('calculated_balance_display').className = 'font-semibold text-green-600';
        } else if (paidAmount > 0) {
            statusSelect.value = 'partial';
            
            // Show partial indicator
            if (statusIndicator) {
                statusIndicator.style.display = 'block';
                const paidPercentage = ((paidAmount / netAmount) * 100).toFixed(1);
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
    
    // Ensure hidden fields are updated before form submission
    const form = document.getElementById('salaryPaymentForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Recalculate payment before submitting to ensure hidden fields are up to date
            calculatePayment();
            
            // Log for debugging
            console.log('Form submitting with:', {
                paid_amount: document.getElementById('paid_amount').value,
                balance_amount: document.getElementById('balance_amount').value,
                status: document.getElementById('status').value,
                payment_amount: document.getElementById('payment_amount').value,
                payment_percentage: document.getElementById('payment_percentage').value,
            });
        });
    }
});
</script>
@endsection

