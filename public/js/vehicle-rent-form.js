/**
 * Shared vehicle rent form builder and helpers.
 * Used by vehicle_rents/index and construction_materials (modal).
 * Set window.VR_FORM_CONTAINER, window.VR_ON_CLOSE, window.VR_ON_SUBMIT_SUCCESS when embedding in construction materials.
 */
function buildVehicleRentForm(data, rent) {
    const isEdit = !!(rent && rent.id);
    const containerId = (typeof window !== 'undefined' && window.VR_FORM_CONTAINER) || 'vehicleRentFormContainer';
    const container = document.getElementById(containerId);
    if (!container) return;

    var today = new Date().toISOString().split('T')[0];
    var rentDate = (rent && rent.rent_date) ? rent.rent_date : today;

    var vehicleTypeOptions = Object.entries(data.vehicleTypes || {}).map(function(_a) {
        var key = _a[0], label = _a[1];
        return '<option value="' + key + '" ' + (rent && rent.vehicle_type === key ? 'selected' : '') + '>' + label + '</option>';
    }).join('');

    var projectOptions = (data.projects || []).map(function(p) {
        return '<option value="' + p.id + '" ' + (rent && rent.project_id == p.id ? 'selected' : '') + '>' + p.name + '</option>';
    }).join('');

    var supplierOptions = (data.suppliers || []).map(function(s) {
        return '<option value="' + s.id + '" ' + (rent && rent.supplier_id == s.id ? 'selected' : '') + '>' + s.name + '</option>';
    }).join('');

    var bankAccountOptions = (data.bankAccounts || []).map(function(ba) {
        return '<option value="' + ba.id + '" ' + (rent && rent.bank_account_id == ba.id ? 'selected' : '') + '>' + ba.account_name + ' - ' + ba.bank_name + '</option>';
    }).join('');

    var cancelOnClick = '(function(){ if (window.VR_ON_CLOSE) window.VR_ON_CLOSE(); else if (typeof closeVehicleRentModal === \'function\') closeVehicleRentModal(); })()';

    container.innerHTML = '<form id="vehicleRentForm" onsubmit="submitVehicleRentForm(event)">' +
        '<div class="row mb-4">' +
        '<div class="col-md-3 mb-3"><label class="form-label">Rent Date <span class="text-danger">*</span></label>' +
        '<input type="date" name="rent_date" id="rent_date" class="form-control" value="' + rentDate + '" required>' +
        '<div class="field-error text-danger small mt-1" data-field="rent_date" style="display: none;"></div></div>' +
        '<div class="col-md-3 mb-3"><label class="form-label">Project</label>' +
        '<select name="project_id" id="project_id" class="form-select"><option value="">None</option>' + projectOptions + '</select></div>' +
        '<div class="col-md-3 mb-3"><label class="form-label">Supplier</label>' +
        '<select name="supplier_id" id="supplier_id" class="form-select"><option value="">None</option>' + supplierOptions + '</select></div>' +
        '<div class="col-md-3 mb-3"><label class="form-label">Vehicle Type <span class="text-danger">*</span></label>' +
        '<select name="vehicle_type" id="vehicle_type" class="form-select" onchange="handleVehicleTypeChange()" required><option value="">Select Vehicle Type</option>' + vehicleTypeOptions + '</select>' +
        '<div class="field-error text-danger small mt-1" data-field="vehicle_type" style="display: none;"></div></div>' +
        '<div class="col-md-3 mb-3"><label class="form-label">Vehicle Number</label>' +
        '<input type="text" name="vehicle_number" id="vehicle_number" class="form-control" value="' + (rent ? (rent.vehicle_number || '') : '') + '" placeholder="Registration number"></div>' +
        '<div class="col-md-3 mb-3"><label class="form-label">Driver Name</label>' +
        '<input type="text" name="driver_name" id="driver_name" class="form-control" value="' + (rent ? (rent.driver_name || '') : '') + '" placeholder="Driver name"></div>' +
        '<div class="col-md-3 mb-3"><label class="form-label">Driver Contact</label>' +
        '<input type="text" name="driver_contact" id="driver_contact" class="form-control" value="' + (rent ? (rent.driver_contact || '') : '') + '" placeholder="Phone number"></div>' +
        '<div class="col-md-3 mb-3"><label class="form-label">Purpose</label>' +
        '<input type="text" name="purpose" id="purpose" class="form-control" value="' + (rent ? (rent.purpose || '') : '') + '" placeholder="Purpose of trip"></div>' +
        '<div class="col-md-6 mb-3"><label class="form-label">Start Location <span class="text-danger">*</span></label>' +
        '<input type="text" name="start_location" id="start_location" class="form-control" value="' + (rent ? (rent.start_location || '') : '') + '" placeholder="e.g., Kathmandu" required>' +
        '<div class="field-error text-danger small mt-1" data-field="start_location" style="display: none;"></div></div>' +
        '<div class="col-md-6 mb-3"><label class="form-label">Destination Location <span class="text-danger">*</span></label>' +
        '<input type="text" name="destination_location" id="destination_location" class="form-control" value="' + (rent ? (rent.destination_location || '') : '') + '" placeholder="e.g., Pokhara" required>' +
        '<div class="field-error text-danger small mt-1" data-field="destination_location" style="display: none;"></div></div>' +
        '</div>' +
        '<div class="card mb-4"><div class="card-header"><h5 class="mb-0">Rate & Payment Information</h5></div><div class="card-body">' +
        '<div class="row mb-3">' +
        '<div class="col-md-3 mb-3"><label class="form-label">Rate Type <span class="text-danger">*</span></label>' +
        '<select name="rate_type" id="rate_type" class="form-select" onchange="toggleRateFields()" required>' +
        '<option value="fixed"' + (rent && rent.rate_type === 'fixed' ? ' selected' : (!rent ? ' selected' : '')) + '>Fixed Rate</option>' +
        '<option value="per_km"' + (rent && rent.rate_type === 'per_km' ? ' selected' : '') + '>Per Kilometer</option>' +
        '<option value="per_hour"' + (rent && rent.rate_type === 'per_hour' ? ' selected' : '') + '>Per Hour</option>' +
        '<option value="daywise"' + (rent && rent.rate_type === 'daywise' ? ' selected' : '') + '>Daywise</option>' +
        '<option value="per_quintal"' + (rent && rent.rate_type === 'per_quintal' ? ' selected' : '') + '>Per Quintal</option>' +
        '<option value="not_fixed"' + (rent && rent.rate_type === 'not_fixed' ? ' selected' : '') + '>Not Fixed</option>' +
        '</select></div>' +
        '<div class="col-md-3 mb-3" id="distance_field" style="display: none;"><label class="form-label">Distance (km)</label>' +
        '<input type="number" name="distance_km" id="distance_km" step="0.01" min="0" class="form-control" value="' + (rent ? (rent.distance_km || '') : '') + '" placeholder="0.00" oninput="calculatePerKm()"></div>' +
        '<div class="col-md-3 mb-3" id="rate_per_km_field" style="display: none;"><label class="form-label">Rate per km</label>' +
        '<input type="number" name="rate_per_km" id="rate_per_km" step="0.01" min="0" class="form-control" value="' + (rent ? (rent.rate_per_km || '') : '') + '" placeholder="0.00" oninput="calculatePerKm()"></div>' +
        '<div class="col-md-3 mb-3" id="hours_field" style="display: none;"><label class="form-label">Hours</label>' +
        '<input type="number" name="hours" id="hours" min="0" max="23" class="form-control" value="' + (rent ? (rent.hours || '') : '') + '" placeholder="0" oninput="calculatePerHour()"></div>' +
        '<div class="col-md-3 mb-3" id="minutes_field" style="display: none;"><label class="form-label">Minutes</label>' +
        '<input type="number" name="minutes" id="minutes" min="0" max="59" class="form-control" value="' + (rent ? (rent.minutes || '') : '') + '" placeholder="0" oninput="calculatePerHour()"></div>' +
        '<div class="col-md-3 mb-3" id="rate_per_hour_field" style="display: none;"><label class="form-label">Rate per hour</label>' +
        '<input type="number" name="rate_per_hour" id="rate_per_hour" step="0.01" min="0" class="form-control" value="' + (rent ? (rent.rate_per_hour || '') : '') + '" placeholder="0.00" oninput="calculatePerHour()"></div>' +
        '<div class="col-md-3 mb-3" id="rent_start_date_field" style="display: none;"><label class="form-label">Rent Start Date <span class="text-danger">*</span></label>' +
        '<input type="date" name="rent_start_date" id="rent_start_date" class="form-control" value="' + (rent ? (rent.rent_start_date || '') : '') + '" onchange="calculateDaywise()"></div>' +
        '<div class="col-md-3 mb-3" id="rent_end_date_field" style="display: none;"><label class="form-label">Rent End Date <small class="text-muted">(Leave empty if ongoing)</small></label>' +
        '<input type="date" name="rent_end_date" id="rent_end_date" class="form-control" value="' + (rent ? (rent.rent_end_date || '') : '') + '" onchange="calculateDaywise()"></div>' +
        '<div class="col-md-3 mb-3" id="number_of_days_field" style="display: none;"><label class="form-label">Number of Days <small class="text-muted">(Manual entry)</small></label>' +
        '<input type="number" name="number_of_days" id="number_of_days" min="1" class="form-control" value="' + (rent ? (rent.number_of_days || '') : '') + '" placeholder="1" oninput="calculateDaywise()"></div>' +
        '<div class="col-md-3 mb-3" id="rate_per_day_field" style="display: none;"><label class="form-label">Rate per day</label>' +
        '<input type="number" name="rate_per_day" id="rate_per_day" step="0.01" min="0" class="form-control" value="' + (rent ? (rent.rate_per_day || '') : '') + '" placeholder="0.00" oninput="calculateDaywise()"></div>' +
        '<div class="col-md-3 mb-3" id="quantity_quintal_field" style="display: none;"><label class="form-label">Quantity (Quintal)</label>' +
        '<input type="number" name="quantity_quintal" id="quantity_quintal" step="0.01" min="0" class="form-control" value="' + (rent ? (rent.quantity_quintal || '') : '') + '" placeholder="0.00" oninput="calculatePerQuintal()"></div>' +
        '<div class="col-md-3 mb-3" id="rate_per_quintal_field" style="display: none;"><label class="form-label">Rate per quintal</label>' +
        '<input type="number" name="rate_per_quintal" id="rate_per_quintal" step="0.01" min="0" class="form-control" value="' + (rent ? (rent.rate_per_quintal || '') : '') + '" placeholder="0.00" oninput="calculatePerQuintal()"></div>' +
        '<div class="col-md-3 mb-3" id="fixed_rate_field" style="display: ' + (rent && rent.rate_type === 'fixed' ? 'block' : (!rent ? 'block' : 'none')) + ';"><label class="form-label">Fixed Rate <span class="text-danger">*</span></label>' +
        '<input type="number" name="fixed_rate" id="fixed_rate" step="0.01" min="0" class="form-control" value="' + (rent ? (rent.fixed_rate || '') : '') + '" placeholder="0.00" oninput="updateTotal()"></div>' +
        '</div>' +
        '<div class="row">' +
        '<div class="col-md-4 mb-3"><label class="form-label">Total Amount <span class="text-danger" id="total_amount_required">*</span></label>' +
        '<input type="number" name="total_amount" id="total_amount" step="0.01" min="0" class="form-control" value="' + (rent ? (rent.total_amount || '') : '') + '" oninput="updateBalance()">' +
        '<div class="field-error text-danger small mt-1" data-field="total_amount" style="display: none;"></div></div>' +
        '<div class="col-md-4 mb-3"><label class="form-label">Paid Amount</label>' +
        '<input type="number" name="paid_amount" id="paid_amount" step="0.01" min="0" class="form-control" value="' + (rent ? (rent.paid_amount || 0) : 0) + '" placeholder="0.00" oninput="updateBalance()"></div>' +
        '<div class="col-md-4 mb-3"><label class="form-label">Balance Amount</label>' +
        '<input type="text" id="balance_amount" class="form-control" readonly value="' + (rent ? (rent.total_amount - rent.paid_amount).toFixed(2) : '0.00') + '"></div>' +
        '</div>' +
        '<div class="row">' +
        '<div class="col-md-6 mb-3"><label class="form-label">Bank Account</label>' +
        '<select name="bank_account_id" id="bank_account_id" class="form-select"><option value="">None</option>' + bankAccountOptions + '</select></div>' +
        '<div class="col-md-6 mb-3"><label class="form-label">Payment Date</label>' +
        '<input type="date" name="payment_date" id="payment_date" class="form-control" value="' + (rent ? (rent.payment_date || '') : '') + '"></div>' +
        '</div></div></div>' +
        '<div class="mb-3"><label class="form-label">Notes</label>' +
        '<textarea name="notes" id="notes" rows="3" class="form-control" placeholder="Additional notes">' + (rent ? (rent.notes || '') : '') + '</textarea></div>' +
        '<div class="d-flex justify-content-end">' +
        '<button type="button" onclick="' + cancelOnClick + '" class="btn btn-secondary me-2">Cancel</button>' +
        '<button type="submit" class="btn btn-primary" id="submitVehicleRentBtn">' + (isEdit ? 'Update' : 'Create') + ' Vehicle Rent</button>' +
        '</div></form>';

    setTimeout(function() {
        if (rent && rent.id) {
            toggleRateFields();
            updateTotal();
        } else {
            toggleRateFields();
            updateTotalAmountField();
            updateBalance();
        }
    }, 100);
}

function handleVehicleTypeChange() {
    var vehicleType = document.getElementById('vehicle_type') && document.getElementById('vehicle_type').value;
    var rateTypeSelect = document.getElementById('rate_type');
    var currentRateType = rateTypeSelect ? rateTypeSelect.value : '';
    if (vehicleType === 'excavator' || vehicleType === 'jcv') {
        if (currentRateType === 'fixed' || currentRateType === '' || !(typeof currentRentId !== 'undefined' && currentRentId)) {
            if (rateTypeSelect) rateTypeSelect.value = 'per_hour';
        }
    }
    toggleRateFields();
}

function toggleRateFields() {
    var rateType = document.getElementById('rate_type') && document.getElementById('rate_type').value;
    if (!rateType) return;
    var fields = ['distance_field', 'rate_per_km_field', 'hours_field', 'minutes_field', 'rate_per_hour_field',
        'rent_start_date_field', 'rent_end_date_field', 'number_of_days_field', 'rate_per_day_field',
        'quantity_quintal_field', 'rate_per_quintal_field', 'fixed_rate_field'];
    fields.forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.style.display = 'none';
    });
    if (rateType === 'per_km') {
        var d = document.getElementById('distance_field'); if (d) d.style.display = 'block';
        var r = document.getElementById('rate_per_km_field'); if (r) r.style.display = 'block';
    } else if (rateType === 'per_hour') {
        var h = document.getElementById('hours_field'); if (h) h.style.display = 'block';
        var m = document.getElementById('minutes_field'); if (m) m.style.display = 'block';
        var rph = document.getElementById('rate_per_hour_field'); if (rph) rph.style.display = 'block';
    } else if (rateType === 'daywise') {
        ['rent_start_date_field', 'rent_end_date_field', 'number_of_days_field', 'rate_per_day_field'].forEach(function(id) {
            var el = document.getElementById(id); if (el) el.style.display = 'block';
        });
    } else if (rateType === 'per_quintal') {
        var q = document.getElementById('quantity_quintal_field'); if (q) q.style.display = 'block';
        var rpq = document.getElementById('rate_per_quintal_field'); if (rpq) rpq.style.display = 'block';
    } else if (rateType === 'fixed') {
        var f = document.getElementById('fixed_rate_field'); if (f) f.style.display = 'block';
    }
    updateTotalAmountField();
    updateTotal();
}

function updateTotalAmountField() {
    var rateType = document.getElementById('rate_type') && document.getElementById('rate_type').value;
    var totalAmountField = document.getElementById('total_amount');
    var requiredIndicator = document.getElementById('total_amount_required');
    if (!rateType || !totalAmountField) return;
    if (rateType === 'not_fixed') {
        totalAmountField.removeAttribute('readonly');
        totalAmountField.removeAttribute('required');
        totalAmountField.placeholder = 'Enter total amount (optional)';
        if (requiredIndicator) requiredIndicator.style.display = 'none';
    } else {
        totalAmountField.setAttribute('readonly', 'readonly');
        totalAmountField.setAttribute('required', 'required');
        totalAmountField.placeholder = '0.00';
        if (requiredIndicator) requiredIndicator.style.display = 'inline';
    }
}

function calculatePerKm() {
    var rateType = document.getElementById('rate_type') && document.getElementById('rate_type').value;
    if (rateType === 'per_km') {
        var distance = parseFloat(document.getElementById('distance_km') && document.getElementById('distance_km').value || 0);
        var ratePerKm = parseFloat(document.getElementById('rate_per_km') && document.getElementById('rate_per_km').value || 0);
        var total = distance * ratePerKm;
        var totalField = document.getElementById('total_amount');
        if (totalField) totalField.value = total.toFixed(2);
        updateBalance();
    }
}

function calculatePerHour() {
    var rateType = document.getElementById('rate_type') && document.getElementById('rate_type').value;
    if (rateType === 'per_hour') {
        var hours = parseInt(document.getElementById('hours') && document.getElementById('hours').value || 0, 10);
        var minutes = parseInt(document.getElementById('minutes') && document.getElementById('minutes').value || 0, 10);
        var ratePerHour = parseFloat(document.getElementById('rate_per_hour') && document.getElementById('rate_per_hour').value || 0);
        var totalHours = hours + (minutes / 60);
        var total = totalHours * ratePerHour;
        var totalField = document.getElementById('total_amount');
        if (totalField) totalField.value = total.toFixed(2);
        updateBalance();
    }
}

function calculateDaywise() {
    var rateType = document.getElementById('rate_type') && document.getElementById('rate_type').value;
    if (rateType === 'daywise') {
        var numberOfDays = 0;
        var ratePerDay = parseFloat(document.getElementById('rate_per_day') && document.getElementById('rate_per_day').value || 0);
        var startDate = document.getElementById('rent_start_date') && document.getElementById('rent_start_date').value;
        var endDate = document.getElementById('rent_end_date') && document.getElementById('rent_end_date').value;
        if (startDate) {
            var start = new Date(startDate);
            var end = endDate ? new Date(endDate) : new Date();
            var diffTime = Math.abs(end - start);
            numberOfDays = Math.max(1, Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1);
        } else {
            numberOfDays = parseInt(document.getElementById('number_of_days') && document.getElementById('number_of_days').value || 0, 10);
        }
        var total = numberOfDays * ratePerDay;
        var totalField = document.getElementById('total_amount');
        if (totalField) totalField.value = total.toFixed(2);
        if (startDate && document.getElementById('number_of_days')) {
            document.getElementById('number_of_days').value = numberOfDays;
        }
        updateBalance();
    }
}

function calculatePerQuintal() {
    var rateType = document.getElementById('rate_type') && document.getElementById('rate_type').value;
    if (rateType === 'per_quintal') {
        var quantityQuintal = parseFloat(document.getElementById('quantity_quintal') && document.getElementById('quantity_quintal').value || 0);
        var ratePerQuintal = parseFloat(document.getElementById('rate_per_quintal') && document.getElementById('rate_per_quintal').value || 0);
        var total = quantityQuintal * ratePerQuintal;
        var totalField = document.getElementById('total_amount');
        if (totalField) totalField.value = total.toFixed(2);
        updateBalance();
    }
}

function updateTotal() {
    var rateType = document.getElementById('rate_type') && document.getElementById('rate_type').value;
    if (!rateType) return;
    var total = 0;
    if (rateType === 'per_km') {
        var distance = parseFloat(document.getElementById('distance_km') && document.getElementById('distance_km').value || 0);
        var ratePerKm = parseFloat(document.getElementById('rate_per_km') && document.getElementById('rate_per_km').value || 0);
        total = distance * ratePerKm;
    } else if (rateType === 'per_hour') {
        var hours = parseInt(document.getElementById('hours') && document.getElementById('hours').value || 0, 10);
        var minutes = parseInt(document.getElementById('minutes') && document.getElementById('minutes').value || 0, 10);
        var ratePerHour = parseFloat(document.getElementById('rate_per_hour') && document.getElementById('rate_per_hour').value || 0);
        total = (hours + (minutes / 60)) * ratePerHour;
    } else if (rateType === 'daywise') {
        var numberOfDays = 0;
        var ratePerDay = parseFloat(document.getElementById('rate_per_day') && document.getElementById('rate_per_day').value || 0);
        var startDate = document.getElementById('rent_start_date') && document.getElementById('rent_start_date').value;
        var endDate = document.getElementById('rent_end_date') && document.getElementById('rent_end_date').value;
        if (startDate) {
            var start = new Date(startDate);
            var end = endDate ? new Date(endDate) : new Date();
            numberOfDays = Math.max(1, Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1);
        } else {
            numberOfDays = parseInt(document.getElementById('number_of_days') && document.getElementById('number_of_days').value || 0, 10);
        }
        total = numberOfDays * ratePerDay;
    } else if (rateType === 'per_quintal') {
        var qty = parseFloat(document.getElementById('quantity_quintal') && document.getElementById('quantity_quintal').value || 0);
        var rpq = parseFloat(document.getElementById('rate_per_quintal') && document.getElementById('rate_per_quintal').value || 0);
        total = qty * rpq;
    } else if (rateType === 'not_fixed') {
        total = parseFloat(document.getElementById('total_amount') && document.getElementById('total_amount').value || 0);
    } else if (rateType === 'fixed') {
        total = parseFloat(document.getElementById('fixed_rate') && document.getElementById('fixed_rate').value || 0);
    }
    var totalField = document.getElementById('total_amount');
    if (rateType !== 'not_fixed' && totalField) totalField.value = total.toFixed(2);
    updateBalance();
}

function updateBalance() {
    var total = parseFloat(document.getElementById('total_amount') && document.getElementById('total_amount').value || 0);
    var paid = parseFloat(document.getElementById('paid_amount') && document.getElementById('paid_amount').value || 0);
    var balance = total - paid;
    var balanceField = document.getElementById('balance_amount');
    if (balanceField) balanceField.value = balance.toFixed(2);
}

function submitVehicleRentForm(e) {
    e.preventDefault();
    var form = document.getElementById('vehicleRentForm');
    if (!form) return;
    var formData = new FormData(form);
    var submitBtn = document.getElementById('submitVehicleRentBtn');
    var originalText = submitBtn ? submitBtn.textContent : 'Save';
    if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Processing...'; }
    var rentId = (typeof currentRentId !== 'undefined') ? currentRentId : null;
    var url = rentId ? '/admin/vehicle-rents/' + rentId : '/admin/vehicle-rents';
    if (rentId) formData.append('_method', 'PUT');
    var csrf = (typeof csrfToken !== 'undefined') ? csrfToken : (document.querySelector('meta[name="csrf-token"]') && document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrf || '',
            'Accept': 'application/json'
        }
    })
    .then(function(response) { return response.json().then(function(data) { return { status: response.status, data: data }; }); })
    .then(function(_a) {
        var status = _a.status, data = _a.data;
        if (data.success) {
            if (window.VR_ON_SUBMIT_SUCCESS) {
                window.VR_ON_SUBMIT_SUCCESS(data);
            } else {
                if (typeof closeVehicleRentModal === 'function') closeVehicleRentModal();
                if (rentId && typeof updateRentRow === 'function') updateRentRow(data.rent);
                else if (typeof addRentRow === 'function') addRentRow(data.rent);
                if (typeof showNotification === 'function') showNotification(data.message || 'Saved.', 'success');
            }
        } else {
            if (data.errors) {
                Object.keys(data.errors).forEach(function(field) {
                    var errorEl = document.querySelector('.field-error[data-field="' + field + '"]');
                    if (errorEl) {
                        errorEl.textContent = data.errors[field][0];
                        errorEl.style.display = 'block';
                    }
                });
            }
            if (typeof showNotification === 'function') showNotification(data.message || 'Validation failed', 'error');
            if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = originalText; }
        }
    })
    .catch(function(err) {
        console.error(err);
        if (typeof showNotification === 'function') showNotification('An error occurred', 'error');
        if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = originalText; }
    });
}
