@extends('admin.layout')

@section('title', 'Completed Work')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <h1 class="h3 mb-0">Completed Work</h1>
    <div class="d-flex align-items-center gap-2">
        @if(isset($projects) && $projects->isNotEmpty())
        <form method="get" action="{{ route('admin.completed-work.index') }}" class="d-inline-flex align-items-center gap-1">
            <label for="project_filter" class="form-label mb-0 small text-muted">Project</label>
            <select name="project_id" id="project_filter" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                <option value="">All Projects</option>
                @foreach($projects as $p)
                    <option value="{{ $p->id }}" {{ (isset($projectId) ? $projectId : request('project_id')) == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
            </select>
        </form>
        @endif
        <a href="{{ route('admin.completed-work.create') }}" class="btn btn-primary btn-sm btn-keep-text d-inline-flex align-items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/></svg>
            Add Completed Work
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">SN</th>
                        <th>Work</th>
                        @if(isset($projects) && $projects->isNotEmpty())
                        <th style="width: 120px;">Project</th>
                        @endif
                        <th style="width: 120px;">Date</th>
                        <th style="width: 200px;">Progress</th>
                        <th style="width: 120px;">Used Materials</th>
                        <th style="width: 180px;" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $record)
                        <tr>
                            <td>{{ $records->firstItem() + $loop->index }}</td>
                            <td>{{ $record->work->name ?? '–' }}</td>
                            @if(isset($projects) && $projects->isNotEmpty())
                            <td>{{ $record->project?->name ?? '–' }}</td>
                            @endif
                            <td>{{ $record->record_date->format('Y-m-d') }}</td>
                            <td>
                                @php
                                    $prog = $record->progress ?? ['progress_percent' => 0, 'total_completed_qty' => 0, 'total_boq_qty' => 0];
                                    $pct = (float)($prog['progress_percent'] ?? 0);
                                    $compQty = (float)($prog['total_completed_qty'] ?? 0);
                                    $boqQty = (float)($prog['total_boq_qty'] ?? 0);
                                    $barColor = $pct >= 100 ? 'bg-success' : ($pct >= 50 ? 'bg-info' : ($pct > 0 ? 'bg-warning' : 'bg-secondary'));
                                    $textColor = $pct >= 100 ? 'text-success' : ($pct >= 50 ? 'text-info' : 'text-muted');
                                @endphp
                                <div class="d-flex align-items-center gap-2">
                                    <div class="flex-grow-1">
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar {{ $barColor }}" role="progressbar" style="width: {{ min(100, $pct) }}%" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100">
                                                <small class="px-1">{{ number_format($pct, 1) }}%</small>
                                            </div>
                                        </div>
                                        <div class="small text-muted mt-1">
                                            {{ number_format($compQty, 2) }} / {{ number_format($boqQty, 2) }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($record->materialUsages->isEmpty())
                                    <span class="text-muted">–</span>
                                @else
                                    {{ $record->materialUsages->count() }} item(s)
                                @endif
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-info p-1 me-1" title="Used Materials" onclick="openMaterialsModal({{ $record->id }}, '{{ addslashes($record->work->name ?? 'Work') }}')"><i class="bi bi-box-seam"></i></button>
                                <a href="{{ route('admin.completed-work.show', $record) }}" class="btn btn-sm btn-outline-primary p-1" title="View"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('admin.completed-work.edit', $record) }}" class="btn btn-sm btn-outline-secondary p-1" title="Edit"><i class="bi bi-pencil"></i></a>
                                <form action="{{ route('admin.completed-work.destroy', $record) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this record?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger p-1" title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ isset($projects) && $projects->isNotEmpty() ? 7 : 6 }}" class="text-muted text-center py-4">No completed work records. <a href="{{ route('admin.completed-work.create') }}">Add one</a>.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($records->hasPages())
        <div class="card-footer">
            {{ $records->links() }}
        </div>
    @endif
</div>

{{-- Modal: Used Materials --}}
<div class="modal fade" id="materialsModal" tabindex="-1" aria-labelledby="materialsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="materialsModalLabel">Used Materials – <span id="materialsModalWorkName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">Materials are taken from Construction Materials. Qty used / remaining there will update when you add or remove here.</p>
                <h6 class="mb-2">Current used materials</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-sm table-bordered" id="materialsUsagesTable">
                        <thead class="table-light">
                            <tr>
                                <th>Material</th>
                                <th class="text-end" style="width: 100px;">Qty</th>
                                <th style="width: 60px;">Unit</th>
                                <th style="width: 50px;"></th>
                            </tr>
                        </thead>
                        <tbody id="materialsUsagesBody">
                        </tbody>
                    </table>
                </div>
                <h6 class="mb-2">Add material</h6>
                <div class="row g-2 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label small mb-0">Material</label>
                        <select class="form-select form-select-sm" id="materialSelect">
                            <option value="">-- Select material --</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-0">Quantity</label>
                        <input type="number" step="0.01" min="0.01" class="form-control form-control-sm" id="materialQty" value="1" placeholder="Qty">
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-primary btn-sm w-100 d-inline-flex align-items-center justify-content-center gap-1" id="materialAddBtn"><i class="bi bi-plus-lg"></i> Add</button>
                    </div>
                </div>
                <p class="small text-muted mt-2 mb-0"><span id="materialRemainingHint"></span></p>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    var materialsModalEl = document.getElementById('materialsModal');
    var materialsModalWorkName = document.getElementById('materialsModalWorkName');
    var materialsUsagesBody = document.getElementById('materialsUsagesBody');
    var materialSelect = document.getElementById('materialSelect');
    var materialQty = document.getElementById('materialQty');
    var materialAddBtn = document.getElementById('materialAddBtn');
    var materialRemainingHint = document.getElementById('materialRemainingHint');
    var currentRecordId = null;
    var materialsList = [];
    var usagesList = [];

    window.openMaterialsModal = function(recordId, workName) {
        currentRecordId = recordId;
        materialsModalWorkName.textContent = workName || 'Work';
        materialsUsagesBody.innerHTML = '<tr><td colspan="4" class="text-muted text-center py-3">Loading…</td></tr>';
        materialSelect.innerHTML = '<option value="">-- Select material --</option>';
        materialRemainingHint.textContent = '';
        var modal = new bootstrap.Modal(materialsModalEl);
        modal.show();
        loadMaterialsData();
    };

    function loadMaterialsData() {
        if (!currentRecordId) return;
        fetch('{{ url("admin/completed-work") }}/' + currentRecordId + '/materials', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function(r) { return r.json(); })
        .then(function(data) {
            materialsList = data.materials || [];
            usagesList = data.usages || [];
            window.usagesByMaterial = data.usagesByMaterial || [];
            renderUsages();
            renderMaterialSelect();
        }).catch(function() {
            materialsUsagesBody.innerHTML = '<tr><td colspan="4" class="text-danger">Failed to load.</td></tr>';
        });
    }

    function renderUsages() {
        var grouped = window.usagesByMaterial || [];
        if (grouped.length === 0) {
            materialsUsagesBody.innerHTML = '<tr><td colspan="4" class="text-muted text-center py-2">No materials used yet.</td></tr>';
            return;
        }
        materialsUsagesBody.innerHTML = grouped.map(function(g, idx) {
            var name = g.material_name || '–';
            var nameAttr = (g.material_name || '').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            return '<tr data-material-name="' + nameAttr + '">' +
                '<td>' + name + '</td>' +
                '<td class="text-end fw-medium">' + parseFloat(g.quantity).toFixed(2) + '</td>' +
                '<td>' + (g.unit || '–') + '</td>' +
                '<td><button type="button" class="btn btn-sm btn-outline-danger p-1 btn-remove-by-name" title="Remove all" data-material-name="' + nameAttr + '"><i class="bi bi-trash"></i></button></td>' +
                '</tr>';
        }).join('');
        materialsUsagesBody.querySelectorAll('.btn-remove-by-name').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var n = btn.getAttribute('data-material-name');
                if (n != null) removeUsageByName(n);
            });
        });
    }

    window.removeUsageByName = function(materialName) {
        if (!confirm('Remove all "' + materialName + '" from used list? Qty will return to Construction Materials.')) return;
        var url = '{{ url("admin/completed-work") }}/' + currentRecordId + '/materials-by-name?material_name=' + encodeURIComponent(materialName);
        fetch(url, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        }).then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) loadMaterialsData();
        });
    };

    function renderMaterialSelect() {
        materialSelect.innerHTML = '<option value="">-- Select material --</option>';
        materialsList.forEach(function(m) {
            var rem = parseFloat(m.quantity_remaining || 0);
            if (rem > 0) {
                materialSelect.innerHTML += '<option value="' + m.id + '" data-remaining="' + rem + '" data-unit="' + (m.unit || '') + '">' + (m.material_name || '') + ' (remaining: ' + rem.toFixed(2) + ' ' + m.unit + ')</option>';
            }
        });
        materialSelect.dispatchEvent(new Event('change'));
    }

    materialSelect.addEventListener('change', function() {
        var opt = materialSelect.options[materialSelect.selectedIndex];
        materialRemainingHint.textContent = opt && opt.value ? 'Remaining: ' + (opt.getAttribute('data-remaining') || '0') + ' ' + (opt.getAttribute('data-unit') || '') : '';
    });

    materialAddBtn.addEventListener('click', function() {
        var mid = materialSelect.value;
        var qty = parseFloat(materialQty.value);
        if (!mid || qty <= 0) {
            alert('Select a material and enter quantity.');
            return;
        }
        materialAddBtn.disabled = true;
        var url = '{{ url("admin/completed-work") }}/' + currentRecordId + '/materials';
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ usages: [{ construction_material_id: parseInt(mid, 10), quantity: qty }] })
        }).then(function(r) {
            if (!r.ok && r.status === 422) return r.json().then(function(d) { throw d; });
            return r.json();
        }).then(function(data) {
            materialAddBtn.disabled = false;
            if (data.success) {
                materialQty.value = '1';
                loadMaterialsData();
            } else {
                alert(data.message || 'Error adding material.');
            }
        }).catch(function(err) {
            materialAddBtn.disabled = false;
            var msg = 'Request failed.';
            if (err && err.errors && typeof err.errors === 'object') {
                var first = Object.values(err.errors)[0];
                msg = Array.isArray(first) ? first[0] : first;
            } else if (err && err.message) msg = err.message;
            alert(msg);
        });
    });
})();
</script>
@endsection
