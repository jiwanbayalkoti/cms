@extends('admin.layout')
@section('title', 'Running Bill')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">{{ $running_bill->bill_title }}</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.running-bills.export.excel', $running_bill) }}" class="btn btn-success"><i class="bi bi-file-earmark-excel me-1"></i>Export Excel</a>
        <a href="{{ route('admin.running-bills.edit', $running_bill) }}" class="btn btn-warning">Edit</a>
        <a href="{{ route('admin.running-bills.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <p class="mb-1"><strong>Company:</strong> {{ $running_bill->company->name ?? '—' }}</p>
        <p class="mb-1"><strong>Client:</strong> {{ $running_bill->project->client_name ?? '—' }}</p>
        <p class="mb-1"><strong>Project:</strong> {{ $running_bill->project->name ?? '—' }}</p>
        <p class="mb-1"><strong>Contract No:</strong> {{ $running_bill->contract_no ?? '—' }}</p>
        <p class="mb-0"><strong>Bill Date:</strong> {{ $running_bill->bill_date?->format('Y-m-d') }}</p>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><strong>Bill Items</strong></div>
    <div class="table-responsive">
        <table class="table table-bordered mb-0">
            <thead class="table-light">
                <tr>
                    <th rowspan="2" class="align-middle">SN</th>
                    <th rowspan="2" class="align-middle">Description of works</th>
                    <th colspan="3" class="text-center">As per boq</th>
                    <th colspan="3" class="text-center">This bill</th>
                    <th rowspan="2" class="align-middle">remaining Qty</th>
                    <th rowspan="2" class="align-middle">Remarks</th>
                </tr>
                <tr>
                    <th>Unit</th>
                    <th>Quantity</th>
                    <th>Unit price</th>
                    <th>Quantity</th>
                    <th>Unit price</th>
                    <th>total price</th>
                </tr>
            </thead>
            <tbody>
                @foreach($running_bill->items as $it)
                <tr>
                    <td>{{ $it->sn }}</td>
                    <td><small>{{ $it->description }}</small></td>
                    <td>{{ $it->unit ?? '—' }}</td>
                    <td>{{ $it->boq_qty !== null ? number_format($it->boq_qty, 4) : '—' }}</td>
                    <td>{{ $it->boq_unit_price !== null ? number_format($it->boq_unit_price, 2) : '—' }}</td>
                    <td>{{ number_format($it->this_bill_qty, 4) }}</td>
                    <td>{{ number_format($it->unit_price, 2) }}</td>
                    <td>{{ number_format($it->total_price, 2) }}</td>
                    <td>{{ $it->remaining_qty !== null ? number_format($it->remaining_qty, 4) : '—' }}</td>
                    <td>{{ $it->remarks ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-borderless mb-0 w-auto">
            <tr>
                <td><strong>Subtotal:</strong></td>
                <td class="text-end">{{ number_format($subtotal, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Tax ({{ $taxPercent }}%):</strong></td>
                <td class="text-end">{{ number_format($taxAmount, 2) }}</td>
            </tr>
            <tr class="fs-5">
                <td><strong>Total:</strong></td>
                <td class="text-end"><strong>{{ number_format($total, 2) }}</strong></td>
            </tr>
        </table>
    </div>
</div>
@endsection
