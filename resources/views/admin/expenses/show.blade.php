@extends('admin.layout')

@section('title', 'Expense Details')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-3xl font-bold text-gray-900">Expense Details</h1>
    <div class="space-x-2">
        <a href="{{ route('admin.expenses.edit', $expense) }}" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg transition duration-200">
            Edit
        </a>
        <a href="{{ route('admin.expenses.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition duration-200">
            Back to List
        </a>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="bg-white shadow-lg rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Expense Information</h2>
        <dl class="space-y-4">
            <div>
                <dt class="text-sm font-medium text-gray-500">Expense Type</dt>
                <dd class="mt-1">
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full 
                        {{ $expense->expense_type === 'salary' ? 'bg-blue-100 text-blue-800' : 
                           ($expense->expense_type === 'advance' ? 'bg-yellow-100 text-yellow-800' : 
                           ($expense->expense_type === 'rent' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800')) }}">
                        {{ ucfirst($expense->expense_type) }}
                    </span>
                </dd>
            </div>
            @if($expense->item_name)
            <div>
                <dt class="text-sm font-medium text-gray-500">Item Name</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $expense->item_name }}</dd>
            </div>
            @endif
            <div>
                <dt class="text-sm font-medium text-gray-500">Amount</dt>
                <dd class="mt-1 text-2xl font-bold text-red-600">${{ number_format($expense->amount, 2) }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Date</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $expense->date->format('M d, Y') }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Category</dt>
                <dd class="mt-1">
                    <a href="{{ route('admin.categories.show', $expense->category) }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                        {{ $expense->category->name }}
                    </a>
                </dd>
            </div>
            @if($expense->subcategory)
            <div>
                <dt class="text-sm font-medium text-gray-500">Subcategory</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $expense->subcategory->name }}</dd>
            </div>
            @endif
            @if($expense->staff)
            <div>
                <dt class="text-sm font-medium text-gray-500">Staff Member</dt>
                <dd class="mt-1">
                    <a href="{{ route('admin.staff.show', $expense->staff) }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                        {{ $expense->staff->name }}
                    </a>
                </dd>
            </div>
            @endif
            <div>
                <dt class="text-sm font-medium text-gray-500">Payment Method</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $expense->payment_method ?? 'N/A' }}</dd>
            </div>
        </dl>
    </div>

    <div class="bg-white shadow-lg rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Additional Details</h2>
        <dl class="space-y-4">
            @if($expense->description)
            <div>
                <dt class="text-sm font-medium text-gray-500">Description</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $expense->description }}</dd>
            </div>
            @endif
            @if($expense->notes)
            <div>
                <dt class="text-sm font-medium text-gray-500">Notes</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $expense->notes }}</dd>
            </div>
            @endif
            <div>
                <dt class="text-sm font-medium text-gray-500">Created At</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $expense->created_at->format('M d, Y H:i') }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Updated At</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $expense->updated_at->format('M d, Y H:i') }}</dd>
            </div>
        </dl>
    </div>
</div>

@if($expense->images && count($expense->images) > 0)
<div class="mt-6 bg-white shadow-lg rounded-lg p-6">
    <h2 class="text-xl font-semibold text-gray-900 mb-4">Images</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach($expense->images as $image)
            <div class="relative group">
                <img src="{{ asset('storage/' . $image) }}" class="w-full h-48 object-cover rounded-lg border border-gray-200 cursor-pointer" alt="Expense Image" onclick="openImageModal('{{ asset('storage/' . $image) }}')">
            </div>
        @endforeach
    </div>
</div>
@endif

<!-- Image Modal -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 hidden items-center justify-center z-50">
    <div class="relative max-w-4xl max-h-full p-4">
        <button onclick="closeImageModal()" class="absolute top-4 right-4 text-white bg-red-500 rounded-full p-2 hover:bg-red-600 transition duration-200 z-10">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <img id="modalImage" src="" class="max-w-full max-h-screen rounded-lg">
    </div>
</div>

<script>
function openImageModal(imageSrc) {
    document.getElementById('modalImage').src = imageSrc;
    document.getElementById('imageModal').classList.remove('hidden');
    document.getElementById('imageModal').classList.add('flex');
}

function closeImageModal() {
    document.getElementById('imageModal').classList.add('hidden');
    document.getElementById('imageModal').classList.remove('flex');
}

// Close modal on click outside
document.getElementById('imageModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeImageModal();
    }
});
</script>
@endsection

