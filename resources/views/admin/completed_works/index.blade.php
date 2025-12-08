@extends('admin.layout')

@section('title', 'Completed Works')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Completed Works</h1>
        <p class="text-muted mb-0">Track completed work quantities for billing</p>
    </div>
    <div class="d-flex gap-2">
        @if($completedWorks->where('status', '!=', 'billed')->count() > 0)
            <a href="{{ route('admin.completed-works.generate-bill', request()->all()) }}" class="btn btn-success">
                <i class="bi bi-receipt me-1"></i> Generate Bill
            </a>
        @endif
        <a href="{{ route('admin.completed-works.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Add Completed Work
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Filters</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.completed-works.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">Project</label>
                <select name="project_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Projects</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                            {{ $project->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Work Type</label>
                <input type="text" name="work_type" class="form-control form-control-sm" value="{{ request('work_type') }}" placeholder="e.g., Soling, PCC" onchange="this.form.submit()">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Status</label>
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="recorded" {{ request('status') == 'recorded' ? 'selected' : '' }}>Recorded</option>
                    <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                    <option value="billed" {{ request('status') == 'billed' ? 'selected' : '' }}>Billed</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Start Date</label>
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}" onchange="this.form.submit()">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">End Date</label>
                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}" onchange="this.form.submit()">
            </div>
            <div class="col-md-1">
                <a href="{{ route('admin.completed-works.index') }}" class="btn btn-outline-secondary btn-sm w-100">
                    <i class="bi bi-arrow-clockwise"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

@if($completedWorks->count() > 0)
<div class="card mb-4 shadow-sm">
    <div class="card-body py-3">
        <div class="row g-3 mb-0">
            <div class="col-md-4">
                <div class="d-flex align-items-center p-3 bg-light rounded">
                    <div class="flex-shrink-0">
                        <i class="bi bi-clipboard-check text-primary fs-4"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <small class="text-muted d-block mb-1">Total Records</small>
                        <h5 class="mb-0 text-primary fw-bold">{{ $completedWorks->total() }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex align-items-center p-3 bg-light rounded">
                    <div class="flex-shrink-0">
                        <i class="bi bi-check-circle-fill text-success fs-4"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <small class="text-muted d-block mb-1">Total Quantity</small>
                        <h5 class="mb-0 text-success fw-bold">{{ number_format($completedWorks->sum('quantity'), 2) }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex align-items-center p-3 bg-light rounded">
                    <div class="flex-shrink-0">
                        <i class="bi bi-receipt text-info fs-4"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <small class="text-muted d-block mb-1">Billed</small>
                        <h5 class="mb-0 text-info fw-bold">{{ $completedWorks->where('status', 'billed')->count() }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Work Date</th>
                        <th>Project</th>
                        <th>Work Type</th>
                        <th>Description</th>
                        <th>Quantity</th>
                        <th>Unit</th>
                        <th>Status</th>
                        <th>Bill Item</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($completedWorks as $index => $work)
                        <tr>
                            <td>{{ $completedWorks->firstItem() + $index }}</td>
                            <td>{{ $work->work_date->format('Y-m-d') }}</td>
                            <td>{{ $work->project->name ?? '—' }}</td>
                            <td>
                                <span class="badge bg-info">{{ $work->work_type }}</span>
                            </td>
                            <td>
                                <small>{{ Str::limit($work->description, 50) }}</small>
                            </td>
                            <td class="text-end">
                                <strong>{{ number_format($work->quantity, 2) }}</strong>
                            </td>
                            <td>{{ $work->uom }}</td>
                            <td>
                                <span class="badge bg-{{ $work->status === 'billed' ? 'success' : ($work->status === 'verified' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($work->status) }}
                                </span>
                            </td>
                            <td>
                                @if($work->billItem)
                                    <small class="text-muted">{{ $work->billItem->description }}</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.completed-works.show', $work) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-eye me-1"></i> View
                                    </a>
                                    <a href="{{ route('admin.completed-works.edit', $work) }}" class="btn btn-outline-warning btn-sm">
                                        <i class="bi bi-pencil me-1"></i> Edit
                                    </a>
                                    <form action="{{ route('admin.completed-works.destroy', $work) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                            <i class="bi bi-trash me-1"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <p class="text-muted mb-0">No completed work records found.</p>
                                <a href="{{ route('admin.completed-works.create') }}" class="btn btn-primary btn-sm mt-2">Add First Record</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <x-pagination :paginator="$completedWorks" :show-info="true" />
    </div>
</div>
@endsection

