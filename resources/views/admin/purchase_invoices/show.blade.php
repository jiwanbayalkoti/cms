@extends('admin.layout')

@section('title', 'Purchase Invoice Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Purchase Invoice Details</h1>
        <p class="text-muted mb-0">{{ $purchaseInvoice->invoice_number }}</p>
    </div>
    <div>
        @if($purchaseInvoice->status === 'draft')
            <a href="{{ route('admin.purchase-invoices.edit', $purchaseInvoice) }}" class="btn btn-warning me-2">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
        @endif
        <a href="{{ route('admin.purchase-invoices.index') }}" class="btn btn-outline-secondary">Back to List</a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Invoice Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="150">Invoice Number:</th>
                                <td><strong>{{ $purchaseInvoice->invoice_number }}</strong></td>
                            </tr>
                            <tr>
                                <th>Invoice Date:</th>
                                <td>{{ $purchaseInvoice->invoice_date->format('Y-m-d') }}</td>
                            </tr>
                            <tr>
                                <th>Due Date:</th>
                                <td>{{ $purchaseInvoice->due_date?->format('Y-m-d') ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Vendor:</th>
                                <td>{{ $purchaseInvoice->vendor->name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Project:</th>
                                <td>{{ $purchaseInvoice->project->name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Reference Number:</th>
                                <td>{{ $purchaseInvoice->reference_number ?? '—' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="150">Status:</th>
                                <td>
                                    <span class="badge bg-{{ $purchaseInvoice->status === 'paid' ? 'success' : ($purchaseInvoice->status === 'pending' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($purchaseInvoice->status) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Payment Status:</th>
                                <td>
                                    <span class="badge bg-{{ $purchaseInvoice->payment_status === 'paid' ? 'success' : ($purchaseInvoice->payment_status === 'partial' ? 'warning' : 'danger') }}">
                                        {{ ucfirst(str_replace('_', ' ', $purchaseInvoice->payment_status)) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Bank Account:</th>
                                <td>{{ $purchaseInvoice->bankAccount->account_name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Created By:</th>
                                <td>{{ $purchaseInvoice->creator->name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Created At:</th>
                                <td>{{ $purchaseInvoice->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Invoice Items</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Item Name</th>
                                <th>Description</th>
                                <th class="text-end">Quantity</th>
                                <th>Unit</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Tax %</th>
                                <th class="text-end">Tax Amount</th>
                                <th class="text-end">Discount</th>
                                <th class="text-end">Line Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchaseInvoice->items as $item)
                                <tr>
                                    <td>{{ $item->line_number }}</td>
                                    <td><strong>{{ $item->item_name }}</strong></td>
                                    <td>{{ $item->description ?? '—' }}</td>
                                    <td class="text-end">{{ number_format($item->quantity, 2) }}</td>
                                    <td>{{ $item->unit ?? '—' }}</td>
                                    <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-end">{{ number_format($item->tax_rate, 2) }}%</td>
                                    <td class="text-end">{{ number_format($item->tax_amount, 2) }}</td>
                                    <td class="text-end">{{ number_format($item->discount_amount, 2) }}</td>
                                    <td class="text-end"><strong>{{ number_format($item->line_total, 2) }}</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="9" class="text-end"><strong>Subtotal:</strong></td>
                                <td class="text-end"><strong>{{ number_format($purchaseInvoice->subtotal, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <td colspan="9" class="text-end"><strong>Tax Amount:</strong></td>
                                <td class="text-end"><strong>{{ number_format($purchaseInvoice->tax_amount, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <td colspan="9" class="text-end"><strong>Discount Amount:</strong></td>
                                <td class="text-end"><strong>{{ number_format($purchaseInvoice->discount_amount, 2) }}</strong></td>
                            </tr>
                            <tr class="table-primary">
                                <td colspan="9" class="text-end"><strong>Total Amount:</strong></td>
                                <td class="text-end"><strong class="fs-5">{{ number_format($purchaseInvoice->total_amount, 2) }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        
        @if($purchaseInvoice->notes || $purchaseInvoice->terms)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Additional Information</h5>
                </div>
                <div class="card-body">
                    @if($purchaseInvoice->notes)
                        <div class="mb-3">
                            <strong>Notes:</strong>
                            <p class="mb-0">{{ $purchaseInvoice->notes }}</p>
                        </div>
                    @endif
                    @if($purchaseInvoice->terms)
                        <div>
                            <strong>Terms & Conditions:</strong>
                            <p class="mb-0">{{ $purchaseInvoice->terms }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Payment Summary</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th>Total Amount:</th>
                        <td class="text-end"><strong>{{ number_format($purchaseInvoice->total_amount, 2) }}</strong></td>
                    </tr>
                    <tr>
                        <th>Paid Amount:</th>
                        <td class="text-end text-success"><strong>{{ number_format($purchaseInvoice->paid_amount, 2) }}</strong></td>
                    </tr>
                    <tr>
                        <th>Balance Amount:</th>
                        <td class="text-end">
                            <strong class="{{ $purchaseInvoice->balance_amount > 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($purchaseInvoice->balance_amount, 2) }}
                            </strong>
                        </td>
                    </tr>
                </table>
                
                @if($purchaseInvoice->balance_amount > 0)
                    <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#paymentModal">
                        <i class="bi bi-cash-coin me-1"></i> Record Payment
                    </button>
                @endif
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                @if($purchaseInvoice->status === 'draft')
                    <a href="{{ route('admin.purchase-invoices.edit', $purchaseInvoice) }}" class="btn btn-warning w-100 mb-2">
                        <i class="bi bi-pencil me-1"></i> Edit Invoice
                    </a>
                @endif
                <a href="{{ route('admin.purchase-invoices.index') }}" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-arrow-left me-1"></i> Back to List
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
@if($purchaseInvoice->balance_amount > 0)
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.purchase-invoices.payment', $purchaseInvoice) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Record Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Payment Amount <span class="text-danger">*</span></label>
                        <input type="number" name="payment_amount" step="0.01" min="0.01" 
                               max="{{ $purchaseInvoice->balance_amount }}" class="form-control" required
                               value="{{ $purchaseInvoice->balance_amount }}">
                        <small class="text-muted">Maximum: {{ number_format($purchaseInvoice->balance_amount, 2) }}</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" class="form-control" 
                               value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bank Account</label>
                        <select name="bank_account_id" class="form-select">
                            <option value="">None</option>
                            @foreach(\App\Models\BankAccount::where('company_id', \App\Support\CompanyContext::getActiveCompanyId())->where('is_active', true)->get() as $account)
                                <option value="{{ $account->id }}">{{ $account->account_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" rows="3" class="form-control" placeholder="Payment notes"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Record Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

