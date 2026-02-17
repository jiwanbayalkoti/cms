@extends('admin.layout')

@section('title', 'New Completed Work')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">New Completed Work</h1>
    <a href="{{ route('admin.completed-work.index') }}" class="btn btn-outline-secondary">Back</a>
</div>

@if(!$selectedWork)
    <div class="card">
        <div class="card-header"><strong>Select Work</strong></div>
        <div class="card-body">
            <p class="text-muted small mb-3">Choose a work to record completed quantities. Click on work description to open the form.</p>
            <div class="list-group">
                @foreach($works as $work)
                    <a href="{{ route('admin.completed-work.create', ['work_id' => $work->id]) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        {{ $work->parent_id ? $work->parent->name . ' » ' . $work->name : $work->name }}
                        <span class="badge bg-secondary">{{ $work->items->count() }} items</span>
                    </a>
                @endforeach
            </div>
            @if($works->isEmpty())
                <p class="text-muted mb-0 mt-3">No works. <a href="{{ route('admin.boq.work-index') }}">Add work in Work & BoQ</a> first.</p>
            @endif
        </div>
    </div>
@else
    <form method="POST" action="{{ route('admin.completed-work.store') }}" id="completedWorkForm">
        @csrf
        <input type="hidden" name="boq_work_id" value="{{ $selectedWork->id }}">

        <div class="card mb-4">
            <div class="card-header"><strong>Header</strong></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Work *</label>
                        <select class="form-select" disabled>
                            <option>{{ $selectedWork->name }}</option>
                        </select>
                        <a href="{{ route('admin.completed-work.create') }}" class="small">Change Work</a>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Contract No</label>
                        <input type="text" class="form-control" placeholder="e.g. NA/NO.2FESBN/NCB/...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Measurement Date *</label>
                        <input type="date" name="record_date" class="form-control" value="{{ old('record_date', date('Y-m-d')) }}" required>
                        @error('record_date')<span class="text-danger small">{{ $message }}</span>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Title (optional)</label>
                        <input type="text" name="notes" class="form-control" value="{{ old('notes') }}" placeholder="e.g. Completed Work - Jan 2025">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Measurement Unit *</label>
                        <select name="dimension_unit" id="dimensionUnitSelect" class="form-select" required>
                            <option value="m" {{ old('dimension_unit', 'm') === 'm' ? 'selected' : '' }}>Meter (m)</option>
                            <option value="ft" {{ old('dimension_unit') === 'ft' ? 'selected' : '' }}>Feet (ft)</option>
                            <option value="in" {{ old('dimension_unit') === 'in' ? 'selected' : '' }}>Inch (in)</option>
                        </select>
                        <small class="text-muted">Length, Breadth, Height columns use this unit</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Measurement Book – Works</strong>
            </div>
            <div class="card-body p-0">
                @if($selectedWork->items->isEmpty())
                    <p class="text-muted p-4 mb-0">No items in this work. <a href="{{ route('admin.boq.works.show', $selectedWork) }}">Add items</a> first.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0" id="completedWorkItemsTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 40px">SN</th>
                                    <th>Works (Description)</th>
                                    <th style="width: 60px">no</th>
                                    <th style="width: 70px">Unit</th>
                                    <th class="text-end" style="width: 90px">BoQ Qty</th>
                                    <th class="text-end" style="width: 90px">Completed (so far)</th>
                                    <th class="text-end" style="width: 85px">Remaining</th>
                                    <th class="text-end cw-th-length" style="width: 90px">Length (m)</th>
                                    <th class="text-end cw-th-breadth" style="width: 90px">Breadth (m)</th>
                                    <th class="text-end cw-th-height" style="width: 90px">Height (m)</th>
                                    <th class="text-end" style="width: 95px">Quantity *</th>
                                    <th style="width: 100px"></th>
                                </tr>
                            </thead>
                            <tbody id="cwItemsBody">
                                @php $rowIndex = 0; $previousSubWorksByBoqItem = $previousSubWorksByBoqItem ?? []; @endphp
                                @foreach($selectedWork->items as $item)
                                    @php
                                        $boqQty = (float) $item->qty;
                                        $completedSoFar = (float) ($completedQtyByItem[$item->id] ?? 0);
                                        $remaining = max(0, $boqQty - $completedSoFar);
                                        $isFullyDone = $boqQty > 0 && abs($completedSoFar - $boqQty) < 0.0001;
                                        $parentIndex = $rowIndex;
                                        $prevSubs = $previousSubWorksByBoqItem[$item->id] ?? [];
                                    @endphp
                                    <tr class="cw-main-row {{ $isFullyDone ? 'table-success' : '' }}" data-row-index="{{ $rowIndex }}" data-boq-item-id="{{ $item->id }}" data-unit="{{ strtolower($item->unit ?? '') }}" data-max="{{ $remaining }}">
                                        <td class="cw-sn">{{ $loop->iteration }}</td>
                                        <td><textarea class="form-control form-control-sm" rows="2" readonly>{{ $item->item_description ?: '–' }}</textarea></td>
                                        <td><input type="number" step="0.0001" min="0" name="items[{{ $rowIndex }}][no]" class="form-control form-control-sm text-end cw-dimension-input" value="{{ old('items.'.$rowIndex.'.no', '1') }}" style="min-width:50px"></td>
                                        <td><input type="text" class="form-control form-control-sm" value="{{ $item->unit ?: '–' }}" readonly style="min-width:60px"></td>
                                        <td class="text-end align-middle">{{ number_format($boqQty, 4) }}</td>
                                        <td class="text-end align-middle">{{ number_format($completedSoFar, 4) }}</td>
                                        <td class="text-end align-middle">{{ $isFullyDone ? '–' : number_format($remaining, 4) }}</td>
                                        <td><input type="number" step="0.0001" min="0" name="items[{{ $rowIndex }}][length]" class="form-control form-control-sm text-end cw-dimension-input" value="{{ old('items.'.$rowIndex.'.length') }}" placeholder="–" style="min-width:70px"></td>
                                        <td><input type="number" step="0.0001" min="0" name="items[{{ $rowIndex }}][breadth]" class="form-control form-control-sm text-end cw-dimension-input" value="{{ old('items.'.$rowIndex.'.breadth') }}" placeholder="–" style="min-width:70px"></td>
                                        <td><input type="number" step="0.0001" min="0" name="items[{{ $rowIndex }}][height]" class="form-control form-control-sm text-end cw-dimension-input" value="{{ old('items.'.$rowIndex.'.height') }}" placeholder="–" style="min-width:70px"></td>
                                        <td>
                                            <input type="hidden" name="items[{{ $rowIndex }}][boq_item_id]" value="{{ $item->id }}">
                                            @if($isFullyDone)
                                                <span class="text-success small fw-medium">Done</span>
                                                <input type="hidden" name="items[{{ $rowIndex }}][completed_qty]" value="0">
                                            @else
                                                <input type="number" step="0.0001" min="0" name="items[{{ $rowIndex }}][completed_qty]" class="form-control form-control-sm text-end completed-qty-input cw-qty-input" value="{{ old('items.'.$rowIndex.'.completed_qty', '0') }}" placeholder="0" data-max="{{ $remaining }}">
                                            @endif
                                        </td>
                                        <td onclick="event.stopPropagation();">
                                            @if($isFullyDone)
                                                <span class="text-muted small">–</span>
                                            @else
                                                <button type="button" class="btn btn-sm btn-success" onclick="addSubWorkRow(this)" title="Add Sub Work"><i class="bi bi-plus-circle"></i> Sub</button>
                                            @endif
                                        </td>
                                    </tr>
                                    @foreach($prevSubs as $sub)
                                        @php $rowIndex++; @endphp
                                        <tr class="cw-sub-row" data-row-index="{{ $rowIndex }}" data-parent-index="{{ $parentIndex }}" data-unit="{{ strtolower($item->unit ?? '') }}">
                                            <td class="cw-sn"></td>
                                            <td class="cw-works-cell"><textarea name="items[{{ $rowIndex }}][description]" class="form-control form-control-sm" rows="2" placeholder="Sub work description">{{ $sub['description'] }}</textarea></td>
                                            <td><input type="number" step="0.0001" min="0" name="items[{{ $rowIndex }}][no]" class="form-control form-control-sm text-end cw-dimension-input" value="{{ $sub['no'] !== null ? $sub['no'] : '1' }}" style="min-width:50px"></td>
                                            <td><input type="text" class="form-control form-control-sm" value="{{ $item->unit ?: '–' }}" readonly style="min-width:60px"></td>
                                            <td class="text-end align-middle">–</td>
                                            <td class="text-end align-middle">–</td>
                                            <td class="text-end align-middle">–</td>
                                            <td><input type="number" step="0.0001" min="0" name="items[{{ $rowIndex }}][length]" class="form-control form-control-sm text-end cw-dimension-input" value="{{ $sub['length'] !== null ? $sub['length'] : '' }}" placeholder="–" style="min-width:70px"></td>
                                            <td><input type="number" step="0.0001" min="0" name="items[{{ $rowIndex }}][breadth]" class="form-control form-control-sm text-end cw-dimension-input" value="{{ $sub['breadth'] !== null ? $sub['breadth'] : '' }}" placeholder="–" style="min-width:70px"></td>
                                            <td><input type="number" step="0.0001" min="0" name="items[{{ $rowIndex }}][height]" class="form-control form-control-sm text-end cw-dimension-input" value="{{ $sub['height'] !== null ? $sub['height'] : '' }}" placeholder="–" style="min-width:70px"></td>
                                            <td><input type="number" step="0.0001" min="0" name="items[{{ $rowIndex }}][completed_qty]" class="form-control form-control-sm text-end cw-qty-input" value="{{ $sub['completed_qty'] !== null ? $sub['completed_qty'] : '0' }}" placeholder="0"></td>
                                            <td>
                                                <input type="hidden" name="items[{{ $rowIndex }}][parent_id]" value="{{ $parentIndex }}">
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeCwSubRow(this)" title="Remove"><i class="bi bi-trash"></i></button>
                                            </td>
                                        </tr>
                                    @endforeach
                                    @php $rowIndex++; @endphp
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        @if(!$selectedWork->items->isEmpty())
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Save Completed Work</button>
                <a href="{{ route('admin.completed-work.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        @endif
    </form>

    <style>
    .cw-main-row { background-color: #f8f9fa; font-weight: 500; }
    .cw-sub-row { background-color: #fff; }
    .cw-sub-row td:first-child { padding-left: 2rem !important; }
    .cw-sub-row .cw-works-cell { padding-left: 1.5rem; }
    .cw-sub-row .cw-works-cell::before { content: "└ "; color: #6c757d; margin-right: 0.25rem; }
    </style>
    <script>
    (function() {
        const initialRowCount = document.querySelectorAll('#cwItemsBody tr.cw-main-row, #cwItemsBody tr.cw-sub-row').length;
        let cwRowIndex = initialRowCount;
        const boqItems = @json($selectedWork->items->keyBy('id')->map(fn($i) => ['id' => $i->id, 'unit' => $i->unit ?? '–'])->values());

        function updateDimensionUnitHeaders() {
            var u = (document.getElementById('dimensionUnitSelect') && document.getElementById('dimensionUnitSelect').value) || 'm';
            var labels = { m: 'm', ft: 'ft', in: 'in' };
            var suf = labels[u] || u;
            document.querySelectorAll('.cw-th-length').forEach(function(el) { el.textContent = 'Length (' + suf + ')'; });
            document.querySelectorAll('.cw-th-breadth').forEach(function(el) { el.textContent = 'Breadth (' + suf + ')'; });
            document.querySelectorAll('.cw-th-height').forEach(function(el) { el.textContent = 'Height (' + suf + ')'; });
        }
        var dimSel = document.getElementById('dimensionUnitSelect');
        if (dimSel) {
            dimSel.addEventListener('change', updateDimensionUnitHeaders);
            updateDimensionUnitHeaders();
        }

        function calcCwQty(row) {
            if (!row) return;
            const no = parseFloat(row.querySelector('input[name*="[no]"]')?.value) || 1;
            const L = parseFloat(row.querySelector('input[name*="[length]"]')?.value) || 0;
            const B = parseFloat(row.querySelector('input[name*="[breadth]"]')?.value) || 0;
            const H = parseFloat(row.querySelector('input[name*="[height]"]')?.value) || 0;
            const unit = (row.getAttribute('data-unit') || row.querySelector('td:nth-child(4) input')?.value || '').toLowerCase().replace(/\s/g,'');
            const qtyInp = row.querySelector('input[name*="[completed_qty]"]');
            if (!qtyInp || qtyInp.type === 'hidden') return;
            const areaUnits = ['sft','rft','sqft','sqm'];
            const volumeUnits = ['cuft','cft','cum','cubic'];
            let q = 0;
            if (L > 0 && B > 0) {
                if (areaUnits.some(function(u){ return unit.indexOf(u) >= 0; })) {
                    q = no * L * B;
                } else if (volumeUnits.some(function(u){ return unit.indexOf(u) >= 0; })) {
                    q = H > 0 ? no * L * B * H : 0;
                } else {
                    q = H > 0 ? no * L * B * H : no * L * B;
                }
            }
            qtyInp.value = q > 0 ? q.toFixed(4) : (qtyInp.hasAttribute('data-max') ? '' : '0');
            if (row.classList.contains('cw-main-row') && qtyInp.hasAttribute('data-max')) {
                var max = parseFloat(qtyInp.getAttribute('data-max'));
                if (!isNaN(max) && parseFloat(qtyInp.value) > max) qtyInp.value = max.toFixed(4);
            }
        }

        document.getElementById('cwItemsBody').addEventListener('input', function(e) {
            if (e.target.classList.contains('cw-dimension-input')) calcCwQty(e.target.closest('tr'));
        });
        document.getElementById('cwItemsBody').addEventListener('change', function(e) {
            if (e.target.classList.contains('cw-dimension-input')) calcCwQty(e.target.closest('tr'));
        });

        window.addSubWorkRow = function(btn) {
            const mainRow = btn.closest('tr.cw-main-row');
            if (!mainRow) return;
            const tbody = document.getElementById('cwItemsBody');
            const parentIndex = parseInt(mainRow.getAttribute('data-row-index') || '0');
            const mainBoqId = mainRow.getAttribute('data-boq-item-id');
            const unitText = (boqItems.find(function(x){ return String(x.id) === String(mainBoqId); }) || {}).unit || '–';
            let insertAfter = mainRow;
            let nextRow = mainRow.nextElementSibling;
            while (nextRow && nextRow.classList.contains('cw-sub-row')) {
                const subParentIdx = parseInt(nextRow.getAttribute('data-parent-index') || '-1');
                if (subParentIdx === parentIndex) {
                    insertAfter = nextRow;
                    nextRow = nextRow.nextElementSibling;
                } else break;
            }
            const tr = document.createElement('tr');
            tr.className = 'cw-sub-row';
            tr.setAttribute('data-row-index', cwRowIndex);
            tr.setAttribute('data-parent-index', parentIndex);
            tr.setAttribute('data-unit', (unitText || '').toLowerCase().replace(/\s/g,''));
            tr.innerHTML = `
                <td class="cw-sn"></td>
                <td class="cw-works-cell"><textarea name="items[${cwRowIndex}][description]" class="form-control form-control-sm" rows="2" placeholder="Sub work description"></textarea></td>
                <td><input type="number" step="0.0001" min="0" name="items[${cwRowIndex}][no]" class="form-control form-control-sm text-end cw-dimension-input" value="1" style="min-width:50px"></td>
                <td><input type="text" class="form-control form-control-sm" value="${unitText}" readonly style="min-width:60px"></td>
                <td class="text-end align-middle">–</td>
                <td class="text-end align-middle">–</td>
                <td class="text-end align-middle">–</td>
                <td><input type="number" step="0.0001" min="0" name="items[${cwRowIndex}][length]" class="form-control form-control-sm text-end cw-dimension-input" placeholder="–" style="min-width:70px"></td>
                <td><input type="number" step="0.0001" min="0" name="items[${cwRowIndex}][breadth]" class="form-control form-control-sm text-end cw-dimension-input" placeholder="–" style="min-width:70px"></td>
                <td><input type="number" step="0.0001" min="0" name="items[${cwRowIndex}][height]" class="form-control form-control-sm text-end cw-dimension-input" placeholder="–" style="min-width:70px"></td>
                <td><input type="number" step="0.0001" min="0" name="items[${cwRowIndex}][completed_qty]" class="form-control form-control-sm text-end cw-qty-input" value="0" placeholder="0"></td>
                <td>
                    <input type="hidden" name="items[${cwRowIndex}][parent_id]" value="${parentIndex}">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeCwSubRow(this)" title="Remove"><i class="bi bi-trash"></i></button>
                </td>
            `;
            insertAfter.insertAdjacentElement('afterend', tr);
            cwRowIndex++;
            renumberCwRows();
        };

        window.removeCwSubRow = function(btn) {
            btn.closest('tr').remove();
            renumberCwRows();
        };

        function renumberCwRows() {
            const allRows = Array.from(document.querySelectorAll('#cwItemsBody tr.cw-main-row, #cwItemsBody tr.cw-sub-row'));
            const parentMap = new Map();
            allRows.forEach(function(tr) {
                if (tr.classList.contains('cw-sub-row')) {
                    let prev = tr.previousElementSibling;
                    while (prev && !prev.classList.contains('cw-main-row')) prev = prev.previousElementSibling;
                    if (prev) parentMap.set(tr, prev);
                }
            });
            let sn = 1;
            allRows.forEach(function(tr, newIndex) {
                tr.setAttribute('data-row-index', newIndex);
                if (tr.classList.contains('cw-main-row')) {
                    const snCell = tr.querySelector('.cw-sn');
                    if (snCell) snCell.textContent = sn++;
                }
                tr.querySelectorAll('input, textarea, select').forEach(function(el) {
                    if (el.name && el.name.match(/items\[\d+\]/)) {
                        el.name = el.name.replace(/items\[\d+\]/, 'items[' + newIndex + ']');
                    }
                });
                const parentIdInput = tr.querySelector('input[name*="[parent_id]"]');
                if (parentIdInput && tr.classList.contains('cw-sub-row')) {
                    const parentRow = parentMap.get(tr);
                    if (parentRow) {
                        const newParentIndex = allRows.indexOf(parentRow);
                        if (newParentIndex >= 0) {
                            parentIdInput.value = newParentIndex;
                            tr.setAttribute('data-parent-index', newParentIndex);
                        }
                    }
                }
            });
            cwRowIndex = allRows.length;
        }

        function capToMax(input) {
            var max = parseFloat(input.getAttribute('data-max'));
            if (isNaN(max) || max < 0) return;
            var val = parseFloat(input.value);
            if (!isNaN(val) && val > max) input.value = max;
        }
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('completed-qty-input')) capToMax(e.target);
        });
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('completed-qty-input')) capToMax(e.target);
        });
        document.addEventListener('blur', function(e) {
            if (e.target.classList.contains('completed-qty-input')) capToMax(e.target);
        });

        var form = document.getElementById('completedWorkForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                renumberCwRows();
                document.querySelectorAll('.completed-qty-input').forEach(function(input) { capToMax(input); });
                var invalid = false;
                document.querySelectorAll('.completed-qty-input').forEach(function(input) {
                    var max = parseFloat(input.getAttribute('data-max'));
                    var val = parseFloat(input.value);
                    if (!isNaN(max) && !isNaN(val) && val > max) invalid = true;
                });
                if (invalid) {
                    e.preventDefault();
                    alert('Completed qty cannot exceed remaining qty.');
                    return false;
                }
            });
        }
    })();
    </script>
@endif
@endsection
