@extends('admin.layout')
@section('title', 'Measurement Book')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Measurement Book</h1>
        <p class="text-muted mb-0">काम कस्तो र कति भयो रेकर्ड गर्नुहोस्</p>
    </div>
    <a href="{{ route('admin.measurement-books.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> New
    </a>
</div>

<form method="GET" class="mb-3 row g-2 align-items-end">
    <div class="col-auto">
        <label class="form-label small mb-0">Project</label>
        <select name="project_id" class="form-select form-select-sm" onchange="this.form.submit()" style="width:220px">
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
                    <th>#</th>
                    <th>Measurement Date</th>
                    <th>Project</th>
                    <th>Contract No</th>
                    <th>Title</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($books as $b)
                <tr>
                    <td>{{ $b->id }}</td>
                    <td>{{ $b->measurement_date->format('Y-m-d') }}</td>
                    <td>{{ $b->project->name ?? '—' }}</td>
                    <td>{{ $b->contract_no ?? '—' }}</td>
                    <td>{{ Str::limit($b->title, 40) ?: '—' }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.measurement-books.show', $b) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye me-1"></i>View</a>
                        <a href="{{ route('admin.measurement-books.edit', $b) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil me-1"></i>Edit</a>
                        <form action="{{ route('admin.measurement-books.destroy', $b) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this Measurement Book?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1"></i>Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No records. <a href="{{ route('admin.measurement-books.create') }}">Create one</a></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($books->hasPages())
    <div class="card-footer">{{ $books->links() }}</div>
    @endif
</div>
@endsection
