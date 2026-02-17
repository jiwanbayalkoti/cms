@extends('admin.layout')

@section('title', 'Measurement Book – ' . $completed_work_record->work->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Measurement Book</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.completed-work.index') }}" class="btn btn-outline-secondary btn-sm p-1" title="Back"><i class="bi bi-arrow-left"></i></a>
        <a href="{{ route('admin.completed-work.edit', $completed_work_record) }}" class="btn btn-outline-primary btn-sm p-1" title="Edit"><i class="bi bi-pencil"></i></a>
        <form action="{{ route('admin.completed-work.destroy', $completed_work_record) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this record?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger p-1" title="Delete"><i class="bi bi-trash"></i></button>
        </form>
    </div>
</div>

<div class="card mb-4 bg-light">
    <div class="card-body py-3">
        <div class="row g-2 small">
            <div class="col-md-4"><strong>Company:</strong> {{ $completed_work_record->company->name ?? '–' }}</div>
            <div class="col-md-4"><strong>Work:</strong> {{ $completed_work_record->work->name }}</div>
            <div class="col-md-4"><strong>Measurement Date:</strong> {{ $completed_work_record->record_date->format('Y-m-d') }}</div>
            <div class="col-md-4"><strong>Title:</strong> {{ $completed_work_record->notes ?: '–' }}</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><strong>Measurement Book – Works</strong></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;">SN</th>
                        <th>Works</th>
                        <th class="text-end" style="width: 70px;">no</th>
                        <th class="text-end" style="width: 90px;">Length (m)</th>
                        <th class="text-end" style="width: 90px;">Breadth (m)</th>
                        <th class="text-end" style="width: 90px;">Height (m)</th>
                        <th class="text-end" style="width: 95px;">Quantity</th>
                        <th class="text-end" style="width: 95px;">Total qty</th>
                        <th style="width: 70px;">Unit</th>
                    </tr>
                </thead>
                <tbody>
                    @php $sn = 1; @endphp
                    @forelse($completed_work_record->recordItems as $ri)
                        @php
                            $hasSubs = $ri->children->isNotEmpty();
                            $mainTotalQty = $hasSubs ? $ri->children->sum('completed_qty') : (float) $ri->completed_qty;
                        @endphp
                        <tr class="table-light">
                            <td>{{ $sn }}</td>
                            <td><small>{{ $ri->boqItem->item_description ?? '–' }}</small></td>
                            <td class="text-end">{{ $ri->no !== null ? number_format($ri->no, 4) : '–' }}</td>
                            @if($hasSubs)
                                <td class="text-end">–</td>
                                <td class="text-end">–</td>
                                <td class="text-end">–</td>
                                <td class="text-end">{{ number_format(0, 4) }}</td>
                            @else
                                <td class="text-end">{{ $ri->length !== null ? number_format($ri->length, 4) : '–' }}</td>
                                <td class="text-end">{{ $ri->breadth !== null ? number_format($ri->breadth, 4) : '–' }}</td>
                                <td class="text-end">{{ $ri->height !== null ? number_format($ri->height, 4) : '–' }}</td>
                                <td class="text-end">{{ number_format($ri->completed_qty, 4) }}</td>
                            @endif
                            <td class="text-end">{{ number_format($mainTotalQty, 4) }}</td>
                            <td>{{ $ri->boqItem->unit ?? '–' }}</td>
                        </tr>
                        @foreach($ri->children as $sub)
                            <tr>
                                <td></td>
                                <td class="ps-4"><small><span class="text-muted">└</span> {{ $sub->description ?: '–' }}</small></td>
                                <td class="text-end">{{ $sub->no !== null ? number_format($sub->no, 4) : '–' }}</td>
                                <td class="text-end">{{ $sub->length !== null ? number_format($sub->length, 4) : '–' }}</td>
                                <td class="text-end">{{ $sub->breadth !== null ? number_format($sub->breadth, 4) : '–' }}</td>
                                <td class="text-end">{{ $sub->height !== null ? number_format($sub->height, 4) : '–' }}</td>
                                <td class="text-end">{{ number_format($sub->completed_qty, 4) }}</td>
                                <td class="text-end">{{ number_format($sub->completed_qty, 4) }}</td>
                                <td>{{ $ri->boqItem->unit ?? '–' }}</td>
                            </tr>
                        @endforeach
                        @php $sn++; @endphp
                    @empty
                        <tr>
                            <td colspan="9" class="text-muted text-center py-3">No items in this record.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
