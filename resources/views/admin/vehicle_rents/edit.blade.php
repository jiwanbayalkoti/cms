@extends('admin.layout')

@section('title', 'Edit Vehicle Rent')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Edit Vehicle Rent</h1>
        <p class="text-muted mb-0">Update vehicle rental record</p>
    </div>
    <a href="{{ route('admin.vehicle-rents.index') }}" class="btn btn-outline-secondary">Back to List</a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.vehicle-rents.update', $vehicleRent) }}" method="POST" id="vehicleRentForm"
              data-validate="true"
              data-validation-route="{{ route('admin.vehicle-rents.validate') }}">
            @csrf
            @method('PUT')
            
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Rent Date <span class="text-danger">*</span></label>
                    <input type="date" name="rent_date" class="form-control @error('rent_date') is-invalid @enderror" 
                           value="{{ old('rent_date', $vehicleRent->rent_date->format('Y-m-d')) }}" required>
                    @error('rent_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3 mb-3">
                    <label class="form-label">Project</label>
                    <select name="project_id" class="form-select">
                        <option value="">None</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ old('project_id', $vehicleRent->project_id) == $project->id ? 'selected' : '' }}>
                                {{ $project->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label class="form-label">Supplier</label>
                    <select name="supplier_id" class="form-select">
                        <option value="">None</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ old('supplier_id', $vehicleRent->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label class="form-label">Vehicle Type <span class="text-danger">*</span></label>
                    <select name="vehicle_type" id="vehicle_type" class="form-select @error('vehicle_type') is-invalid @enderror" required onchange="handleVehicleTypeChange()">
                        <option value="">Select Vehicle Type</option>
                        @foreach($vehicleTypes as $key => $label)
                            <option value="{{ $key }}" {{ old('vehicle_type', $vehicleRent->vehicle_type) == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('vehicle_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3 mb-3">
                    <label class="form-label">Vehicle Number</label>
                    <input type="text" name="vehicle_number" class="form-control" value="{{ old('vehicle_number', $vehicleRent->vehicle_number) }}" 
                           placeholder="Registration number">
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Start Location <span class="text-danger">*</span></label>
                    <input type="text" name="start_location" class="form-control @error('start_location') is-invalid @enderror" 
                           value="{{ old('start_location', $vehicleRent->start_location) }}" required>
                    @error('start_location')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Destination Location <span class="text-danger">*</span></label>
                    <input type="text" name="destination_location" class="form-control @error('destination_location') is-invalid @enderror" 
                           value="{{ old('destination_location', $vehicleRent->destination_location) }}" required>
                    @error('destination_location')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Driver Name</label>
                    <input type="text" name="driver_name" class="form-control" value="{{ old('driver_name', $vehicleRent->driver_name) }}">
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">Driver Contact</label>
                    <input type="text" name="driver_contact" class="form-control" value="{{ old('driver_contact', $vehicleRent->driver_contact) }}">
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">Purpose</label>
                    <input type="text" name="purpose" class="form-control" value="{{ old('purpose', $vehicleRent->purpose) }}">
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Rate & Payment Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Rate Type <span class="text-danger">*</span></label>
                            <select name="rate_type" id="rate_type" class="form-select" required onchange="toggleRateFields()">
                                <option value="fixed" {{ old('rate_type', $vehicleRent->rate_type) === 'fixed' ? 'selected' : '' }}>Fixed Rate</option>
                                <option value="per_km" {{ old('rate_type', $vehicleRent->rate_type) === 'per_km' ? 'selected' : '' }}>Per Kilometer</option>
                                <option value="per_hour" {{ old('rate_type', $vehicleRent->rate_type) === 'per_hour' ? 'selected' : '' }}>Per Hour</option>
                                <option value="daywise" {{ old('rate_type', $vehicleRent->rate_type) === 'daywise' ? 'selected' : '' }}>Daywise</option>
                                <option value="per_quintal" {{ old('rate_type', $vehicleRent->rate_type) === 'per_quintal' ? 'selected' : '' }}>Per Quintal</option>
                                <option value="not_fixed" {{ old('rate_type', $vehicleRent->rate_type) === 'not_fixed' ? 'selected' : '' }}>Not Fixed</option>
                            </select>
                        </div>
                        
                        @php
                            $rateType = old('rate_type', $vehicleRent->rate_type);
                            $showDistance = $rateType === 'per_km';
                            $showHourly = $rateType === 'per_hour';
                            $showDaywise = $rateType === 'daywise';
                            $showQuintal = $rateType === 'per_quintal';
                            $showFixed = $rateType === 'fixed';
                            $isNotFixed = $rateType === 'not_fixed';
                        @endphp
                        
                        <div class="col-md-3 mb-3" id="distance_field" style="display: {{ $showDistance ? 'block' : 'none' }};">
                            <label class="form-label">Distance (km)</label>
                            <input type="number" name="distance_km" id="distance_km" step="0.01" min="0" 
                                   class="form-control" value="{{ old('distance_km', $vehicleRent->distance_km) }}" oninput="calculatePerKm()">
                        </div>
                        
                        <div class="col-md-3 mb-3" id="rate_per_km_field" style="display: {{ $showDistance ? 'block' : 'none' }};">
                            <label class="form-label">Rate per km</label>
                            <input type="number" name="rate_per_km" id="rate_per_km" step="0.01" min="0" 
                                   class="form-control" value="{{ old('rate_per_km', $vehicleRent->rate_per_km) }}" oninput="calculatePerKm()">
                        </div>
                        
                        <div class="col-md-3 mb-3" id="hours_field" style="display: {{ $showHourly ? 'block' : 'none' }};">
                            <label class="form-label">Hours</label>
                            <input type="number" name="hours" id="hours" min="0" max="23" 
                                   class="form-control" value="{{ old('hours', $vehicleRent->hours) }}" oninput="calculatePerHour()">
                        </div>
                        
                        <div class="col-md-3 mb-3" id="minutes_field" style="display: {{ $showHourly ? 'block' : 'none' }};">
                            <label class="form-label">Minutes</label>
                            <input type="number" name="minutes" id="minutes" min="0" max="59" 
                                   class="form-control" value="{{ old('minutes', $vehicleRent->minutes) }}" oninput="calculatePerHour()">
                        </div>
                        
                        <div class="col-md-3 mb-3" id="rate_per_hour_field" style="display: {{ $showHourly ? 'block' : 'none' }};">
                            <label class="form-label">Rate per hour</label>
                            <input type="number" name="rate_per_hour" id="rate_per_hour" step="0.01" min="0" 
                                   class="form-control" value="{{ old('rate_per_hour', $vehicleRent->rate_per_hour) }}" oninput="calculatePerHour()">
                        </div>
                        
                        <div class="col-md-3 mb-3" id="rent_start_date_field" style="display: {{ $showDaywise ? 'block' : 'none' }};">
                            <label class="form-label">Rent Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="rent_start_date" id="rent_start_date" 
                                   class="form-control" value="{{ old('rent_start_date', $vehicleRent->rent_start_date ? $vehicleRent->rent_start_date->format('Y-m-d') : '') }}" onchange="calculateDaywise()">
                        </div>
                        
                        <div class="col-md-3 mb-3" id="rent_end_date_field" style="display: {{ $showDaywise ? 'block' : 'none' }};">
                            <label class="form-label">Rent End Date <small class="text-muted">(Leave empty if ongoing)</small></label>
                            <input type="date" name="rent_end_date" id="rent_end_date" 
                                   class="form-control" value="{{ old('rent_end_date', $vehicleRent->rent_end_date ? $vehicleRent->rent_end_date->format('Y-m-d') : '') }}" onchange="calculateDaywise()">
                        </div>
                        
                        <div class="col-md-3 mb-3" id="number_of_days_field" style="display: {{ $showDaywise ? 'block' : 'none' }};">
                            <label class="form-label">Number of Days <small class="text-muted">(Manual entry)</small></label>
                            <input type="number" name="number_of_days" id="number_of_days" min="1" 
                                   class="form-control" value="{{ old('number_of_days', $vehicleRent->number_of_days) }}" oninput="calculateDaywise()">
                        </div>
                        
                        <div class="col-md-3 mb-3" id="rate_per_day_field" style="display: {{ $showDaywise ? 'block' : 'none' }};">
                            <label class="form-label">Rate per day</label>
                            <input type="number" name="rate_per_day" id="rate_per_day" step="0.01" min="0" 
                                   class="form-control" value="{{ old('rate_per_day', $vehicleRent->rate_per_day) }}" oninput="calculateDaywise()">
                        </div>
                        
                        <div class="col-md-3 mb-3" id="quantity_quintal_field" style="display: {{ $showQuintal ? 'block' : 'none' }};">
                            <label class="form-label">Quantity (Quintal)</label>
                            <input type="number" name="quantity_quintal" id="quantity_quintal" step="0.01" min="0" 
                                   class="form-control" value="{{ old('quantity_quintal', $vehicleRent->quantity_quintal) }}" oninput="calculatePerQuintal()">
                        </div>
                        
                        <div class="col-md-3 mb-3" id="rate_per_quintal_field" style="display: {{ $showQuintal ? 'block' : 'none' }};">
                            <label class="form-label">Rate per quintal</label>
                            <input type="number" name="rate_per_quintal" id="rate_per_quintal" step="0.01" min="0" 
                                   class="form-control" value="{{ old('rate_per_quintal', $vehicleRent->rate_per_quintal) }}" oninput="calculatePerQuintal()">
                        </div>
                        
                        <div class="col-md-3 mb-3" id="fixed_rate_field" style="display: {{ $showFixed ? 'block' : 'none' }};">
                            <label class="form-label">Fixed Rate <span class="text-danger">*</span></label>
                            <input type="number" name="fixed_rate" id="fixed_rate" step="0.01" min="0" 
                                   class="form-control" value="{{ old('fixed_rate', $vehicleRent->fixed_rate) }}" oninput="updateTotal()">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Total Amount <span class="text-danger" id="total_amount_required">{{ $isNotFixed ? '' : '*' }}</span></label>
                            <input type="number" name="total_amount" id="total_amount" step="0.01" min="0" 
                                   class="form-control @error('total_amount') is-invalid @enderror" 
                                   value="{{ old('total_amount', $vehicleRent->total_amount) }}" 
                                   {{ $isNotFixed ? '' : 'readonly' }} {{ $isNotFixed ? '' : 'required' }} oninput="updateBalance()">
                            @error('total_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Paid Amount</label>
                            <input type="number" name="paid_amount" id="paid_amount" step="0.01" min="0" 
                                   class="form-control" value="{{ old('paid_amount', $vehicleRent->paid_amount) }}" oninput="updateBalance()">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Balance Amount</label>
                            <input type="text" id="balance_amount" class="form-control" readonly value="{{ number_format($vehicleRent->balance_amount, 2) }}">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Bank Account</label>
                            <select name="bank_account_id" class="form-select">
                                <option value="">None</option>
                                @foreach($bankAccounts as $account)
                                    <option value="{{ $account->id }}" {{ old('bank_account_id', $vehicleRent->bank_account_id) == $account->id ? 'selected' : '' }}>
                                        {{ $account->account_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Payment Date</label>
                            <input type="date" name="payment_date" class="form-control" 
                                   value="{{ old('payment_date', $vehicleRent->payment_date ? $vehicleRent->payment_date->format('Y-m-d') : '') }}">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Notes</label>
                <textarea name="notes" rows="3" class="form-control">{{ old('notes', $vehicleRent->notes) }}</textarea>
            </div>
            
            <div class="d-flex justify-content-end">
                <a href="{{ route('admin.vehicle-rents.index') }}" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Vehicle Rent</button>
            </div>
        </form>
    </div>
</div>

<script>
function handleVehicleTypeChange() {
    const vehicleType = document.getElementById('vehicle_type').value;
    const rateTypeSelect = document.getElementById('rate_type');
    const currentRateType = rateTypeSelect.value;
    
    // Auto-select appropriate rate type based on vehicle type
    // Only change if it makes sense (don't override user's existing selection unnecessarily)
    if (vehicleType === 'excavator' || vehicleType === 'jcv') {
        // Excavator and JCV typically use hourly rates
        // Only suggest change if current rate type doesn't match vehicle type
        if (currentRateType === 'fixed' || currentRateType === 'per_km') {
            rateTypeSelect.value = 'per_hour';
        }
    }
    
    // Always update fields visibility after vehicle type change
    toggleRateFields();
}

function toggleRateFields() {
    const rateType = document.getElementById('rate_type').value;
    const vehicleType = document.getElementById('vehicle_type').value;
    
    // Hide all fields first
    document.getElementById('distance_field').style.display = 'none';
    document.getElementById('rate_per_km_field').style.display = 'none';
    document.getElementById('hours_field').style.display = 'none';
    document.getElementById('minutes_field').style.display = 'none';
    document.getElementById('rate_per_hour_field').style.display = 'none';
    document.getElementById('rent_start_date_field').style.display = 'none';
    document.getElementById('rent_end_date_field').style.display = 'none';
    document.getElementById('number_of_days_field').style.display = 'none';
    document.getElementById('rate_per_day_field').style.display = 'none';
    document.getElementById('quantity_quintal_field').style.display = 'none';
    document.getElementById('rate_per_quintal_field').style.display = 'none';
    document.getElementById('fixed_rate_field').style.display = 'none';
    
    // Show relevant fields based on rate type
    if (rateType === 'per_km') {
        document.getElementById('distance_field').style.display = 'block';
        document.getElementById('rate_per_km_field').style.display = 'block';
    } else if (rateType === 'per_hour') {
        document.getElementById('hours_field').style.display = 'block';
        document.getElementById('minutes_field').style.display = 'block';
        document.getElementById('rate_per_hour_field').style.display = 'block';
    } else if (rateType === 'daywise') {
        document.getElementById('rent_start_date_field').style.display = 'block';
        document.getElementById('rent_end_date_field').style.display = 'block';
        document.getElementById('number_of_days_field').style.display = 'block';
        document.getElementById('rate_per_day_field').style.display = 'block';
    } else if (rateType === 'per_quintal') {
        document.getElementById('quantity_quintal_field').style.display = 'block';
        document.getElementById('rate_per_quintal_field').style.display = 'block';
    } else if (rateType === 'not_fixed') {
        // For not_fixed, no rate fields needed - user enters total directly
        // Total amount field will be editable
    } else if (rateType === 'fixed') {
        document.getElementById('fixed_rate_field').style.display = 'block';
    }
    
    // Update total amount field readonly status
    updateTotalAmountField();
    updateTotal();
}

function updateTotalAmountField() {
    const rateType = document.getElementById('rate_type').value;
    const totalAmountField = document.getElementById('total_amount');
    const requiredIndicator = document.getElementById('total_amount_required');
    
    if (rateType === 'not_fixed') {
        // Make total amount editable for not_fixed and not required
        totalAmountField.removeAttribute('readonly');
        totalAmountField.removeAttribute('required');
        totalAmountField.placeholder = 'Enter total amount (optional)';
        requiredIndicator.style.display = 'none';
        // Only clear if switching from another rate type (not on initial load)
        const previousRateType = totalAmountField.dataset.previousRateType || '';
        if (previousRateType && previousRateType !== 'not_fixed' && totalAmountField.value && parseFloat(totalAmountField.value) > 0) {
            totalAmountField.value = '';
        }
        totalAmountField.dataset.previousRateType = rateType;
    } else {
        // Make total amount readonly for calculated rates and required
        totalAmountField.setAttribute('readonly', 'readonly');
        totalAmountField.setAttribute('required', 'required');
        totalAmountField.placeholder = '0.00';
        requiredIndicator.style.display = 'inline';
        totalAmountField.dataset.previousRateType = rateType;
    }
}

function calculatePerKm() {
    const rateType = document.getElementById('rate_type').value;
    if (rateType === 'per_km') {
        const distance = parseFloat(document.getElementById('distance_km').value) || 0;
        const ratePerKm = parseFloat(document.getElementById('rate_per_km').value) || 0;
        const total = distance * ratePerKm;
        document.getElementById('total_amount').value = total.toFixed(2);
        updateBalance();
    }
}

function calculatePerHour() {
    const rateType = document.getElementById('rate_type').value;
    if (rateType === 'per_hour') {
        const hours = parseInt(document.getElementById('hours').value) || 0;
        const minutes = parseInt(document.getElementById('minutes').value) || 0;
        const ratePerHour = parseFloat(document.getElementById('rate_per_hour').value) || 0;
        const totalHours = hours + (minutes / 60);
        const total = totalHours * ratePerHour;
        document.getElementById('total_amount').value = total.toFixed(2);
        updateBalance();
    }
}

function calculateDaywise() {
    const rateType = document.getElementById('rate_type').value;
    if (rateType === 'daywise') {
        let numberOfDays = 0;
        const ratePerDay = parseFloat(document.getElementById('rate_per_day').value) || 0;
        
        // Calculate days from dates if start date is provided
        const startDate = document.getElementById('rent_start_date').value;
        const endDate = document.getElementById('rent_end_date').value;
        
        if (startDate) {
            const start = new Date(startDate);
            const end = endDate ? new Date(endDate) : new Date(); // Use today if end date is empty
            const diffTime = Math.abs(end - start);
            numberOfDays = Math.max(1, Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1); // +1 to include both dates
        } else {
            // Fallback to manual number of days
            numberOfDays = parseInt(document.getElementById('number_of_days').value) || 0;
        }
        
        const total = numberOfDays * ratePerDay;
        document.getElementById('total_amount').value = total.toFixed(2);
        
        // Update number_of_days field to show calculated value
        if (startDate) {
            document.getElementById('number_of_days').value = numberOfDays;
        }
        
        updateBalance();
    }
}

function calculatePerQuintal() {
    const rateType = document.getElementById('rate_type').value;
    if (rateType === 'per_quintal') {
        const quantityQuintal = parseFloat(document.getElementById('quantity_quintal').value) || 0;
        const ratePerQuintal = parseFloat(document.getElementById('rate_per_quintal').value) || 0;
        const total = quantityQuintal * ratePerQuintal;
        document.getElementById('total_amount').value = total.toFixed(2);
        updateBalance();
    }
}

function updateTotal() {
    const rateType = document.getElementById('rate_type').value;
    let total = 0;
    
    if (rateType === 'per_km') {
        const distance = parseFloat(document.getElementById('distance_km').value) || 0;
        const ratePerKm = parseFloat(document.getElementById('rate_per_km').value) || 0;
        total = distance * ratePerKm;
    } else if (rateType === 'per_hour') {
        const hours = parseInt(document.getElementById('hours').value) || 0;
        const minutes = parseInt(document.getElementById('minutes').value) || 0;
        const ratePerHour = parseFloat(document.getElementById('rate_per_hour').value) || 0;
        const totalHours = hours + (minutes / 60);
        total = totalHours * ratePerHour;
    } else if (rateType === 'daywise') {
        let numberOfDays = 0;
        const ratePerDay = parseFloat(document.getElementById('rate_per_day').value) || 0;
        
        // Calculate days from dates if start date is provided
        const startDate = document.getElementById('rent_start_date').value;
        const endDate = document.getElementById('rent_end_date').value;
        
        if (startDate) {
            const start = new Date(startDate);
            const end = endDate ? new Date(endDate) : new Date(); // Use today if end date is empty
            const diffTime = Math.abs(end - start);
            numberOfDays = Math.max(1, Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1); // +1 to include both dates
        } else {
            // Fallback to manual number of days
            numberOfDays = parseInt(document.getElementById('number_of_days').value) || 0;
        }
        
        total = numberOfDays * ratePerDay;
    } else if (rateType === 'per_quintal') {
        const quantityQuintal = parseFloat(document.getElementById('quantity_quintal').value) || 0;
        const ratePerQuintal = parseFloat(document.getElementById('rate_per_quintal').value) || 0;
        total = quantityQuintal * ratePerQuintal;
    } else if (rateType === 'not_fixed') {
        // For not_fixed, use the value directly from the input field
        total = parseFloat(document.getElementById('total_amount').value) || 0;
    } else if (rateType === 'fixed') {
        total = parseFloat(document.getElementById('fixed_rate').value) || 0;
    }
    
    // Only update total amount if it's not not_fixed (for not_fixed, user enters it directly)
    if (rateType !== 'not_fixed') {
        document.getElementById('total_amount').value = total.toFixed(2);
    } else {
        // For not_fixed, keep existing value or allow user to enter
        const totalField = document.getElementById('total_amount');
        // Don't clear if there's already a value (preserve existing data)
        if (!totalField.value || parseFloat(totalField.value) === 0) {
            totalField.value = '';
        }
    }
    updateBalance();
}

function updateBalance() {
    const total = parseFloat(document.getElementById('total_amount').value) || 0;
    const paid = parseFloat(document.getElementById('paid_amount').value) || 0;
    const balance = total - paid;
    document.getElementById('balance_amount').value = balance.toFixed(2);
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Initialize field visibility based on current values
    toggleRateFields();
    updateTotalAmountField();
    updateBalance();
    
    // Note: We don't auto-change rate type in edit mode to preserve existing data
    // But we ensure fields are shown/hidden correctly
});
</script>
@endsection

