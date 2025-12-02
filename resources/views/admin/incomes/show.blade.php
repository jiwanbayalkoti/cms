@extends('admin.layout')

@section('title', 'Income Details')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-3xl font-bold text-gray-900">Income Details</h1>
    <div class="space-x-2">
        <a href="{{ route('admin.incomes.edit', $income) }}" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg transition duration-200">
            Edit
        </a>
        <a href="{{ route('admin.incomes.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition duration-200">
            Back to List
        </a>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="bg-white shadow-lg rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Income Information</h2>
        <dl class="space-y-4">
            <div>
                <dt class="text-sm font-medium text-gray-500">Source</dt>
                <dd class="mt-1 text-sm font-semibold text-gray-900">{{ $income->source }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Amount</dt>
                <dd class="mt-1 text-2xl font-bold text-green-600">${{ number_format($income->amount, 2) }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Date</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $income->date->format('M d, Y') }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Category</dt>
                <dd class="mt-1">
                    <a href="{{ route('admin.categories.show', $income->category) }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                        {{ $income->category->name }}
                    </a>
                </dd>
            </div>
            @if($income->subcategory)
            <div>
                <dt class="text-sm font-medium text-gray-500">Subcategory</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $income->subcategory->name }}</dd>
            </div>
            @endif
            <div>
                <dt class="text-sm font-medium text-gray-500">Payment Method</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $income->payment_method ?? 'N/A' }}</dd>
            </div>
        </dl>
    </div>

    <div class="bg-white shadow-lg rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Additional Details</h2>
        <dl class="space-y-4">
            @if($income->description)
            <div>
                <dt class="text-sm font-medium text-gray-500">Description</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $income->description }}</dd>
            </div>
            @endif
            @if($income->notes)
            <div>
                <dt class="text-sm font-medium text-gray-500">Notes</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $income->notes }}</dd>
            </div>
            @endif
            <div>
                <dt class="text-sm font-medium text-gray-500">Created At</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $income->created_at->format('M d, Y H:i') }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Updated At</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $income->updated_at->format('M d, Y H:i') }}</dd>
            </div>
        </dl>
    </div>
</div>
@endsection

