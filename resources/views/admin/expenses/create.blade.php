@extends('admin.layout')

@section('title', 'Add New Expense')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Add New Expense</h1>
    <p class="mt-2 text-gray-600">Record a new expense</p>
</div>

<div class="bg-white shadow-lg rounded-lg p-6">
    <form action="{{ route('admin.expenses.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="project_id" class="block text-sm font-medium text-gray-700 mb-2">Project</label>
                <select name="project_id" id="project_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('project_id') border-red-500 @enderror">
                    <option value="">Select a project (optional)</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>
                            {{ $project->name }}
                        </option>
                    @endforeach
                </select>
                @error('project_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
    <label for="expense_type_id" class="block text-sm font-medium text-gray-700 mb-2">Expense Type <span class="text-red-500">*</span></label>
    <select name="expense_type_id" id="expense_type_id" required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('expense_type_id') border-red-500 @enderror">
        <option value="">Select Expense Type</option>
        @foreach($expenseTypes as $type)
            <option value="{{ $type->id }}" {{ old('expense_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
        @endforeach
    </select>
    @error('expense_type_id')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

            <div id="staff_field" style="display: none;">
                <label for="staff_id" class="block text-sm font-medium text-gray-700 mb-2">Staff Member <span class="text-red-500">*</span></label>
                <select name="staff_id" id="staff_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('staff_id') border-red-500 @enderror">
                    <option value="">Select staff member</option>
                    @foreach($staff as $member)
                        <option value="{{ $member->id }}" {{ old('staff_id') == $member->id ? 'selected' : '' }}>
                            {{ $member->name }} - {{ $member->position ? $member->position->name : 'N/A' }}
                        </option>
                    @endforeach
                </select>
                @error('staff_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div id="item_field">
                <label for="item_name" class="block text-sm font-medium text-gray-700 mb-2">Item Name</label>
                <input type="text" name="item_name" id="item_name" value="{{ old('item_name') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('item_name') border-red-500 @enderror"
                       placeholder="e.g., Office Supplies, Equipment">
                @error('item_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">Category <span class="text-red-500">*</span></label>
                <select name="category_id" id="category_id" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('category_id') border-red-500 @enderror">
                    <option value="">Select a category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="subcategory_id" class="block text-sm font-medium text-gray-700 mb-2">Subcategory</label>
                <select name="subcategory_id" id="subcategory_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('subcategory_id') border-red-500 @enderror">
                    <option value="">Select a subcategory (optional)</option>
                </select>
                @error('subcategory_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">Amount <span class="text-red-500">*</span></label>
                <input type="number" name="amount" id="amount" value="{{ old('amount') }}" step="0.01" min="0" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('amount') border-red-500 @enderror">
                @error('amount')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="date" class="block text-sm font-medium text-gray-700 mb-2">Date <span class="text-red-500">*</span></label>
                <input type="date" name="date" id="date" value="{{ old('date', date('Y-m-d')) }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('date') border-red-500 @enderror">
                @error('date')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                <select name="payment_method" id="payment_method"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('payment_method') border-red-500 @enderror">
                    <option value="">Select payment method</option>
                    <option value="Cash" {{ old('payment_method') == 'Cash' ? 'selected' : '' }}>Cash</option>
                    <option value="Bank Transfer" {{ old('payment_method') == 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    <option value="Cheque" {{ old('payment_method') == 'Cheque' ? 'selected' : '' }}>Cheque</option>
                    <option value="Credit Card" {{ old('payment_method') == 'Credit Card' ? 'selected' : '' }}>Credit Card</option>
                    <option value="Other" {{ old('payment_method') == 'Other' ? 'selected' : '' }}>Other</option>
                </select>
                @error('payment_method')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea name="description" id="description" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                <textarea name="notes" id="notes" rows="2"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('notes') border-red-500 @enderror">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label for="images" class="block text-sm font-medium text-gray-700 mb-2">Upload Images</label>
                <input type="file" name="images[]" id="images" multiple accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('images.*') border-red-500 @enderror">
                <p class="mt-1 text-sm text-gray-500">You can select multiple images (JPEG, PNG, JPG, GIF, WebP). Max 5MB per image.</p>
                @error('images.*')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                
                <div id="imagePreview" class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4"></div>
            </div>
        </div>

        <div class="flex items-center justify-end space-x-4 mt-6">
            <a href="{{ route('admin.expenses.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">
                Cancel
            </a>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-200">
                Add Expense
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Show/hide fields based on expense type
        function toggleFields() {
            const expenseType = document.getElementById('expense_type_id').value;
            const staffField = document.getElementById('staff_field');
            const itemField = document.getElementById('item_field');
            const staffSelect = document.getElementById('staff_id');
            
            if (expenseType === 'salary' || expenseType === 'advance') {
                staffField.style.display = 'block';
                itemField.style.display = 'none';
                staffSelect.setAttribute('required', 'required');
            } else {
                staffField.style.display = 'none';
                itemField.style.display = 'block';
                staffSelect.removeAttribute('required');
                staffSelect.value = '';
            }
            
            if (expenseType === 'rent') {
                itemField.style.display = 'none';
            }
        }
        
        const expenseTypeSelect = document.getElementById('expense_type_id');
        if (expenseTypeSelect) {
            expenseTypeSelect.addEventListener('change', toggleFields);
            toggleFields(); // Run on page load
        }
        
        // Load subcategories dynamically when category changes
        const categorySelect = document.getElementById('category_id');
        const subcategorySelect = document.getElementById('subcategory_id');
        
        if (categorySelect && subcategorySelect) {
            categorySelect.addEventListener('change', function() {
                const categoryId = this.value;
                
                // Clear existing options except the first one
                subcategorySelect.innerHTML = '<option value="">Select a subcategory (optional)</option>';
                
                if (!categoryId) {
                    return;
                }
                
                // Show loading state
                subcategorySelect.disabled = true;
                subcategorySelect.innerHTML = '<option value="">Loading subcategories...</option>';
                
                // Fetch subcategories via AJAX
                fetch(`/admin/categories/${categoryId}/subcategories`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    subcategorySelect.innerHTML = '<option value="">Select a subcategory (optional)</option>';
                    
                    if (data && Array.isArray(data) && data.length > 0) {
                        data.forEach(subcategory => {
                            const option = document.createElement('option');
                            option.value = subcategory.id;
                            option.textContent = subcategory.name;
                            subcategorySelect.appendChild(option);
                        });
                    } else {
                        // No subcategories found
                        subcategorySelect.innerHTML = '<option value="">No subcategories available</option>';
                    }
                    
                    subcategorySelect.disabled = false;
                })
                .catch(error => {
                    console.error('Error loading subcategories:', error);
                    subcategorySelect.innerHTML = '<option value="">Error loading subcategories</option>';
                    subcategorySelect.disabled = false;
                });
            });
            
            // Load subcategories on page load if category is pre-selected
            const categoryId = categorySelect.value;
            if (categoryId) {
                categorySelect.dispatchEvent(new Event('change'));
            }
        }
    });

    // Image preview functionality
    document.getElementById('images').addEventListener('change', function(e) {
        const preview = document.getElementById('imagePreview');
        preview.innerHTML = '';
        
        const files = e.target.files;
        if (files.length > 0) {
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
    });
</script>
@endsection

