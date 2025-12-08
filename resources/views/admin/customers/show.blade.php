@extends('admin.layout')

@section('title', 'Customer Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Customer Details</h1>
        <p class="text-muted mb-0">{{ $customer->name }}</p>
    </div>
    <div>
        <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-warning me-2">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
        <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">Back to List</a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Customer Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="200">Name:</th>
                        <td><strong>{{ $customer->name }}</strong></td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td>{{ $customer->email ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th>Phone:</th>
                        <td>{{ $customer->phone ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th>Mobile:</th>
                        <td>{{ $customer->mobile ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th>Address:</th>
                        <td>{{ $customer->address ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th>City:</th>
                        <td>{{ $customer->city ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th>State:</th>
                        <td>{{ $customer->state ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th>Country:</th>
                        <td>{{ $customer->country ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th>ZIP Code:</th>
                        <td>{{ $customer->zip ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th>Tax Number:</th>
                        <td>{{ $customer->tax_number ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>
                            <span class="badge rounded-pill {{ $customer->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $customer->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                    </tr>
                    @if($customer->notes)
                        <tr>
                            <th>Notes:</th>
                            <td>{{ $customer->notes }}</td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Financial Summary</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="border rounded p-3">
                            <h6 class="text-muted mb-2">Total Sales</h6>
                            <h4 class="text-primary mb-0">{{ number_format($totalSales, 2) }}</h4>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3">
                            <h6 class="text-muted mb-2">Total Received</h6>
                            <h4 class="text-success mb-0">{{ number_format($totalReceived, 2) }}</h4>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3">
                            <h6 class="text-muted mb-2">Outstanding</h6>
                            <h4 class="text-danger mb-0">{{ number_format($totalOutstanding, 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        @if($customer->salesInvoices->count() > 0)
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Recent Sales Invoices ({{ $customer->salesInvoices->count() }})</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Date</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-end">Received</th>
                                    <th class="text-end">Balance</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($customer->salesInvoices as $invoice)
                                    <tr>
                                        <td><strong>{{ $invoice->invoice_number }}</strong></td>
                                        <td>{{ $invoice->invoice_date->format('Y-m-d') }}</td>
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
                                            <a href="{{ route('admin.sales-invoices.show', $invoice) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <a href="{{ route('admin.sales-invoices.create') }}?customer_id={{ $customer->id }}" class="btn btn-primary w-100 mb-2">
                    <i class="bi bi-receipt me-1"></i> Create Sales Invoice
                </a>
                @if($customer->salesInvoices->count() === 0)
                    <form action="{{ route('admin.customers.destroy', $customer) }}" method="POST" 
                          onsubmit="return confirm('Are you sure you want to delete this customer?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-trash me-1"></i> Delete Customer
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

