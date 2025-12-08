@extends('admin.layout')

@section('title', 'Completed Work Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Completed Work Details</h1>
    <div>
        <a href="{{ route('admin.completed-works.edit', $completed_work) }}" class="btn btn-warning me-2">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
        <a href="{{ route('admin.completed-works.index') }}" class="btn btn-outline-secondary">Back to List</a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Work Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="200">Project:</th>
                        <td><strong>{{ $completed_work->project->name ?? '—' }}</strong></td>
                    </tr>
                    <tr>
                        <th>Work Type:</th>
                        <td><span class="badge bg-info">{{ $completed_work->work_type }}</span></td>
                    </tr>
                    <tr>
                        <th>Description:</th>
                        <td>{{ $completed_work->description }}</td>
                    </tr>
                    <tr>
                        <th>Quantity:</th>
                        <td><strong>{{ number_format($completed_work->quantity, 3) }} {{ $completed_work->uom }}</strong></td>
                    </tr>
                    <tr>
                        <th>Work Date:</th>
                        <td>{{ $completed_work->work_date->format('F d, Y') }}</td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>
                            <span class="badge bg-{{ $completed_work->status === 'billed' ? 'success' : ($completed_work->status === 'verified' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($completed_work->status) }}
                            </span>
                        </td>
                    </tr>
                    @if($completed_work->remarks)
                        <tr>
                            <th>Remarks:</th>
                            <td>{{ $completed_work->remarks }}</td>
                        </tr>
                    @endif
                    <tr>
                        <th>Recorded By:</th>
                        <td>{{ $completed_work->recorder->name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th>Recorded At:</th>
                        <td>{{ $completed_work->created_at->format('F d, Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        @if($completed_work->billItem)
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Linked Bill Item</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="200">Bill Module:</th>
                            <td>
                                @if($completed_work->billItem->billModule)
                                    <a href="{{ route('admin.bill-modules.show', $completed_work->billItem->billModule) }}" class="text-decoration-none">
                                        {{ $completed_work->billItem->billModule->title }}
                                    </a>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Category:</th>
                            <td>{{ $completed_work->billCategory->name ?? ($completed_work->billItem->category ?? '—') }}</td>
                        </tr>
                        <tr>
                            <th>Subcategory:</th>
                            <td>{{ $completed_work->billSubcategory->name ?? ($completed_work->billItem->subcategory ?? '—') }}</td>
                        </tr>
                        <tr>
                            <th>Description:</th>
                            <td>{{ $completed_work->billItem->description }}</td>
                        </tr>
                        <tr>
                            <th>Estimated Quantity:</th>
                            <td>{{ number_format($completed_work->billItem->quantity, 3) }} {{ $completed_work->billItem->uom }}</td>
                        </tr>
                        <tr>
                            <th>Completed Quantity:</th>
                            <td><strong>{{ number_format($completed_work->quantity, 3) }} {{ $completed_work->uom }}</strong></td>
                        </tr>
                        <tr>
                            <th>Progress:</th>
                            <td>
                                @php
                                    $progress = $completed_work->billItem->quantity > 0 
                                        ? ($completed_work->quantity / $completed_work->billItem->quantity) * 100 
                                        : 0;
                                @endphp
                                <div class="progress" style="height: 25px;">
                                    <div class="progress-bar {{ $progress >= 100 ? 'bg-success' : ($progress >= 75 ? 'bg-info' : ($progress >= 50 ? 'bg-warning' : 'bg-danger')) }}" 
                                         role="progressbar" 
                                         style="width: {{ min(100, $progress) }}%">
                                        {{ number_format($progress, 1) }}%
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

