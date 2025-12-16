@extends('admin.layout')

@section('title', 'Expense Records')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-3xl font-bold text-gray-900">Expense Records</h1>
    <a href="{{ route('admin.expenses.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition duration-200">
        Add New Expense
    </a>
</div>

@if($projects->count() > 0)
<div class="mb-4 bg-white shadow-lg rounded-lg p-4">
    <form method="GET" action="{{ route('admin.expenses.index') }}" class="flex gap-4 items-end">
        <div class="flex-1">
            <label for="project_id" class="block text-sm font-medium text-gray-700 mb-2">Filter by Project</label>
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
    </form>
</div>
@endif

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
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $expense->expense_type === 'salary' ? 'bg-blue-100 text-blue-800' : 
                               ($expense->expense_type === 'advance' ? 'bg-yellow-100 text-yellow-800' : 
                               ($expense->expense_type === 'rent' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800')) }}">
                            {{ ucfirst($expense->expense_type) }}
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

