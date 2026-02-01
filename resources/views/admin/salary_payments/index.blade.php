@extends('admin.layout')

@section('title', 'Salary Payments')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-3xl font-bold text-gray-900">Salary Payments</h1>
    <button onclick="openCreateSalaryPaymentModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition duration-200">
        <i class="bi bi-plus-lg me-1"></i> <span class="salary-payment-btn-text">Add Salary Payment</span>
    </button>
</div>

<div class="mb-4 bg-white shadow-lg rounded-lg p-4">
    <form id="filterForm" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
        <div>
            <label for="filter_staff_id" class="block text-sm font-medium text-gray-700 mb-2">Staff</label>
            <select name="staff_id" id="filter_staff_id" onchange="applyFiltersDebounced()"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="" {{ !request('staff_id') ? 'selected' : '' }}>All Staff</option>
                @foreach($staff as $member)
                    <option value="{{ $member->id }}" {{ request('staff_id') == $member->id ? 'selected' : '' }}>
                        {{ $member->name }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <div>
            <label for="filter_project_id" class="block text-sm font-medium text-gray-700 mb-2">Project</label>
            <select name="project_id" id="filter_project_id" onchange="applyFiltersDebounced()"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="" {{ !request('project_id') ? 'selected' : '' }}>All Projects</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                        {{ $project->name }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <div>
            <label for="filter_status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select name="status" id="filter_status" onchange="applyFiltersDebounced()"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="" {{ !request('status') ? 'selected' : '' }}>All Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>
        
        <div>
            <label for="filter_from_date" class="block text-sm font-medium text-gray-700 mb-2">From Month</label>
            <input type="month" name="from_date" id="filter_from_date" value="{{ request('from_date') ?: '' }}"
                   onchange="applyFiltersDebounced()"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                   pattern="[0-9]{4}-[0-9]{2}">
        </div>
        
        <div>
            <label for="filter_to_date" class="block text-sm font-medium text-gray-700 mb-2">To Month</label>
            <input type="month" name="to_date" id="filter_to_date" value="{{ request('to_date') ?: '' }}"
                   onchange="applyFiltersDebounced()"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                   pattern="[0-9]{4}-[0-9]{2}">
        </div>
        
        <div class="flex items-end">
            <button type="button" onclick="resetFilters()" class="w-full bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                Reset
            </button>
        </div>
    </form>
</div>

<div class="bg-white shadow-lg rounded-lg overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Staff Payment Summary</h2>
        <p class="text-sm text-gray-600 mt-1">Summary of all staff members and their payment status</p>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SN</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Staff</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Payments</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Paid</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Remaining</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Latest Payment</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($staffSummaries as $summary)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $loop->iteration }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $summary['staff_name'] }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500">{{ $summary['position_name'] ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500">{{ $summary['project_name'] ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="text-sm font-medium text-gray-900">{{ $summary['payment_count'] }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <span class="text-sm font-semibold text-green-600">Rs. {{ $summary['total_paid'] }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <span class="text-sm font-semibold text-red-600">Rs. {{ $summary['total_remaining'] }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($summary['latest_payment_id'])
                                <div class="text-sm text-gray-900">{{ $summary['latest_payment_month'] }}</div>
                                <div class="text-xs text-gray-500">{{ $summary['latest_payment_date'] }}</div>
                            @else
                                <span class="text-sm text-gray-400">No payments</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <button onclick="openViewStaffPaymentModal({{ $summary['staff_id'] }})" class="btn btn-sm btn-outline-primary" title="View Details">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if(!isset($staffSummaries) || $staffSummaries->count() === 0)
        <div class="px-6 py-8 text-center text-gray-500">
            No staff members found.
        </div>
    @endif
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteSalaryPaymentConfirmationModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-2xl max-w-md w-full" onclick="event.stopPropagation()">
        <div class="p-6">
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 text-center mb-2">Delete Salary Payment</h3>
            <p class="text-gray-600 text-center mb-6">
                Are you sure you want to delete salary payment for <span class="font-semibold text-gray-900" id="delete-payment-staff-name"></span> (<span id="delete-payment-month"></span>)? This action cannot be undone.
            </p>
            <div class="flex space-x-3">
                <button onclick="closeDeleteSalaryPaymentConfirmation()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors font-medium">
                    Cancel
                </button>
                <button onclick="confirmDeleteSalaryPayment()" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium">
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    @media (max-width: 768px) {
        .salary-payment-btn-text {
            display: none;
        }
    }
</style>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
let currentPaymentId = null;
let deletePaymentId = null;

function handleStaffChange(selectElement) {
    const selectedOption = selectElement.options[selectElement.selectedIndex];
    const salary = selectedOption.getAttribute('data-salary');
    const baseSalaryInput = document.getElementById('base_salary');
    
    if (salary && baseSalaryInput) {
        // Always update base salary when staff changes
        // In create mode, always fill it
        // In edit mode, only fill if empty (preserve existing value if already set)
        if (!currentPaymentId) {
            // Create mode - always update
            baseSalaryInput.value = salary;
            calculateAmounts();
        } else {
            // Edit mode - only update if empty
            if (!baseSalaryInput.value || baseSalaryInput.value === '0') {
                baseSalaryInput.value = salary;
                calculateAmounts();
            }
        }
    }
}

function showNotification(message, type = 'success') {
    const notificationDiv = document.createElement('div');
    notificationDiv.className = `fixed top-4 right-4 px-6 py-4 rounded-lg shadow-2xl z-50 transition-all duration-300 flex items-center gap-3 min-w-[300px] max-w-[500px]`;
    
    if (type === 'success') {
        notificationDiv.className += ' bg-green-500 text-white';
    } else if (type === 'error') {
        notificationDiv.className += ' bg-red-500 text-white';
    } else if (type === 'warning') {
        notificationDiv.className += ' bg-yellow-500 text-white';
    } else {
        notificationDiv.className += ' bg-blue-500 text-white';
    }
    
    notificationDiv.innerHTML = `
        <span>${message}</span>
        <button onclick="this.parentElement.remove()" class="ml-2 text-white hover:text-gray-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    `;
    
    document.body.appendChild(notificationDiv);
    
    setTimeout(() => {
        notificationDiv.style.opacity = '0';
        setTimeout(() => notificationDiv.remove(), 300);
    }, 3000);
}

// Modal management functions
function openCreateSalaryPaymentModal() {
    currentPaymentId = null;
    fetch('{{ route("admin.salary-payments.create") }}', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        buildSalaryPaymentForm(data, null);
        document.getElementById('salaryPaymentModal').classList.remove('hidden');
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error loading form', 'error');
    });
}

function openEditSalaryPaymentModal(paymentId) {
    currentPaymentId = paymentId;
    Promise.all([
        fetch(`/admin/salary-payments/${paymentId}/edit`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        }).then(r => r.json()),
        fetch('{{ route("admin.salary-payments.create") }}', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        }).then(r => r.json())
    ])
    .then(([paymentData, formData]) => {
        buildSalaryPaymentForm(formData, paymentData.payment);
        document.getElementById('salaryPaymentModal').classList.remove('hidden');
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error loading payment data', 'error');
    });
}

function openViewSalaryPaymentModal(paymentId) {
    fetch(`/admin/salary-payments/${paymentId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Store staffId for filtering
        if (data.payment && data.payment.staff_id) {
            window.currentStaffId = data.payment.staff_id;
        }
        buildSalaryPaymentView(data);
        document.getElementById('viewSalaryPaymentModal').classList.remove('hidden');
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error loading payment details', 'error');
    });
}

function openViewStaffPaymentModal(staffId) {
    // Store staffId for filtering
    window.currentStaffId = staffId;
    
    fetch(`/admin/salary-payments/staff/${staffId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        buildSalaryPaymentView(data);
        document.getElementById('viewSalaryPaymentModal').classList.remove('hidden');
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error loading staff payment details', 'error');
    });
}

function closeSalaryPaymentModal() {
    document.getElementById('salaryPaymentModal').classList.add('hidden');
    document.getElementById('salaryPaymentFormContainer').innerHTML = '';
    currentPaymentId = null;
}

function closeViewSalaryPaymentModal() {
    document.getElementById('viewSalaryPaymentModal').classList.add('hidden');
    document.getElementById('viewSalaryPaymentContent').innerHTML = '';
}

function showDeleteSalaryPaymentConfirmation(paymentId, staffName, month) {
    deletePaymentId = paymentId;
    document.getElementById('delete-payment-staff-name').textContent = staffName;
    document.getElementById('delete-payment-month').textContent = month;
    document.getElementById('deleteSalaryPaymentConfirmationModal').classList.remove('hidden');
}

function closeDeleteSalaryPaymentConfirmation() {
    document.getElementById('deleteSalaryPaymentConfirmationModal').classList.add('hidden');
    deletePaymentId = null;
}

function confirmDeleteSalaryPayment() {
    if (!deletePaymentId) return;
    
    const deleteBtn = event.target;
    deleteBtn.disabled = true;
    deleteBtn.textContent = 'Deleting...';
    
    fetch(`/admin/salary-payments/${deletePaymentId}`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeDeleteSalaryPaymentConfirmation();
            showNotification(data.message, 'success');
            deletePaymentRow(deletePaymentId);
        } else {
            showNotification(data.message || 'Failed to delete salary payment', 'error');
            deleteBtn.disabled = false;
            deleteBtn.textContent = 'Delete';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while deleting', 'error');
        deleteBtn.disabled = false;
        deleteBtn.textContent = 'Delete';
    });
}

// Form building and submission
function buildSalaryPaymentForm(data, payment) {
    const isEdit = !!payment;
    const container = document.getElementById('salaryPaymentFormContainer');
    
    container.innerHTML = `
        <form id="salaryPaymentForm" onsubmit="submitSalaryPaymentForm(event)">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="staff_id" class="block text-sm font-medium text-gray-700 mb-2">Staff Member <span class="text-red-500">*</span></label>
                    <select name="staff_id" id="staff_id" onchange="handleStaffChange(this)" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select Staff Member</option>
                        ${data.staff.map(s => `<option value="${s.id}" data-salary="${s.salary}" ${payment && payment.staff_id == s.id ? 'selected' : ''}>${s.name} - ${s.position_name} (Rs. ${parseFloat(s.salary).toFixed(2)})</option>`).join('')}
                    </select>
                    <div class="field-error text-red-600 text-sm mt-1" data-field="staff_id" style="display: none;"></div>
                </div>
                <div>
                    <label for="project_id" class="block text-sm font-medium text-gray-700 mb-2">Project</label>
                    <select name="project_id" id="project_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">None</option>
                        ${data.projects.map(p => `<option value="${p.id}" ${payment && payment.project_id == p.id ? 'selected' : ''}>${p.name}</option>`).join('')}
                    </select>
                </div>
                <div>
                    <label for="payment_month" class="block text-sm font-medium text-gray-700 mb-2">Payment Month <span class="text-red-500">*</span></label>
                    <input type="month" name="payment_month" id="payment_month" value="${payment ? payment.payment_month : '{{ date("Y-m") }}'}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <div class="field-error text-red-600 text-sm mt-1" data-field="payment_month" style="display: none;"></div>
                </div>
                <div>
                    <label for="payment_date" class="block text-sm font-medium text-gray-700 mb-2">Payment Date <span class="text-red-500">*</span></label>
                    <input type="date" name="payment_date" id="payment_date" value="${payment ? payment.payment_date : '{{ date("Y-m-d") }}'}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <div class="field-error text-red-600 text-sm mt-1" data-field="payment_date" style="display: none;"></div>
                </div>
            </div>
            
            <div class="border-t border-gray-200 pt-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Salary Calculation</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
                    <div>
                        <label for="base_salary" class="block text-sm font-medium text-gray-700 mb-2">Base Salary <span class="text-red-500">*</span></label>
                        <input type="number" name="base_salary" id="base_salary" step="0.01" min="0" value="${payment ? payment.base_salary : ''}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <div class="field-error text-red-600 text-sm mt-1" data-field="base_salary" style="display: none;"></div>
                    </div>
                    <div>
                        <label for="working_days" class="block text-sm font-medium text-gray-700 mb-2">Working Days</label>
                        <input type="number" name="working_days" id="working_days" min="1" value="${payment ? payment.working_days || '' : ''}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Leave empty for full month">
                    </div>
                    <div>
                        <label for="total_days" class="block text-sm font-medium text-gray-700 mb-2">Total Days in Month</label>
                        <input type="number" name="total_days" id="total_days" min="1" value="${payment ? payment.total_days || '' : ''}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Auto-calculated">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
                    <div>
                        <label for="overtime_amount" class="block text-sm font-medium text-gray-700 mb-2">Overtime Amount</label>
                        <input type="number" name="overtime_amount" id="overtime_amount" step="0.01" min="0" value="${payment ? payment.overtime_amount || 0 : 0}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="bonus_amount" class="block text-sm font-medium text-gray-700 mb-2">Bonus Amount</label>
                        <input type="number" name="bonus_amount" id="bonus_amount" step="0.01" min="0" value="${payment ? payment.bonus_amount || 0 : 0}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="allowance_amount" class="block text-sm font-medium text-gray-700 mb-2">Allowance Amount</label>
                        <input type="number" name="allowance_amount" id="allowance_amount" step="0.01" min="0" value="${payment ? payment.allowance_amount || 0 : 0}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                    <div>
                        <label for="deduction_amount" class="block text-sm font-medium text-gray-700 mb-2">Deduction Amount</label>
                        <input type="number" name="deduction_amount" id="deduction_amount" step="0.01" min="0" value="${payment ? payment.deduction_amount || 0 : 0}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="advance_deduction" class="block text-sm font-medium text-gray-700 mb-2">Advance Deduction</label>
                        <input type="number" name="advance_deduction" id="advance_deduction" step="0.01" min="0" value="${payment ? payment.advance_deduction || 0 : 0}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                <div class="mb-4">
                    <label for="assessment_type" class="block text-sm font-medium text-gray-700 mb-2">Tax Assessment Type</label>
                    <select name="assessment_type" id="assessment_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="single" ${payment && payment.assessment_type == 'single' ? 'selected' : ''}>Single Assessment</option>
                        <option value="couple" ${payment && payment.assessment_type == 'couple' ? 'selected' : ''}>Couple Assessment</option>
                    </select>
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
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Payment Amount (Optional - for partial payments)</label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="payment_percentage" class="block text-xs text-gray-600 mb-1">Payment Percentage (%)</label>
                            <input type="number" name="payment_percentage" id="payment_percentage" step="0.01" min="0" max="100" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="e.g., 25">
                        </div>
                        <div>
                            <label for="payment_amount" class="block text-xs text-gray-600 mb-1">Or Payment Amount (Rs.)</label>
                            <input type="number" name="payment_amount" id="payment_amount" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="e.g., 5000">
                        </div>
                        <div class="flex items-end">
                            <div class="w-full bg-white p-3 rounded-lg border border-gray-300">
                                <p class="text-xs text-gray-600 mb-1">Total Payment</p>
                                <p class="text-lg font-semibold text-indigo-600" id="calculated_payment_display">Rs. 0.00</p>
                                <p class="text-xs text-gray-500 mt-1">Balance: <span id="calculated_balance_display" class="font-semibold text-red-600">Rs. 0.00</span></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status <span class="text-red-500">*</span></label>
                        <select name="status" id="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="pending" ${payment && payment.status == 'pending' ? 'selected' : ''}>Pending</option>
                            <option value="partial" ${payment && payment.status == 'partial' ? 'selected' : ''}>Partial</option>
                            <option value="paid" ${payment && payment.status == 'paid' ? 'selected' : ''}>Paid</option>
                            <option value="cancelled" ${payment && payment.status == 'cancelled' ? 'selected' : ''}>Cancelled</option>
                        </select>
                        <div class="field-error text-red-600 text-sm mt-1" data-field="status" style="display: none;"></div>
                    </div>
                    <div>
                        <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                        <select name="payment_method" id="payment_method" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select Payment Method</option>
                            ${data.paymentModes.map(pm => `<option value="${pm.name}" ${payment && payment.payment_method == pm.name ? 'selected' : ''}>${pm.name}</option>`).join('')}
                        </select>
                    </div>
                    <div>
                        <label for="bank_account_id" class="block text-sm font-medium text-gray-700 mb-2">Bank Account</label>
                        <select name="bank_account_id" id="bank_account_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">None</option>
                            ${data.bankAccounts.map(ba => `<option value="${ba.id}" ${payment && payment.bank_account_id == ba.id ? 'selected' : ''}>${ba.account_name} - ${ba.bank_name}</option>`).join('')}
                        </select>
                    </div>
                    <div>
                        <label for="transaction_reference" class="block text-sm font-medium text-gray-700 mb-2">Transaction Reference</label>
                        <input type="text" name="transaction_reference" id="transaction_reference" value="${payment ? payment.transaction_reference || '' : ''}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Transaction ID, Cheque Number, etc.">
                    </div>
                    <div class="md:col-span-2">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                        <textarea name="notes" id="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Additional notes...">${payment ? payment.notes || '' : ''}</textarea>
                    </div>
                </div>
                <input type="hidden" name="paid_amount" id="paid_amount" value="${payment ? payment.paid_amount || 0 : 0}">
                <input type="hidden" name="balance_amount" id="balance_amount" value="${payment ? payment.balance_amount || 0 : 0}">
            </div>
            
            <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                <button type="button" onclick="closeSalaryPaymentModal()" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700" id="submitSalaryPaymentBtn">${isEdit ? 'Update' : 'Create'} Salary Payment</button>
            </div>
        </form>
    `;
    
    // Initialize calculations after form is built
    setTimeout(() => {
        initializeSalaryPaymentCalculations();
        if (payment) {
            // Trigger calculation for edit mode
            calculateAmounts();
            calculatePayment();
        }
    }, 100);
}

function initializeSalaryPaymentCalculations() {
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
    const assessmentTypeInput = document.getElementById('assessment_type');
    const paymentPercentageInput = document.getElementById('payment_percentage');
    const paymentAmountInput = document.getElementById('payment_amount');
    const statusSelect = document.getElementById('status');
    
    // Event listener is now handled by inline onchange in the HTML
    
    // Auto-calculate total days when month is selected
    if (paymentMonthInput) {
        paymentMonthInput.addEventListener('change', function() {
            const monthValue = this.value;
            if (monthValue && totalDaysInput) {
                const [year, month] = monthValue.split('-');
                const daysInMonth = new Date(year, month, 0).getDate();
                totalDaysInput.value = daysInMonth;
                calculateAmounts();
            }
        });
    }
    
    // Calculate amounts when inputs change
    [baseSalaryInput, workingDaysInput, totalDaysInput, overtimeInput, bonusInput, allowanceInput, deductionInput, advanceDeductionInput, assessmentTypeInput].forEach(input => {
        if (input) {
            input.addEventListener('input', function() {
                calculateAmounts();
                calculatePayment();
            });
            input.addEventListener('change', function() {
                calculateAmounts();
                calculatePayment();
            });
        }
    });
    
    // Calculate payment when percentage or amount changes
    if (paymentPercentageInput) {
        paymentPercentageInput.addEventListener('input', function() {
            if (this.value && paymentAmountInput) {
                paymentAmountInput.value = '';
            }
            calculatePayment();
        });
    }
    
    if (paymentAmountInput) {
        paymentAmountInput.addEventListener('input', function() {
            if (this.value && paymentPercentageInput) {
                paymentPercentageInput.value = '';
            }
            calculatePayment();
        });
    }
    
    // Update payment when status changes
    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            if (this.value === 'paid') {
                const netAmount = parseFloat(document.getElementById('net_amount_display')?.textContent.replace('Rs. ', '').replace(',', '') || 0);
                if (paymentAmountInput) paymentAmountInput.value = netAmount.toFixed(2);
                if (paymentPercentageInput) paymentPercentageInput.value = '100';
                calculatePayment();
            } else if (this.value === 'pending') {
                if (paymentAmountInput) paymentAmountInput.value = '';
                if (paymentPercentageInput) paymentPercentageInput.value = '';
                calculatePayment();
            }
        });
    }
    
    // Initial calculation
    calculateAmounts();
}

function calculateAmounts() {
    const baseSalaryInput = document.getElementById('base_salary');
    const workingDaysInput = document.getElementById('working_days');
    const totalDaysInput = document.getElementById('total_days');
    const overtimeInput = document.getElementById('overtime_amount');
    const bonusInput = document.getElementById('bonus_amount');
    const allowanceInput = document.getElementById('allowance_amount');
    const deductionInput = document.getElementById('deduction_amount');
    const advanceDeductionInput = document.getElementById('advance_deduction');
    const assessmentTypeInput = document.getElementById('assessment_type');
    
    if (!baseSalaryInput) return;
    
    let baseSalary = parseFloat(baseSalaryInput.value) || 0;
    const workingDays = parseFloat(workingDaysInput?.value || 0);
    const totalDays = parseFloat(totalDaysInput?.value || 0);
    
    // If partial month, calculate prorated salary
    if (workingDays && totalDays) {
        baseSalary = (baseSalary / totalDays) * workingDays;
    }
    
    const overtime = parseFloat(overtimeInput?.value || 0);
    const bonus = parseFloat(bonusInput?.value || 0);
    const allowance = parseFloat(allowanceInput?.value || 0);
    const deduction = parseFloat(deductionInput?.value || 0);
    const advanceDeduction = parseFloat(advanceDeductionInput?.value || 0);
    
    const grossAmount = baseSalary + overtime + bonus + allowance;
    const totalDeductions = deduction + advanceDeduction;
    
    // Calculate tax
    const assessmentType = assessmentTypeInput?.value || 'single';
    const taxResult = calculateTax(grossAmount, workingDays, totalDays, assessmentType);
    const taxAmount = taxResult.monthlyTax;
    const annualTaxableIncome = taxResult.annualTaxableIncome;
    
    const netAmount = grossAmount - totalDeductions - taxAmount;
    
    const grossDisplay = document.getElementById('gross_amount_display');
    const deductionsDisplay = document.getElementById('total_deductions_display');
    const taxDisplay = document.getElementById('tax_amount_display');
    const taxInfo = document.getElementById('tax_info');
    const netDisplay = document.getElementById('net_amount_display');
    
    if (grossDisplay) grossDisplay.textContent = 'Rs. ' + grossAmount.toFixed(2);
    if (deductionsDisplay) deductionsDisplay.textContent = 'Rs. ' + totalDeductions.toFixed(2);
    if (taxDisplay) taxDisplay.textContent = 'Rs. ' + taxAmount.toFixed(2);
    if (taxInfo) taxInfo.textContent = 'Annual: Rs. ' + annualTaxableIncome.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    if (netDisplay) netDisplay.textContent = 'Rs. ' + netAmount.toFixed(2);
    
    calculatePayment();
}

function calculateTax(monthlyGross, workingDays, totalDays, assessmentType) {
    let fullMonthGross = monthlyGross;
    if (workingDays && totalDays && totalDays > 0) {
        fullMonthGross = (monthlyGross / workingDays) * totalDays;
    }
    
    const annualTaxableIncome = fullMonthGross * 12;
    
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
    
    let monthlyTax = totalTax / 12;
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
    const netAmount = parseFloat(document.getElementById('net_amount_display')?.textContent.replace('Rs. ', '').replace(',', '') || 0);
    const paymentPercentageInput = document.getElementById('payment_percentage');
    const paymentAmountInput = document.getElementById('payment_amount');
    const statusSelect = document.getElementById('status');
    const paidAmountInput = document.getElementById('paid_amount');
    const balanceAmountInput = document.getElementById('balance_amount');
    
    let paidAmount = 0;
    let paymentPercentage = parseFloat(paymentPercentageInput?.value || 0);
    let paymentAmount = parseFloat(paymentAmountInput?.value || 0);
    
    if (paymentPercentage > 0 && paymentPercentage <= 100) {
        paidAmount = (netAmount * paymentPercentage) / 100;
        if (paymentAmountInput) paymentAmountInput.value = paidAmount.toFixed(2);
    } else if (paymentAmount > 0) {
        paidAmount = Math.min(paymentAmount, netAmount);
        if (netAmount > 0 && paymentPercentageInput) {
            paymentPercentage = (paidAmount / netAmount) * 100;
            paymentPercentageInput.value = paymentPercentage.toFixed(2);
        }
    } else if (statusSelect?.value === 'paid') {
        paidAmount = netAmount;
    }
    
    const balanceAmount = netAmount - paidAmount;
    
    const paymentDisplay = document.getElementById('calculated_payment_display');
    const balanceDisplay = document.getElementById('calculated_balance_display');
    
    if (paymentDisplay) paymentDisplay.textContent = 'Rs. ' + paidAmount.toFixed(2);
    if (balanceDisplay) {
        balanceDisplay.textContent = 'Rs. ' + balanceAmount.toFixed(2);
        balanceDisplay.className = balanceAmount > 0 ? 'font-semibold text-red-600' : 'font-semibold text-green-600';
    }
    
    if (paidAmountInput) paidAmountInput.value = paidAmount.toFixed(2);
    if (balanceAmountInput) balanceAmountInput.value = balanceAmount.toFixed(2);
    
    // Auto-update status
    if (statusSelect && netAmount > 0) {
        if (paidAmount >= netAmount - 0.01) {
            statusSelect.value = 'paid';
            if (paidAmountInput) paidAmountInput.value = netAmount.toFixed(2);
            if (balanceAmountInput) balanceAmountInput.value = '0';
        } else if (paidAmount > 0) {
            statusSelect.value = 'partial';
        } else {
            statusSelect.value = 'pending';
        }
    }
}

function submitSalaryPaymentForm(e) {
    e.preventDefault();
    
    const form = document.getElementById('salaryPaymentForm');
    const formData = new FormData(form);
    const submitBtn = document.getElementById('submitSalaryPaymentBtn');
    const originalText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Processing...';
    
    const url = currentPaymentId 
        ? `/admin/salary-payments/${currentPaymentId}`
        : '/admin/salary-payments';
    
    if (currentPaymentId) {
        formData.append('_method', 'PUT');
    }
    
    // Recalculate before submitting
    calculatePayment();
    
    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            closeSalaryPaymentModal();
            
            // Reload page to refresh staff summary
            window.location.reload();
        } else {
            if (data.errors) {
                Object.keys(data.errors).forEach(field => {
                    const errorEl = document.querySelector(`.field-error[data-field="${field}"]`);
                    if (errorEl) {
                        errorEl.textContent = data.errors[field][0];
                        errorEl.style.display = 'block';
                    }
                });
            }
            showNotification(data.message || 'Validation failed', 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred', 'error');
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
}

function buildSalaryPaymentView(data, skipInitialFilter = false) {
    const payment = data.payment;
    let monthlyPayments = data.monthly_payments || [];
    const overallTotalPaid = data.overall_total_paid || '0.00';
    const overallTotalRemaining = data.overall_total_remaining || '0.00';
    
    // Store original data for filtering
    window.salaryPaymentViewData = {
        originalMonthlyPayments: JSON.parse(JSON.stringify(monthlyPayments)),
        originalTotalPaid: overallTotalPaid,
        originalTotalRemaining: overallTotalRemaining
    };
    
    const container = document.getElementById('viewSalaryPaymentContent');
    
    function renderPayments(filteredPayments = monthlyPayments) {
        let monthlyPaymentsHtml = '';
        if (filteredPayments.length > 0) {
            monthlyPaymentsHtml = filteredPayments.map(monthData => {
            if (!monthData || !monthData.payments) return '';
            let paymentsHtml = monthData.payments.map(p => {
                const statusClass = p.status === 'paid' ? 'bg-green-100 text-green-800' :
                                  p.status === 'partial' ? 'bg-blue-100 text-blue-800' :
                                  p.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                  'bg-red-100 text-red-800';
                return `
                    <tr class="border-b border-gray-200">
                        <td class="px-4 py-2 text-sm text-gray-600">${p.payment_date}</td>
                        <td class="px-4 py-2 text-sm text-gray-900">${p.project_name || '-'}</td>
                        <td class="px-4 py-2 text-sm text-right font-medium text-gray-900">Rs. ${p.net_amount}</td>
                        <td class="px-4 py-2 text-sm text-right font-medium text-green-600">Rs. ${p.paid_amount}</td>
                        <td class="px-4 py-2 text-sm text-right font-medium text-red-600">Rs. ${p.balance_amount}</td>
                        <td class="px-4 py-2 text-sm">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full ${statusClass}">
                                ${p.status.charAt(0).toUpperCase() + p.status.slice(1)}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-sm text-center">
                            <button onclick="closeViewSalaryPaymentModal(); openEditSalaryPaymentModal(${p.id})" class="btn btn-sm btn-outline-warning" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
            
            return `
                <div class="mb-6 bg-gray-50 rounded-lg p-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">${monthData.month}</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white rounded-lg overflow-hidden">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Payment Date</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Project</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-700 uppercase">Net Amount</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-700 uppercase">Paid</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-700 uppercase">Remaining</th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-700 uppercase">Status</th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-700 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${paymentsHtml}
                                <tr class="bg-gray-100 font-semibold">
                                    <td colspan="2" class="px-4 py-2 text-sm text-gray-900">Month Total:</td>
                                    <td class="px-4 py-2 text-sm text-right text-gray-900">Rs. ${monthData.total_net}</td>
                                    <td class="px-4 py-2 text-sm text-right text-green-600">Rs. ${monthData.total_paid}</td>
                                    <td class="px-4 py-2 text-sm text-right text-red-600">Rs. ${monthData.total_remaining}</td>
                                    <td class="px-4 py-2"></td>
                                    <td class="px-4 py-2"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
            }).join('');
        } else {
            monthlyPaymentsHtml = '<div class="text-center text-gray-500 py-8">No payment history found for the selected date range.</div>';
        }
        
        // Calculate filtered totals
        let filteredTotalPaid = 0;
        let filteredTotalRemaining = 0;
        filteredPayments.forEach(monthData => {
            if (monthData && monthData.payments) {
                monthData.payments.forEach(p => {
                    const paid = parseFloat(String(p.paid_amount || '0').replace(/,/g, '')) || 0;
                    const remaining = parseFloat(String(p.balance_amount || '0').replace(/,/g, '')) || 0;
                    filteredTotalPaid += paid;
                    filteredTotalRemaining += remaining;
                });
            }
        });
        
        const filteredTotalPaidFormatted = filteredTotalPaid.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        const filteredTotalRemainingFormatted = filteredTotalRemaining.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        
        container.innerHTML = `
            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white shadow rounded-lg p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Staff Information</h2>
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Staff Member</dt>
                                <dd class="mt-1 text-sm font-semibold text-gray-900">${payment.staff_name}</dd>
                                ${payment.staff_position ? `<dd class="text-sm text-gray-600">${payment.staff_position}</dd>` : ''}
                            </div>
                        </dl>
                    </div>
                    <div class="bg-white shadow rounded-lg p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Payment Summary</h2>
                        <dl class="space-y-4">
                            <div class="border-b pb-3">
                                <dt class="text-sm font-medium text-gray-500">Total Payment Made</dt>
                                <dd class="mt-1 text-xl font-bold text-green-600" id="filteredTotalPaid">Rs. ${filteredTotalPaidFormatted}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Total Remaining Payment</dt>
                                <dd class="mt-1 text-xl font-bold text-red-600" id="filteredTotalRemaining">Rs. ${filteredTotalRemainingFormatted}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
                
                <div class="bg-white shadow rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold text-gray-900">Payment History by Month</h2>
                    </div>
                    <div class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">From Month</label>
                            <input type="month" id="filterFromDate" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" pattern="[0-9]{4}-[0-9]{2}" onchange="console.log('From month changed:', this.value); if(typeof filterSalaryPaymentsByDate === 'function') { filterSalaryPaymentsByDate(); } else { console.error('filterSalaryPaymentsByDate function not found'); }">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">To Month</label>
                            <input type="month" id="filterToDate" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" value="${new Date().toISOString().slice(0, 7)}" pattern="[0-9]{4}-[0-9]{2}" onchange="console.log('To month changed:', this.value); if(typeof filterSalaryPaymentsByDate === 'function') { filterSalaryPaymentsByDate(); } else { console.error('filterSalaryPaymentsByDate function not found'); }">
                        </div>
                        <div class="flex items-end">
                            <button type="button" onclick="resetDateFilter()" class="w-full px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                                Reset Filter
                            </button>
                        </div>
                    </div>
                    <div id="filteredPaymentsContainer">
                        ${monthlyPaymentsHtml}
                    </div>
                </div>
            </div>
        `;
    }
    
    renderPayments(monthlyPayments);
    
    // Don't trigger initial filter - let user manually filter if needed
    // The default "To Date" is just for display, not for automatic filtering
}

function updateFilteredPaymentsView(data) {
    // Preserve current date filter values
    const fromDateEl = document.getElementById('filterFromDate');
    const toDateEl = document.getElementById('filterToDate');
    const currentFromDate = fromDateEl ? fromDateEl.value : '';
    const currentToDate = toDateEl ? toDateEl.value : '';
    
    const monthlyPayments = data.monthly_payments || [];
    const overallTotalPaid = data.overall_total_paid || '0.00';
    const overallTotalRemaining = data.overall_total_remaining || '0.00';
    
    // Generate HTML for filtered payments
    let monthlyPaymentsHtml = '';
    if (monthlyPayments.length > 0) {
        monthlyPaymentsHtml = monthlyPayments.map(monthData => {
            if (!monthData || !monthData.payments) return '';
            let paymentsHtml = monthData.payments.map(p => {
                const statusClass = p.status === 'paid' ? 'bg-green-100 text-green-800' :
                                  p.status === 'partial' ? 'bg-blue-100 text-blue-800' :
                                  p.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                  'bg-red-100 text-red-800';
                return `
                    <tr class="border-b border-gray-200">
                        <td class="px-4 py-2 text-sm text-gray-600">${p.payment_date}</td>
                        <td class="px-4 py-2 text-sm text-gray-900">${p.project_name || '-'}</td>
                        <td class="px-4 py-2 text-sm text-right font-medium text-gray-900">Rs. ${p.net_amount}</td>
                        <td class="px-4 py-2 text-sm text-right font-medium text-green-600">Rs. ${p.paid_amount}</td>
                        <td class="px-4 py-2 text-sm text-right font-medium text-red-600">Rs. ${p.balance_amount}</td>
                        <td class="px-4 py-2 text-sm">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full ${statusClass}">
                                ${p.status.charAt(0).toUpperCase() + p.status.slice(1)}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-sm text-center">
                            <button onclick="closeViewSalaryPaymentModal(); openEditSalaryPaymentModal(${p.id})" class="btn btn-sm btn-outline-warning" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
            
            return `
                <div class="mb-6 bg-gray-50 rounded-lg p-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">${monthData.month}</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white rounded-lg overflow-hidden">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Payment Date</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Project</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-700 uppercase">Net Amount</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-700 uppercase">Paid</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-700 uppercase">Remaining</th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-700 uppercase">Status</th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-700 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${paymentsHtml}
                                <tr class="bg-gray-100 font-semibold">
                                    <td colspan="2" class="px-4 py-2 text-sm text-gray-900">Month Total:</td>
                                    <td class="px-4 py-2 text-sm text-right text-gray-900">Rs. ${monthData.total_net}</td>
                                    <td class="px-4 py-2 text-sm text-right text-green-600">Rs. ${monthData.total_paid}</td>
                                    <td class="px-4 py-2 text-sm text-right text-red-600">Rs. ${monthData.total_remaining}</td>
                                    <td class="px-4 py-2"></td>
                                    <td class="px-4 py-2"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
        }).join('');
    } else {
        monthlyPaymentsHtml = '<div class="text-center text-gray-500 py-8">No payment history found for the selected date range.</div>';
    }
    
    // Calculate totals from filtered data
    let filteredTotalPaid = 0;
    let filteredTotalRemaining = 0;
    monthlyPayments.forEach(monthData => {
        if (monthData && monthData.payments) {
            monthData.payments.forEach(p => {
                const paid = parseFloat(String(p.paid_amount || '0').replace(/,/g, '')) || 0;
                const remaining = parseFloat(String(p.balance_amount || '0').replace(/,/g, '')) || 0;
                filteredTotalPaid += paid;
                filteredTotalRemaining += remaining;
            });
        }
    });
    
    const filteredTotalPaidFormatted = filteredTotalPaid.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    const filteredTotalRemainingFormatted = filteredTotalRemaining.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    
    // Update only the payments container and totals (preserve date inputs)
    const paymentsContainer = document.getElementById('filteredPaymentsContainer');
    const totalPaidEl = document.getElementById('filteredTotalPaid');
    const totalRemainingEl = document.getElementById('filteredTotalRemaining');
    
    if (paymentsContainer) {
        paymentsContainer.innerHTML = monthlyPaymentsHtml;
    }
    if (totalPaidEl) {
        totalPaidEl.textContent = `Rs. ${filteredTotalPaidFormatted}`;
    }
    if (totalRemainingEl) {
        totalRemainingEl.textContent = `Rs. ${filteredTotalRemainingFormatted}`;
    }
    
    // Restore date filter values (in case they were lost)
    if (fromDateEl && currentFromDate) {
        fromDateEl.value = currentFromDate;
    }
    if (toDateEl && currentToDate) {
        toDateEl.value = currentToDate;
    }
}

// Debounce timer for filter
let filterDebounceTimer = null;
let isFiltering = false;

function filterSalaryPaymentsByDate() {
    // Clear existing debounce timer
    if (filterDebounceTimer) {
        clearTimeout(filterDebounceTimer);
    }
    
    // Prevent concurrent filter requests
    if (isFiltering) {
        console.log('Filter already in progress, skipping...');
        return;
    }
    
        // Debounce the filter call
        filterDebounceTimer = setTimeout(() => {
            const fromDateEl = document.getElementById('filterFromDate');
            const toDateEl = document.getElementById('filterToDate');
            
            if (!fromDateEl || !toDateEl) {
                console.error('Date filter elements not found');
                return;
            }
            
            const fromDate = fromDateEl.value;
            const toDate = toDateEl.value;
            
            console.log('Filter triggered with dates:', { fromDate, toDate });
            
            // Get staffId from stored value
            const staffId = window.currentStaffId;
            
            if (!staffId) {
                console.error('Staff ID not found');
                return;
            }
            
            // Set filtering flag
            isFiltering = true;
            
            // Show loading state
            const paymentsContainer = document.getElementById('filteredPaymentsContainer');
            if (paymentsContainer) {
                paymentsContainer.innerHTML = '<div class="text-center text-gray-500 py-8">Loading...</div>';
            }
            
            // Build query string
            const params = new URLSearchParams();
            if (fromDate) {
                params.append('from_date', fromDate);
                console.log('Adding from_date:', fromDate);
            }
            if (toDate) {
                params.append('to_date', toDate);
                console.log('Adding to_date:', toDate);
            }
            
            const url = `/admin/salary-payments/staff/${staffId}?${params.toString()}`;
            console.log('Fetching from URL:', url);
            
            // Fetch filtered data from server
            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Filter response data:', data);
                console.log('Monthly payments count:', data.monthly_payments ? data.monthly_payments.length : 0);
                
                // Update only the payments container and totals, not the entire modal
                updateFilteredPaymentsView(data);
                
                console.log('Filter applied successfully:', { fromDate, toDate });
                isFiltering = false;
            })
            .catch(error => {
                console.error('Error filtering payments:', error);
                showNotification('Error filtering payment data', 'error');
                if (paymentsContainer) {
                    paymentsContainer.innerHTML = '<div class="text-center text-red-500 py-8">Error loading filtered data</div>';
                }
                isFiltering = false;
            });
        }, 300); // 300ms debounce
}

function resetDateFilter() {
    const fromDateEl = document.getElementById('filterFromDate');
    const toDateEl = document.getElementById('filterToDate');
    if (fromDateEl) fromDateEl.value = '';
    if (toDateEl) {
        // Reset to current month
        toDateEl.value = new Date().toISOString().slice(0, 7);
    }
    filterSalaryPaymentsByDate();
}

function addPaymentRow(payment) {
    const tbody = document.querySelector('table tbody');
    const row = document.createElement('tr');
    row.setAttribute('data-payment-id', payment.id);
    
    const statusClass = payment.status === 'paid' ? 'bg-green-100 text-green-800' :
                        payment.status === 'partial' ? 'bg-blue-100 text-blue-800' :
                        payment.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                        'bg-red-100 text-red-800';
    
    row.innerHTML = `
        <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm font-medium text-gray-900">${payment.staff_name}</div>
            ${payment.project_name ? `<div class="text-sm text-gray-500">${payment.project_name}</div>` : ''}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${payment.payment_month_name}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rs. ${payment.base_salary}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rs. ${payment.gross_amount}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-orange-600 font-medium">Rs. ${payment.tax_amount}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">Rs. ${payment.net_amount}</td>
        <td class="px-6 py-4 whitespace-nowrap">
            <span class="px-2 py-1 text-xs font-semibold rounded-full ${statusClass}">${payment.status.charAt(0).toUpperCase() + payment.status.slice(1)}</span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${payment.payment_date}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
            <div class="d-flex gap-1 text-nowrap">
                <button onclick="openViewSalaryPaymentModal(${payment.id})" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-eye me-1"></i> View
                </button>
                <button onclick="openEditSalaryPaymentModal(${payment.id})" class="btn btn-sm btn-outline-warning">
                    <i class="bi bi-pencil me-1"></i> Edit
                </button>
                <button onclick="showDeleteSalaryPaymentConfirmation(${payment.id}, '${payment.staff_name}', '${payment.payment_month_name}')" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-trash me-1"></i> Delete
                </button>
            </div>
        </td>
    `;
    
    tbody.insertBefore(row, tbody.firstChild);
}

function updatePaymentRow(payment) {
    const row = document.querySelector(`tr[data-payment-id="${payment.id}"]`);
    if (!row) return;
    
    const statusClass = payment.status === 'paid' ? 'bg-green-100 text-green-800' :
                        payment.status === 'partial' ? 'bg-blue-100 text-blue-800' :
                        payment.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                        'bg-red-100 text-red-800';
    
    row.innerHTML = `
        <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm font-medium text-gray-900">${payment.staff_name}</div>
            <div class="text-sm text-gray-500">${payment.project_name}</div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${payment.payment_month_name}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rs. ${payment.base_salary}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rs. ${payment.gross_amount}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-orange-600 font-medium">Rs. ${payment.tax_amount}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">Rs. ${payment.net_amount}</td>
        <td class="px-6 py-4 whitespace-nowrap">
            <span class="px-2 py-1 text-xs font-semibold rounded-full ${statusClass}">${payment.status.charAt(0).toUpperCase() + payment.status.slice(1)}</span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${payment.payment_date}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
            <div class="d-flex gap-1 text-nowrap">
                <button onclick="openViewSalaryPaymentModal(${payment.id})" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-eye me-1"></i> View
                </button>
                <button onclick="openEditSalaryPaymentModal(${payment.id})" class="btn btn-sm btn-outline-warning">
                    <i class="bi bi-pencil me-1"></i> Edit
                </button>
                <button onclick="showDeleteSalaryPaymentConfirmation(${payment.id}, '${payment.staff_name}', '${payment.payment_month_name}')" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-trash me-1"></i> Delete
                </button>
            </div>
        </td>
    `;
}

function deletePaymentRow(paymentId) {
    const row = document.querySelector(`tr[data-payment-id="${paymentId}"]`);
    if (row) {
        row.style.transition = 'opacity 0.3s';
        row.style.opacity = '0';
        setTimeout(() => row.remove(), 300);
    }
}

// Modal event listeners
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        if (!document.getElementById('salaryPaymentModal').classList.contains('hidden')) {
            closeSalaryPaymentModal();
        }
        if (!document.getElementById('viewSalaryPaymentModal').classList.contains('hidden')) {
            closeViewSalaryPaymentModal();
        }
        if (!document.getElementById('deleteSalaryPaymentConfirmationModal').classList.contains('hidden')) {
            closeDeleteSalaryPaymentConfirmation();
        }
    }
});

document.getElementById('deleteSalaryPaymentConfirmationModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeDeleteSalaryPaymentConfirmation();
});

// Filter functions
let currentSalaryPage = 1;
let isLoadingSalaryPayments = false;

// Debounced filter function for performance
const applyFiltersDebounced = window.debounce ? window.debounce(applyFilters, 300) : applyFilters;

function applyFilters(page = 1) {
    if (isLoadingSalaryPayments) return;
    
    isLoadingSalaryPayments = true;
    currentSalaryPage = page;
    
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams();
    
    // Add form values to params
    for (const [key, value] of formData.entries()) {
        if (value) {
            params.append(key, value);
        }
    }
    
    // Add page parameter
    if (page > 1) {
        params.append('page', page);
    }
    
    // Reload page with filters
    window.location.href = `{{ route('admin.salary-payments.index') }}?${params.toString()}`;
}

function updateSalaryPaymentsTable(salaryPayments) {
    const tbody = document.getElementById('salaryPaymentsTableBody');
    
    if (!salaryPayments || salaryPayments.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="10" class="px-6 py-4 text-center text-gray-500">
                    No salary payments found.
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = salaryPayments.map(payment => {
        const staffName = payment.staff_name || 'N/A';
        const escapedStaffName = staffName.replace(/'/g, "\\'");
        const projectName = payment.project_name ? `<div class="text-sm text-gray-500">${payment.project_name}</div>` : '';
        const taxInfo = parseFloat(payment.tax_amount) > 0 ? `<div class="text-xs text-gray-500 mt-1">${payment.assessment_type}</div>` : '';
        const partialInfo = payment.paid_amount ? `<div class="text-xs text-gray-500 mt-1">Paid: Rs. ${payment.paid_amount}</div>` : '';
        
        return `
            <tr data-payment-id="${payment.id}">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">${staffName}</div>
                    ${projectName}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    ${payment.payment_month_name}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    Rs. ${payment.base_salary}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    Rs. ${payment.gross_amount}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-orange-600 font-medium">
                    Rs. ${payment.tax_amount}
                    ${taxInfo}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                    Rs. ${payment.net_amount}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 text-xs font-semibold rounded-full ${payment.status_class}">
                        ${payment.status}
                    </span>
                    ${partialInfo}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    ${payment.payment_date}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="d-flex gap-1 text-nowrap">
                        <button onclick="openViewSalaryPaymentModal(${payment.id})" class="btn btn-sm btn-outline-primary" title="View">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button onclick="openEditSalaryPaymentModal(${payment.id})" class="btn btn-sm btn-outline-warning" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button onclick="showDeleteSalaryPaymentConfirmation(${payment.id}, '${escapedStaffName}', '${payment.payment_month_name.replace(/'/g, "\\'")}')" class="btn btn-sm btn-outline-danger" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

function updateSalaryPagination(paginationHtml) {
    const paginationContainer = document.getElementById('salaryPaymentsPagination');
    if (!paginationContainer) {
        console.error('Pagination container not found');
        return;
    }
    
    if (paginationHtml && paginationHtml.trim() !== '') {
        paginationContainer.innerHTML = paginationHtml;
        
        // Attach click handlers to pagination links
        setTimeout(() => {
            paginationContainer.querySelectorAll('a[href*="page="]').forEach(link => {
                // Remove existing listeners to avoid duplicates
                const newLink = link.cloneNode(true);
                link.parentNode.replaceChild(newLink, link);
                
                newLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const url = new URL(this.href);
                    const page = url.searchParams.get('page') || 1;
                    applyFilters(parseInt(page));
                });
            });
        }, 100);
    } else {
        paginationContainer.innerHTML = '';
    }
}

function updateSalaryURL(params) {
    const newURL = window.location.pathname + (params ? '?' + params : '');
    window.history.pushState({path: newURL}, '', newURL);
}

function resetFilters() {
    document.getElementById('filter_staff_id').value = '';
    document.getElementById('filter_project_id').value = '';
    document.getElementById('filter_status').value = '';
    document.getElementById('filter_from_date').value = '';
    document.getElementById('filter_to_date').value = '';
    applyFilters();
}

// Load pagination handlers on page load
document.addEventListener('DOMContentLoaded', function() {
    // Attach click handlers to existing pagination links
    const paginationContainer = document.getElementById('salaryPaymentsPagination');
    if (paginationContainer) {
        paginationContainer.querySelectorAll('a[href*="page="]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const url = new URL(this.href);
                const page = url.searchParams.get('page') || 1;
                applyFilters(parseInt(page));
            });
        });
    }
    
    // Handle browser back/forward buttons
    window.addEventListener('popstate', function(e) {
        if (e.state && e.state.path) {
            const url = new URL(window.location.href);
            const page = url.searchParams.get('page') || 1;
            applyFilters(parseInt(page));
        }
    });
});

</script>

<!-- Salary Payment Modal -->
<div id="salaryPaymentModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-lg shadow-2xl max-w-4xl w-full my-8 relative" onclick="event.stopPropagation()">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 rounded-t-lg z-10">
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-900" id="salaryPaymentModalTitle">Add Salary Payment</h2>
                <button onclick="closeSalaryPaymentModal()" class="text-gray-400 hover:text-gray-600 focus:outline-none" type="button">
                    <i class="bi bi-x-lg text-2xl"></i>
                </button>
            </div>
        </div>
        <div class="p-6 overflow-y-auto max-h-[calc(100vh-200px)]">
            <div id="salaryPaymentFormContainer"></div>
        </div>
    </div>
</div>

<!-- View Salary Payment Modal -->
<div id="viewSalaryPaymentModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-lg shadow-2xl max-w-4xl w-full my-8 relative" onclick="event.stopPropagation()">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 rounded-t-lg z-10">
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-900">Salary Payment Details</h2>
                <button onclick="closeViewSalaryPaymentModal()" class="text-gray-400 hover:text-gray-600 focus:outline-none" type="button">
                    <i class="bi bi-x-lg text-2xl"></i>
                </button>
            </div>
        </div>
        <div class="p-6 overflow-y-auto max-h-[calc(100vh-200px)]">
            <div id="viewSalaryPaymentContent"></div>
            <div class="flex justify-end mt-6">
                <button onclick="closeViewSalaryPaymentModal()" class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

