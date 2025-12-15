@extends('admin.layout')

@section('title', 'Material Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Material Details</h1>
    <div>
        <a href="{{ route('admin.construction-materials.edit', $material) }}" class="btn btn-primary me-2">Edit</a>
        <a href="{{ route('admin.construction-materials.index') }}" class="btn btn-outline-secondary">Back to List</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header">
                <strong>Material &amp; Project</strong>
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-md-6">
                        <strong>Material Name:</strong>
                        <div>{{ $material->material_name }}</div>
                    </div>
                    <div class="col-md-3">
                        <strong>Category:</strong>
                        <div>{{ $material->material_category }}</div>
                    </div>
                    <div class="col-md-3">
                        <strong>Unit:</strong>
                        <div>{{ $material->unit }}</div>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-6">
                        <strong>Project:</strong>
                        <div>{{ $material->project_name }}</div>
                    </div>
                    <div class="col-md-6">
                        <strong>Work Type:</strong>
                        <div>{{ $material->work_type }}</div>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-6">
                        <strong>Delivery Site:</strong>
                        <div>{{ $material->delivery_site }}</div>
                    </div>
                    <div class="col-md-3">
                        <strong>Delivered By:</strong>
                        <div>{{ $material->delivered_by }}</div>
                    </div>
                    <div class="col-md-3">
                        <strong>Received By:</strong>
                        <div>{{ $material->received_by }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <strong>Usage &amp; Approval</strong>
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-md-4">
                        <strong>Quantity Received:</strong>
                        <div>{{ number_format($material->quantity_received, 2) }} {{ $material->unit }}</div>
                    </div>
                    <div class="col-md-4">
                        <strong>Quantity Used:</strong>
                        <div>{{ number_format($material->quantity_used, 2) }} {{ $material->unit }}</div>
                    </div>
                    <div class="col-md-4">
                        <strong>Remaining:</strong>
                        <div>{{ number_format($material->quantity_remaining, 2) }} {{ $material->unit }}</div>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4">
                        <strong>Wastage:</strong>
                        <div>{{ number_format($material->wastage_quantity, 2) }} {{ $material->unit }}</div>
                    </div>
                    <div class="col-md-4">
                        <strong>Status:</strong>
                        <div><span class="badge bg-secondary">{{ $material->status }}</span></div>
                    </div>
                    <div class="col-md-4">
                        <strong>Delivery Date:</strong>
                        <div>{{ optional($material->delivery_date)->format('Y-m-d') }}</div>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-6">
                        <strong>Approved By:</strong>
                        <div>{{ $material->approved_by }}</div>
                    </div>
                    <div class="col-md-6">
                        <strong>Approval Date:</strong>
                        <div>{{ optional($material->approval_date)->format('Y-m-d') }}</div>
                    </div>
                </div>
                <div class="mb-2">
                    <strong>Usage Purpose:</strong>
                    <p class="mb-0">{{ $material->usage_purpose }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header">
                <strong>Financial</strong>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <strong>Rate per Unit:</strong>
                    <div>{{ number_format($material->rate_per_unit, 2) }}</div>
                </div>
                <div class="mb-2">
                    <strong>Total Cost:</strong>
                    <div>{{ number_format($material->total_cost, 2) }}</div>
                </div>
                <div class="mb-2">
                    <strong>Bill Number:</strong>
                    <div>{{ $material->bill_number }}</div>
                </div>
                <div class="mb-2">
                    <strong>Bill Date:</strong>
                    <div>{{ optional($material->bill_date)->format('Y-m-d') }}</div>
                </div>
                <div class="mb-2">
                    <strong>Payment Status:</strong>
                    <div>{{ $material->payment_status }}</div>
                </div>
                <div class="mb-2">
                    <strong>Payment Mode:</strong>
                    <div>{{ $material->payment_mode }}</div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <strong>Supplier</strong>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <strong>Name:</strong>
                    <div>{{ $material->supplier_name }}</div>
                </div>
                <div class="mb-2">
                    <strong>Contact:</strong>
                    <div>{{ $material->supplier_contact }}</div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <strong>Attachments</strong>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <strong>Bill Attachment:</strong>
                    <div>
                        @php
                            $billUrl = storage_url($material->bill_attachment);
                        @endphp
                        @if($billUrl)
                            <a href="{{ $billUrl }}" target="_blank">View Bill</a>
                        @else
                            <span class="text-muted">Not uploaded</span>
                        @endif
                    </div>
                </div>
                <div class="mb-2">
                    <strong>Delivery Photo:</strong>
                    <div>
                        @php
                            $photoUrl = storage_url($material->delivery_photo);
                        @endphp
                        @if($photoUrl)
                            <a href="{{ $photoUrl }}" target="_blank">View Photo</a>
                        @else
                            <span class="text-muted">Not uploaded</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


