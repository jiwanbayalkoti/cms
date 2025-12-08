@extends('admin.layout')

@section('title', 'Edit Journal Entry')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Edit Journal Entry</h1>
        <p class="text-muted mb-0">{{ $journalEntry->entry_number }}</p>
    </div>
    <a href="{{ route('admin.journal-entries.index') }}" class="btn btn-outline-secondary">Back to List</a>
</div>

@if($journalEntry->is_posted)
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-2"></i>This journal entry is posted and cannot be edited.
    </div>
@endif

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.journal-entries.update', $journalEntry) }}" method="POST" id="journalEntryForm">
            @csrf
            @method('PUT')
            
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Entry Date <span class="text-danger">*</span></label>
                    <input type="date" name="entry_date" class="form-control @error('entry_date') is-invalid @enderror" 
                           value="{{ old('entry_date', $journalEntry->entry_date->format('Y-m-d')) }}" required>
                    @error('entry_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3 mb-3">
                    <label class="form-label">Entry Type <span class="text-danger">*</span></label>
                    <select name="entry_type" class="form-select @error('entry_type') is-invalid @enderror" required>
                        <option value="manual" {{ old('entry_type', $journalEntry->entry_type) === 'manual' ? 'selected' : '' }}>Manual</option>
                        <option value="adjustment" {{ old('entry_type', $journalEntry->entry_type) === 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                        <option value="closing" {{ old('entry_type', $journalEntry->entry_type) === 'closing' ? 'selected' : '' }}>Closing</option>
                    </select>
                    @error('entry_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3 mb-3">
                    <label class="form-label">Project</label>
                    <select name="project_id" class="form-select">
                        <option value="">None</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ old('project_id', $journalEntry->project_id) == $project->id ? 'selected' : '' }}>
                                {{ $project->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label class="form-label">Reference</label>
                    <input type="text" name="reference" class="form-control" value="{{ old('reference', $journalEntry->reference) }}" 
                           placeholder="Reference number">
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" rows="2" class="form-control" 
                          placeholder="Journal entry description">{{ old('description', $journalEntry->description) }}</textarea>
            </div>
            
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Journal Entry Items</h5>
                    <button type="button" class="btn btn-sm btn-primary" onclick="addRow()">
                        <i class="bi bi-plus-circle me-1"></i> Add Row
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="itemsTable">
                            <thead>
                                <tr>
                                    <th style="width: 30%;">Account <span class="text-danger">*</span></th>
                                    <th style="width: 15%;">Type <span class="text-danger">*</span></th>
                                    <th style="width: 20%;">Amount <span class="text-danger">*</span></th>
                                    <th style="width: 30%;">Description</th>
                                    <th style="width: 5%;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                @foreach($journalEntry->items as $index => $item)
                                    <tr class="item-row">
                                        <td>
                                            <select name="items[{{ $index }}][account_id]" class="form-select account-select" required>
                                                <option value="">Select Account</option>
                                                @foreach($accounts as $account)
                                                    <option value="{{ $account->id }}" {{ old("items.{$index}.account_id", $item->account_id) == $account->id ? 'selected' : '' }}>
                                                        {{ $account->account_code }} - {{ $account->account_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="items[{{ $index }}][entry_type]" class="form-select entry-type" required>
                                                <option value="debit" {{ old("items.{$index}.entry_type", $item->entry_type) === 'debit' ? 'selected' : '' }}>Debit</option>
                                                <option value="credit" {{ old("items.{$index}.entry_type", $item->entry_type) === 'credit' ? 'selected' : '' }}>Credit</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="items[{{ $index }}][amount]" step="0.01" min="0.01" 
                                                   class="form-control amount-input" value="{{ old("items.{$index}.amount", $item->amount) }}" required placeholder="0.00">
                                        </td>
                                        <td>
                                            <input type="text" name="items[{{ $index }}][description]" class="form-control" 
                                                   value="{{ old("items.{$index}.description", $item->description) }}" placeholder="Narration">
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2" class="text-end"><strong>Total:</strong></td>
                                    <td>
                                        <div class="d-flex justify-content-between">
                                            <span>Debit: <strong id="totalDebit">0.00</strong></span>
                                            <span>Credit: <strong id="totalCredit">0.00</strong></span>
                                        </div>
                                        <div id="balanceStatus" class="mt-2"></div>
                                    </td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-end">
                <a href="{{ route('admin.journal-entries.index') }}" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary" id="submitBtn" {{ $journalEntry->is_posted ? 'disabled' : '' }}>Update Journal Entry</button>
            </div>
        </form>
    </div>
</div>

<script>
let rowCount = {{ $journalEntry->items->count() }};

function addRow() {
    const tbody = document.getElementById('itemsBody');
    const row = document.createElement('tr');
    row.className = 'item-row';
    
    row.innerHTML = `
        <td>
            <select name="items[${rowCount}][account_id]" class="form-select account-select" required>
                <option value="">Select Account</option>
                @foreach($accounts as $account)
                    <option value="{{ $account->id }}">{{ $account->account_code }} - {{ $account->account_name }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <select name="items[${rowCount}][entry_type]" class="form-select entry-type" required>
                <option value="debit">Debit</option>
                <option value="credit">Credit</option>
            </select>
        </td>
        <td>
            <input type="number" name="items[${rowCount}][amount]" step="0.01" min="0.01" 
                   class="form-control amount-input" required placeholder="0.00">
        </td>
        <td>
            <input type="text" name="items[${rowCount}][description]" class="form-control" placeholder="Narration">
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    `;
    
    tbody.appendChild(row);
    rowCount++;
    updateTotals();
    updateRemoveButtons();
}

function removeRow(btn) {
    const row = btn.closest('tr');
    row.remove();
    updateTotals();
    updateRemoveButtons();
    reindexRows();
}

function reindexRows() {
    const rows = document.querySelectorAll('#itemsBody tr');
    rows.forEach((row, index) => {
        row.querySelectorAll('input, select').forEach(input => {
            if (input.name) {
                input.name = input.name.replace(/\[\d+\]/, `[${index}]`);
            }
        });
    });
    rowCount = rows.length;
}

function updateTotals() {
    let totalDebit = 0;
    let totalCredit = 0;
    
    document.querySelectorAll('.item-row').forEach(row => {
        const entryType = row.querySelector('.entry-type').value;
        const amount = parseFloat(row.querySelector('.amount-input').value) || 0;
        
        if (entryType === 'debit') {
            totalDebit += amount;
        } else {
            totalCredit += amount;
        }
    });
    
    document.getElementById('totalDebit').textContent = totalDebit.toFixed(2);
    document.getElementById('totalCredit').textContent = totalCredit.toFixed(2);
    
    const diff = Math.abs(totalDebit - totalCredit);
    const balanceStatus = document.getElementById('balanceStatus');
    const submitBtn = document.getElementById('submitBtn');
    
    if (diff < 0.01) {
        balanceStatus.innerHTML = '<span class="badge bg-success">Balanced</span>';
        if (!submitBtn.disabled) {
            submitBtn.disabled = false;
        }
    } else {
        balanceStatus.innerHTML = `<span class="badge bg-danger">Difference: ${diff.toFixed(2)}</span>`;
        submitBtn.disabled = true;
    }
}

function updateRemoveButtons() {
    const rows = document.querySelectorAll('#itemsBody tr');
    rows.forEach((row, index) => {
        const removeBtn = row.querySelector('.btn-danger');
        if (rows.length <= 2) {
            removeBtn.disabled = true;
        } else {
            removeBtn.disabled = false;
        }
    });
}

// Add event listeners
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.amount-input, .entry-type').forEach(element => {
        element.addEventListener('input', updateTotals);
        element.addEventListener('change', updateTotals);
    });
    
    updateTotals();
    updateRemoveButtons();
});

// Add event listeners for dynamically added rows
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('amount-input') || e.target.classList.contains('entry-type')) {
        updateTotals();
    }
});
</script>
@endsection

