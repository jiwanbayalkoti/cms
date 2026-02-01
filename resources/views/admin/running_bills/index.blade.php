@extends('admin.layout')
@section('title', 'Bill Statement')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Bill Statement (Running Bill)</h1>
        <p class="text-muted mb-0">Total bill (BOQ, this bill, 13% tax)</p>
    </div>
    <a href="{{ route('admin.running-bills.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> New
    </a>
</div>

<form id="runningBillsFilterForm" method="GET" class="mb-3 row g-2 align-items-end">
    <div class="col-auto">
        <label class="form-label small mb-0">Project</label>
        <select name="project_id" class="form-select form-select-sm" style="width:220px">
            <option value="">All</option>
            @foreach($projects as $p)
                <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto"><button type="submit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-funnel me-1"></i>Filter</button></div>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>SN</th>
                    <th>Bill Date</th>
                    <th>Bill Title</th>
                    <th>Project</th>
                    <th>Contract No</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody id="runningBillsTbody">
                @forelse($bills as $b)
                <tr>
                    <td>{{ ($bills->currentPage() - 1) * $bills->perPage() + $loop->iteration }}</td>
                    <td>{{ $b->bill_date->format('Y-m-d') }}</td>
                    <td>{{ $b->bill_title }}</td>
                    <td>{{ $b->project->name ?? '—' }}</td>
                    <td>{{ $b->contract_no ?? '—' }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.running-bills.show', $b) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye me-1"></i>View</a>
                        <a href="{{ route('admin.running-bills.edit', $b) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil me-1"></i>Edit</a>
                        <form action="{{ route('admin.running-bills.destroy', $b) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this Running Bill?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1"></i>Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No records. <a href="{{ route('admin.running-bills.create') }}">Create one</a></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div id="runningBillsPagination" class="card-footer">
        @if($bills->hasPages())
        {{ $bills->links() }}
        @endif
    </div>
</div>

@push('scripts')
<script>
document.getElementById('runningBillsFilterForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var form = this;
    var params = new URLSearchParams(new FormData(form));
    var url = '{{ route("admin.running-bills.index") }}?' + params.toString();
    var tbody = document.getElementById('runningBillsTbody');
    var pagination = document.getElementById('runningBillsPagination');
    tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4"><span class="spinner-border spinner-border-sm"></span> Loading...</td></tr>';
    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            tbody.innerHTML = data.tbody || '';
            pagination.innerHTML = data.pagination || '';
        })
        .catch(function() {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger py-4">Error loading data.</td></tr>';
        });
});
document.querySelector('#runningBillsFilterForm select[name="project_id"]').addEventListener('change', function() {
    document.getElementById('runningBillsFilterForm').dispatchEvent(new Event('submit'));
});
</script>
@endpush
@endsection
