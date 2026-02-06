@extends('admin.layout')

@section('title', 'Income Records')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-3xl font-bold text-gray-900">Income Records</h1>
    <div class="flex gap-2">
        <a href="{{ route('admin.incomes.export') }}" id="incomesExportLink" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center gap-2" title="Export with current filters">
            <i class="bi bi-file-earmark-excel me-0 me-md-1"></i>
            <span class="d-none d-md-inline income-btn-text">Export Excel</span>
        </a>
        <button onclick="openCreateIncomeModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center gap-2">
            <i class="bi bi-plus-circle"></i>
            <span class="income-btn-text">Add New Income</span>
        </button>
    </div>
</div>

<div class="mb-4 bg-white shadow-lg rounded-lg p-4">
    <form method="GET" action="{{ route('admin.incomes.index') }}" id="filterForm" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-7 gap-4">
        @if($projects->count() > 0)
        <div>
            <label for="project_id" class="block text-sm font-medium text-gray-700 mb-2">Project</label>
            <select name="project_id" id="project_id" onchange="applyFilters()"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Projects</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                        {{ $project->name }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif
        
        <div>
            <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
            <select name="category_id" id="category_id" onchange="loadSubcategoriesAndFilter()"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <div>
            <label for="subcategory_id" class="block text-sm font-medium text-gray-700 mb-2">Subcategory</label>
            <select name="subcategory_id" id="subcategory_id" onchange="applyFilters()"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Subcategories</option>
                @foreach($subcategories as $subcategory)
                    <option value="{{ $subcategory->id }}" {{ request('subcategory_id') == $subcategory->id ? 'selected' : '' }}>
                        {{ $subcategory->name }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <div>
            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
            <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" onchange="applyFilters()"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        
        <div>
            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
            <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" onchange="applyFilters()"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        
        <div>
            <label for="keyword" class="block text-sm font-medium text-gray-700 mb-2">Keyword</label>
            <input type="text" name="keyword" id="keyword" value="{{ request('keyword') }}" placeholder="Source, description..."
                   onkeyup="applyFilters()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        
        <div class="flex items-end">
            <button type="button" onclick="clearFilters()" class="w-full px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition duration-200">
                Clear Filters
            </button>
        </div>
    </form>
</div>

<div class="bg-white shadow-lg rounded-lg overflow-hidden">
    <div id="incomes-loading" class="hidden p-8 text-center">
        <svg class="animate-spin h-8 w-8 text-indigo-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p class="mt-2 text-gray-600">Loading incomes...</p>
    </div>
    <div id="incomes-table-container">
        <div class="overflow-x-auto -mx-4 sm:mx-0">
            <div class="inline-block min-w-full align-middle">
                <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SN</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200" id="incomes-tbody">
                @forelse($incomes as $income)
                    <tr data-income-id="{{ $income->id }}">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ ($incomes->currentPage() - 1) * $incomes->perPage() + $loop->iteration }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $income->date->format('M d, Y') }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $income->source }}</div>
                            @if($income->description)
                                <div class="text-sm text-gray-500">{{ Str::limit($income->description, 30) }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $income->project ? $income->project->name : 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $income->category->name }}</div>
                            @if($income->subcategory)
                                <div class="text-xs text-gray-500">{{ $income->subcategory->name }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-green-600">${{ number_format($income->amount, 2) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500">{{ $income->payment_method ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="d-flex gap-1 text-nowrap">
                                <button onclick="openViewIncomeModal({{ $income->id }})" class="btn btn-outline-primary btn-sm" title="View">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button onclick="openEditIncomeModal({{ $income->id }})" class="btn btn-outline-warning btn-sm" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button onclick="showDeleteIncomeConfirmation({{ $income->id }}, '{{ addslashes($income->source) }}')" class="btn btn-outline-danger btn-sm" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-6 py-4 text-center text-sm text-gray-500">
                            No income records found. <button onclick="openCreateIncomeModal()" class="text-indigo-600 hover:text-indigo-900">Add one now</button>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot class="bg-gray-50 border-t-2 border-gray-200" id="incomes-table-tfoot" style="{{ $incomes->count() > 0 ? '' : 'display:none' }}">
                <tr>
                    <td colspan="7" class="px-6 py-3 text-right text-sm font-semibold text-gray-900">Total</td>
                    <td class="px-6 py-3 text-right text-sm font-bold text-green-600" id="incomes-table-total">${{ number_format($incomes->sum('amount'), 2) }}</td>
                    <td class="px-6 py-3"></td>
                </tr>
            </tfoot>
        </table>
            </div>
        </div>
    </div>
</div>

<div id="incomes-pagination" class="mt-4">
    <x-pagination :paginator="$incomes" wrapper-class="mt-4" />
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteIncomeConfirmationModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-2xl max-w-md w-full" onclick="event.stopPropagation()">
        <div class="p-6">
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 text-center mb-2">Delete Income Record</h3>
            <p class="text-gray-600 text-center mb-6">
                Are you sure you want to delete income record for <span class="font-semibold text-gray-900" id="delete-income-name"></span>? This action cannot be undone.
            </p>
            <div class="flex space-x-3">
                <button onclick="closeDeleteIncomeConfirmation()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors font-medium">
                    Cancel
                </button>
                <button onclick="confirmDeleteIncome()" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium">
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Modal -->
<div id="incomeModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-6 border-b">
            <h3 class="text-xl font-semibold text-gray-900" id="income-modal-title">Add New Income</h3>
            <button onclick="closeIncomeModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6">
            <form id="incomeForm" onsubmit="submitIncomeForm(event)">
                @csrf
                <input type="hidden" name="_method" id="income-method" value="POST">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="income-project-id" class="block text-sm font-medium text-gray-700 mb-2">Project</label>
                        <select name="project_id" id="income-project-id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select a project (optional)</option>
                        </select>
                        <div class="field-error text-red-600 text-sm mt-1" data-field="project_id" style="display: none;"></div>
                    </div>

                    <div>
                        <label for="income-category-id" class="block text-sm font-medium text-gray-700 mb-2">Category <span class="text-red-500">*</span></label>
                        <select name="category_id" id="income-category-id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select a category</option>
                        </select>
                        <div class="field-error text-red-600 text-sm mt-1" data-field="category_id" style="display: none;"></div>
                    </div>

                    <div>
                        <label for="income-subcategory-id" class="block text-sm font-medium text-gray-700 mb-2">Subcategory</label>
                        <select name="subcategory_id" id="income-subcategory-id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select a subcategory (optional)</option>
                        </select>
                        <div class="field-error text-red-600 text-sm mt-1" data-field="subcategory_id" style="display: none;"></div>
                    </div>

                    <div>
                        <label for="income-source" class="block text-sm font-medium text-gray-700 mb-2">Income Source <span class="text-red-500">*</span></label>
                        <input type="text" name="source" id="income-source" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                               placeholder="e.g., Sales, Services, Rent Income">
                        <div class="field-error text-red-600 text-sm mt-1" data-field="source" style="display: none;"></div>
                    </div>

                    <div>
                        <label for="income-amount" class="block text-sm font-medium text-gray-700 mb-2">Amount <span class="text-red-500">*</span></label>
                        <input type="number" name="amount" id="income-amount" step="0.01" min="0" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <div class="field-error text-red-600 text-sm mt-1" data-field="amount" style="display: none;"></div>
                    </div>

                    <div>
                        <label for="income-date" class="block text-sm font-medium text-gray-700 mb-2">Date <span class="text-red-500">*</span></label>
                        <input type="date" name="date" id="income-date" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <div class="field-error text-red-600 text-sm mt-1" data-field="date" style="display: none;"></div>
                    </div>

                    <div>
                        <label for="income-payment-method" class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                        <select name="payment_method" id="income-payment-method"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select payment method</option>
                            <option value="Cash">Cash</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Cheque">Cheque</option>
                            <option value="Credit Card">Credit Card</option>
                            <option value="Other">Other</option>
                        </select>
                        <div class="field-error text-red-600 text-sm mt-1" data-field="payment_method" style="display: none;"></div>
                    </div>

                    <div class="md:col-span-2">
                        <label for="income-description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea name="description" id="income-description" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                        <div class="field-error text-red-600 text-sm mt-1" data-field="description" style="display: none;"></div>
                    </div>

                    <div class="md:col-span-2">
                        <label for="income-notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                        <textarea name="notes" id="income-notes" rows="2"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                        <div class="field-error text-red-600 text-sm mt-1" data-field="notes" style="display: none;"></div>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-4 mt-6">
                    <button type="button" onclick="closeIncomeModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-200" id="income-submit-btn">
                        Add Income
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Modal -->
<div id="viewIncomeModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-6 border-b">
            <h3 class="text-xl font-semibold text-gray-900">Income Details</h3>
            <button onclick="closeViewIncomeModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6" id="view-income-content">
            <div class="flex items-center justify-center py-12">
                <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<style>
    @media (max-width: 768px) {
        .income-btn-text {
            display: none;
        }
    }
</style>

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
let currentIncomeId = null;
let deleteIncomeId = null;
let allSubcategories = [];

function openCreateIncomeModal() {
    currentIncomeId = null;
    const modal = document.getElementById('incomeModal');
    const title = document.getElementById('income-modal-title');
    const form = document.getElementById('incomeForm');
    const methodInput = document.getElementById('income-method');
    const submitBtn = document.getElementById('income-submit-btn');
    
    modal.classList.remove('hidden');
    title.textContent = 'Add New Income';
    methodInput.value = 'POST';
    submitBtn.disabled = false;
    submitBtn.textContent = 'Add Income';
    form.reset();
    document.getElementById('income-date').value = new Date().toISOString().split('T')[0];
    
    document.querySelectorAll('.field-error').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
    
    // Load categories, subcategories, and projects
    fetch('/admin/incomes/create', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const categorySelect = document.getElementById('income-category-id');
        categorySelect.innerHTML = '<option value="">Select a category</option>';
        data.categories.forEach(cat => {
            const option = document.createElement('option');
            option.value = cat.id;
            option.textContent = cat.name;
            categorySelect.appendChild(option);
        });
        
        const subcategorySelect = document.getElementById('income-subcategory-id');
        subcategorySelect.innerHTML = '<option value="">Select a subcategory (optional)</option>';
        allSubcategories = data.subcategories || [];
        allSubcategories.forEach(sub => {
            const option = document.createElement('option');
            option.value = sub.id;
            option.textContent = sub.name;
            option.setAttribute('data-category', sub.category_id);
            option.style.display = 'none';
            subcategorySelect.appendChild(option);
        });
        
        const projectSelect = document.getElementById('income-project-id');
        projectSelect.innerHTML = '<option value="">Select a project (optional)</option>';
        data.projects.forEach(proj => {
            const option = document.createElement('option');
            option.value = proj.id;
            option.textContent = proj.name;
            projectSelect.appendChild(option);
        });
        
        // Filter subcategories based on selected category
        categorySelect.addEventListener('change', function() {
            const categoryId = this.value;
            const subcategorySelect = document.getElementById('income-subcategory-id');
            const options = subcategorySelect.querySelectorAll('option');
            
            options.forEach(option => {
                if (option.value === '') {
                    option.style.display = 'block';
                } else {
                    const optionCategoryId = option.getAttribute('data-category');
                    if (optionCategoryId === categoryId) {
                        option.style.display = 'block';
                    } else {
                        option.style.display = 'none';
                    }
                }
            });
            
            subcategorySelect.value = '';
        });
    })
    .catch(error => {
        console.error('Error loading form data:', error);
        showNotification('Failed to load form data', 'error');
    });
}

function openEditIncomeModal(incomeId) {
    currentIncomeId = incomeId;
    const modal = document.getElementById('incomeModal');
    const title = document.getElementById('income-modal-title');
    const form = document.getElementById('incomeForm');
    const methodInput = document.getElementById('income-method');
    const submitBtn = document.getElementById('income-submit-btn');
    
    modal.classList.remove('hidden');
    title.textContent = 'Edit Income';
    methodInput.value = 'PUT';
    submitBtn.disabled = false;
    submitBtn.textContent = 'Update Income';
    
    document.querySelectorAll('.field-error').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
    
    // Load income data
    fetch(`/admin/incomes/${incomeId}/edit`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const categorySelect = document.getElementById('income-category-id');
        categorySelect.innerHTML = '<option value="">Select a category</option>';
        data.categories.forEach(cat => {
            const option = document.createElement('option');
            option.value = cat.id;
            option.textContent = cat.name;
            option.selected = cat.id == data.income.category_id;
            categorySelect.appendChild(option);
        });
        
        const subcategorySelect = document.getElementById('income-subcategory-id');
        subcategorySelect.innerHTML = '<option value="">Select a subcategory (optional)</option>';
        allSubcategories = data.subcategories || [];
        allSubcategories.forEach(sub => {
            const option = document.createElement('option');
            option.value = sub.id;
            option.textContent = sub.name;
            option.setAttribute('data-category', sub.category_id);
            if (sub.category_id == data.income.category_id) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
            if (sub.id == data.income.subcategory_id) {
                option.selected = true;
            }
            subcategorySelect.appendChild(option);
        });
        
        const projectSelect = document.getElementById('income-project-id');
        projectSelect.innerHTML = '<option value="">Select a project (optional)</option>';
        data.projects.forEach(proj => {
            const option = document.createElement('option');
            option.value = proj.id;
            option.textContent = proj.name;
            option.selected = proj.id == data.income.project_id;
            projectSelect.appendChild(option);
        });
        
        document.getElementById('income-source').value = data.income.source || '';
        document.getElementById('income-amount').value = data.income.amount || '';
        document.getElementById('income-date').value = data.income.date || '';
        document.getElementById('income-payment-method').value = data.income.payment_method || '';
        document.getElementById('income-description').value = data.income.description || '';
        document.getElementById('income-notes').value = data.income.notes || '';
        
        // Filter subcategories based on selected category
        categorySelect.addEventListener('change', function() {
            const categoryId = this.value;
            const subcategorySelect = document.getElementById('income-subcategory-id');
            const options = subcategorySelect.querySelectorAll('option');
            
            options.forEach(option => {
                if (option.value === '') {
                    option.style.display = 'block';
                } else {
                    const optionCategoryId = option.getAttribute('data-category');
                    if (optionCategoryId === categoryId) {
                        option.style.display = 'block';
                    } else {
                        option.style.display = 'none';
                    }
                }
            });
            
            if (subcategorySelect.value) {
                const selectedOption = subcategorySelect.options[subcategorySelect.selectedIndex];
                if (selectedOption.getAttribute('data-category') !== categoryId) {
                    subcategorySelect.value = '';
                }
            }
        });
    })
    .catch(error => {
        console.error('Error loading income data:', error);
        showNotification('Failed to load income data', 'error');
    });
}

function submitIncomeForm(e) {
    e.preventDefault();
    const form = e.target;
    const submitBtn = document.getElementById('income-submit-btn');
    const originalText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';
    
    const formData = new FormData(form);
    const url = currentIncomeId 
        ? `/admin/incomes/${currentIncomeId}`
        : '/admin/incomes';
    
    if (currentIncomeId) {
        formData.append('_method', 'PUT');
    }
    
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
            closeIncomeModal();
            
            // Refresh the filtered incomes list
            applyFilters(currentIncomePage);
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
        showNotification('An error occurred while saving', 'error');
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
}

function closeIncomeModal() {
    document.getElementById('incomeModal').classList.add('hidden');
    currentIncomeId = null;
    document.getElementById('incomeForm').reset();
    const submitBtn = document.getElementById('income-submit-btn');
    submitBtn.disabled = false;
    submitBtn.textContent = 'Add Income';
}

function addIncomeRow(income) {
    const tbody = document.querySelector('table tbody');
    const emptyRow = tbody.querySelector('tr td[colspan]');
    
    if (emptyRow) {
        emptyRow.closest('tr').remove();
    }
    
    const row = document.createElement('tr');
    row.setAttribute('data-income-id', income.id);
    row.innerHTML = `
        <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm text-gray-900">${income.date}</div>
        </td>
        <td class="px-6 py-4">
            <div class="text-sm font-medium text-gray-900">${income.source}</div>
            ${income.description ? `<div class="text-sm text-gray-500">${income.description.substring(0, 30)}${income.description.length > 30 ? '...' : ''}</div>` : ''}
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm text-gray-900">${income.project_name}</div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm text-gray-900">${income.category_name}</div>
            ${income.subcategory_name ? `<div class="text-xs text-gray-500">${income.subcategory_name}</div>` : ''}
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm font-semibold text-green-600">$${income.amount}</div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm text-gray-500">${income.payment_method}</div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
            <div class="d-flex gap-1 text-nowrap">
                <button onclick="openViewIncomeModal(${income.id})" class="btn btn-outline-primary btn-sm" title="View">
                    <i class="bi bi-eye"></i>
                </button>
                <button onclick="openEditIncomeModal(${income.id})" class="btn btn-outline-warning btn-sm" title="Edit">
                    <i class="bi bi-pencil"></i>
                </button>
                <button onclick="showDeleteIncomeConfirmation(${income.id}, '${(income.source || '').replace(/'/g, "\\'")}')" class="btn btn-outline-danger btn-sm" title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </td>
    `;
    
    tbody.insertBefore(row, tbody.firstChild);
}

function updateIncomeRow(income) {
    const row = document.querySelector(`tr[data-income-id="${income.id}"]`);
    if (row) {
        row.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">${income.date}</div>
            </td>
            <td class="px-6 py-4">
                <div class="text-sm font-medium text-gray-900">${income.source}</div>
                ${income.description ? `<div class="text-sm text-gray-500">${income.description.substring(0, 30)}${income.description.length > 30 ? '...' : ''}</div>` : ''}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">${income.project_name}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">${income.category_name}</div>
                ${income.subcategory_name ? `<div class="text-xs text-gray-500">${income.subcategory_name}</div>` : ''}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-semibold text-green-600">$${income.amount}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-500">${income.payment_method}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="d-flex gap-1 text-nowrap">
                    <button onclick="openViewIncomeModal(${income.id})" class="btn btn-outline-primary btn-sm" title="View">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button onclick="openEditIncomeModal(${income.id})" class="btn btn-outline-warning btn-sm" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button onclick="showDeleteIncomeConfirmation(${income.id}, '${(income.source || '').replace(/'/g, "\\'")}')" class="btn btn-outline-danger btn-sm" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        `;
    }
}

function openViewIncomeModal(incomeId) {
    const modal = document.getElementById('viewIncomeModal');
    const content = document.getElementById('view-income-content');
    
    modal.classList.remove('hidden');
    content.innerHTML = `
        <div class="flex items-center justify-center py-12">
            <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    `;
    
    fetch(`/admin/incomes/${incomeId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const inc = data.income;
        content.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Income Information</h3>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Source</dt>
                        <dd class="mt-1 text-sm font-semibold text-gray-900">${inc.source || ''}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Amount</dt>
                        <dd class="mt-1 text-2xl font-bold text-green-600">$${inc.amount}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Date</dt>
                        <dd class="mt-1 text-sm text-gray-900">${inc.formatted_date || ''}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Category</dt>
                        <dd class="mt-1 text-sm text-gray-900">${inc.category_name || ''}</dd>
                    </div>
                    ${inc.subcategory_name ? `
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Subcategory</dt>
                            <dd class="mt-1 text-sm text-gray-900">${inc.subcategory_name}</dd>
                        </div>
                    ` : ''}
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Payment Method</dt>
                        <dd class="mt-1 text-sm text-gray-900">${inc.payment_method || 'N/A'}</dd>
                    </div>
                </div>
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Additional Details</h3>
                    ${inc.description ? `
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Description</dt>
                            <dd class="mt-1 text-sm text-gray-900">${inc.description}</dd>
                        </div>
                    ` : ''}
                    ${inc.notes ? `
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Notes</dt>
                            <dd class="mt-1 text-sm text-gray-900">${inc.notes}</dd>
                        </div>
                    ` : ''}
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Project</dt>
                        <dd class="mt-1 text-sm text-gray-900">${inc.project_name || 'N/A'}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Created At</dt>
                        <dd class="mt-1 text-sm text-gray-900">${inc.created_at || ''}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Updated At</dt>
                        <dd class="mt-1 text-sm text-gray-900">${inc.updated_at || ''}</dd>
                    </div>
                </div>
            </div>
            <div class="mt-4 flex justify-end gap-2">
                <button onclick="closeViewIncomeModal(); openEditIncomeModal(${inc.id})" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                    Edit
                </button>
                <button onclick="closeViewIncomeModal()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    Close
                </button>
            </div>
        `;
    })
    .catch(error => {
        console.error('Error loading income:', error);
        content.innerHTML = `
            <div class="text-center py-12">
                <p class="text-red-600 mb-4">Failed to load income details</p>
                <button onclick="closeViewIncomeModal()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    Close
                </button>
            </div>
        `;
    });
}

function closeViewIncomeModal() {
    document.getElementById('viewIncomeModal').classList.add('hidden');
}

function showDeleteIncomeConfirmation(incomeId, incomeSource) {
    deleteIncomeId = incomeId;
    document.getElementById('delete-income-name').textContent = incomeSource;
    document.getElementById('deleteIncomeConfirmationModal').classList.remove('hidden');
}

function closeDeleteIncomeConfirmation() {
    document.getElementById('deleteIncomeConfirmationModal').classList.add('hidden');
    deleteIncomeId = null;
}

function confirmDeleteIncome() {
    if (!deleteIncomeId) return;
    
    const incomeIdToDelete = deleteIncomeId;
    const row = document.querySelector(`tr[data-income-id="${incomeIdToDelete}"]`);
    const deleteBtn = event.target;
    
    deleteBtn.disabled = true;
    deleteBtn.textContent = 'Deleting...';
    
    fetch(`/admin/incomes/${incomeIdToDelete}`, {
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
            closeDeleteIncomeConfirmation();
            showNotification(data.message, 'success');
            
            // Refresh the filtered incomes list
            applyFilters(currentIncomePage);
        } else {
            showNotification(data.message || 'Failed to delete income record', 'error');
            deleteBtn.disabled = false;
            deleteBtn.textContent = 'Delete';
        }
    })
    .catch(error => {
        console.error('Error deleting income:', error);
        showNotification('An error occurred while deleting', 'error');
        deleteBtn.disabled = false;
        deleteBtn.textContent = 'Delete';
    });
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

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        if (!document.getElementById('incomeModal').classList.contains('hidden')) {
            closeIncomeModal();
        }
        if (!document.getElementById('viewIncomeModal').classList.contains('hidden')) {
            closeViewIncomeModal();
        }
        if (!document.getElementById('deleteIncomeConfirmationModal').classList.contains('hidden')) {
            closeDeleteIncomeConfirmation();
        }
    }
});

document.getElementById('incomeModal').addEventListener('click', function(e) {
    if (e.target === this) closeIncomeModal();
});

document.getElementById('viewIncomeModal').addEventListener('click', function(e) {
    if (e.target === this) closeViewIncomeModal();
});

document.getElementById('deleteIncomeConfirmationModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteIncomeConfirmation();
});

// Filter and Pagination Functions
let currentIncomePage = 1;
let isLoadingIncomes = false;

function applyFilters(page = 1) {
    if (isLoadingIncomes) return;
    
    isLoadingIncomes = true;
    currentIncomePage = page;
    
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
    
    // Show loading state
    document.getElementById('incomes-loading').classList.remove('hidden');
    document.getElementById('incomes-table-container').style.opacity = '0.5';
    
    // Fetch filtered incomes via AJAX
    fetch(`{{ route('admin.incomes.index') }}?${params.toString()}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        updateIncomesTable(data.incomes, data.current_page, data.per_page, data.total_amount);
        updateIncomesPagination(data.pagination);
        updateIncomeURL(params.toString());
        updateIncomesExportLink();
        
        // Hide loading state
        document.getElementById('incomes-loading').classList.add('hidden');
        document.getElementById('incomes-table-container').style.opacity = '1';
        isLoadingIncomes = false;
    })
    .catch(error => {
        console.error('Error loading incomes:', error);
        document.getElementById('incomes-loading').classList.add('hidden');
        document.getElementById('incomes-table-container').style.opacity = '1';
        showNotification('Failed to load incomes. Please refresh the page.', 'error');
        isLoadingIncomes = false;
    });
}

function updateIncomesTable(incomes, currentPage, perPage, totalAmount) {
    const tbody = document.getElementById('incomes-tbody');
    const tfoot = document.getElementById('incomes-table-tfoot');
    const totalCell = document.getElementById('incomes-table-total');
    
    if (!incomes || incomes.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="9" class="px-6 py-4 text-center text-sm text-gray-500">
                    No income records found. <button onclick="openCreateIncomeModal()" class="text-indigo-600 hover:text-indigo-900">Add one now</button>
                </td>
            </tr>
        `;
        if (tfoot) tfoot.style.display = 'none';
        return;
    }
    
    if (tfoot && totalCell && totalAmount != null) {
        totalCell.textContent = '$' + parseFloat(totalAmount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        tfoot.style.display = '';
    }
    
    const startSn = (currentPage && perPage) ? (currentPage - 1) * perPage : 0;
    tbody.innerHTML = incomes.map((income, idx) => {
        const source = income.source || 'N/A';
        const escapedSource = source.replace(/'/g, "\\'");
        const description = income.description ? `<div class="text-sm text-gray-500">${income.description.substring(0, 30)}${income.description.length > 30 ? '...' : ''}</div>` : '';
        const sn = startSn + idx + 1;
        return `
            <tr data-income-id="${income.id}">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${sn}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">${income.date}</div>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm font-medium text-gray-900">${source}</div>
                    ${description}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">${income.project_name}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">${income.category_name}</div>
                    ${income.subcategory_name ? `<div class="text-xs text-gray-500">${income.subcategory_name}</div>` : ''}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-semibold text-green-600">$${income.amount}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-500">${income.payment_method}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="d-flex gap-1 text-nowrap">
                        <button onclick="openViewIncomeModal(${income.id})" class="btn btn-outline-primary btn-sm" title="View">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button onclick="openEditIncomeModal(${income.id})" class="btn btn-outline-warning btn-sm" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button onclick="showDeleteIncomeConfirmation(${income.id}, '${escapedSource}')" class="btn btn-outline-danger btn-sm" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

function updateIncomesPagination(paginationHtml) {
    const paginationContainer = document.getElementById('incomes-pagination');
    if (paginationHtml) {
        paginationContainer.innerHTML = paginationHtml;
        
        // Attach click handlers to pagination links
        paginationContainer.querySelectorAll('a[href*="page="]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const url = new URL(this.href);
                const page = url.searchParams.get('page') || 1;
                applyFilters(parseInt(page));
            });
        });
    } else {
        paginationContainer.innerHTML = '';
    }
}

function updateIncomeURL(params) {
    const newURL = window.location.pathname + (params ? '?' + params : '');
    window.history.pushState({path: newURL}, '', newURL);
}

function updateIncomesExportLink() {
    const link = document.getElementById('incomesExportLink');
    if (!link) return;
    const form = document.getElementById('filterForm');
    if (!form) return;
    const formData = new FormData(form);
    const params = new URLSearchParams();
    const exportParams = ['project_id', 'category_id', 'subcategory_id', 'start_date', 'end_date', 'keyword'];
    for (const key of exportParams) {
        const val = formData.get(key);
        if (val) params.append(key, val);
    }
    link.href = '{{ route("admin.incomes.export") }}' + (params.toString() ? '?' + params.toString() : '');
}

function loadSubcategoriesAndFilter() {
    const categoryId = document.getElementById('category_id').value;
    const subcategorySelect = document.getElementById('subcategory_id');
    
    // Clear existing options
    subcategorySelect.innerHTML = '<option value="">All Subcategories</option>';
    subcategorySelect.value = '';
    
    if (!categoryId) {
        // If no category selected, apply filters
        applyFilters();
        return;
    }
    
    // Show loading state
    subcategorySelect.disabled = true;
    subcategorySelect.innerHTML = '<option value="">Loading...</option>';
    
    // Fetch subcategories via AJAX
    fetch(`/admin/categories/${categoryId}/subcategories`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        subcategorySelect.innerHTML = '<option value="">All Subcategories</option>';
        
        if (data && Array.isArray(data) && data.length > 0) {
            data.forEach(subcategory => {
                const option = document.createElement('option');
                option.value = subcategory.id;
                option.textContent = subcategory.name;
                subcategorySelect.appendChild(option);
            });
        }
        
        subcategorySelect.disabled = false;
        
        // Apply filters after loading subcategories
        applyFilters();
    })
    .catch(error => {
        console.error('Error loading subcategories:', error);
        subcategorySelect.innerHTML = '<option value="">Error loading</option>';
        subcategorySelect.disabled = false;
        applyFilters();
    });
}

function loadSubcategories() {
    const categoryId = document.getElementById('category_id').value;
    const subcategorySelect = document.getElementById('subcategory_id');
    
    // Clear existing options
    subcategorySelect.innerHTML = '<option value="">All Subcategories</option>';
    
    if (!categoryId) {
        return;
    }
    
    // Show loading state
    subcategorySelect.disabled = true;
    subcategorySelect.innerHTML = '<option value="">Loading...</option>';
    
    // Fetch subcategories via AJAX
    fetch(`/admin/categories/${categoryId}/subcategories`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        subcategorySelect.innerHTML = '<option value="">All Subcategories</option>';
        
        if (data && Array.isArray(data) && data.length > 0) {
            data.forEach(subcategory => {
                const option = document.createElement('option');
                option.value = subcategory.id;
                option.textContent = subcategory.name;
                // Preserve selected value if it matches
                const selectedId = '{{ request("subcategory_id") }}';
                if (selectedId && subcategory.id == selectedId) {
                    option.selected = true;
                }
                subcategorySelect.appendChild(option);
            });
        }
        
        subcategorySelect.disabled = false;
    })
    .catch(error => {
        console.error('Error loading subcategories:', error);
        subcategorySelect.innerHTML = '<option value="">Error loading</option>';
        subcategorySelect.disabled = false;
    });
}

function clearFilters() {
    // Reset all filter selects and keyword
    if (document.getElementById('project_id')) {
        document.getElementById('project_id').value = '';
    }
    document.getElementById('category_id').value = '';
    document.getElementById('subcategory_id').innerHTML = '<option value="">All Subcategories</option>';
    document.getElementById('subcategory_id').value = '';
    document.getElementById('start_date').value = '';
    document.getElementById('end_date').value = '';
    document.getElementById('keyword').value = '';
    
    // Apply filters (which will load all incomes)
    applyFilters();
}

// Load subcategories on page load if category is pre-selected
document.addEventListener('DOMContentLoaded', function() {
    updateIncomesExportLink();
    const categoryId = document.getElementById('category_id').value;
    if (categoryId) {
        // Don't auto-submit, just load the subcategories
        loadSubcategories();
    }
    
    // Attach click handlers to existing pagination links
    document.getElementById('incomes-pagination').querySelectorAll('a[href*="page="]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const url = new URL(this.href);
            const page = url.searchParams.get('page') || 1;
            applyFilters(parseInt(page));
        });
    });
    
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
@endpush
@endsection

