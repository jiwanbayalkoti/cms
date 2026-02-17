@extends('admin.layout')
@section('title', 'Measurement Book (BoQ)')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Measurement Book (BoQ)</h1>
    <a href="{{ route('admin.boq-measurement-books.index') }}" class="btn btn-outline-secondary">Back</a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <p class="mb-1"><strong>Company:</strong> {{ $completed_work_record->company->name ?? '—' }}</p>
        <p class="mb-1"><strong>Work:</strong> {{ $completed_work_record->work->name ?? '—' }}</p>
        <p class="mb-1"><strong>Measurement Date:</strong> {{ $completed_work_record->record_date?->format('Y-m-d') }}</p>
        @if($completed_work_record->notes)<p class="mb-0"><strong>Notes:</strong> {{ $completed_work_record->notes }}</p>@endif
    </div>
</div>

<div class="card">
    <div class="card-header"><strong>Measurement Book – Works</strong></div>
    <div class="table-responsive">
        <table class="table table-bordered mb-0">
            <thead class="table-light">
                <tr>
                    <th>SN</th>
                    <th>Works</th>
                    <th>Unit</th>
                    <th>Quantity</th>
                </tr>
            </thead>
            <tbody>
                @foreach($completed_work_record->recordItems as $ri)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td><small>{{ $ri->boqItem->item_description ?? '—' }}</small></td>
                    <td>{{ $ri->boqItem->unit ?? '—' }}</td>
                    <td>{{ number_format($ri->completed_qty, 4) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
