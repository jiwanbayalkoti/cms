@extends('admin.layout')

@section('title', 'Expense Records')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-3xl font-bold text-gray-900">Expense Records</h1>
    <div class="flex gap-2">
        <a href="{{ route('admin.expenses.export') }}" id="expensesExportLink" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center gap-2" title="Export with current filters">
            <i class="bi bi-file-earmark-excel me-0 me-md-1"></i>
            <span class="d-none d-md-inline expense-btn-text">Export Excel</span>
        </a>
        <button onclick="openCreateExpenseModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center gap-2">
            <i class="bi bi-plus-circle"></i>
            <span class="expense-btn-text">Add New Expense</span>
        </button>
    </div>
</div>

<div class="mb-4 bg-white shadow-lg rounded-lg p-4">
    <form method="GET" action="{{ route('admin.expenses.index') }}" id="filterForm" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
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
            <label for="expense_type_id" class="block text-sm font-medium text-gray-700 mb-2">Expense Type</label>
            <select name="expense_type_id" id="expense_type_id" onchange="applyFilters()"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Types</option>
                @foreach($expenseTypes as $type)
                    <option value="{{ $type->id }}" {{ request('expense_type_id') == $type->id ? 'selected' : '' }}>
                        {{ $type->name }}
                    </option>
                @endforeach
            </select>
        </div>
        
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
            <label for="keyword" class="block text-sm font-medium text-gray-700 mb-2">Keyword</label>
            <input type="text" name="keyword" id="keyword" value="{{ request('keyword') }}" placeholder="Item, description..."
                   onkeyup="applyFilters()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        
        <div class="flex items-end">
            <button type="button" onclick="clearFilters()" class="w-full px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition duration-200">
                Clear Filters
            </button>
        </div>
    </form>
</div>

<script>
    let currentPage = 1;
    let isLoading = false;
    
    function applyFilters(page = 1) {
        if (isLoading) return;
        
        isLoading = true;
        currentPage = page;
        
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
        document.getElementById('expenses-loading').classList.remove('hidden');
        document.getElementById('expenses-table-container').style.opacity = '0.5';
        
        // Fetch filtered expenses via AJAX
        fetch(`{{ route('admin.expenses.index') }}?${params.toString()}`, {
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
            updateExpensesTable(data.expenses, data.current_page, data.per_page);
            updatePagination(data.pagination);
            updateURL(params.toString());
            updateExpensesExportLink();
            
            // Hide loading state
            document.getElementById('expenses-loading').classList.add('hidden');
            document.getElementById('expenses-table-container').style.opacity = '1';
            isLoading = false;
        })
        .catch(error => {
            console.error('Error loading expenses:', error);
            document.getElementById('expenses-loading').classList.add('hidden');
            document.getElementById('expenses-table-container').style.opacity = '1';
            showNotification('Failed to load expenses. Please refresh the page.', 'error');
            isLoading = false;
        });
    }
    
    function updateExpensesTable(expenses, currentPage, perPage) {
        const tbody = document.getElementById('expenses-tbody');
        
        if (!expenses || expenses.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="10" class="px-6 py-4 text-center text-sm text-gray-500">
                        No expense records found. <button onclick="openCreateExpenseModal()" class="text-indigo-600 hover:text-indigo-900">Add one now</button>
                    </td>
                </tr>
            `;
            return;
        }
        
        const startSn = (currentPage && perPage) ? (currentPage - 1) * perPage : 0;
        tbody.innerHTML = expenses.map((expense, idx) => {
            const itemName = expense.item_name || 'N/A';
            const escapedItemName = itemName.replace(/'/g, "\\'");
            const sn = startSn + idx + 1;
            return `
                <tr data-expense-id="${expense.id}">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${sn}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">${expense.date}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${expense.type_class}">
                            ${expense.type_name}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">${itemName}</div>
                        ${expense.has_construction_material ? '<div class="text-xs text-indigo-600 mt-1"><i class="bi bi-link-45deg"></i> Linked to Material Purchase</div>' : ''}
                        ${expense.has_advance_payment ? '<div class="text-xs text-purple-600 mt-1"><i class="bi bi-link-45deg"></i> Linked to Advance Payment</div>' : ''}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">${expense.project_name}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">${expense.category_name}</div>
                        ${expense.subcategory_name ? `<div class="text-xs text-gray-500">${expense.subcategory_name}</div>` : ''}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">${expense.staff_name}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-semibold text-red-600">$${expense.amount}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="d-flex gap-1 text-nowrap">
                            <button onclick="openViewExpenseModal(${expense.id})" class="btn btn-outline-primary btn-sm" title="View">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button onclick="openEditExpenseModal(${expense.id})" class="btn btn-outline-warning btn-sm" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button onclick="cloneExpense(${expense.id})" class="btn btn-outline-info btn-sm" title="Duplicate">
                                <i class="bi bi-files"></i>
                            </button>
                            <button onclick="showDeleteExpenseConfirmation(${expense.id}, '${escapedItemName}')" class="btn btn-outline-danger btn-sm" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }
    
    function updatePagination(paginationHtml) {
        const paginationContainer = document.getElementById('expenses-pagination');
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
    
    function updateURL(params) {
        const newURL = window.location.pathname + (params ? '?' + params : '');
        window.history.pushState({path: newURL}, '', newURL);
    }
    
    function updateExpensesExportLink() {
        const link = document.getElementById('expensesExportLink');
        if (!link) return;
        const form = document.getElementById('filterForm');
        if (!form) return;
        const formData = new FormData(form);
        const params = new URLSearchParams();
        const exportParams = ['project_id', 'expense_type_id', 'category_id', 'subcategory_id', 'keyword'];
        for (const key of exportParams) {
            const val = formData.get(key);
            if (val) params.append(key, val);
        }
        link.href = '{{ route("admin.expenses.export") }}' + (params.toString() ? '?' + params.toString() : '');
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
        document.getElementById('project_id').value = '';
        document.getElementById('expense_type_id').value = '';
        document.getElementById('category_id').value = '';
        document.getElementById('subcategory_id').innerHTML = '<option value="">All Subcategories</option>';
        document.getElementById('subcategory_id').value = '';
        document.getElementById('keyword').value = '';
        
        // Apply filters (which will load all expenses)
        applyFilters();
    }
    
    // Load subcategories on page load if category is pre-selected
    document.addEventListener('DOMContentLoaded', function() {
        updateExpensesExportLink();
        const categoryId = document.getElementById('category_id').value;
        if (categoryId) {
            // Don't auto-submit, just load the subcategories
            loadSubcategories();
        }
        
        // Attach click handlers to existing pagination links
        const paginationContainer = document.getElementById('expenses-pagination');
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

<div class="bg-white shadow-lg rounded-lg overflow-hidden">
    <div id="expenses-loading" class="hidden p-8 text-center">
        <svg class="animate-spin h-8 w-8 text-indigo-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p class="mt-2 text-gray-600">Loading expenses...</p>
    </div>
    <div id="expenses-table-container">
        <div class="overflow-x-auto -mx-4 sm:mx-0">
            <div class="inline-block min-w-full align-middle">
                <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SN</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item/Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Staff</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200" id="expenses-tbody">
                @forelse($expenses as $expense)
                    <tr data-expense-id="{{ $expense->id }}">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ ($expenses->currentPage() - 1) * $expenses->perPage() + $loop->iteration }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $expense->date->format('M d, Y') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $typeName = '';
                                $typeClass = 'bg-gray-100 text-gray-800';
                                
                                if ($expense->constructionMaterial) {
                                    $typeName = 'Purchase';
                                    $typeClass = 'bg-blue-100 text-blue-800';
                                } elseif ($expense->advancePayment) {
                                    $typeName = 'Advance';
                                    $typeClass = 'bg-yellow-100 text-yellow-800';
                                } elseif ($expense->vehicleRent) {
                                    $typeName = 'Vehicle rent';
                                    $typeClass = 'bg-purple-100 text-purple-800';
                                } elseif ($expense->expenseType) {
                                    $typeName = $expense->expenseType->name;
                                    $typeClass = 'bg-gray-100 text-gray-800';
                                } else {
                                    $typeName = 'N/A';
                                }
                            @endphp
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $typeClass }}">
                                {{ $typeName }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $expense->item_name ?? ($expense->description ? Str::limit($expense->description, 30) : 'N/A') }}</div>
                            @if($expense->constructionMaterial)
                                <div class="text-xs text-indigo-600 mt-1">
                                    <i class="bi bi-link-45deg"></i> Linked to Material Purchase
                                </div>
                            @endif
                            @if($expense->advancePayment)
                                <div class="text-xs text-purple-600 mt-1">
                                    <i class="bi bi-link-45deg"></i> Linked to Advance Payment
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $expense->project ? $expense->project->name : 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $expense->category->name }}</div>
                            @if($expense->subcategory)
                                <div class="text-xs text-gray-500">{{ $expense->subcategory->name }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($expense->staff)
                                <div class="text-sm text-gray-900">{{ $expense->staff->name }}</div>
                            @else
                                <div class="text-sm text-gray-400">N/A</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-red-600">${{ number_format($expense->amount, 2) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="d-flex gap-1 text-nowrap">
                                <button onclick="openViewExpenseModal({{ $expense->id }})" class="btn btn-outline-primary btn-sm" title="View">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button onclick="openEditExpenseModal({{ $expense->id }})" class="btn btn-outline-warning btn-sm" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button onclick="cloneExpense({{ $expense->id }})" class="btn btn-outline-info btn-sm" title="Duplicate">
                                    <i class="bi bi-files"></i>
                                </button>
                                <button onclick="showDeleteExpenseConfirmation({{ $expense->id }}, '{{ addslashes($expense->item_name ?? ($expense->description ? Str::limit($expense->description, 30) : 'Expense')) }}')" class="btn btn-outline-danger btn-sm" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-6 py-4 text-center text-sm text-gray-500">
                            No expense records found. <button onclick="openCreateExpenseModal()" class="text-indigo-600 hover:text-indigo-900">Add one now</button>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
            </div>
        </div>
    </div>
</div>

<div id="expenses-pagination" class="mt-4">
    <x-pagination :paginator="$expenses" wrapper-class="mt-4" />
</div>

<!-- Clone Confirmation Modal -->
<div id="cloneExpenseConfirmationModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-2xl max-w-md w-full" onclick="event.stopPropagation()">
        <div class="p-6">
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-blue-100 rounded-full">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 text-center mb-2">Duplicate Expense Record</h3>
            <p class="text-gray-600 text-center mb-6">
                Are you sure you want to duplicate expense record for <span class="font-semibold text-gray-900" id="clone-expense-name"></span>? A new expense record will be created with the same information.
            </p>
            <div class="flex space-x-3">
                <button onclick="closeCloneExpenseConfirmation()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors font-medium">
                    Cancel
                </button>
                <button onclick="confirmCloneExpense()" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                    Duplicate
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteExpenseConfirmationModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-2xl max-w-md w-full" onclick="event.stopPropagation()">
        <div class="p-6">
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 text-center mb-2">Delete Expense Record</h3>
            <p class="text-gray-600 text-center mb-6">
                Are you sure you want to delete expense record for <span class="font-semibold text-gray-900" id="delete-expense-name"></span>? This action cannot be undone.
            </p>
            <div class="flex space-x-3">
                <button onclick="closeDeleteExpenseConfirmation()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors font-medium">
                    Cancel
                </button>
                <button onclick="confirmDeleteExpense()" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium">
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Modal -->
<div id="expenseModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-5xl w-full max-h-[90vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-6 border-b">
            <h3 class="text-xl font-semibold text-gray-900" id="expense-modal-title">Add New Expense</h3>
            <button onclick="closeExpenseModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6">
            <form id="expenseForm" onsubmit="submitExpenseForm(event)" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_method" id="expense-method" value="POST">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="expense-project-id" class="block text-sm font-medium text-gray-700 mb-2">Project</label>
                        <select name="project_id" id="expense-project-id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select a project (optional)</option>
                        </select>
                        <div class="field-error text-red-600 text-sm mt-1" data-field="project_id" style="display: none;"></div>
                    </div>

                    <div>
                        <label for="expense-expense-type-id" class="block text-sm font-medium text-gray-700 mb-2">Expense Type <span class="text-red-500">*</span></label>
                        <select name="expense_type_id" id="expense-expense-type-id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select Expense Type</option>
                        </select>
                        <div class="field-error text-red-600 text-sm mt-1" data-field="expense_type_id" style="display: none;"></div>
                    </div>

                    <div id="expense-staff-field" style="display: none;">
                        <label for="expense-staff-id" class="block text-sm font-medium text-gray-700 mb-2">Staff Member <span class="text-red-500">*</span></label>
                        <select name="staff_id" id="expense-staff-id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select staff member</option>
                        </select>
                        <div class="field-error text-red-600 text-sm mt-1" data-field="staff_id" style="display: none;"></div>
                    </div>

                    <div id="expense-item-field">
                        <label for="expense-item-name" class="block text-sm font-medium text-gray-700 mb-2">Item Name</label>
                        <input type="text" name="item_name" id="expense-item-name"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                               placeholder="e.g., Office Supplies, Equipment">
                        <div class="field-error text-red-600 text-sm mt-1" data-field="item_name" style="display: none;"></div>
                    </div>

                    <div>
                        <label for="expense-category-id" class="block text-sm font-medium text-gray-700 mb-2">Category <span class="text-red-500">*</span></label>
                        <select name="category_id" id="expense-category-id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select a category</option>
                        </select>
                        <div class="field-error text-red-600 text-sm mt-1" data-field="category_id" style="display: none;"></div>
                    </div>

                    <div>
                        <label for="expense-subcategory-id" class="block text-sm font-medium text-gray-700 mb-2">Subcategory</label>
                        <select name="subcategory_id" id="expense-subcategory-id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select a subcategory (optional)</option>
                        </select>
                        <div class="field-error text-red-600 text-sm mt-1" data-field="subcategory_id" style="display: none;"></div>
                    </div>

                    <div>
                        <label for="expense-amount" class="block text-sm font-medium text-gray-700 mb-2">Amount <span class="text-red-500">*</span></label>
                        <input type="number" name="amount" id="expense-amount" step="0.01" min="0" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <div class="field-error text-red-600 text-sm mt-1" data-field="amount" style="display: none;"></div>
                    </div>

                    <div>
                        <label for="expense-date" class="block text-sm font-medium text-gray-700 mb-2">Date <span class="text-red-500">*</span></label>
                        <input type="date" name="date" id="expense-date" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <div class="field-error text-red-600 text-sm mt-1" data-field="date" style="display: none;"></div>
                    </div>

                    <div>
                        <label for="expense-payment-method" class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                        <select name="payment_method" id="expense-payment-method"
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
                        <label for="expense-description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea name="description" id="expense-description" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                        <div class="field-error text-red-600 text-sm mt-1" data-field="description" style="display: none;"></div>
                    </div>

                    <div class="md:col-span-2">
                        <label for="expense-notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                        <textarea name="notes" id="expense-notes" rows="2"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                        <div class="field-error text-red-600 text-sm mt-1" data-field="notes" style="display: none;"></div>
                    </div>

                    <div class="md:col-span-2">
                        <label for="expense-images" class="block text-sm font-medium text-gray-700 mb-2">Upload Images</label>
                        <input type="file" name="images[]" id="expense-images" multiple accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <p class="mt-1 text-sm text-gray-500">You can select multiple images (JPEG, PNG, JPG, GIF, WebP). Max 5MB per image.</p>
                        <div id="expense-image-preview" class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4"></div>
                        <div id="expense-existing-images" class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4"></div>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-4 mt-6">
                    <button type="button" onclick="closeExpenseModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-200" id="expense-submit-btn">
                        Add Expense
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Modal -->
<div id="viewExpenseModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-6 border-b">
            <h3 class="text-xl font-semibold text-gray-900">Expense Details</h3>
            <button onclick="closeViewExpenseModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6" id="view-expense-content">
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
        .expense-btn-text {
            display: none;
        }
    }
</style>

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
let currentExpenseId = null;
let deleteExpenseId = null;
let cloneExpenseId = null;
let allSubcategories = [];
let existingImagesToDelete = [];

// Expense modal functions will be added here
// Due to complexity, I'll add the core functions
function openCreateExpenseModal() {
    currentExpenseId = null;
    const modal = document.getElementById('expenseModal');
    const title = document.getElementById('expense-modal-title');
    const form = document.getElementById('expenseForm');
    const methodInput = document.getElementById('expense-method');
    const submitBtn = document.getElementById('expense-submit-btn');
    
    modal.classList.remove('hidden');
    title.textContent = 'Add New Expense';
    methodInput.value = 'POST';
    submitBtn.textContent = 'Add Expense';
    form.reset();
    document.getElementById('expense-date').value = new Date().toISOString().split('T')[0];
    document.getElementById('expense-image-preview').innerHTML = '';
    document.getElementById('expense-existing-images').innerHTML = '';
    existingImagesToDelete = [];
    
    document.querySelectorAll('.field-error').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
    
    // Load form data
    fetch('/admin/expenses/create', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Populate dropdowns
        populateExpenseFormDropdowns(data);
        setupExpenseFormHandlers();
    })
    .catch(error => {
        console.error('Error loading form data:', error);
        showNotification('Failed to load form data', 'error');
    });
}

function openEditExpenseModal(expenseId) {
    currentExpenseId = expenseId;
    const modal = document.getElementById('expenseModal');
    const title = document.getElementById('expense-modal-title');
    const form = document.getElementById('expenseForm');
    const methodInput = document.getElementById('expense-method');
    const submitBtn = document.getElementById('expense-submit-btn');
    
    modal.classList.remove('hidden');
    title.textContent = 'Edit Expense';
    methodInput.value = 'PUT';
    submitBtn.textContent = 'Update Expense';
    
    document.querySelectorAll('.field-error').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
    
    // Load expense data
    fetch(`/admin/expenses/${expenseId}/edit`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        populateExpenseFormDropdowns(data);
        populateExpenseFormFields(data);
        setupExpenseFormHandlers();
    })
    .catch(error => {
        console.error('Error loading expense data:', error);
        showNotification('Failed to load expense data', 'error');
    });
}

function populateExpenseFormDropdowns(data) {
    // Projects
    const projectSelect = document.getElementById('expense-project-id');
    projectSelect.innerHTML = '<option value="">Select a project (optional)</option>';
    (data.projects || []).forEach(proj => {
        const option = document.createElement('option');
        option.value = proj.id;
        option.textContent = proj.name;
        projectSelect.appendChild(option);
    });
    
    // Expense Types
    const expenseTypeSelect = document.getElementById('expense-expense-type-id');
    expenseTypeSelect.innerHTML = '<option value="">Select Expense Type</option>';
    (data.expenseTypes || []).forEach(type => {
        const option = document.createElement('option');
        option.value = type.id;
        option.textContent = type.name;
        expenseTypeSelect.appendChild(option);
    });
    
    // Staff
    const staffSelect = document.getElementById('expense-staff-id');
    staffSelect.innerHTML = '<option value="">Select staff member</option>';
    (data.staff || []).forEach(staff => {
        const option = document.createElement('option');
        option.value = staff.id;
        option.textContent = `${staff.name} - ${staff.position_name || 'N/A'}`;
        staffSelect.appendChild(option);
    });
    
    // Categories
    const categorySelect = document.getElementById('expense-category-id');
    categorySelect.innerHTML = '<option value="">Select a category</option>';
    (data.categories || []).forEach(cat => {
        const option = document.createElement('option');
        option.value = cat.id;
        option.textContent = cat.name;
        categorySelect.appendChild(option);
    });
    
    // Subcategories
    const subcategorySelect = document.getElementById('expense-subcategory-id');
    subcategorySelect.innerHTML = '<option value="">Select a subcategory (optional)</option>';
    allSubcategories = data.subcategories || [];
}

function populateExpenseFormFields(data) {
    if (data.expense) {
        const exp = data.expense;
        document.getElementById('expense-project-id').value = exp.project_id || '';
        document.getElementById('expense-expense-type-id').value = exp.expense_type_id || '';
        document.getElementById('expense-staff-id').value = exp.staff_id || '';
        document.getElementById('expense-item-name').value = exp.item_name || '';
        document.getElementById('expense-category-id').value = exp.category_id || '';
        document.getElementById('expense-amount').value = exp.amount || '';
        document.getElementById('expense-date').value = exp.date || '';
        document.getElementById('expense-payment-method').value = exp.payment_method || '';
        document.getElementById('expense-description').value = exp.description || '';
        document.getElementById('expense-notes').value = exp.notes || '';
        
        // Load subcategories for selected category
        if (exp.category_id) {
            loadExpenseSubcategories(exp.category_id, exp.subcategory_id);
        }
        
        // Toggle fields based on expense type
        toggleExpenseFields(exp.expense_type_id);
        
        // Display existing images
        displayExistingImages(exp.images || []);
    }
}

function setupExpenseFormHandlers() {
    // Expense type change handler
    const expenseTypeSelect = document.getElementById('expense-expense-type-id');
    expenseTypeSelect.onchange = function() {
        toggleExpenseFields(this.value);
    };
    
    // Category change handler
    const categorySelect = document.getElementById('expense-category-id');
    categorySelect.onchange = function() {
        loadExpenseSubcategories(this.value);
    };
    
    // Image preview handler
    const imageInput = document.getElementById('expense-images');
    imageInput.onchange = function(e) {
        previewExpenseImages(e.target.files);
    };
}

function toggleExpenseFields(expenseTypeId) {
    const staffField = document.getElementById('expense-staff-field');
    const itemField = document.getElementById('expense-item-field');
    const staffSelect = document.getElementById('expense-staff-id');
    const expenseTypeSelect = document.getElementById('expense-expense-type-id');
    
    if (!expenseTypeId) {
        staffField.style.display = 'none';
        itemField.style.display = 'block';
        return;
    }
    
    // Get selected expense type option
    const selectedOption = expenseTypeSelect.options[expenseTypeSelect.selectedIndex];
    const expenseTypeName = selectedOption ? selectedOption.textContent.toLowerCase() : '';
    
    // Show staff field for salary/advance types, hide item field
    if (expenseTypeName.includes('salary') || expenseTypeName.includes('advance')) {
        staffField.style.display = 'block';
        itemField.style.display = 'none';
        staffSelect.required = true;
    } else {
        staffField.style.display = 'none';
        itemField.style.display = 'block';
        staffSelect.required = false;
        staffSelect.value = '';
    }
}

function loadExpenseSubcategories(categoryId, selectedId = null) {
    if (!categoryId) {
        document.getElementById('expense-subcategory-id').innerHTML = '<option value="">Select a subcategory (optional)</option>';
        return;
    }
    
    const subcategorySelect = document.getElementById('expense-subcategory-id');
    subcategorySelect.disabled = true;
    subcategorySelect.innerHTML = '<option value="">Loading...</option>';
    
    fetch(`/admin/categories/${categoryId}/subcategories`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        subcategorySelect.innerHTML = '<option value="">Select a subcategory (optional)</option>';
        if (data && Array.isArray(data) && data.length > 0) {
            data.forEach(sub => {
                const option = document.createElement('option');
                option.value = sub.id;
                option.textContent = sub.name;
                if (selectedId && sub.id == selectedId) {
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

function previewExpenseImages(files) {
    const preview = document.getElementById('expense-image-preview');
    preview.innerHTML = '';
    
    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'relative group';
                div.innerHTML = `
                    <img src="${e.target.result}" class="w-full h-32 object-cover rounded-lg border border-gray-200">
                    <div class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer" onclick="this.parentElement.remove()">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                `;
                preview.appendChild(div);
            };
            reader.readAsDataURL(file);
        }
    }
}

function displayExistingImages(images) {
    const container = document.getElementById('expense-existing-images');
    container.innerHTML = '';
    
    images.forEach((imageUrl, index) => {
        const div = document.createElement('div');
        div.className = 'relative group';
        div.innerHTML = `
            <img src="${imageUrl}" class="w-full h-32 object-cover rounded-lg border border-gray-200">
            <div class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer" onclick="removeExistingImage(${index}, '${imageUrl}')">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>
        `;
        container.appendChild(div);
    });
}

function removeExistingImage(index, imageUrl) {
    existingImagesToDelete.push(imageUrl);
    document.getElementById('expense-existing-images').innerHTML = '';
}

function submitExpenseForm(e) {
    e.preventDefault();
    const form = e.target;
    const submitBtn = document.getElementById('expense-submit-btn');
    const originalText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';
    
    const formData = new FormData(form);
    const url = currentExpenseId 
        ? `/admin/expenses/${currentExpenseId}`
        : '/admin/expenses';
    
    if (currentExpenseId) {
        formData.append('_method', 'PUT');
    }
    
    // Add images to delete
    existingImagesToDelete.forEach(img => {
        formData.append('delete_images[]', img);
    });
    
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
            closeExpenseModal();
            
            // Refresh the filtered expenses list
            applyFilters(currentPage);
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

function closeExpenseModal() {
    document.getElementById('expenseModal').classList.add('hidden');
    currentExpenseId = null;
    document.getElementById('expenseForm').reset();
    document.getElementById('expense-image-preview').innerHTML = '';
    document.getElementById('expense-existing-images').innerHTML = '';
    existingImagesToDelete = [];
}

function openViewExpenseModal(expenseId) {
    const modal = document.getElementById('viewExpenseModal');
    const content = document.getElementById('view-expense-content');
    
    modal.classList.remove('hidden');
    content.innerHTML = `
        <div class="flex items-center justify-center py-12">
            <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    `;
    
    fetch(`/admin/expenses/${expenseId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const exp = data.expense;
        let imagesHtml = '';
        if (exp.images && exp.images.length > 0) {
            imagesHtml = '<div class="mt-4"><h4 class="font-semibold mb-2">Images:</h4><div class="grid grid-cols-2 md:grid-cols-4 gap-4">';
            exp.images.forEach(img => {
                imagesHtml += `<img src="${img}" class="w-full h-32 object-cover rounded-lg border">`;
            });
            imagesHtml += '</div></div>';
        }
        
        content.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Expense Information</h3>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Type</dt>
                        <dd class="mt-1 text-sm text-gray-900">${exp.type_name || 'N/A'}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Item Name</dt>
                        <dd class="mt-1 text-sm text-gray-900">${exp.item_name || 'N/A'}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Amount</dt>
                        <dd class="mt-1 text-2xl font-bold text-red-600">$${exp.amount}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Date</dt>
                        <dd class="mt-1 text-sm text-gray-900">${exp.formatted_date || ''}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Category</dt>
                        <dd class="mt-1 text-sm text-gray-900">${exp.category_name || ''}</dd>
                    </div>
                    ${exp.subcategory_name ? `
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Subcategory</dt>
                            <dd class="mt-1 text-sm text-gray-900">${exp.subcategory_name}</dd>
                        </div>
                    ` : ''}
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Payment Method</dt>
                        <dd class="mt-1 text-sm text-gray-900">${exp.payment_method || 'N/A'}</dd>
                    </div>
                </div>
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Additional Details</h3>
                    ${exp.description ? `
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Description</dt>
                            <dd class="mt-1 text-sm text-gray-900">${exp.description}</dd>
                        </div>
                    ` : ''}
                    ${exp.notes ? `
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Notes</dt>
                            <dd class="mt-1 text-sm text-gray-900">${exp.notes}</dd>
                        </div>
                    ` : ''}
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Project</dt>
                        <dd class="mt-1 text-sm text-gray-900">${exp.project_name || 'N/A'}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Staff</dt>
                        <dd class="mt-1 text-sm text-gray-900">${exp.staff_name || 'N/A'}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Created At</dt>
                        <dd class="mt-1 text-sm text-gray-900">${exp.created_at || ''}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Updated At</dt>
                        <dd class="mt-1 text-sm text-gray-900">${exp.updated_at || ''}</dd>
                    </div>
                </div>
            </div>
            ${imagesHtml}
            <div class="mt-4 flex justify-end gap-2">
                <button onclick="closeViewExpenseModal(); openEditExpenseModal(${exp.id})" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                    Edit
                </button>
                <button onclick="closeViewExpenseModal()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    Close
                </button>
            </div>
        `;
    })
    .catch(error => {
        console.error('Error loading expense:', error);
        content.innerHTML = `
            <div class="text-center py-12">
                <p class="text-red-600 mb-4">Failed to load expense details</p>
                <button onclick="closeViewExpenseModal()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    Close
                </button>
            </div>
        `;
    });
}

function closeViewExpenseModal() {
    document.getElementById('viewExpenseModal').classList.add('hidden');
}

function showDeleteExpenseConfirmation(expenseId, expenseName) {
    deleteExpenseId = expenseId;
    document.getElementById('delete-expense-name').textContent = expenseName;
    document.getElementById('deleteExpenseConfirmationModal').classList.remove('hidden');
}

function closeDeleteExpenseConfirmation() {
    document.getElementById('deleteExpenseConfirmationModal').classList.add('hidden');
    deleteExpenseId = null;
}

function confirmDeleteExpense() {
    if (!deleteExpenseId) return;
    
    const expenseIdToDelete = deleteExpenseId;
    const row = document.querySelector(`tr[data-expense-id="${expenseIdToDelete}"]`);
    const deleteBtn = event.target;
    
    deleteBtn.disabled = true;
    deleteBtn.textContent = 'Deleting...';
    
    fetch(`/admin/expenses/${expenseIdToDelete}`, {
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
            closeDeleteExpenseConfirmation();
            showNotification(data.message, 'success');
            
            // Refresh the filtered expenses list
            applyFilters(currentPage);
        } else {
            showNotification(data.message || 'Failed to delete expense record', 'error');
            deleteBtn.disabled = false;
            deleteBtn.textContent = 'Delete';
        }
    })
    .catch(error => {
        console.error('Error deleting expense:', error);
        showNotification('An error occurred while deleting', 'error');
        deleteBtn.disabled = false;
        deleteBtn.textContent = 'Delete';
    });
}

function cloneExpense(expenseId) {
    cloneExpenseId = expenseId;
    // Get expense name for display
    const row = document.querySelector(`tr[data-expense-id="${expenseId}"]`);
    let expenseName = 'Expense';
    if (row) {
        const itemNameCell = row.querySelector('td:nth-child(3) .text-sm');
        if (itemNameCell) {
            expenseName = itemNameCell.textContent.trim() || 'Expense';
        }
    }
    document.getElementById('clone-expense-name').textContent = expenseName;
    document.getElementById('cloneExpenseConfirmationModal').classList.remove('hidden');
}

function closeCloneExpenseConfirmation() {
    document.getElementById('cloneExpenseConfirmationModal').classList.add('hidden');
    cloneExpenseId = null;
}

function confirmCloneExpense() {
    if (!cloneExpenseId) return;
    
    const expenseIdToClone = cloneExpenseId;
    const cloneBtn = event.target;
    
    cloneBtn.disabled = true;
    cloneBtn.textContent = 'Duplicating...';
    
    fetch(`/admin/expenses/${expenseIdToClone}/clone`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeCloneExpenseConfirmation();
            showNotification(data.message, 'success');
            
            // Refresh the filtered expenses list
            applyFilters(currentPage);
        } else {
            showNotification(data.message || 'Failed to duplicate expense record', 'error');
            cloneBtn.disabled = false;
            cloneBtn.textContent = 'Duplicate';
        }
    })
    .catch(error => {
        console.error('Error cloning expense:', error);
        showNotification('An error occurred while duplicating', 'error');
        cloneBtn.disabled = false;
        cloneBtn.textContent = 'Duplicate';
    });
}

function addExpenseRow(expense) {
    const tbody = document.querySelector('table tbody');
    const emptyRow = tbody.querySelector('tr td[colspan]');
    
    if (emptyRow) {
        emptyRow.closest('tr').remove();
    }
    
    const typeClass = expense.type_name === 'Purchase' ? 'bg-blue-100 text-blue-800' :
                     expense.type_name === 'Advance' ? 'bg-yellow-100 text-yellow-800' :
                     expense.type_name === 'Vehicle rent' ? 'bg-purple-100 text-purple-800' :
                     'bg-gray-100 text-gray-800';
    
    const row = document.createElement('tr');
    row.setAttribute('data-expense-id', expense.id);
    row.innerHTML = `
        <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm text-gray-900">${expense.date}</div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${typeClass}">
                ${expense.type_name || 'N/A'}
            </span>
        </td>
        <td class="px-6 py-4">
            <div class="text-sm font-medium text-gray-900">${expense.item_name || 'N/A'}</div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm text-gray-900">${expense.project_name}</div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm text-gray-900">${expense.category_name}</div>
            ${expense.subcategory_name ? `<div class="text-xs text-gray-500">${expense.subcategory_name}</div>` : ''}
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm text-gray-900">${expense.staff_name}</div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm font-semibold text-red-600">$${expense.amount}</div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
            <div class="d-flex gap-1 text-nowrap">
                <button onclick="openViewExpenseModal(${expense.id})" class="btn btn-outline-primary btn-sm" title="View">
                    <i class="bi bi-eye"></i>
                </button>
                <button onclick="openEditExpenseModal(${expense.id})" class="btn btn-outline-warning btn-sm" title="Edit">
                    <i class="bi bi-pencil"></i>
                </button>
                <button onclick="cloneExpense(${expense.id})" class="btn btn-outline-info btn-sm" title="Duplicate">
                    <i class="bi bi-files"></i>
                </button>
                <button onclick="showDeleteExpenseConfirmation(${expense.id}, '${(expense.item_name || 'Expense').replace(/'/g, "\\'")}')" class="btn btn-outline-danger btn-sm" title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </td>
    `;
    
    tbody.insertBefore(row, tbody.firstChild);
}

function updateExpenseRow(expense) {
    const row = document.querySelector(`tr[data-expense-id="${expense.id}"]`);
    if (row) {
        const typeClass = expense.type_name === 'Purchase' ? 'bg-blue-100 text-blue-800' :
                         expense.type_name === 'Advance' ? 'bg-yellow-100 text-yellow-800' :
                         expense.type_name === 'Vehicle rent' ? 'bg-purple-100 text-purple-800' :
                         'bg-gray-100 text-gray-800';
        
        row.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">${expense.date}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${typeClass}">
                    ${expense.type_name || 'N/A'}
                </span>
            </td>
            <td class="px-6 py-4">
                <div class="text-sm font-medium text-gray-900">${expense.item_name || 'N/A'}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">${expense.project_name}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">${expense.category_name}</div>
                ${expense.subcategory_name ? `<div class="text-xs text-gray-500">${expense.subcategory_name}</div>` : ''}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">${expense.staff_name}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-semibold text-red-600">$${expense.amount}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="d-flex gap-1 text-nowrap">
                    <button onclick="openViewExpenseModal(${expense.id})" class="btn btn-outline-primary btn-sm" title="View">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button onclick="openEditExpenseModal(${expense.id})" class="btn btn-outline-warning btn-sm" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button onclick="cloneExpense(${expense.id})" class="btn btn-outline-info btn-sm" title="Duplicate">
                        <i class="bi bi-files"></i>
                    </button>
                    <button onclick="showDeleteExpenseConfirmation(${expense.id}, '${(expense.item_name || 'Expense').replace(/'/g, "\\'")}')" class="btn btn-outline-danger btn-sm" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        `;
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

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        if (!document.getElementById('expenseModal').classList.contains('hidden')) {
            closeExpenseModal();
        }
        if (!document.getElementById('viewExpenseModal').classList.contains('hidden')) {
            closeViewExpenseModal();
        }
        if (!document.getElementById('deleteExpenseConfirmationModal').classList.contains('hidden')) {
            closeDeleteExpenseConfirmation();
        }
        if (!document.getElementById('cloneExpenseConfirmationModal').classList.contains('hidden')) {
            closeCloneExpenseConfirmation();
        }
    }
});

document.getElementById('expenseModal').addEventListener('click', function(e) {
    if (e.target === this) closeExpenseModal();
});

document.getElementById('viewExpenseModal').addEventListener('click', function(e) {
    if (e.target === this) closeViewExpenseModal();
});

document.getElementById('deleteExpenseConfirmationModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteExpenseConfirmation();
});

document.getElementById('cloneExpenseConfirmationModal').addEventListener('click', function(e) {
    if (e.target === this) closeCloneExpenseConfirmation();
});
</script>
@endpush
@endsection

