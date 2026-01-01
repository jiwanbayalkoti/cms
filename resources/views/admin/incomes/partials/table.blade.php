<tbody id="incomesTableBody">
    @forelse($incomes as $income)
        <tr data-income-id="{{ $income->id }}">
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
            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                No income records found. <button onclick="openCreateIncomeModal()" class="text-indigo-600 hover:text-indigo-900">Add one now</button>
            </td>
        </tr>
    @endforelse
</tbody>

