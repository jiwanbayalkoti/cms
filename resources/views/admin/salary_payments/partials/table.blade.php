@forelse($salaryPayments as $payment)
    <tr data-payment-id="{{ $payment->id }}">
        <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm font-medium text-gray-900">{{ $payment->staff->name }}</div>
            @if($payment->project)
                <div class="text-sm text-gray-500">{{ $payment->project->name }}</div>
            @endif
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
            {{ $payment->payment_month_name }}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
            Rs. {{ number_format($payment->base_salary, 2) }}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
            Rs. {{ number_format($payment->gross_amount, 2) }}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-orange-600 font-medium">
            Rs. {{ number_format($payment->tax_amount ?? 0, 2) }}
            @if($payment->tax_amount > 0)
                <div class="text-xs text-gray-500 mt-1">
                    {{ ucfirst($payment->assessment_type ?? 'single') }}
                </div>
            @endif
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
            Rs. {{ number_format($payment->net_amount, 2) }}
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <span class="px-2 py-1 text-xs font-semibold rounded-full
                @if($payment->status === 'paid') bg-green-100 text-green-800
                @elseif($payment->status === 'partial') bg-blue-100 text-blue-800
                @elseif($payment->status === 'pending') bg-yellow-100 text-yellow-800
                @else bg-red-100 text-red-800
                @endif">
                {{ ucfirst($payment->status) }}
            </span>
            @if($payment->status === 'partial')
                <div class="text-xs text-gray-500 mt-1">
                    Paid: Rs. {{ number_format($payment->paid_amount, 2) }}
                </div>
            @endif
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
            {{ $payment->payment_date->format('M d, Y') }}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
            <div class="d-flex gap-1 text-nowrap">
                <button onclick="openViewSalaryPaymentModal({{ $payment->id }})" class="btn btn-sm btn-outline-primary" title="View">
                    <i class="bi bi-eye"></i>
                </button>
                <button onclick="openEditSalaryPaymentModal({{ $payment->id }})" class="btn btn-sm btn-outline-warning" title="Edit">
                    <i class="bi bi-pencil"></i>
                </button>
                <button onclick="showDeleteSalaryPaymentConfirmation({{ $payment->id }}, '{{ addslashes($payment->staff->name) }}', '{{ $payment->payment_month_name }}')" class="btn btn-sm btn-outline-danger" title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="9" class="px-6 py-4 text-center text-gray-500">
            No salary payments found.
        </td>
    </tr>
@endforelse

