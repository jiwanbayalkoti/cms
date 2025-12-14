@extends('admin.layout')

@section('title', 'Edit Expense')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Edit Expense Record</h1>
    <p class="mt-2 text-gray-600">Update expense information</p>
</div>

<div class="bg-white shadow-lg rounded-lg p-6">
    <form action="{{ route('admin.expenses.update', $expense) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="project_id" class="block text-sm font-medium text-gray-700 mb-2">Project</label>
                <select name="project_id" id="project_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('project_id') border-red-500 @enderror">
                    <option value="">Select a project (optional)</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" {{ old('project_id', $expense->project_id) == $project->id ? 'selected' : '' }}>
                            {{ $project->name }}
                        </option>
                    @endforeach
                </select>
                @error('project_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="expense_type" class="block text-sm font-medium text-gray-700 mb-2">Expense Type <span class="text-red-500">*</span></label>
                <select name="expense_type" id="expense_type" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('expense_type') border-red-500 @enderror">
                    <option value="purchase" {{ old('expense_type', $expense->expense_type) == 'purchase' ? 'selected' : '' }}>Purchase</option>
                    <option value="salary" {{ old('expense_type', $expense->expense_type) == 'salary' ? 'selected' : '' }}>Salary</option>
                    <option value="advance" {{ old('expense_type', $expense->expense_type) == 'advance' ? 'selected' : '' }}>Advance</option>
                    <option value="rent" {{ old('expense_type', $expense->expense_type) == 'rent' ? 'selected' : '' }}>Rent</option>
                </select>
                @error('expense_type')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div id="staff_field" style="display: none;">
                <label for="staff_id" class="block text-sm font-medium text-gray-700 mb-2">Staff Member <span class="text-red-500">*</span></label>
                <select name="staff_id" id="staff_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('staff_id') border-red-500 @enderror">
                    <option value="">Select staff member</option>
                    @foreach($staff as $member)
                        <option value="{{ $member->id }}" {{ old('staff_id', $expense->staff_id) == $member->id ? 'selected' : '' }}>
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
                <input type="text" name="item_name" id="item_name" value="{{ old('item_name', $expense->item_name) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('item_name') border-red-500 @enderror">
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
                        <option value="{{ $category->id }}" {{ old('category_id', $expense->category_id) == $category->id ? 'selected' : '' }}>
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
                    @foreach($subcategories as $subcategory)
                        <option value="{{ $subcategory->id }}" {{ old('subcategory_id', $expense->subcategory_id) == $subcategory->id ? 'selected' : '' }}
                                data-category="{{ $subcategory->category_id }}">
                            {{ $subcategory->name }}
                        </option>
                    @endforeach
                </select>
                @error('subcategory_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">Amount <span class="text-red-500">*</span></label>
                <input type="number" name="amount" id="amount" value="{{ old('amount', $expense->amount) }}" step="0.01" min="0" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('amount') border-red-500 @enderror">
                @error('amount')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="date" class="block text-sm font-medium text-gray-700 mb-2">Date <span class="text-red-500">*</span></label>
                <input type="date" name="date" id="date" value="{{ old('date', $expense->date->format('Y-m-d')) }}" required
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
                    <option value="Cash" {{ old('payment_method', $expense->payment_method) == 'Cash' ? 'selected' : '' }}>Cash</option>
                    <option value="Bank Transfer" {{ old('payment_method', $expense->payment_method) == 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    <option value="Cheque" {{ old('payment_method', $expense->payment_method) == 'Cheque' ? 'selected' : '' }}>Cheque</option>
                    <option value="Credit Card" {{ old('payment_method', $expense->payment_method) == 'Credit Card' ? 'selected' : '' }}>Credit Card</option>
                    <option value="Other" {{ old('payment_method', $expense->payment_method) == 'Other' ? 'selected' : '' }}>Other</option>
                </select>
                @error('payment_method')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea name="description" id="description" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('description') border-red-500 @enderror">{{ old('description', $expense->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                <textarea name="notes" id="notes" rows="2"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('notes') border-red-500 @enderror">{{ old('notes', $expense->notes) }}</textarea>
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

                <!-- Display existing images -->
                @if($expense->images && count($expense->images) > 0)
                    <div class="mt-4">
                        <p class="text-sm font-medium text-gray-700 mb-2">Current Images:</p>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            @foreach($expense->images as $image)
                                <div class="relative group">
                                    <img src="{{ asset('storage/' . $image) }}" class="w-full h-32 object-cover rounded-lg border border-gray-200" alt="Expense Image">
                                    <label class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
                                        <input type="checkbox" name="delete_images[]" value="{{ $image }}" class="hidden" onchange="this.previousElementSibling.style.opacity = this.checked ? '1' : '0'">
                                        <svg class="w-4 h-4 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Check images to delete them</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="flex items-center justify-end space-x-4 mt-6">
            <a href="{{ route('admin.expenses.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">
                Cancel
            </a>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-200">
                Update Expense
            </button>
        </div>
    </form>
</div>

<script>
    function toggleFields() {
        const expenseType = document.getElementById('expense_type').value;
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
        }
        
        if (expenseType === 'rent') {
            itemField.style.display = 'none';
        }
    }
    
    document.getElementById('expense_type').addEventListener('change', toggleFields);
    toggleFields();
    
    document.getElementById('category_id').addEventListener('change', function() {
        const categoryId = this.value;
        const subcategorySelect = document.getElementById('subcategory_id');
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

