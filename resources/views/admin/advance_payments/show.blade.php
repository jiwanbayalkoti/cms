@extends('admin.layout')

@section('title', 'View Advance Payment')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Advance Payment Details</h1>
        <p class="text-muted mb-0">View advance payment information</p>
    </div>
<div>
        <a href="{{ route('admin.advance-payments.edit', $advancePayment) }}" class="btn btn-primary me-2">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
        <a href="{{ route('admin.advance-payments.index') }}" class="btn btn-outline-secondary">Back to List</a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Payment Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="200">Payment Date:</th>
                        <td>{{ $advancePayment->payment_date->format('F d, Y') }}</td>
                    </tr>
                    <tr>
                        <th>Payment Type:</th>
                        <td>
                            <span class="badge bg-info">
                                {{ ucfirst(str_replace('_', ' ', $advancePayment->payment_type)) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Project:</th>
                        <td>{{ $advancePayment->project->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Supplier:</th>
                        <td><strong>{{ $advancePayment->supplier->name ?? 'N/A' }}</strong></td>
                    </tr>
                    <tr>
                        <th>Reference:</th>
                        <td>N/A</td>
                    </tr>
                    <tr>
                        <th>Payment Amount:</th>
                        <td><strong class="text-primary">Rs. {{ number_format($advancePayment->amount, 2) }}</strong></td>
                    </tr>
                    <tr>
                        <th>Payment Method:</th>
                        <td>{{ ucfirst(str_replace('_', ' ', $advancePayment->payment_method ?? 'N/A')) }}</td>
                    </tr>
                    <tr>
                        <th>Bank Account:</th>
                        <td>{{ $advancePayment->bankAccount->account_name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Transaction Reference:</th>
                        <td>{{ $advancePayment->transaction_reference ?? 'N/A' }}</td>
                    </tr>
                    @if($advancePayment->notes)
                    <tr>
                        <th>Notes:</th>
                        <td>{{ $advancePayment->notes }}</td>
                    </tr>
                    @endif
                    @if($advancePayment->expense)
                    <tr>
                        <th>Linked Expense Entry:</th>
                        <td>
                            <a href="{{ route('admin.expenses.show', $advancePayment->expense) }}" class="text-primary">
                                View Expense Entry
                            </a>
                            <div class="text-muted small mt-1">
                                Created: {{ $advancePayment->expense->created_at->format('Y-m-d H:i') }}
                            </div>
                        </td>
                    </tr>
                    @else
                    <tr>
                        <th>Expense Entry:</th>
                        <td>
                            <div class="alert alert-info small mb-0 py-2">
                                <i class="bi bi-info-circle"></i> Expense entry will be created automatically.
                            </div>
                        </td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Metadata</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm">
                    <tr>
                        <th>Created By:</th>
                        <td>{{ $advancePayment->creator->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Created At:</th>
                        <td>{{ $advancePayment->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                    @if($advancePayment->updater)
                    <tr>
                        <th>Updated By:</th>
                        <td>{{ $advancePayment->updater->name }}</td>
                    </tr>
                    <tr>
                        <th>Updated At:</th>
                        <td>{{ $advancePayment->updated_at->format('Y-m-d H:i') }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
