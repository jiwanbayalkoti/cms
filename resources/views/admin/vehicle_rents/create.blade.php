@extends('admin.layout')

@section('title', 'Add Vehicle Rent')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Add Vehicle Rent</h1>
        <p class="text-muted mb-0">Record a vehicle rental transaction</p>
    </div>
    <a href="{{ route('admin.vehicle-rents.index') }}" class="btn btn-outline-secondary">Back to List</a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.vehicle-rents.store') }}" method="POST" id="vehicleRentForm">
            @csrf
            
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Rent Date <span class="text-danger">*</span></label>
                    <input type="date" name="rent_date" class="form-control @error('rent_date') is-invalid @enderror" 
                           value="{{ old('rent_date', date('Y-m-d')) }}" required>
                    @error('rent_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3 mb-3">
                    <label class="form-label">Project</label>
                    <select name="project_id" class="form-select">
                        <option value="">None</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>
                                {{ $project->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label class="form-label">Vehicle Type <span class="text-danger">*</span></label>
                    <select name="vehicle_type" id="vehicle_type" class="form-select @error('vehicle_type') is-invalid @enderror" required onchange="handleVehicleTypeChange()">
                        <option value="">Select Vehicle Type</option>
                        @foreach($vehicleTypes as $key => $label)
                            <option value="{{ $key }}" {{ old('vehicle_type') == $key ? 'selected' : '' }}>
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
                    <input type="text" name="vehicle_number" class="form-control" value="{{ old('vehicle_number') }}" 
                           placeholder="Registration number">
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Start Location <span class="text-danger">*</span></label>
                    <input type="text" name="start_location" class="form-control @error('start_location') is-invalid @enderror" 
                           value="{{ old('start_location') }}" required placeholder="e.g., Kathmandu">
                    @error('start_location')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Destination Location <span class="text-danger">*</span></label>
                    <input type="text" name="destination_location" class="form-control @error('destination_location') is-invalid @enderror" 
                           value="{{ old('destination_location') }}" required placeholder="e.g., Pokhara">
                    @error('destination_location')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Driver Name</label>
                    <input type="text" name="driver_name" class="form-control" value="{{ old('driver_name') }}" 
                           placeholder="Driver name">
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">Driver Contact</label>
                    <input type="text" name="driver_contact" class="form-control" value="{{ old('driver_contact') }}" 
                           placeholder="Phone number">
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">Purpose</label>
                    <input type="text" name="purpose" class="form-control" value="{{ old('purpose') }}" 
                           placeholder="Purpose of trip">
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
                                <option value="fixed" {{ old('rate_type', 'fixed') === 'fixed' ? 'selected' : '' }}>Fixed Rate</option>
                                <option value="per_km" {{ old('rate_type') === 'per_km' ? 'selected' : '' }}>Per Kilometer</option>
                                <option value="per_hour" {{ old('rate_type') === 'per_hour' ? 'selected' : '' }}>Per Hour</option>
                                <option value="daywise" {{ old('rate_type') === 'daywise' ? 'selected' : '' }}>Daywise</option>
                                <option value="per_quintal" {{ old('rate_type') === 'per_quintal' ? 'selected' : '' }}>Per Quintal</option>
                                <option value="not_fixed" {{ old('rate_type') === 'not_fixed' ? 'selected' : '' }}>Not Fixed</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3 mb-3" id="distance_field" style="display: none;">
                            <label class="form-label">Distance (km)</label>
                            <input type="number" name="distance_km" id="distance_km" step="0.01" min="0" 
                                   class="form-control" value="{{ old('distance_km') }}" placeholder="0.00" oninput="calculatePerKm()">
                        </div>
                        
                        <div class="col-md-3 mb-3" id="rate_per_km_field" style="display: none;">
                            <label class="form-label">Rate per km</label>
                            <input type="number" name="rate_per_km" id="rate_per_km" step="0.01" min="0" 
                                   class="form-control" value="{{ old('rate_per_km') }}" placeholder="0.00" oninput="calculatePerKm()">
                        </div>
                        
                        <div class="col-md-3 mb-3" id="hours_field" style="display: none;">
                            <label class="form-label">Hours</label>
                            <input type="number" name="hours" id="hours" min="0" max="23" 
                                   class="form-control" value="{{ old('hours') }}" placeholder="0" oninput="calculatePerHour()">
                        </div>
                        
                        <div class="col-md-3 mb-3" id="minutes_field" style="display: none;">
                            <label class="form-label">Minutes</label>
                            <input type="number" name="minutes" id="minutes" min="0" max="59" 
                                   class="form-control" value="{{ old('minutes') }}" placeholder="0" oninput="calculatePerHour()">
                        </div>
                        
                        <div class="col-md-3 mb-3" id="rate_per_hour_field" style="display: none;">
                            <label class="form-label">Rate per hour</label>
                            <input type="number" name="rate_per_hour" id="rate_per_hour" step="0.01" min="0" 
                                   class="form-control" value="{{ old('rate_per_hour') }}" placeholder="0.00" oninput="calculatePerHour()">
                        </div>
                        
                        <div class="col-md-3 mb-3" id="rent_start_date_field" style="display: none;">
                            <label class="form-label">Rent Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="rent_start_date" id="rent_start_date" 
                                   class="form-control" value="{{ old('rent_start_date') }}" onchange="calculateDaywise()">
                        </div>
                        
                        <div class="col-md-3 mb-3" id="rent_end_date_field" style="display: none;">
                            <label class="form-label">Rent End Date <small class="text-muted">(Leave empty if ongoing)</small></label>
                            <input type="date" name="rent_end_date" id="rent_end_date" 
                                   class="form-control" value="{{ old('rent_end_date') }}" onchange="calculateDaywise()">
                        </div>
                        
                        <div class="col-md-3 mb-3" id="number_of_days_field" style="display: none;">
                            <label class="form-label">Number of Days <small class="text-muted">(Manual entry)</small></label>
                            <input type="number" name="number_of_days" id="number_of_days" min="1" 
                                   class="form-control" value="{{ old('number_of_days') }}" placeholder="1" oninput="calculateDaywise()">
                        </div>
                        
                        <div class="col-md-3 mb-3" id="rate_per_day_field" style="display: none;">
                            <label class="form-label">Rate per day</label>
                            <input type="number" name="rate_per_day" id="rate_per_day" step="0.01" min="0" 
                                   class="form-control" value="{{ old('rate_per_day') }}" placeholder="0.00" oninput="calculateDaywise()">
                        </div>
                        
                        <div class="col-md-3 mb-3" id="quantity_quintal_field" style="display: none;">
                            <label class="form-label">Quantity (Quintal)</label>
                            <input type="number" name="quantity_quintal" id="quantity_quintal" step="0.01" min="0" 
                                   class="form-control" value="{{ old('quantity_quintal') }}" placeholder="0.00" oninput="calculatePerQuintal()">
                        </div>
                        
                        <div class="col-md-3 mb-3" id="rate_per_quintal_field" style="display: none;">
                            <label class="form-label">Rate per quintal</label>
                            <input type="number" name="rate_per_quintal" id="rate_per_quintal" step="0.01" min="0" 
                                   class="form-control" value="{{ old('rate_per_quintal') }}" placeholder="0.00" oninput="calculatePerQuintal()">
                        </div>
                        
                        <div class="col-md-3 mb-3" id="fixed_rate_field" style="display: {{ old('rate_type', 'fixed') === 'fixed' ? 'block' : 'none' }};">
                            <label class="form-label">Fixed Rate <span class="text-danger">*</span></label>
                            <input type="number" name="fixed_rate" id="fixed_rate" step="0.01" min="0" 
                                   class="form-control" value="{{ old('fixed_rate') }}" placeholder="0.00" oninput="updateTotal()">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Total Amount <span class="text-danger" id="total_amount_required">*</span></label>
                            <input type="number" name="total_amount" id="total_amount" step="0.01" min="0" 
                                   class="form-control @error('total_amount') is-invalid @enderror" 
                                   value="{{ old('rate_type') === 'not_fixed' ? '' : (old('total_amount') ?: '') }}" 
                                   oninput="updateBalance()">
                            @error('total_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Paid Amount</label>
                            <input type="number" name="paid_amount" id="paid_amount" step="0.01" min="0" 
                                   class="form-control" value="{{ old('paid_amount', 0) }}" placeholder="0.00" oninput="updateBalance()">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Balance Amount</label>
                            <input type="text" id="balance_amount" class="form-control" readonly value="0.00">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Bank Account</label>
                            <select name="bank_account_id" class="form-select">
                                <option value="">None</option>
                                @foreach($bankAccounts as $account)
                                    <option value="{{ $account->id }}" {{ old('bank_account_id') == $account->id ? 'selected' : '' }}>
                                        {{ $account->account_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Payment Date</label>
                            <input type="date" name="payment_date" class="form-control" value="{{ old('payment_date') }}">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Notes</label>
                <textarea name="notes" rows="3" class="form-control" placeholder="Additional notes">{{ old('notes') }}</textarea>
            </div>
            
            <div class="d-flex justify-content-end">
                <a href="{{ route('admin.vehicle-rents.index') }}" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Create Vehicle Rent</button>
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
    if (vehicleType === 'excavator' || vehicleType === 'jcv') {
        // Excavator and JCV typically use hourly rates
        if (currentRateType === 'fixed' || currentRateType === 'per_km' || currentRateType === '') {
            rateTypeSelect.value = 'per_hour';
        }
    } else if (vehicleType === 'tipper_6wheel' || vehicleType === 'tipper_10wheel') {
        // Tippers typically use per quintal for material transport
        // But allow user to change if needed
        // Don't auto-change if user has already selected a rate type
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
        // Clear the total amount when switching to not_fixed
        if (totalAmountField.value && parseFloat(totalAmountField.value) > 0) {
            totalAmountField.value = '';
        }
    } else {
        // Make total amount readonly for calculated rates and required
        totalAmountField.setAttribute('readonly', 'readonly');
        totalAmountField.setAttribute('required', 'required');
        totalAmountField.placeholder = '0.00';
        requiredIndicator.style.display = 'inline';
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
        // For not_fixed, ensure field is empty or 0 if no value entered
        const totalField = document.getElementById('total_amount');
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
    // Set initial state based on vehicle type if already selected
    const vehicleType = document.getElementById('vehicle_type').value;
    const rateType = document.getElementById('rate_type').value;
    
    // If vehicle type is excavator/jcv and rate type is not set or is default, set to per_hour
    if ((vehicleType === 'excavator' || vehicleType === 'jcv') && (rateType === 'fixed' || rateType === '')) {
        document.getElementById('rate_type').value = 'per_hour';
    }
    
    // Initialize field visibility
    toggleRateFields();
    updateTotalAmountField();
    updateBalance();
});
</script>
@endsection

