@extends('admin.layout')

@section('title', 'BoQ')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">BoQ – Bill of Quantities</h1>
    <a href="{{ route('admin.boq.work-index') }}" class="btn btn-outline-primary">Work (Types & Works)</a>
</div>

<div class="card">
    <div class="card-header">
        <strong>Works – click to add or edit items</strong>
    </div>
    <div class="card-body">
        @forelse($types as $type)
            <div class="mb-4">
                <h6 class="text-primary mb-2">{{ $type->name }}</h6>
                <ul class="list-group list-group-flush">
                    @forelse($type->works as $work)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>{{ $work->name }}</span>
                            <a href="{{ route('admin.boq.works.show', $work) }}" class="btn btn-sm btn-primary">Add / Edit Items</a>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">No works in this type.</li>
                    @endforelse
                </ul>
            </div>
        @empty
            <p class="text-muted mb-0">No types or works yet. Go to <a href="{{ route('admin.boq.work-index') }}">Work</a> to add types and works.</p>
        @endforelse
    </div>
</div>
@endsection
