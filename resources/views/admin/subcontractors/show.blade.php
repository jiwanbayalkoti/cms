@extends('admin.layout')

@section('title', $subcontractor->name)

@section('content')
<div class="mb-6 flex flex-wrap justify-between items-start gap-4">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">{{ $subcontractor->name }}</h1>
        <p class="text-sm text-gray-500 mt-1">Sub-contractor details & payments (linked to expenses)</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.expenses.index', ['subcontractor_id' => $subcontractor->id]) }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 text-sm">
            View in Expenses
        </a>
        <a href="{{ route('admin.subcontractors.index', ['edit' => $subcontractor->id]) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm">Edit</a>
        <a href="{{ route('admin.subcontractors.index') }}" class="px-4 py-2 text-indigo-600 hover:text-indigo-800 text-sm">← All sub-contractors</a>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3">{{ session('success') }}</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="lg:col-span-1 bg-white shadow-lg rounded-lg p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Profile</h2>
        <dl class="space-y-2 text-sm">
            <div><dt class="text-gray-500">Contact person</dt><dd class="font-medium text-gray-900">{{ $subcontractor->contact_person ?? '—' }}</dd></div>
            <div><dt class="text-gray-500">Phone</dt><dd class="font-medium text-gray-900">{{ $subcontractor->phone ?? '—' }}</dd></div>
            <div><dt class="text-gray-500">Email</dt><dd class="font-medium text-gray-900">{{ $subcontractor->email ?? '—' }}</dd></div>
            <div><dt class="text-gray-500">PAN / VAT</dt><dd class="font-medium text-gray-900">{{ $subcontractor->pan_number ?? '—' }}</dd></div>
            <div>
                <dt class="text-gray-500">Work type</dt>
                <dd class="mt-1">
                    @if(!empty($subcontractor->work_types) && is_array($subcontractor->work_types))
                        <div class="flex flex-wrap gap-1">
                            @foreach($subcontractor->work_types as $wt)
                                <span class="inline-flex px-2 py-0.5 text-xs rounded-md bg-indigo-50 text-indigo-800 border border-indigo-100">{{ $wt }}</span>
                            @endforeach
                        </div>
                    @else
                        <span class="text-gray-400">—</span>
                    @endif
                </dd>
            </div>
            <div><dt class="text-gray-500">Address</dt><dd class="text-gray-900 whitespace-pre-line">{{ $subcontractor->address ?? '—' }}</dd></div>
            <div><dt class="text-gray-500">Notes</dt><dd class="text-gray-900 whitespace-pre-line">{{ $subcontractor->notes ?? '—' }}</dd></div>
            <div>
                <dt class="text-gray-500">Status</dt>
                <dd>
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $subcontractor->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ $subcontractor->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </dd>
            </div>
        </dl>
    </div>

    <div class="lg:col-span-2 bg-white shadow-lg rounded-lg p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Record payment</h2>
        <p class="text-sm text-gray-600 mb-4">Creates an <strong>expense</strong> row for this sub-contractor. Category and expense type are set automatically from your company defaults (first active expense category; first expense type if any).</p>

        <form action="{{ route('admin.subcontractors.payments.store', $subcontractor) }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="project_id" class="block text-sm font-medium text-gray-700 mb-2">Project <span class="text-red-500">*</span></label>
                    <select name="project_id" id="project_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('project_id') border-red-500 @enderror">
                        <option value="">Select project</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}" {{ (string) old('project_id') === (string) $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                    @error('project_id')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-2">Date <span class="text-red-500">*</span></label>
                    <input type="date" name="date" id="date" required value="{{ old('date', now()->toDateString()) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('date') border-red-500 @enderror">
                    @error('date')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">Amount <span class="text-red-500">*</span></label>
                    <input type="number" name="amount" id="amount" step="0.01" min="0.01" required value="{{ old('amount') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('amount') border-red-500 @enderror">
                    @error('amount')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                @php
                    $paymentMethodChoices = ['Cash', 'Bank Transfer', 'Cheque', 'Credit Card', 'Other'];
                    $pmOld = old('payment_method');
                    $showLegacyPaymentMethod = $pmOld !== null && $pmOld !== '' && ! in_array($pmOld, $paymentMethodChoices, true);
                @endphp
                <div>
                    <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">Payment method</label>
                    <select name="payment_method" id="payment_method"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('payment_method') border-red-500 @enderror">
                        <option value="">Select payment method</option>
                        @if ($showLegacyPaymentMethod)
                            <option value="{{ $pmOld }}" selected>{{ $pmOld }}</option>
                        @endif
                        @foreach ($paymentMethodChoices as $pm)
                            <option value="{{ $pm }}" {{ $pmOld === $pm ? 'selected' : '' }}>{{ $pm }}</option>
                        @endforeach
                    </select>
                    @error('payment_method')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <input type="text" name="description" id="description" value="{{ old('description') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="md:col-span-2">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                    <textarea name="notes" id="notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                </div>
            </div>
            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Save payment</button>
        </form>
    </div>
</div>

<div class="bg-white shadow-lg rounded-lg overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 flex flex-wrap justify-between items-center gap-2">
        <h2 class="text-lg font-semibold text-gray-900">Payment history (expenses)</h2>
        <p class="text-sm font-semibold text-gray-900">Total: <span class="text-indigo-700">{{ number_format((float) $paymentsTotal, 2) }}</span></p>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Project</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($expenses as $exp)
                    <tr>
                        <td class="px-6 py-3 text-sm text-gray-900">{{ $exp->date->format('M d, Y') }}</td>
                        <td class="px-6 py-3 text-sm text-gray-700">{{ $exp->project ? $exp->project->name : '—' }}</td>
                        <td class="px-6 py-3 text-sm text-gray-700">{{ $exp->category->name }}{{ $exp->subcategory ? ' / '.$exp->subcategory->name : '' }}</td>
                        <td class="px-6 py-3 text-sm font-medium text-gray-900">{{ number_format($exp->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">No payments recorded yet. Use the form above.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<x-pagination :paginator="$expenses" wrapper-class="mt-4" />

@endsection
