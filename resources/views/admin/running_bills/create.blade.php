@extends('admin.layout')
@section('title', 'New Running Bill')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">New Running Bill (Bill Statement)</h1>
    <a href="{{ route('admin.running-bills.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<form method="POST" action="{{ route('admin.running-bills.store') }}" id="rbForm">
    @csrf
    <div class="card mb-4">
        <div class="card-header"><strong>Header</strong></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Project *</label>
                    <select name="project_id" class="form-select" required>
                        <option value="">Select Project</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Contract No</label>
                    <input type="text" name="contract_no" class="form-control" placeholder="e.g. NA/NO.2FESBN/...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Bill Date *</label>
                    <input type="date" name="bill_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Bill Title *</label>
                    <input type="text" name="bill_title" class="form-control" placeholder="e.g. 1ST Running Bill" required>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <strong>Bill Items – As per BOQ & This bill</strong>
            <div class="d-flex gap-1">
                <button type="button" class="btn btn-sm btn-outline-primary" id="btnLoadFromMb" title="Measurement Book मा add gareko work description, unit, quantity यहाँ ल्याउनुहोस् (Project select गर्नुपर्छ)"><i class="bi bi-journal-text me-1"></i>Load from Measurement Book</button>
                <button type="button" class="btn btn-sm btn-success" onclick="addRbRow()"><i class="bi bi-plus"></i> Add Row</button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered mb-0" id="rbItemsTable">
                    <thead class="table-light">
                        <tr>
                            <th rowspan="2" class="align-middle">SN</th>
                            <th rowspan="2" class="align-middle">Description of works *</th>
                            <th colspan="3" class="text-center">As per boq</th>
                            <th colspan="3" class="text-center">This bill</th>
                            <th rowspan="2" class="align-middle">remaining Qty</th>
                            <th rowspan="2" class="align-middle">Remarks</th>
                            <th rowspan="2" class="align-middle" width="40"></th>
                        </tr>
                        <tr>
                            <th>Unit</th>
                            <th>Quantity</th>
                            <th>Unit price</th>
                            <th>Quantity</th>
                            <th>Unit price</th>
                            <th>total price</th>
                        </tr>
                    </thead>
                    <tbody id="rbItemsBody">
                        <tr class="rb-item-row">
                            <td>1</td>
                            <td><textarea name="items[0][description]" class="form-control form-control-sm" rows="2" required></textarea></td>
                            <td><input type="text" name="items[0][unit]" class="form-control form-control-sm" placeholder="sft/cuft" list="rbUom"></td>
                            <td><input type="number" name="items[0][boq_qty]" class="form-control form-control-sm" step="0.0001" min="0" onchange="calcRow(this)" value="0"></td>
                            <td><input type="number" name="items[0][boq_unit_price]" class="form-control form-control-sm" step="0.01" min="0" onchange="calcRow(this)" value="0"></td>
                            <td><input type="number" name="items[0][this_bill_qty]" class="form-control form-control-sm" step="0.0001" min="0" onchange="calcRow(this)" required></td>
                            <td><input type="number" name="items[0][unit_price]" class="form-control form-control-sm" step="0.01" min="0" onchange="calcRow(this)" required></td>
                            <td><input type="number" name="items[0][total_price]" class="form-control form-control-sm" step="0.01" readonly placeholder="auto"></td>
                            <td><input type="number" name="items[0][remaining_qty]" class="form-control form-control-sm" step="0.0001" readonly placeholder="auto"></td>
                            <td><input type="text" name="items[0][remarks]" class="form-control form-control-sm"></td>
                            <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRbRow(this)"><i class="bi bi-trash"></i></button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <datalist id="rbUom"><option value="nos"><option value="nos."><option value="sft"><option value="cuft"><option value="rft"><option value="cft"><option value="sqft"><option value="sqm"><option value="cum"></datalist>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Save Running Bill</button>
        <a href="{{ route('admin.running-bills.index') }}" class="btn btn-secondary"><i class="bi bi-x-lg me-1"></i>Cancel</a>
    </div>
</form>

@push('scripts')
<script>
let rbRowIndex = 1;
function addRbRow(item) {
    const tbody = document.getElementById('rbItemsBody');
    const r = tbody.querySelector('.rb-item-row');
    const tr = document.createElement('tr');
    tr.className = 'rb-item-row';
    tr.innerHTML = r.innerHTML.replace(/items\[0\]/g, 'items[' + rbRowIndex + ']');
    // BOQ: Item Description→Description of works, Unit→Unit, Quantity→As per boq Qty, Rate(NRs)→As per boq Unit price
    var d = (item && typeof item === 'object' && item.description != null) ? String(item.description) : (typeof item === 'string' ? item : '');
    var u = (item && typeof item === 'object' && item.unit != null) ? String(item.unit) : '';
    var q = (item && typeof item === 'object' && (item.boq_qty !== undefined && item.boq_qty !== '')) ? String(item.boq_qty) : '0';
    var p = (item && typeof item === 'object' && (item.boq_unit_price !== undefined && item.boq_unit_price !== '')) ? String(item.boq_unit_price) : '0';
    var tq = (item && typeof item === 'object' && (item.this_bill_qty !== undefined && item.this_bill_qty !== '')) ? String(item.this_bill_qty) : '';
    var up = (item && typeof item === 'object' && (item.unit_price !== undefined && item.unit_price !== '')) ? String(item.unit_price) : '';
    tr.querySelector('textarea[name*="[description]"]').value = d;
    tr.querySelector('input[name$="[unit]"]').value = u;
    tr.querySelector('input[name*="[boq_qty]"]').value = q;
    tr.querySelector('input[name*="[boq_unit_price]"]').value = p;
    tr.querySelector('input[name*="[this_bill_qty]"]').value = tq;
    tr.querySelector('input[name*="[unit_price]"]').value = up;
    tr.querySelector('input[name*="[total_price]"]').value = '';
    tr.querySelector('input[name*="[remaining_qty]"]').value = '';
    tr.querySelector('input[name*="[remarks]"]').value = '';
    tbody.appendChild(tr);
    rbRowIndex++;
    renumberRbRows();
    recalcAllRows();
}
function removeRbRow(btn) {
    const tbody = document.getElementById('rbItemsBody');
    if (tbody.querySelectorAll('.rb-item-row').length <= 1) return;
    btn.closest('tr').remove();
    renumberRbRows();
}
function renumberRbRows() {
    document.querySelectorAll('#rbItemsBody .rb-item-row').forEach((tr, i) => {
        tr.querySelector('td:first-child').textContent = i + 1;
        tr.querySelectorAll('input, textarea').forEach(el => {
            if (el.name && el.name.match(/items\[\d+\]/)) el.name = el.name.replace(/items\[\d+\]/, 'items[' + i + ']');
        });
    });
    rbRowIndex = document.querySelectorAll('#rbItemsBody .rb-item-row').length;
}
function calcRow(inp) {
    const row = inp.closest('tr');
    if (!row) return;
    var boqUp = row.querySelector('input[name*="[boq_unit_price]"]');
    var billUp = row.querySelector('input[name*="[unit_price]"]');
    if (boqUp && billUp) {
        if (inp === boqUp || (inp.name && inp.name.indexOf('[boq_unit_price]') >= 0)) {
            billUp.value = inp.value || '0';
        } else if (inp === billUp || (inp.name && inp.name.indexOf('[unit_price]') >= 0 && inp.name.indexOf('boq_unit_price') < 0)) {
            boqUp.value = inp.value || '0';
        }
    }
    var boq = parseFloat(row.querySelector('input[name*="[boq_qty]"]').value) || 0;
    var tbill = parseFloat(row.querySelector('input[name*="[this_bill_qty]"]').value) || 0;
    var up = parseFloat((billUp || row.querySelector('input[name*="[unit_price]"]')).value) || 0;
    row.querySelector('input[name*="[total_price]"]').value = (tbill * up).toFixed(2);
    row.querySelector('input[name*="[remaining_qty]"]').value = (boq - tbill).toFixed(4);
}
function recalcAllRows() {
    document.querySelectorAll('#rbItemsBody .rb-item-row').forEach(function(tr){
        var inp = tr.querySelector('input[name*="[boq_qty]"]');
        if (inp) calcRow(inp);
    });
}
document.addEventListener('DOMContentLoaded', function(){
    recalcAllRows();
    var btnLoad = document.getElementById('btnLoadFromMb');
    var projectSelect = document.querySelector('select[name="project_id"]');
    if (btnLoad && projectSelect) {
        btnLoad.addEventListener('click', function(){
            var pid = (projectSelect.value || '').trim();
            if (!pid) { alert('पहिले Project select गर्नुहोस्।'); return; }
            btnLoad.disabled = true;
            btnLoad.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Loading...';
            fetch('{{ route('admin.running-bills.measurement-book-items') }}?project_id=' + encodeURIComponent(pid), { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                .then(function(r){ return r.json().then(function(d){ return r.ok ? d : Promise.reject(d); }); })
                .then(function(data){
                    var arr = data.items || [];
                    if (arr.length === 0) { alert('यस Project को Measurement Book मा कुनै work description भेटिएन।'); return; }
                    var existingMap = new Map();
                    // Map existing items by description + unit to their row elements
                    document.querySelectorAll('#rbItemsBody .rb-item-row').forEach(function(tr){
                        var d = (tr.querySelector('textarea[name*="[description]"]')?.value || '').trim().toLowerCase();
                        var u = (tr.querySelector('input[name*="[unit]"]')?.value || '').trim().toLowerCase();
                        if (d && u) {
                            existingMap.set(d + '|' + u, tr);
                        }
                    });
                    var added = 0;
                    var updated = 0;
                    arr.forEach(function(it){
                        var d = (String(it.description||'').trim()).toLowerCase();
                        var u = (String(it.unit||'').trim()).toLowerCase();
                        var k = d + '|' + u;
                        // Skip if description or unit is empty
                        if (!d || !u) return;
                        
                        var existingRow = existingMap.get(k);
                        if (existingRow) {
                            // Item already exists - update the quantity if it has changed
                            var qtyInput = existingRow.querySelector('input[name*="[this_bill_qty]"]');
                            var oldQty = parseFloat(qtyInput?.value || 0);
                            var newQty = parseFloat(it.this_bill_qty || 0);
                            if (qtyInput && newQty > 0) {
                                // Only update if quantity has changed
                                if (Math.abs(oldQty - newQty) > 0.0001) {
                                    qtyInput.value = newQty;
                                    // Trigger calculation to update total_price and remaining_qty
                                    if (typeof calcRow === 'function') {
                                        calcRow(qtyInput);
                                    }
                                    updated++;
                                }
                            }
                        } else {
                            // New item - add it
                            addRbRow(it);
                            existingMap.set(k, null); // Mark as added
                            added++;
                        }
                    });
                    var msg = '';
                    if (updated > 0 && added > 0) {
                        msg = updated + ' item(s) को quantity update भयो र ' + added + ' नयाँ item(s) थपियो।';
                    } else if (updated > 0) {
                        msg = updated + ' item(s) को quantity update भयो (नयाँ sub-work add भएकोले)।';
                    } else if (added > 0) {
                        msg = added + ' item(s) Measurement Book बाट ल्याइयो। Description, Unit, This bill Qty भरियो। Unit price भर्नुहोस्।';
                    } else {
                        msg = 'सबै item पहिले नै थपिएको छ र quantity पनि same छ। नयाँ कुनै छैन।';
                    }
                    alert(msg);
                })
                .catch(function(e){ alert(e && e.message ? e.message : 'Load गर्दा समस्या भयो।'); })
                .finally(function(){ btnLoad.disabled = false; btnLoad.innerHTML = '<i class="bi bi-journal-text me-1"></i>Load from Measurement Book'; });
        });
    }
});
</script>
@endpush
@endsection
