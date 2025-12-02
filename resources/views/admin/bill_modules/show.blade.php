@extends('admin.layout')

@section('title', 'Bill Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">{{ $bill_module->title }}</h1>
        <small class="text-muted">Project: {{ $bill_module->project->name ?? '—' }} | Version: {{ $bill_module->version }}</small>
    </div>
    <div>
        @if($bill_module->canEdit())
            <a href="{{ route('admin.bill-modules.edit', $bill_module) }}" class="btn btn-primary">Edit</a>
        @endif
        @if($bill_module->status === 'draft')
            <form action="{{ route('admin.bill-modules.submit', $bill_module) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-info">Submit for Approval</button>
            </form>
        @endif
        <a href="{{ route('admin.bill-modules.export.excel', $bill_module) }}" class="btn btn-success">Export Excel</a>
        <a href="{{ route('admin.bill-modules.report', $bill_module) }}" class="btn btn-outline-primary" target="_blank">View Report</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><strong>Bill Information</strong></div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr><th>Project:</th><td>{{ $bill_module->project->name ?? '—' }}</td></tr>
                    <tr><th>Status:</th><td><span class="badge bg-{{ $bill_module->status === 'approved' ? 'success' : ($bill_module->status === 'rejected' ? 'danger' : 'secondary') }}">{{ ucfirst($bill_module->status) }}</span></td></tr>
                    <tr><th>MB Number:</th><td>{{ $bill_module->mb_number ?? '—' }}</td></tr>
                    <tr><th>MB Date:</th><td>{{ $bill_module->mb_date ? $bill_module->mb_date->format('Y-m-d') : '—' }}</td></tr>
                    <tr><th>Created By:</th><td>{{ $bill_module->creator->name ?? '—' }}</td></tr>
                    @if($bill_module->approver)
                        <tr><th>Approved By:</th><td>{{ $bill_module->approver->name }}</td></tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><strong>Summary</strong></div>
            <div class="card-body">
                @if($bill_module->aggregate)
                    <table class="table table-sm">
                        <tr><th>Subtotal:</th><td class="text-end">{{ number_format($bill_module->aggregate->subtotal, 2) }}</td></tr>
                        <tr><th>Tax Total:</th><td class="text-end">{{ number_format($bill_module->aggregate->tax_total, 2) }}</td></tr>
                        <tr><th>Overhead ({{ $bill_module->aggregate->overhead_percent }}%):</th><td class="text-end">{{ number_format($bill_module->aggregate->overhead_amount, 2) }}</td></tr>
                        <tr><th>Contingency ({{ $bill_module->aggregate->contingency_percent }}%):</th><td class="text-end">{{ number_format($bill_module->aggregate->contingency_amount, 2) }}</td></tr>
                        <tr class="table-primary"><th><strong>Grand Total:</strong></th><td class="text-end"><strong>{{ number_format($bill_module->aggregate->grand_total, 2) }}</strong></td></tr>
                    </table>
                @endif
            </div>
        </div>
    </div>
</div>

@if($bill_module->canApprove())
    <div class="card mb-4">
        <div class="card-header"><strong>Approve/Reject Bill</strong></div>
        <div class="card-body">
            <form action="{{ route('admin.bill-modules.approve', $bill_module) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Comment</label>
                    <textarea name="comment" class="form-control" rows="2"></textarea>
                </div>
                <button type="submit" name="action" value="approve" class="btn btn-success">Approve</button>
                <button type="submit" name="action" value="reject" class="btn btn-danger">Reject</button>
            </form>
        </div>
    </div>
@endif

<div class="card">
    <div class="card-header"><strong>Bill Items</strong></div>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>S.N.</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Unit</th>
                    <th>Quantity</th>
                    <th>Wastage %</th>
                    <th>Effective Qty</th>
                    <th>Unit Rate</th>
                    <th>Amount</th>
                    <th>Tax %</th>
                    <th>Net Amount</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bill_module->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->billCategory->name ?? $item->category ?? '—' }}<br><small class="text-muted">{{ $item->billSubcategory->name ?? $item->subcategory ?? '' }}</small></td>
                        <td>{{ $item->description }}</td>
                        <td>{{ $item->uom }}</td>
                        <td>{{ number_format($item->quantity, 3) }}</td>
                        <td>{{ number_format($item->wastage_percent, 2) }}%</td>
                        <td>{{ number_format($item->effective_quantity, 3) }}</td>
                        <td>{{ number_format($item->unit_rate, 2) }}</td>
                        <td>{{ number_format($item->total_amount, 2) }}</td>
                        <td>{{ number_format($item->tax_percent, 2) }}%</td>
                        <td>{{ number_format($item->net_amount, 2) }}</td>
                        <td>{{ $item->remarks }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@if($bill_module->notes)
    <div class="card mt-4">
        <div class="card-header"><strong>Notes</strong></div>
        <div class="card-body">{{ $bill_module->notes }}</div>
    </div>
@endif

@if($bill_module->history->count() > 0)
    <div class="card mt-4">
        <div class="card-header"><strong>History</strong></div>
        <div class="card-body">
            <ul class="list-group">
                @foreach($bill_module->history as $history)
                    <li class="list-group-item">
                        <strong>{{ ucfirst($history->action) }}</strong> by {{ $history->user->name ?? 'System' }}
                        <small class="text-muted">{{ $history->created_at->format('Y-m-d H:i') }}</small>
                        @if($history->comment)
                            <br><small>{{ $history->comment }}</small>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
@endsection

