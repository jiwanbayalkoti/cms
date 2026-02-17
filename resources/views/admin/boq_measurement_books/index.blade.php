@extends('admin.layout')
@section('title', 'Measurement Book (BoQ)')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="h3 mb-0">Measurement Book (BoQ)</h1>
        <p class="text-muted mb-0">Completed work by project</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        @if(isset($projects) && $projects->isNotEmpty())
        <form method="get" action="{{ route('admin.boq-measurement-books.index') }}" class="d-inline-flex align-items-center gap-1">
            <label for="project_filter" class="form-label mb-0 small text-muted">Project</label>
            <select name="project_id" id="project_filter" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                <option value="">All Projects</option>
                @foreach($projects as $p)
                    <option value="{{ $p->id }}" {{ (isset($projectId) ? $projectId : request('project_id')) == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
            </select>
        </form>
        @endif
        @if(!empty($workAggregated))
            <a href="{{ route('admin.boq-measurement-books.export.excel', isset($projectId) && $projectId ? ['project_id' => $projectId] : []) }}" class="btn btn-success btn-sm"><i class="bi bi-file-earmark-excel me-1"></i>Export Excel</a>
        @endif
    </div>
</div>

@if(session('info'))
    <div class="alert alert-info alert-dismissible fade show">{{ session('info') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(empty($workAggregated))
    <div class="card">
        <div class="card-body text-center text-muted py-4">
            No completed work records. <a href="{{ route('admin.completed-work.create') }}">Add Completed Work</a> first.
        </div>
    </div>
@else
    {{-- Header: Company, Client, Project, Contract, Bill Date (same as Work & BoQ details) --}}
    <div class="card mb-4">
        <div class="card-body py-3">
            <div class="row g-3 small">
                <div class="col-md-6 col-lg-4"><strong>Company:</strong> {{ $company->name ?? '—' }}</div>
                <div class="col-md-6 col-lg-4"><strong>Client:</strong> {{ $company->client ?? '—' }}</div>
                <div class="col-md-6 col-lg-4"><strong>Project:</strong> {{ $company->project ?? '—' }}</div>
                <div class="col-md-6 col-lg-4"><strong>Contract No:</strong> {{ $company->contract_no ?? '—' }}</div>
                <div class="col-md-6 col-lg-4"><strong>Bill Date:</strong> {{ $company->bill_date?->format('Y-m-d') ?? '—' }}</div>
            </div>
        </div>
    </div>

    @foreach($workAggregated as $block)
        <div class="card mb-4">
            <div class="card-header"><strong>Measurement Book – Works</strong> <span class="ms-2 text-muted">(Work: {{ $block['work']->name ?? '—' }})</span></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;">SN</th>
                                <th>Works</th>
                                <th class="text-end" style="width: 70px;">no</th>
                                <th class="text-end" style="width: 90px;">Length ({{ $block['dimension_unit'] ?? 'm' }})</th>
                                <th class="text-end" style="width: 90px;">Breadth ({{ $block['dimension_unit'] ?? 'm' }})</th>
                                <th class="text-end" style="width: 90px;">Height ({{ $block['dimension_unit'] ?? 'm' }})</th>
                                <th class="text-end" style="width: 95px;">Quantity</th>
                                <th class="text-end" style="width: 95px;">Total qty</th>
                                <th style="width: 70px;">Unit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $sn = 1; @endphp
                            @foreach($block['rows'] as $row)
                            <tr class="{{ $row['type'] === 'main' ? 'table-light' : '' }}">
                                <td>{{ $row['type'] === 'main' ? $sn : '' }}</td>
                                <td class="{{ $row['type'] === 'sub' ? 'ps-4' : '' }}">
                                    @if($row['type'] === 'sub')
                                        <span class="text-muted">└</span> <small>{{ $row['description'] ?: '—' }}</small>
                                    @else
                                        <small>{{ $row['boqItem']->item_description ?? '—' }}</small>
                                    @endif
                                </td>
                                <td class="text-end">{{ isset($row['no']) && $row['no'] !== null && $row['no'] !== '' ? (int)(float)$row['no'] : '–' }}</td>
                                <td class="text-end">{{ isset($row['length']) && $row['length'] !== null && $row['length'] !== '' ? number_format((float)$row['length'], 4) : '–' }}</td>
                                <td class="text-end">{{ isset($row['breadth']) && $row['breadth'] !== null && $row['breadth'] !== '' ? number_format((float)$row['breadth'], 4) : '–' }}</td>
                                <td class="text-end">{{ isset($row['height']) && $row['height'] !== null && $row['height'] !== '' ? number_format((float)$row['height'], 4) : '–' }}</td>
                                <td class="text-end">{{ number_format($row['quantity'] ?? $row['total_qty'], 4) }}</td>
                                <td class="text-end">{{ number_format($row['total_qty'], 4) }}</td>
                                <td>{{ $row['type'] === 'main' ? ($row['boqItem']->unit ?? '–') : ($row['unit'] ?? '–') }}</td>
                            </tr>
                            @if($row['type'] === 'main') @php $sn++; @endphp @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
@endif
@endsection
