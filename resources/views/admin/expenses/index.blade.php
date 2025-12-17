@extends('admin.layout')

@section('title', 'Expense Records')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-3xl font-bold text-gray-900">Expense Records</h1>
    <a href="{{ route('admin.expenses.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition duration-200">
        Add New Expense
    </a>
</div>

<div class="mb-4 bg-white shadow-lg rounded-lg p-4">
    <form method="GET" action="{{ route('admin.expenses.index') }}" id="filterForm" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
        @if($projects->count() > 0)
        <div>
            <label for="project_id" class="block text-sm font-medium text-gray-700 mb-2">Project</label>
            <select name="project_id" id="project_id" onchange="this.form.submit()"
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
            <select name="expense_type_id" id="expense_type_id" onchange="this.form.submit()"
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
            <select name="category_id" id="category_id" onchange="loadSubcategories()"
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
            <select name="subcategory_id" id="subcategory_id" onchange="this.form.submit()"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Subcategories</option>
                @foreach($subcategories as $subcategory)
                    <option value="{{ $subcategory->id }}" {{ request('subcategory_id') == $subcategory->id ? 'selected' : '' }}>
                        {{ $subcategory->name }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <div class="flex items-end">
            <button type="button" onclick="clearFilters()" class="w-full px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition duration-200">
                Clear Filters
            </button>
        </div>
    </form>
</div>

<script>
    function loadSubcategories() {
        const categoryId = document.getElementById('category_id').value;
        const subcategorySelect = document.getElementById('subcategory_id');
        const form = document.getElementById('filterForm');
        
        // Clear existing options
        subcategorySelect.innerHTML = '<option value="">All Subcategories</option>';
        
        if (!categoryId) {
            // If no category selected, submit form to clear subcategory filter
            form.submit();
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
            
            // Auto-submit form to apply category filter
            form.submit();
        })
        .catch(error => {
            console.error('Error loading subcategories:', error);
            subcategorySelect.innerHTML = '<option value="">Error loading</option>';
            subcategorySelect.disabled = false;
        });
    }
    
    function clearFilters() {
        window.location.href = '{{ route("admin.expenses.index") }}';
    }
    
    // Load subcategories on page load if category is pre-selected
    document.addEventListener('DOMContentLoaded', function() {
        const categoryId = document.getElementById('category_id').value;
        if (categoryId) {
            // Don't auto-submit, just load the subcategories
            const subcategorySelect = document.getElementById('subcategory_id');
            subcategorySelect.disabled = true;
            subcategorySelect.innerHTML = '<option value="">Loading...</option>';
            
            fetch(`/admin/categories/${categoryId}/subcategories`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                subcategorySelect.innerHTML = '<option value="">All Subcategories</option>';
                
                if (data && Array.isArray(data) && data.length > 0) {
                    data.forEach(subcategory => {
                        const option = document.createElement('option');
                        option.value = subcategory.id;
                        option.textContent = subcategory.name;
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
                subcategorySelect.innerHTML = '<option value="">All Subcategories</option>';
                subcategorySelect.disabled = false;
            });
        }
    });
</script>

<div class="bg-white shadow-lg rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
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
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($expenses as $expense)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $expense->date->format('M d, Y') }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
    {{ $expense->expenseType ? $expense->expenseType->name : '' }}
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
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.expenses.show', $expense) }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-eye me-1"></i> View
                            </a>
                            <a href="{{ route('admin.expenses.edit', $expense) }}" class="btn btn-outline-warning btn-sm">
                                <i class="bi bi-pencil me-1"></i> Edit
                            </a>
                            <form action="{{ route('admin.expenses.destroy', $expense) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this expense record?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                    <i class="bi bi-trash me-1"></i> Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                        No expense records found. <a href="{{ route('admin.expenses.create') }}" class="text-indigo-600 hover:text-indigo-900">Add one now</a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<x-pagination :paginator="$expenses" wrapper-class="mt-4" />
@endsection

