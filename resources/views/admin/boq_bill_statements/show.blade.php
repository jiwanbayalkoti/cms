@extends('admin.layout')
@section('title', 'Bill Statement (BoQ)')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Bill Statement – {{ $completed_work_record->work->name ?? 'BoQ' }}</h1>
    <a href="{{ route('admin.boq-bill-statements.index') }}" class="btn btn-outline-secondary">Back</a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <p class="mb-1"><strong>Company:</strong> {{ $completed_work_record->company->name ?? '—' }}</p>
        <p class="mb-1"><strong>Work:</strong> {{ $completed_work_record->work->name ?? '—' }}</p>
        <p class="mb-0"><strong>Bill Date:</strong> {{ $completed_work_record->record_date?->format('Y-m-d') }}</p>
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
                @foreach($completed_work_record->recordItems as $ri)
                @php
                    $boqItem = $ri->boqItem;
                    $boqQty = (float) ($boqItem->qty ?? 0);
                    $unitPrice = (float) ($boqItem->rate ?? 0);
                    $thisQty = (float) $ri->completed_qty;
                    $totalPrice = $thisQty * $unitPrice;
                    $completedSoFar = (float) ($completedSoFarByItem[$boqItem->id] ?? 0);
                    $remaining = max(0, $boqQty - $completedSoFar);
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td><small>{{ $boqItem->item_description ?? '—' }}</small></td>
                    <td>{{ $boqItem->unit ?? '—' }}</td>
                    <td>{{ $boqQty ? number_format($boqQty, 4) : '—' }}</td>
                    <td>{{ $unitPrice ? number_format($unitPrice, 2) : '—' }}</td>
                    <td>{{ number_format($thisQty, 4) }}</td>
                    <td>{{ number_format($unitPrice, 2) }}</td>
                    <td>{{ number_format($totalPrice, 2) }}</td>
                    <td>{{ number_format($remaining, 4) }}</td>
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
