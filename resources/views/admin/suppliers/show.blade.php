@extends('admin.layout')

@section('title', 'Supplier Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Supplier Details</h1>
    <div>
        <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="btn btn-warning me-2">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
        <a href="{{ route('admin.suppliers.index') }}" class="btn btn-outline-secondary">Back to List</a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        @php
            $f = $financial ?? null;
            $fmt = fn ($v) => number_format((float) ($v ?? 0), 2);
        @endphp

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Supplier Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="200">Name:</th>
                        <td><strong>{{ $supplier->name }}</strong></td>
                    </tr>
                    @if($supplier->contact)
                        <tr>
                            <th>Contact:</th>
                            <td>{{ $supplier->contact }}</td>
                        </tr>
                    @endif
                    @if($supplier->email)
                        <tr>
                            <th>Email:</th>
                            <td>{{ $supplier->email }}</td>
                        </tr>
                    @endif
                    @if($supplier->address)
                        <tr>
                            <th>Address:</th>
                            <td>{{ $supplier->address }}</td>
                        </tr>
                    @endif
                    <tr>
                        <th>Status:</th>
                        <td>
                            <span class="badge {{ $supplier->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        @if($f)
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Financial Details</h5>
                    <span class="badge {{ ($f['net_balance'] ?? 0) <= 0 ? 'bg-success' : 'bg-danger' }}">
                        {{ ($f['net_balance'] ?? 0) <= 0 ? 'Clear' : 'Due' }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <div class="p-3 border rounded bg-primary bg-opacity-10">
                                <div class="text-muted small">Total (Invoices + Vehicle Rent)</div>
                                <div class="h5 mb-0">{{ $fmt($f['gross_total'] ?? 0) }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 border rounded bg-success bg-opacity-10">
                                <div class="text-muted small">Paid (Direct + Advance)</div>
                                <div class="h5 mb-0">{{ $fmt(($f['gross_paid'] ?? 0) + ($f['advance_payments_total'] ?? 0)) }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 border rounded {{ ($f['net_balance'] ?? 0) <= 0 ? 'bg-success bg-opacity-10' : 'bg-danger bg-opacity-10' }}">
                                <div class="text-muted small">Balance (Net Due)</div>
                                <div class="h5 mb-0">{{ $fmt($f['net_balance'] ?? 0) }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Type</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Paid</th>
                                    <th class="text-end">Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Purchase Invoices</td>
                                    <td class="text-end">{{ $fmt($f['purchase_total'] ?? 0) }}</td>
                                    <td class="text-end">{{ $fmt($f['purchase_paid'] ?? 0) }}</td>
                                    <td class="text-end">{{ $fmt($f['purchase_balance'] ?? 0) }}</td>
                                </tr>
                                <tr>
                                    <td>Vehicle Rent</td>
                                    <td class="text-end">{{ $fmt($f['vehicle_total'] ?? 0) }}</td>
                                    <td class="text-end">{{ $fmt($f['vehicle_paid'] ?? 0) }}</td>
                                    <td class="text-end">{{ $fmt($f['vehicle_balance'] ?? 0) }}</td>
                                </tr>
                                <tr>
                                    <td>Advance Payments</td>
                                    <td class="text-end">{{ $fmt($f['advance_payments_total'] ?? 0) }}</td>
                                    <td class="text-end">—</td>
                                    <td class="text-end">—</td>
                                </tr>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th>Gross</th>
                                    <th class="text-end">{{ $fmt($f['gross_total'] ?? 0) }}</th>
                                    <th class="text-end">{{ $fmt($f['gross_paid'] ?? 0) }}</th>
                                    <th class="text-end">{{ $fmt($f['gross_balance'] ?? 0) }}</th>
                                </tr>
                                <tr>
                                    <th>Net Due (Gross Balance - Advance)</th>
                                    <th class="text-end">—</th>
                                    <th class="text-end">—</th>
                                    <th class="text-end">{{ $fmt($f['net_balance'] ?? 0) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        @if($supplier->bank_name || $supplier->account_number || $supplier->account_holder_name)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Bank Details</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        @if($supplier->bank_name)
                            <tr>
                                <th width="200">Bank Name:</th>
                                <td>{{ $supplier->bank_name }}</td>
                            </tr>
                        @endif
                        @if($supplier->account_holder_name)
                            <tr>
                                <th>Account Holder Name:</th>
                                <td><strong>{{ $supplier->account_holder_name }}</strong></td>
                            </tr>
                        @endif
                        @if($supplier->account_number)
                            <tr>
                                <th>Account Number:</th>
                                <td><strong>{{ $supplier->account_number }}</strong></td>
                            </tr>
                        @endif
                        @if($supplier->branch_name)
                            <tr>
                                <th>Branch Name:</th>
                                <td>{{ $supplier->branch_name }}</td>
                            </tr>
                        @endif
                        @if($supplier->branch_address)
                            <tr>
                                <th>Branch Address:</th>
                                <td>{{ $supplier->branch_address }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>
        @endif
    </div>

    <div class="col-md-4">
        @if($supplier->qr_code_image)
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">QR Code</h5>
                </div>
                <div class="card-body text-center">
                    @php
                        $qrUrl = storage_url($supplier->qr_code_image);
                    @endphp
                    @if($qrUrl)
                        <img src="{{ $qrUrl }}" alt="QR Code" class="img-fluid" style="max-width: 300px;">
                    @endif
                    <p class="text-muted small mt-2">Bank QR Code</p>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

