@extends('admin.layout')

@section('title', 'Customers')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Customers</h1>
        <p class="text-muted mb-0">Manage customer information</p>
    </div>
    <a href="{{ route('admin.customers.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Add Customer
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>Address</th>
                        <th>Invoices</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        <tr>
                            <td><strong>{{ $customer->name }}</strong></td>
                            <td>{{ $customer->phone ?? $customer->mobile ?? '—' }}</td>
                            <td>{{ $customer->email ?? '—' }}</td>
                            <td>{{ Str::limit($customer->address ?? '—', 30) }}</td>
                            <td>
                                <span class="badge bg-info">{{ $customer->sales_invoices_count }}</span>
                            </td>
                            <td>
                                <span class="badge rounded-pill {{ $customer->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $customer->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-eye me-1"></i> View
                                    </a>
                                    <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-outline-warning btn-sm">
                                        <i class="bi bi-pencil me-1"></i> Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <p class="text-muted mb-0">No customers found.</p>
                                <a href="{{ route('admin.customers.create') }}" class="btn btn-primary btn-sm mt-2">Add First Customer</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <x-pagination :paginator="$customers" />
    </div>
</div>
@endsection

