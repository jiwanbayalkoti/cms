@extends('admin.layout')
@section('title', 'Bill Statement (BoQ)')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="h3 mb-0">Bill Statement (BoQ)</h1>
        <p class="text-muted mb-0">Completed work by project</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        @if(isset($projects) && $projects->isNotEmpty())
        <form method="get" action="{{ route('admin.boq-bill-statements.index') }}" class="d-inline-flex align-items-center gap-1">
            <label for="project_filter" class="form-label mb-0 small text-muted">Project</label>
            <select name="project_id" id="project_filter" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                <option value="">All Projects</option>
                @foreach($projects as $p)
                    <option value="{{ $p->id }}" {{ (isset($projectId) ? $projectId : request('project_id')) == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
            </select>
        </form>
        @endif
        @if(!empty($rows))
            <a href="{{ route('admin.boq-bill-statements.export.excel', isset($projectId) && $projectId ? ['project_id' => $projectId] : []) }}" class="btn btn-success btn-sm"><i class="bi bi-file-earmark-excel me-1"></i>Export Excel</a>
        @endif
    </div>
</div>

@if(session('info'))
    <div class="alert alert-info alert-dismissible fade show">{{ session('info') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(empty($rows))
    <div class="card">
        <div class="card-body text-center text-muted py-4">
            No completed work records. <a href="{{ route('admin.completed-work.create') }}">Add Completed Work</a> first.
        </div>
    </div>
@else
    {{-- Header section: Company, Client, Project, Contract, Bill Date --}}
    <div class="card mb-4">
        <div class="card-body py-3">
            <div class="row g-3 small">
                <div class="col-md-6 col-lg-4"><strong>Company:</strong> {{ $company->name ?? '—' }}</div>
                <div class="col-md-6 col-lg-4"><strong>Client:</strong> {{ $company->client ?? '—' }}</div>
                <div class="col-md-6 col-lg-4"><strong>Project:</strong> {{ $company->project ?? '—' }}</div>
                <div class="col-md-6 col-lg-4"><strong>Contract No:</strong> {{ $company->contract_no ?? '—' }}</div>
                <div class="col-md-6 col-lg-4"><strong>Bill Date:</strong> {{ $billDate ?? '—' }}</div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><strong>Bill Items</strong></div>
        <div class="table-responsive">
            <table class="table table-bordered mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th rowspan="2" class="align-middle text-center" style="width: 45px;">SN</th>
                        <th rowspan="2" class="align-middle" style="min-width: 220px;">Description of works</th>
                        <th colspan="3" class="text-center border-start">As per boq</th>
                        <th colspan="3" class="text-center border-start">This bill</th>
                        <th rowspan="2" class="align-middle text-end border-start" style="width: 95px;">remaining Qty</th>
                        <th rowspan="2" class="align-middle text-center border-start" style="width: 80px;">Remarks</th>
                    </tr>
                    <tr>
                        <th class="text-center" style="width: 60px;">Unit</th>
                        <th class="text-end" style="width: 90px;">Quantity</th>
                        <th class="text-end border-end" style="width: 95px;">Unit price</th>
                        <th class="text-end" style="width: 95px;">Quantity</th>
                        <th class="text-end" style="width: 95px;">Unit price</th>
                        <th class="text-end border-end" style="width: 100px;">total price</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $sn => $row)
                    <tr>
                        <td class="text-center">{{ $sn + 1 }}</td>
                        <td><small class="text-break">{{ $row['boqItem']->item_description ?? '—' }}</small></td>
                        <td class="text-center">{{ $row['boqItem']->unit ?? '—' }}</td>
                        <td class="text-end">{{ number_format((float)($row['boqItem']->qty ?? 0), 4) }}</td>
                        <td class="text-end">{{ number_format((float)($row['boqItem']->rate ?? 0), 2) }}</td>
                        <td class="text-end">{{ number_format($row['total_qty'], 4) }}</td>
                        <td class="text-end">{{ number_format((float)($row['boqItem']->rate ?? 0), 2) }}</td>
                        <td class="text-end">{{ number_format($row['total_price'], 2) }}</td>
                        <td class="text-end">{{ number_format($row['remaining_qty'] ?? 0, 4) }}</td>
                        <td class="text-center">–</td>
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
                    <td class="text-end ps-3">{{ number_format($subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Tax ({{ $taxPercent }}%):</strong></td>
                    <td class="text-end ps-3">{{ number_format($taxAmount, 2) }}</td>
                </tr>
                <tr class="fs-5">
                    <td><strong>Total:</strong></td>
                    <td class="text-end ps-3"><strong>{{ number_format($total, 2) }}</strong></td>
                </tr>
            </table>
        </div>
    </div>
@endif
@endsection
