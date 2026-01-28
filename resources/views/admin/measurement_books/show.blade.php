@extends('admin.layout')
@section('title', 'Measurement Book')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Measurement Book</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.measurement-books.export.excel', $measurement_book) }}" class="btn btn-success"><i class="bi bi-file-earmark-excel me-1"></i>Export Excel</a>
        <a href="{{ route('admin.measurement-books.edit', $measurement_book) }}" class="btn btn-warning"><i class="bi bi-pencil me-1"></i>Edit</a>
        <a href="{{ route('admin.measurement-books.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <p class="mb-1"><strong>Company:</strong> {{ $measurement_book->company->name ?? '—' }}</p>
        <p class="mb-1"><strong>Client:</strong> {{ $measurement_book->project->client_name ?? '—' }}</p>
        <p class="mb-1"><strong>Project:</strong> {{ $measurement_book->project->name ?? '—' }}</p>
        <p class="mb-1"><strong>Contract No:</strong> {{ $measurement_book->contract_no ?? '—' }}</p>
        <p class="mb-1"><strong>Measurement Date:</strong> {{ $measurement_book->measurement_date?->format('Y-m-d') }}</p>
        @if($measurement_book->title)<p class="mb-0"><strong>Title:</strong> {{ $measurement_book->title }}</p>@endif
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
                    <th>no</th>
                    @php
                        $du = $measurement_book->dimension_unit ?? 'ft';
                        if ($du === 'ft_in') $du = 'ft'; if ($du === 'm_cm') $du = 'm';
                    @endphp
                    <th>Length ({{ $du }})</th>
                    <th>Breadth ({{ $du }})</th>
                    <th>Height ({{ $du }})</th>
                    <th>Quantity</th>
                    <th>Total qty</th>
                    <th>Unit</th>
                </tr>
            </thead>
            <tbody>
                @foreach($measurement_book->mainItems as $mainItem)
                <tr class="table-light">
                    <td>{{ $mainItem->sn }}</td>
                    <td><strong>{{ $mainItem->works }}</strong></td>
                    <td>{{ $mainItem->no }}</td>
                    <td>{{ $mainItem->length_ft !== null && $mainItem->length_ft !== '' ? $mainItem->length_ft : '—' }}</td>
                    <td>{{ $mainItem->breadth_ft !== null && $mainItem->breadth_ft !== '' ? $mainItem->breadth_ft : '—' }}</td>
                    <td>{{ $mainItem->height_ft !== null && $mainItem->height_ft !== '' ? $mainItem->height_ft : '—' }}</td>
                    <td>{{ number_format($mainItem->quantity, 4) }}</td>
                    <td>{{ $mainItem->total_qty ? number_format($mainItem->total_qty, 4) : '—' }}</td>
                    <td>{{ $mainItem->unit ?? '—' }}</td>
                </tr>
                @foreach($mainItem->children as $subItem)
                <tr>
                    <td></td>
                    <td><small style="padding-left: 1.5rem;">└─ {{ $subItem->works }}</small></td>
                    <td>{{ $subItem->no }}</td>
                    <td>{{ $subItem->length_ft !== null && $subItem->length_ft !== '' ? $subItem->length_ft : '—' }}</td>
                    <td>{{ $subItem->breadth_ft !== null && $subItem->breadth_ft !== '' ? $subItem->breadth_ft : '—' }}</td>
                    <td>{{ $subItem->height_ft !== null && $subItem->height_ft !== '' ? $subItem->height_ft : '—' }}</td>
                    <td>{{ number_format($subItem->quantity, 4) }}</td>
                    <td>{{ $subItem->total_qty ? number_format($subItem->total_qty, 4) : '—' }}</td>
                    <td>{{ $subItem->unit ?? '—' }}</td>
                </tr>
                @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
