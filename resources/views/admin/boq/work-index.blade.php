@extends('admin.layout')

@section('title', 'Work & BoQ')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Work & BoQ</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.boq.work-index.export.excel') }}" class="btn btn-success btn-sm"><i class="bi bi-file-earmark-excel me-1"></i>Export Excel</a>
        <a href="{{ route('admin.boq.work-index.export.pdf') }}" class="btn btn-danger btn-sm"><i class="bi bi-file-earmark-pdf me-1"></i>Export PDF</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Measurement Book / Bill details (shown at top of Measurement Book and Bill Statement) --}}
<div class="card mb-4">
    <div class="card-header"><strong>Measurement Book & Bill Details</strong></div>
    <div class="card-body">
        <p class="text-muted small mb-3">These appear at the top of <a href="{{ route('admin.boq-measurement-books.index') }}">Measurement Book</a> and <a href="{{ route('admin.boq-bill-statements.index') }}">Bill Statement</a>. Project is manual entry (no auto-fill).</p>
        <form action="{{ route('admin.company-bill-details.update') }}" method="POST" class="row g-3">
            @csrf
            @method('PUT')
            <div class="col-md-6">
                <label class="form-label small mb-0">Company</label>
                <input type="text" class="form-control form-control-sm" value="{{ optional($company)->name ?? '—' }}" readonly disabled>
            </div>
            <div class="col-md-6">
                <label class="form-label small mb-0">Client</label>
                <input type="text" name="client" class="form-control form-control-sm" value="{{ old('client', optional($company)->client ?? '') }}" placeholder="e.g. Nepal Police">
            </div>
            <div class="col-md-6">
                <label class="form-label small mb-0">Project</label>
                <input type="text" name="project" class="form-control form-control-sm" value="{{ old('project', optional($company)->project ?? '') }}" placeholder="e.g. Gulmi">
            </div>
            <div class="col-md-6">
                <label class="form-label small mb-0">Contract No</label>
                <input type="text" name="contract_no" class="form-control form-control-sm" value="{{ old('contract_no', optional($company)->contract_no ?? '') }}" placeholder="e.g. PPE-COBM/GULMI/082-083/01">
            </div>
            <div class="col-md-6">
                <label class="form-label small mb-0">Bill Date</label>
                <input type="date" name="bill_date" class="form-control form-control-sm" value="{{ old('bill_date', optional($company)->bill_date?->format('Y-m-d') ?? '') }}">
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check-lg me-1"></i>Save Details</button>
            </div>
        </form>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <strong>Add Work</strong>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.boq.works.store') }}" method="POST" class="row g-2 align-items-end">
            @csrf
            <div class="col-md-4">
                <label class="form-label small mb-0">Work Name</label>
                <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. Foundation, Beam" required>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1" title="Add Work"><i class="bi bi-plus-lg"></i> Add Work</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <strong>Work List</strong>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;"></th>
                        <th style="width: 50px;">SN</th>
                        <th>Work Name</th>
                        <th class="text-end" style="width: 120px;">Total Amount</th>
                        <th style="width: 200px;" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @php $sn = 0; @endphp
                    @forelse($works as $work)
                        @php $sn++; @endphp
                        <tr class="work-toggle-row align-middle" data-work-id="{{ $work->id }}">
                            <td class="work-toggle-header text-center py-2" style="cursor: pointer;">
                                <span class="work-chevron d-inline-block" style="transition: transform 0.2s;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z"/></svg>
                                </span>
                            </td>
                            <td class="work-toggle-header" style="cursor: pointer;">{{ $sn }}</td>
                            <td class="work-toggle-header fw-medium" style="cursor: pointer;">{{ $work->name }}</td>
                            <td class="work-toggle-header text-end" style="cursor: pointer;">{{ number_format($work->items->sum('amount') + $work->children->sum(fn($c) => $c->items->sum('amount')), 2) }}</td>
                            <td class="py-2" onclick="event.stopPropagation();">
                                <div class="d-flex gap-1 justify-content-end flex-wrap">
                                    <a href="{{ route('admin.boq.works.show', $work) }}" class="btn btn-sm btn-outline-primary p-1" title="Add Items"><i class="bi bi-plus-square"></i></a>
                                    <a href="{{ route('admin.boq.works.edit', $work) }}" class="btn btn-sm btn-outline-secondary p-1" title="Edit"><i class="bi bi-pencil"></i></a>
                                    <form action="{{ route('admin.boq.works.destroy', $work) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this work and all subworks/items?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger p-1" title="Delete"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <tr class="work-toggle-content-row" id="work-content-{{ $work->id }}" style="display: none;">
                            <td colspan="5" class="p-0 bg-light">
                                <div class="p-3 small">
                                    @if($work->items->isEmpty())
                                        <p class="text-muted mb-0">No items. <a href="{{ route('admin.boq.works.show', $work) }}">Add Items</a></p>
                                    @else
                                        <table class="table table-sm table-bordered mb-2">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 40px;">SN</th>
                                                    <th style="width: 40%; max-width: 40%;">Item Description</th>
                                                    <th style="width: 80px;">Unit</th>
                                                    <th class="text-end" style="width: 100px;">Qty</th>
                                                    <th class="text-end" style="width: 100px;">Rate</th>
                                                    <th>Rate in Words</th>
                                                    <th class="text-end" style="width: 110px;">Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($work->items as $item)
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>{{ $item->item_description ?: '–' }}</td>
                                                        <td>{{ $item->unit ?: '–' }}</td>
                                                        <td class="text-end">{{ number_format($item->qty, 4) }}</td>
                                                        <td class="text-end">{{ number_format($item->rate, 4) }}</td>
                                                        <td>{{ $item->rate_in_words ?: '–' }}</td>
                                                        <td class="text-end">{{ number_format($item->amount, 2) }}</td>
                                                    </tr>
                                                @endforeach
                                                <tr class="table-light fw-semibold">
                                                    <td colspan="6" class="text-end">Total:</td>
                                                    <td class="text-end">{{ number_format($work->items->sum('amount'), 2) }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <a href="{{ route('admin.boq.works.show', $work) }}" class="btn btn-sm btn-outline-primary p-1" title="Edit Items"><i class="bi bi-pencil"></i></a>
                                    @endif
                                    @if($work->children->isNotEmpty())
                                        <hr class="my-2">
                                        <p class="mb-1 fw-semibold">Subworks</p>
                                        <ul class="list-unstyled mb-0">
                                            @foreach($work->children as $sub)
                                                <li class="d-flex align-items-center gap-2 mb-1">
                                                    <span>{{ $sub->name }}</span>
                                                    <span class="text-muted">({{ $sub->items->count() }} items)</span>
                                                    <a href="{{ route('admin.boq.works.edit', $sub) }}" class="btn btn-sm btn-outline-secondary p-1" title="Edit"><i class="bi bi-pencil-square"></i></a>
                                                    <a href="{{ route('admin.boq.works.show', $sub) }}" class="btn btn-sm btn-outline-primary p-1" title="Edit Items"><i class="bi bi-list-ul"></i></a>
                                                    <form action="{{ route('admin.boq.works.destroy', $sub) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this subwork and all items?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger p-1" title="Delete"><i class="bi bi-trash"></i></button>
                                                    </form>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                    <hr class="my-2">
                                    <form action="{{ route('admin.boq.works.store') }}" method="POST" class="row g-2 align-items-end">
                                        @csrf
                                        <input type="hidden" name="parent_id" value="{{ $work->id }}">
                                        <div class="col-auto">
                                            <label class="form-label small mb-0">Add Subwork</label>
                                            <input type="text" name="name" class="form-control form-control-sm" placeholder="Subwork name" required style="width: 180px;">
                                        </div>
                                        <div class="col-auto">
                                            <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-plus-circle me-1"></i>Add Subwork</button>
                                        </div>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @foreach($work->children as $sub)
                            @php $sn++; @endphp
                            <tr class="work-toggle-row align-middle table-secondary" data-work-id="{{ $sub->id }}">
                                <td class="work-toggle-header text-center py-2" style="cursor: pointer;">
                                    <span class="work-chevron d-inline-block" style="transition: transform 0.2s;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z"/></svg>
                                    </span>
                                </td>
                                <td class="work-toggle-header" style="cursor: pointer;">{{ $sn }}</td>
                                <td class="work-toggle-header fw-medium" style="cursor: pointer;"><span class="ms-3">└ {{ $sub->name }}</span></td>
                                <td class="work-toggle-header text-end" style="cursor: pointer;">{{ number_format($sub->items->sum('amount'), 2) }}</td>
                                <td class="py-2" onclick="event.stopPropagation();">
                                    <div class="d-flex gap-1 justify-content-end flex-wrap">
                                        <a href="{{ route('admin.boq.works.show', $sub) }}" class="btn btn-sm btn-outline-primary p-1" title="Add Items"><i class="bi bi-plus-square"></i></a>
                                        <a href="{{ route('admin.boq.works.edit', $sub) }}" class="btn btn-sm btn-outline-secondary p-1" title="Edit"><i class="bi bi-pencil"></i></a>
                                        <form action="{{ route('admin.boq.works.destroy', $sub) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this subwork and all items?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger p-1" title="Delete"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <tr class="work-toggle-content-row" id="work-content-{{ $sub->id }}" style="display: none;">
                                <td colspan="5" class="p-0 bg-light">
                                    <div class="p-3 small ms-3">
                                        @if($sub->items->isEmpty())
                                            <p class="text-muted mb-0">No items. <a href="{{ route('admin.boq.works.show', $sub) }}">Add Items</a></p>
                                        @else
                                            <table class="table table-sm table-bordered mb-2">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th style="width: 40px;">SN</th>
                                                        <th style="width: 40%;">Item Description</th>
                                                        <th style="width: 80px;">Unit</th>
                                                        <th class="text-end" style="width: 100px;">Qty</th>
                                                        <th class="text-end" style="width: 100px;">Rate</th>
                                                        <th>Rate in Words</th>
                                                        <th class="text-end" style="width: 110px;">Amount</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($sub->items as $item)
                                                        <tr>
                                                            <td>{{ $loop->iteration }}</td>
                                                            <td>{{ $item->item_description ?: '–' }}</td>
                                                            <td>{{ $item->unit ?: '–' }}</td>
                                                            <td class="text-end">{{ number_format($item->qty, 4) }}</td>
                                                            <td class="text-end">{{ number_format($item->rate, 4) }}</td>
                                                            <td>{{ $item->rate_in_words ?: '–' }}</td>
                                                            <td class="text-end">{{ number_format($item->amount, 2) }}</td>
                                                        </tr>
                                                    @endforeach
                                                    <tr class="table-light fw-semibold">
                                                        <td colspan="6" class="text-end">Total:</td>
                                                        <td class="text-end">{{ number_format($sub->items->sum('amount'), 2) }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <a href="{{ route('admin.boq.works.show', $sub) }}" class="btn btn-sm btn-outline-primary p-1" title="Edit Items"><i class="bi bi-pencil"></i></a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="5" class="text-muted text-center py-4">No works yet. Add work above.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.work-toggle-content-row td { vertical-align: top !important; }
</style>
<script>
(function() {
    document.querySelectorAll('.work-toggle-header').forEach(function(cell) {
        cell.addEventListener('click', function() {
            var mainRow = this.closest('tr.work-toggle-row');
            if (!mainRow) return;
            var contentRow = mainRow.nextElementSibling;
            if (!contentRow || !contentRow.classList.contains('work-toggle-content-row')) return;
            var chevron = mainRow.querySelector('.work-chevron');
            var isOpen = contentRow.style.display !== 'none';
            contentRow.style.display = isOpen ? 'none' : 'table-row';
            if (chevron) chevron.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(-90deg)';
        });
    });
})();
</script>
@endsection
