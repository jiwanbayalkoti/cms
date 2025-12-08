@extends('admin.layout')

@section('title', 'Sales Invoices')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Sales Invoices</h1>
        <p class="text-muted mb-0">Manage sales invoices to customers</p>
    </div>
    <a href="{{ route('admin.sales-invoices.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> New Sales Invoice
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Project</th>
                        <th class="text-end">Total Amount</th>
                        <th class="text-end">Received</th>
                        <th class="text-end">Balance</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                        <tr>
                            <td><strong>{{ $invoice->invoice_number }}</strong></td>
                            <td>{{ $invoice->invoice_date->format('Y-m-d') }}</td>
                            <td>{{ $invoice->customer->name ?? '—' }}</td>
                            <td>{{ $invoice->project->name ?? '—' }}</td>
                            <td class="text-end">{{ number_format($invoice->total_amount, 2) }}</td>
                            <td class="text-end">{{ number_format($invoice->received_amount, 2) }}</td>
                            <td class="text-end">
                                <strong class="{{ $invoice->balance_amount > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($invoice->balance_amount, 2) }}
                                </strong>
                            </td>
                            <td>
                                <span class="badge bg-{{ $invoice->status === 'paid' ? 'success' : ($invoice->status === 'pending' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($invoice->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.sales-invoices.show', $invoice) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-eye me-1"></i> View
                                    </a>
                                    @if($invoice->status === 'draft')
                                        <a href="{{ route('admin.sales-invoices.edit', $invoice) }}" class="btn btn-outline-warning btn-sm">
                                            <i class="bi bi-pencil me-1"></i> Edit
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <p class="text-muted mb-0">No sales invoices found.</p>
                                <a href="{{ route('admin.sales-invoices.create') }}" class="btn btn-primary btn-sm mt-2">Create First Invoice</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <x-pagination :paginator="$invoices" />
    </div>
</div>
@endsection

