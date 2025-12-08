@extends('admin.layout')

@section('title', 'Create Purchase Invoice')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Create Purchase Invoice</h1>
        <p class="text-muted mb-0">Record a purchase from vendor</p>
    </div>
    <a href="{{ route('admin.purchase-invoices.index') }}" class="btn btn-outline-secondary">Back to List</a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.purchase-invoices.store') }}" method="POST" id="purchaseInvoiceForm">
            @csrf
            
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Invoice Date <span class="text-danger">*</span></label>
                    <input type="date" name="invoice_date" class="form-control @error('invoice_date') is-invalid @enderror" 
                           value="{{ old('invoice_date', date('Y-m-d')) }}" required>
                    @error('invoice_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3 mb-3">
                    <label class="form-label">Due Date</label>
                    <input type="date" name="due_date" class="form-control" value="{{ old('due_date') }}">
                </div>
                
                <div class="col-md-3 mb-3">
                    <label class="form-label">Vendor</label>
                    <select name="vendor_id" class="form-select">
                        <option value="">Select Vendor</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}" {{ old('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                {{ $vendor->name }}
                            </option>
                        @endforeach
                    </select>
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
            </div>
            
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Reference Number</label>
                    <input type="text" name="reference_number" class="form-control" value="{{ old('reference_number') }}" 
                           placeholder="Vendor's invoice number">
                </div>
                
                <div class="col-md-4 mb-3">
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
            </div>
            
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Invoice Items</h5>
                    <button type="button" class="btn btn-sm btn-primary" onclick="addItemRow()">
                        <i class="bi bi-plus-circle me-1"></i> Add Item
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="itemsTable">
                            <thead>
                                <tr>
                                    <th style="width: 25%;">Item Name <span class="text-danger">*</span></th>
                                    <th style="width: 15%;">Quantity <span class="text-danger">*</span></th>
                                    <th style="width: 10%;">Unit</th>
                                    <th style="width: 15%;">Unit Price <span class="text-danger">*</span></th>
                                    <th style="width: 10%;">Tax %</th>
                                    <th style="width: 10%;">Discount</th>
                                    <th style="width: 10%;">Total</th>
                                    <th style="width: 5%;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                <tr class="item-row">
                                    <td>
                                        <input type="text" name="items[0][item_name]" class="form-control" required placeholder="Item name">
                                        <textarea name="items[0][description]" rows="2" class="form-control mt-1" 
                                                  placeholder="Description (optional)" style="font-size: 0.875rem;"></textarea>
                                    </td>
                                    <td>
                                        <input type="number" name="items[0][quantity]" step="0.01" min="0.01" 
                                               class="form-control quantity-input" value="1" required>
                                    </td>
                                    <td>
                                        <input type="text" name="items[0][unit]" class="form-control" placeholder="e.g., kg, pcs">
                                    </td>
                                    <td>
                                        <input type="number" name="items[0][unit_price]" step="0.01" min="0" 
                                               class="form-control unit-price-input" required placeholder="0.00">
                                    </td>
                                    <td>
                                        <input type="number" name="items[0][tax_rate]" step="0.01" min="0" max="100" 
                                               class="form-control tax-rate-input" value="13" placeholder="13">
                                    </td>
                                    <td>
                                        <input type="number" name="items[0][discount_amount]" step="0.01" min="0" 
                                               class="form-control discount-input" value="0" placeholder="0.00">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control line-total" readonly value="0.00">
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="removeItemRow(this)" disabled>
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5" class="text-end"><strong>Subtotal:</strong></td>
                                    <td colspan="2"><strong id="subtotal">0.00</strong></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="text-end"><strong>Tax Amount:</strong></td>
                                    <td colspan="2"><strong id="taxAmount">0.00</strong></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="text-end"><strong>Total Amount:</strong></td>
                                    <td colspan="2"><strong id="totalAmount" class="text-primary fs-5">0.00</strong></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" rows="3" class="form-control" placeholder="Additional notes">{{ old('notes') }}</textarea>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Terms & Conditions</label>
                    <textarea name="terms" rows="3" class="form-control" placeholder="Payment terms">{{ old('terms') }}</textarea>
                </div>
            </div>
            
            <div class="d-flex justify-content-end">
                <a href="{{ route('admin.purchase-invoices.index') }}" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Create Purchase Invoice</button>
            </div>
        </form>
    </div>
</div>

<script>
let itemRowCount = 1;

function addItemRow() {
    const tbody = document.getElementById('itemsBody');
    const row = document.createElement('tr');
    row.className = 'item-row';
    
    row.innerHTML = `
        <td>
            <input type="text" name="items[${itemRowCount}][item_name]" class="form-control" required placeholder="Item name">
            <textarea name="items[${itemRowCount}][description]" rows="2" class="form-control mt-1" 
                      placeholder="Description (optional)" style="font-size: 0.875rem;"></textarea>
        </td>
        <td>
            <input type="number" name="items[${itemRowCount}][quantity]" step="0.01" min="0.01" 
                   class="form-control quantity-input" value="1" required>
        </td>
        <td>
            <input type="text" name="items[${itemRowCount}][unit]" class="form-control" placeholder="e.g., kg, pcs">
        </td>
        <td>
            <input type="number" name="items[${itemRowCount}][unit_price]" step="0.01" min="0" 
                   class="form-control unit-price-input" required placeholder="0.00">
        </td>
        <td>
            <input type="number" name="items[${itemRowCount}][tax_rate]" step="0.01" min="0" max="100" 
                   class="form-control tax-rate-input" value="13" placeholder="13">
        </td>
        <td>
            <input type="number" name="items[${itemRowCount}][discount_amount]" step="0.01" min="0" 
                   class="form-control discount-input" value="0" placeholder="0.00">
        </td>
        <td>
            <input type="text" class="form-control line-total" readonly value="0.00">
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-danger" onclick="removeItemRow(this)">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    `;
    
    tbody.appendChild(row);
    itemRowCount++;
    attachEventListeners(row);
    calculateTotals();
    updateRemoveButtons();
}

function removeItemRow(btn) {
    const row = btn.closest('tr');
    row.remove();
    reindexRows();
    calculateTotals();
    updateRemoveButtons();
}

function reindexRows() {
    const rows = document.querySelectorAll('#itemsBody tr');
    rows.forEach((row, index) => {
        row.querySelectorAll('input, textarea').forEach(input => {
            if (input.name) {
                input.name = input.name.replace(/\[\d+\]/, `[${index}]`);
            }
        });
    });
    itemRowCount = rows.length;
}

function calculateLineTotal(row) {
    const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
    const unitPrice = parseFloat(row.querySelector('.unit-price-input').value) || 0;
    const taxRate = parseFloat(row.querySelector('.tax-rate-input').value) || 0;
    const discount = parseFloat(row.querySelector('.discount-input').value) || 0;
    
    const subtotal = (quantity * unitPrice) - discount;
    const taxAmount = subtotal * (taxRate / 100);
    const lineTotal = subtotal + taxAmount;
    
    row.querySelector('.line-total').value = lineTotal.toFixed(2);
    return { subtotal, taxAmount, lineTotal };
}

function calculateTotals() {
    let subtotal = 0;
    let totalTax = 0;
    let totalAmount = 0;
    
    document.querySelectorAll('.item-row').forEach(row => {
        const line = calculateLineTotal(row);
        subtotal += line.subtotal;
        totalTax += line.taxAmount;
        totalAmount += line.lineTotal;
    });
    
    document.getElementById('subtotal').textContent = subtotal.toFixed(2);
    document.getElementById('taxAmount').textContent = totalTax.toFixed(2);
    document.getElementById('totalAmount').textContent = totalAmount.toFixed(2);
}

function attachEventListeners(row) {
    row.querySelectorAll('.quantity-input, .unit-price-input, .tax-rate-input, .discount-input').forEach(input => {
        input.addEventListener('input', () => {
            calculateLineTotal(row);
            calculateTotals();
        });
    });
}

function updateRemoveButtons() {
    const rows = document.querySelectorAll('#itemsBody tr');
    rows.forEach((row, index) => {
        const removeBtn = row.querySelector('.btn-danger');
        if (rows.length <= 1) {
            removeBtn.disabled = true;
        } else {
            removeBtn.disabled = false;
        }
    });
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.item-row').forEach(row => {
        attachEventListeners(row);
    });
    calculateTotals();
    updateRemoveButtons();
});

// Add event listeners for dynamically added rows
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('quantity-input') || 
        e.target.classList.contains('unit-price-input') || 
        e.target.classList.contains('tax-rate-input') || 
        e.target.classList.contains('discount-input')) {
        const row = e.target.closest('.item-row');
        if (row) {
            calculateLineTotal(row);
            calculateTotals();
        }
    }
});
</script>
@endsection

