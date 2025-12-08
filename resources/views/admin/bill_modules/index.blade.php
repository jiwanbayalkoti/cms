@extends('admin.layout')

@section('title', 'Construction Final Bills')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Construction Final Bills / Estimates</h1>
        <small class="text-muted">Manage bill modules and BOQ</small>
    </div>
    <a href="{{ route('admin.bill-modules.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Create Bill
    </a>
</div>

<div class="card mb-4">
    <div class="card-header">
        <strong>Search & Filter</strong>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.bill-modules.index') }}" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Project</label>
                <select name="project_id" class="form-select">
                    <option value="">All Projects</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                            {{ $project->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Submitted</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Title or MB Number">
            </div>
            <div class="col-md-12 d-flex justify-content-end mt-2">
                <a href="{{ route('admin.bill-modules.index') }}" class="btn btn-outline-secondary me-2">Reset</a>
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <strong>Bill Modules</strong>
    </div>
    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Project</th>
                    <th>Version</th>
                    <th>MB Number</th>
                    <th>Status</th>
                    <th>Grand Total</th>
                    <th>Created By</th>
                    <th>Created At</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bills as $bill)
                    <tr>
                        <td>{{ $bill->id }}</td>
                        <td>
                            <a href="{{ route('admin.bill-modules.show', $bill) }}" class="text-decoration-none">
                                {{ $bill->title }}
                            </a>
                        </td>
                        <td>{{ $bill->project->name ?? '—' }}</td>
                        <td>{{ $bill->version }}</td>
                        <td>{{ $bill->mb_number ?? '—' }}</td>
                        <td>
                            @php
                                $badgeClass = [
                                    'draft' => 'bg-secondary',
                                    'submitted' => 'bg-info',
                                    'approved' => 'bg-success',
                                    'rejected' => 'bg-danger',
                                    'archived' => 'bg-dark',
                                ][$bill->status] ?? 'bg-secondary';
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ ucfirst($bill->status) }}</span>
                        </td>
                        <td>{{ number_format($bill->aggregate->grand_total ?? 0, 2) }}</td>
                        <td>{{ $bill->creator->name ?? '—' }}</td>
                        <td>{{ $bill->created_at->format('Y-m-d') }}</td>
                        <td class="text-end">
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="{{ route('admin.bill-modules.show', $bill) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i> View
                                </a>
                                @if($bill->canEdit())
                                    <a href="{{ route('admin.bill-modules.edit', $bill) }}" class="btn btn-sm btn-outline-warning">
                                        <i class="bi bi-pencil me-1"></i> Edit
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-3">No bills found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pagination :paginator="$bills" wrapper-class="card-footer" />
</div>
@endsection

