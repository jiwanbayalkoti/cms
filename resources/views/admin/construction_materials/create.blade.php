@extends('admin.layout')

@section('title', 'Add Construction Material')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Add Construction Material</h1>
    <a href="{{ route('admin.construction-materials.index') }}" class="btn btn-outline-secondary">Back to List</a>
</div>

<div class="card">
    <div class="card-header">
        <strong>Material Details</strong>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.construction-materials.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Material Name *</label>
                    <select name="material_name" class="form-select" required>
                        <option value="">Select material name</option>
                        @foreach($materialNames as $materialName)
                            <option value="{{ $materialName->name }}" {{ old('material_name') === $materialName->name ? 'selected' : '' }}>
                                {{ $materialName->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Material Category</label>
                    <select name="material_category" class="form-select">
                        <option value="">Select category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->name }}" {{ old('material_category') === $category->name ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Unit *</label>
                    <select name="unit" class="form-select" required>
                        <option value="">Select unit</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->name }}" {{ old('unit') === $unit->name ? 'selected' : '' }}>
                                {{ $unit->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Quantity Received *</label>
                    <input type="number" step="0.01" name="quantity_received" class="form-control" value="{{ old('quantity_received', 0) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Rate per Unit *</label>
                    <input type="number" step="0.01" name="rate_per_unit" class="form-control" value="{{ old('rate_per_unit', 0) }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Supplier Name</label>
                    <select name="supplier_name" class="form-select">
                        <option value="">Select supplier</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->name }}" {{ old('supplier_name') === $supplier->name ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Supplier Contact</label>
                    <input type="text" name="supplier_contact" class="form-control" value="{{ old('supplier_contact') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Bill Number</label>
                    <input type="text" name="bill_number" class="form-control" value="{{ old('bill_number') }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Bill Date</label>
                    <input type="date" name="bill_date" class="form-control" value="{{ old('bill_date') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Payment Status *</label>
                    <select name="payment_status" class="form-select" required>
                        <option value="Paid" {{ old('payment_status') === 'Paid' ? 'selected' : '' }}>Paid</option>
                        <option value="Unpaid" {{ old('payment_status', 'Unpaid') === 'Unpaid' ? 'selected' : '' }}>Unpaid</option>
                        <option value="Partial" {{ old('payment_status') === 'Partial' ? 'selected' : '' }}>Partial</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Payment Mode</label>
                    <select name="payment_mode" class="form-select">
                        <option value="">Select payment mode</option>
                        @foreach($paymentModes as $paymentMode)
                            <option value="{{ $paymentMode->name }}" {{ old('payment_mode') === $paymentMode->name ? 'selected' : '' }}>
                                {{ $paymentMode->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Purchased / Payment By</label>
                    <select name="purchased_by_id" class="form-select">
                        <option value="">Select person</option>
                        @foreach($purchasedBies as $purchasedBy)
                            <option value="{{ $purchasedBy->id }}" {{ old('purchased_by_id') == $purchasedBy->id ? 'selected' : '' }}>
                                {{ $purchasedBy->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Delivery Date</label>
                    <input type="date" name="delivery_date" class="form-control" value="{{ old('delivery_date') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Delivery Site</label>
                    <input type="text" name="delivery_site" class="form-control" value="{{ old('delivery_site') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Delivered By</label>
                    <input type="text" name="delivered_by" class="form-control" value="{{ old('delivered_by') }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Received By</label>
                    <input type="text" name="received_by" class="form-control" value="{{ old('received_by') }}">
                </div>
<div class="col-md-4">
    <label class="form-label">Project *</label>
    <select name="project_id" class="form-select" required>
        <option value="">Select project</option>
        @foreach($projects as $project)
            <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>
                {{ $project->name }}
            </option>
        @endforeach
    </select>
</div>
                <div class="col-md-4">
                    <label class="form-label">Work Type</label>
                    <select name="work_type" class="form-select">
                        <option value="">Select work type</option>
                        @foreach($workTypes as $workType)
                            <option value="{{ $workType->name }}" {{ old('work_type') === $workType->name ? 'selected' : '' }}>
                                {{ $workType->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Usage Purpose</label>
                    <textarea name="usage_purpose" rows="3" class="form-control">{{ old('usage_purpose') }}</textarea>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Status *</label>
                    <select name="status" class="form-select" required>
                        <option value="Received" {{ old('status', 'Received') === 'Received' ? 'selected' : '' }}>Received</option>
                        <option value="Pending" {{ old('status') === 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Returned" {{ old('status') === 'Returned' ? 'selected' : '' }}>Returned</option>
                        <option value="Damaged" {{ old('status') === 'Damaged' ? 'selected' : '' }}>Damaged</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Approved By</label>
                    <input type="text" name="approved_by" class="form-control" value="{{ old('approved_by') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Approval Date</label>
                    <input type="date" name="approval_date" class="form-control" value="{{ old('approval_date') }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Bill Attachment (PDF / Image)</label>
                    <input type="file" name="bill_attachment" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Delivery Photo</label>
                    <input type="file" name="delivery_photo" class="form-control">
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">Save Material</button>
            </div>
        </form>
    </div>
</div>
@endsection


