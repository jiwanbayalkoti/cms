@extends('admin.layout')

@section('title', 'Construction Material Calculator')

@section('content')
<div class="mb-4">
    <div class="row g-3 align-items-stretch">
        <div class="col-12 col-lg-8 mb-3 mb-lg-0">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Construction Material Calculator</h5>
                </div>
                <div class="card-body">
                    <form id="workForm" onsubmit="event.preventDefault(); addWorkItem();">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Work Type</label>
                                <select class="form-select" id="workType" required>
                                    <option value="">-- Select Work Type --</option>
                                    <option value="concrete">Concrete Works</option>
                                    <option value="masonry">Masonry / Wall</option>
                                    <option value="plaster">Plaster / Finish</option>
                                    <option value="soling">Soling / Base</option>
                                    <option value="steel">Rod / Steel Reinforcement</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Custom Label (optional)</label>
                                <input type="text" id="workLabel" class="form-control" placeholder="e.g., Foundation Footing A">
                            </div>
                        </div>

                        {{-- Concrete Inputs --}}
                        <div class="mt-4 d-none" data-section="concrete">
                            <h6 class="fw-semibold text-primary">Concrete Dimensions</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Length (m)</label>
                                    <input type="number" step="0.01" min="0" id="concreteLength" class="form-control" placeholder="e.g., 5" />
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Width (m)</label>
                                    <input type="number" step="0.01" min="0" id="concreteWidth" class="form-control" placeholder="e.g., 0.6" />
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Height / Depth (m)</label>
                                    <input type="number" step="0.01" min="0" id="concreteDepth" class="form-control" placeholder="e.g., 0.5" />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Concrete Element</label>
                                    <select id="concreteElement" class="form-select">
                                        <option value="Foundation">Foundation</option>
                                        <option value="Column / Pillar">Column / Pillar</option>
                                        <option value="Beam">Beam</option>
                                        <option value="Slab">Slab</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Concrete Grade</label>
                                    <select id="concreteGrade" class="form-select">
                                        @foreach($concreteGrades as $grade)
                                            <option value="{{ $grade['value'] }}">{{ $grade['label'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Masonry Inputs --}}
                        <div class="mt-4 d-none" data-section="masonry">
                            <h6 class="fw-semibold text-primary">Masonry Dimensions</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Wall Length (m)</label>
                                    <input type="number" step="0.01" min="0" id="wallLength" class="form-control" placeholder="e.g., 6" />
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Wall Height (m)</label>
                                    <input type="number" step="0.01" min="0" id="wallHeight" class="form-control" placeholder="e.g., 3" />
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Wall Thickness (m)</label>
                                    <input type="number" step="0.01" min="0" id="wallThickness" class="form-control" placeholder="e.g., 0.23" />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Mortar Mix</label>
                                    <select id="masonryMortar" class="form-select">
                                        @foreach($mortarMixes as $mix)
                                            <option value="{{ $mix['value'] }}">{{ $mix['label'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Plaster Inputs --}}
                        <div class="mt-4 d-none" data-section="plaster">
                            <h6 class="fw-semibold text-primary">Plaster Details</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Surface Area (m²)</label>
                                    <input type="number" step="0.01" min="0" id="plasterArea" class="form-control" placeholder="e.g., 40" />
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Thickness (cm)</label>
                                    <input type="number" step="0.1" min="0" id="plasterThickness" class="form-control" placeholder="e.g., 1.5" />
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Mortar Mix</label>
                                    <select id="plasterMortar" class="form-select">
                                        @foreach($mortarMixes as $mix)
                                            <option value="{{ $mix['value'] }}">{{ $mix['label'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Soling Inputs --}}
                        <div class="mt-4 d-none" data-section="soling">
                            <h6 class="fw-semibold text-primary">Soling / Base</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Area (m²)</label>
                                    <input type="number" step="0.01" min="0" id="solingArea" class="form-control" placeholder="e.g., 50" />
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Thickness (cm)</label>
                                    <input type="number" step="0.1" min="0" id="solingThickness" class="form-control" placeholder="e.g., 15" />
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Material Type</label>
                                    <select id="solingMaterial" class="form-select">
                                        @foreach($solingMaterials as $material)
                                            <option value="{{ $material['value'] }}">{{ $material['label'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Steel / Rod Inputs --}}
                        <div class="mt-4 d-none" data-section="steel">
                            <h6 class="fw-semibold text-primary">Steel / Rod Reinforcement</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Bar Diameter (mm)</label>
                                    <select id="steelDiameter" class="form-select">
                                        <option value="6">6 mm</option>
                                        <option value="8">8 mm</option>
                                        <option value="10">10 mm</option>
                                        <option value="12">12 mm</option>
                                        <option value="16">16 mm</option>
                                        <option value="20">20 mm</option>
                                        <option value="25">25 mm</option>
                                        <option value="32">32 mm</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Number of Bars</label>
                                    <input type="number" step="1" min="1" id="steelBars" class="form-control" placeholder="e.g., 10" />
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Length per Bar (m)</label>
                                    <input type="number" step="0.01" min="0" id="steelLength" class="form-control" placeholder="e.g., 6" />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Element Type</label>
                                    <select id="steelElement" class="form-select">
                                        <option value="Foundation">Foundation</option>
                                        <option value="Column / Pillar">Column / Pillar</option>
                                        <option value="Beam">Beam</option>
                                        <option value="Slab">Slab</option>
                                        <option value="Wall">Wall</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Calculation Method</label>
                                    <select id="steelMethod" class="form-select">
                                        <option value="direct">Direct (Number × Length)</option>
                                        <option value="area">Area Based (Area × Spacing)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row g-3 mt-2" id="steelAreaMethod" style="display: none;">
                                <div class="col-md-4">
                                    <label class="form-label">Area (m²)</label>
                                    <input type="number" step="0.01" min="0" id="steelArea" class="form-control" placeholder="e.g., 50" />
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Spacing (cm)</label>
                                    <input type="number" step="0.5" min="0" id="steelSpacing" class="form-control" placeholder="e.g., 15" />
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Layers</label>
                                    <input type="number" step="1" min="1" id="steelLayers" class="form-control" value="1" placeholder="e.g., 2" />
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mt-4">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Wastage Allowance (%)</label>
                                <input type="number" step="0.1" min="0" max="15" id="wastagePercent" class="form-control" value="5">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Notes (optional)</label>
                                <input type="text" id="workNotes" class="form-control" placeholder="Any specific remarks">
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="reset" class="btn btn-outline-secondary me-2" onclick="resetForm()">Reset</button>
                            <button type="submit" class="btn btn-primary">Add Work Item</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light fw-bold">
                    Unit Prices <small class="text-muted">(optional)</small>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Currency</label>
                            <select id="currency" class="form-select" onchange="renderSummary()">
                                <option value="NPR">NPR (Rs)</option>
                                <option value="INR">INR (₹)</option>
                                <option value="USD">USD ($)</option>
                                <option value="EUR">EUR (€)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cement / bag</label>
                            <input type="number" min="0" step="0.01" class="form-control cost-input" data-key="cement_bag" placeholder="0.00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Sand / m³</label>
                            <input type="number" min="0" step="0.01" class="form-control cost-input" data-key="sand_m3" placeholder="0.00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Aggregate / m³</label>
                            <input type="number" min="0" step="0.01" class="form-control cost-input" data-key="aggregate_m3" placeholder="0.00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Water / litre</label>
                            <input type="number" min="0" step="0.01" class="form-control cost-input" data-key="water_litre" placeholder="0.00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Brick / block</label>
                            <input type="number" min="0" step="0.01" class="form-control cost-input" data-key="brick_unit" placeholder="0.00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Soling / m³</label>
                            <input type="number" min="0" step="0.01" class="form-control cost-input" data-key="soling_m3" placeholder="0.00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Steel / Rod (per kg)</label>
                            <input type="number" min="0" step="0.01" class="form-control cost-input" data-key="steel_kg" placeholder="0.00">
                        </div>
                    </div>
                    <div class="alert alert-info mt-4 mb-0 small d-flex">
                        <i class="bi bi-info-circle me-2 mt-1"></i>
                        <span>
                            Costs are optional. Leave blank if you only need quantities.
                            Updating costs recalculates totals instantly for all work items.
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">Calculated Work Items</h5>
            <small class="text-muted">All added works with material breakdown and optional cost.</small>
        </div>
        <div class="d-flex gap-2">
            <form id="saveSetForm" method="POST" action="{{ route('admin.material-calculator.save') }}" class="me-2">
                @csrf
                <input type="hidden" name="name">
                <input type="hidden" name="calculations">
                <input type="hidden" name="summary">
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="saveForFuture()">
                    <i class="bi bi-bookmark-plus me-1"></i> Save for future
                </button>
            </form>
            <form id="excelExportForm" method="POST" action="{{ route('admin.material-calculator.export.excel') }}">
                @csrf
                <input type="hidden" name="calculations">
                <input type="hidden" name="summary">
                <button type="button" class="btn btn-success btn-sm" onclick="exportData('excel')">
                    <i class="bi bi-file-earmark-excel-fill me-1"></i> Export Excel
                </button>
            </form>
            <form id="pdfExportForm" method="POST" action="{{ route('admin.material-calculator.export.pdf') }}">
                @csrf
                <input type="hidden" name="calculations">
                <input type="hidden" name="summary">
                <button type="button" class="btn btn-danger btn-sm" onclick="exportData('pdf')">
                    <i class="bi bi-file-earmark-pdf-fill me-1"></i> Export PDF
                </button>
            </form>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover table-sm align-middle mb-0" id="resultsTable">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Work & Description</th>
                        <th>Cement (bags)</th>
                        <th>Sand (m³)</th>
                        <th>Aggregate (m³)</th>
                        <th>Bricks / Blocks</th>
                        <th>Water (L)</th>
                        <th>Soling Vol (m³)</th>
                        <th>Steel / Rod (kg)</th>
                        <th>Cost</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="no-data-row">
                        <td colspan="11" class="text-center text-muted py-4">
                            No work items added yet. Use the form above to calculate materials.
                        </td>
                    </tr>
                </tbody>
                <tfoot class="table-light d-none" id="resultsFooter">
                    <tr class="fw-semibold table-primary">
                        <td colspan="2">Totals</td>
                        <td id="totalCement">0</td>
                        <td id="totalSand">0</td>
                        <td id="totalAggregate">0</td>
                        <td id="totalBricks">0</td>
                        <td id="totalWater">0</td>
                        <td id="totalSoling">0</td>
                        <td id="totalSteel">0</td>
                        <td id="totalCost">0</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

@if($savedSets->isNotEmpty())
<div class="card shadow-sm mt-5">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">My Calculation History</h5>
            <small class="text-muted">Previously saved calculation sets for this company.</small>
        </div>
        <span class="badge rounded-pill bg-light text-dark border">
            <i class="bi bi-clock-history me-1"></i> {{ $savedSets->count() }} recent
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Saved By</th>
                        <th>Items</th>
                        <th>Saved On</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $historyPayload = $savedSets->mapWithKeys(function ($set) {
                            return [
                                $set->id => [
                                    'name' => $set->name,
                                    'user' => $set->user?->name ?? 'N/A',
                                    'created_at' => $set->created_at->format('d M Y, H:i'),
                                    'calculations' => $set->calculations ?? [],
                                    'summary' => $set->summary ?? [],
                                ],
                            ];
                        });
                    @endphp
                    @foreach($savedSets as $index => $set)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $set->name }}</td>
                            <td>{{ $set->user?->name ?? 'N/A' }}</td>
                            <td>{{ count($set->calculations ?? []) }}</td>
                            <td>{{ $set->created_at->format('d M Y, H:i') }}</td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-secondary"
                                    onclick="showHistoryDetails({{ $set->id }})">
                                    View
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<!-- History Modal -->
<div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Calculation Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="historyDetails"></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const concreteRatios = @json(collect($concreteGrades)->keyBy('value')->map(fn($grade) => $grade['ratio'])->toArray());
const mortarRatios = @json(collect($mortarMixes)->keyBy('value')->map(fn($mix) => $mix['ratio'])->toArray());
const defaultCosts = @json($defaultCosts);
const historySets = @json($historyPayload ?? []);

const results = [];

document.getElementById('workType').addEventListener('change', (event) => {
    const value = event.target.value;
    document.querySelectorAll('[data-section]').forEach(section => {
        if (section.getAttribute('data-section') === value) {
            section.classList.remove('d-none');
        } else {
            section.classList.add('d-none');
        }
    });
});

const steelMethodEl = document.getElementById('steelMethod');
if (steelMethodEl) {
    steelMethodEl.addEventListener('change', (event) => {
        const areaMethod = document.getElementById('steelAreaMethod');
        if (areaMethod) {
            if (event.target.value === 'area') {
                areaMethod.style.display = 'block';
            } else {
                areaMethod.style.display = 'none';
            }
        }
    });
}

document.querySelectorAll('.cost-input').forEach(input => {
    input.addEventListener('input', () => renderSummary());
});

function resetForm() {
    document.getElementById('workForm').reset();
    document.querySelectorAll('[data-section]').forEach(section => section.classList.add('d-none'));
    const steelAreaMethod = document.getElementById('steelAreaMethod');
    if (steelAreaMethod) {
        steelAreaMethod.style.display = 'none';
    }
}

function roundValue(value, precision = 2) {
    return Math.round((value + Number.EPSILON) * Math.pow(10, precision)) / Math.pow(10, precision);
}

function getUnitCosts() {
    const costs = { ...defaultCosts };
    document.querySelectorAll('.cost-input').forEach(input => {
        const key = input.dataset.key;
        const val = parseFloat(input.value);
        if (!isNaN(val)) {
            costs[key] = val;
        }
    });
    return costs;
}

function addWorkItem() {
    const type = document.getElementById('workType').value;
    if (!type) {
        alert('Select a work type first.');
        return;
    }
    const wastage = parseFloat(document.getElementById('wastagePercent').value) || 0;
    const label = document.getElementById('workLabel').value || '';
    const notes = document.getElementById('workNotes').value || '';

    let calculation = null;

    if (type === 'concrete') {
        calculation = calculateConcrete(wastage, label, notes);
    } else if (type === 'masonry') {
        calculation = calculateMasonry(wastage, label, notes);
    } else if (type === 'plaster') {
        calculation = calculatePlaster(wastage, label, notes);
    } else if (type === 'soling') {
        calculation = calculateSoling(wastage, label, notes);
    } else if (type === 'steel') {
        calculation = calculateSteel(wastage, label, notes);
    }

    if (!calculation) {
        return;
    }

    results.push(calculation);
    renderSummary();
    resetForm();
}

function calculateConcrete(wastage, label, notes) {
    const length = parseFloat(document.getElementById('concreteLength').value);
    const width = parseFloat(document.getElementById('concreteWidth').value);
    const depth = parseFloat(document.getElementById('concreteDepth').value);
    const grade = document.getElementById('concreteGrade').value;
    const element = document.getElementById('concreteElement').value;

    if ([length, width, depth].some(v => !v || v <= 0)) {
        alert('Provide concrete dimensions (length, width, depth).');
        return null;
    }

    const ratio = concreteRatios[grade] || [1,2,4];
    const totalParts = ratio[0] + ratio[1] + ratio[2];
    const volume = length * width * depth;
    const dryVolume = volume * 1.54;
    const wastageFactor = 1 + wastage / 100;

    const cementVolume = dryVolume * (ratio[0] / totalParts) * wastageFactor;
    const sandVolume = dryVolume * (ratio[1] / totalParts) * wastageFactor;
    const aggregateVolume = dryVolume * (ratio[2] / totalParts) * wastageFactor;
    const cementBags = cementVolume / 0.035;
    const waterLitres = cementBags * 50 * 0.5;

    const description = `${element} (${length}m x ${width}m x ${depth}m) - Grade ${grade}` + (label ? ` | ${label}` : '');

    return {
        id: Date.now(),
        work_type: `Concrete - ${element}`,
        description,
        notes,
        materials: {
            cement_bags: roundValue(cementBags),
            sand_m3: roundValue(sandVolume),
            aggregate_m3: roundValue(aggregateVolume),
            water_litres: roundValue(waterLitres),
            concrete_volume_m3: roundValue(volume),
        }
    };
}

function calculateMasonry(wastage, label, notes) {
    const length = parseFloat(document.getElementById('wallLength').value);
    const height = parseFloat(document.getElementById('wallHeight').value);
    const thickness = parseFloat(document.getElementById('wallThickness').value);
    const mix = document.getElementById('masonryMortar').value;

    if ([length, height, thickness].some(v => !v || v <= 0)) {
        alert('Provide wall length, height and thickness.');
        return null;
    }

    const ratio = mortarRatios[mix] || [1,6];
    const totalParts = ratio[0] + ratio[1];
    const wallVolume = length * height * thickness;
    const brickVolume = 0.19 * 0.09 * 0.09;
    const bricksNeeded = (wallVolume / brickVolume) * 1.05;

    const mortarWetVolume = wallVolume * 0.25;
    const dryVolume = mortarWetVolume * 1.33 * (1 + wastage / 100);
    const cementVolume = dryVolume * (ratio[0] / totalParts);
    const sandVolume = dryVolume * (ratio[1] / totalParts);
    const cementBags = cementVolume / 0.035;

    const description = `Wall ${length}m x ${height}m x ${thickness}m | Mortar ${mix}` + (label ? ` | ${label}` : '');

    return {
        id: Date.now(),
        work_type: 'Masonry / Wall',
        description,
        notes,
        materials: {
            cement_bags: roundValue(cementBags),
            sand_m3: roundValue(sandVolume),
            bricks_units: Math.ceil(bricksNeeded),
            wall_volume_m3: roundValue(wallVolume),
        }
    };
}

function calculatePlaster(wastage, label, notes) {
    const area = parseFloat(document.getElementById('plasterArea').value);
    const thicknessCm = parseFloat(document.getElementById('plasterThickness').value);
    const mix = document.getElementById('plasterMortar').value;

    if (!area || area <= 0 || !thicknessCm || thicknessCm <= 0) {
        alert('Provide plaster area and thickness.');
        return null;
    }

    const ratio = mortarRatios[mix] || [1,4];
    const totalParts = ratio[0] + ratio[1];
    const thicknessM = thicknessCm / 100;
    const wetVolume = area * thicknessM;
    const dryVolume = wetVolume * 1.33 * (1 + wastage / 100);
    const cementVolume = dryVolume * (ratio[0] / totalParts);
    const sandVolume = dryVolume * (ratio[1] / totalParts);
    const cementBags = cementVolume / 0.035;

    const description = `Plaster ${area}m² @ ${thicknessCm}cm | Mix ${mix}` + (label ? ` | ${label}` : '');

    return {
        id: Date.now(),
        work_type: 'Plaster / Finish',
        description,
        notes,
        materials: {
            cement_bags: roundValue(cementBags),
            sand_m3: roundValue(sandVolume),
            plaster_area_m2: roundValue(area),
        }
    };
}

function calculateSoling(wastage, label, notes) {
    const area = parseFloat(document.getElementById('solingArea').value);
    const thicknessCm = parseFloat(document.getElementById('solingThickness').value);
    const material = document.getElementById('solingMaterial').value;

    if (!area || area <= 0 || !thicknessCm || thicknessCm <= 0) {
        alert('Provide soling area and thickness.');
        return null;
    }

    const thicknessM = thicknessCm / 100;
    const volume = area * thicknessM * (1 + wastage / 100);

    const description = `Soling ${area}m² @ ${thicknessCm}cm | ${material}` + (label ? ` | ${label}` : '');

    return {
        id: Date.now(),
        work_type: 'Soling / Base',
        description,
        notes,
        materials: {
            soling_volume_m3: roundValue(volume),
            soling_material: material,
        }
    };
}

function calculateSteel(wastage, label, notes) {
    const diameter = parseFloat(document.getElementById('steelDiameter').value);
    const method = document.getElementById('steelMethod').value;
    const element = document.getElementById('steelElement').value;
    
    let totalLength = 0;
    let numberOfBars = 0;
    let description = '';

    if (method === 'direct') {
        const bars = parseFloat(document.getElementById('steelBars').value);
        const lengthPerBar = parseFloat(document.getElementById('steelLength').value);

        if (!bars || bars <= 0 || !lengthPerBar || lengthPerBar <= 0) {
            alert('Provide number of bars and length per bar.');
            return null;
        }

        numberOfBars = bars;
        totalLength = bars * lengthPerBar;
        description = `${diameter}mm bars × ${bars} nos × ${lengthPerBar}m | ${element}` + (label ? ` | ${label}` : '');
    } else if (method === 'area') {
        const area = parseFloat(document.getElementById('steelArea').value);
        const spacingCm = parseFloat(document.getElementById('steelSpacing').value);
        const layers = parseFloat(document.getElementById('steelLayers').value) || 1;
        const lengthPerBar = parseFloat(document.getElementById('steelLength').value);

        if (!area || area <= 0 || !spacingCm || spacingCm <= 0) {
            alert('Provide area and spacing for area-based calculation.');
            return null;
        }

        // Calculate number of bars based on area and spacing
        const spacingM = spacingCm / 100;
        const barsPerDirection = Math.ceil(Math.sqrt(area) / spacingM) + 1; // Approximate calculation
        numberOfBars = barsPerDirection * layers;
        
        // If length per bar is provided, use it; otherwise estimate from area
        if (lengthPerBar && lengthPerBar > 0) {
            totalLength = numberOfBars * lengthPerBar;
            description = `${diameter}mm bars | Area: ${area}m² @ ${spacingCm}cm spacing | ${layers} layer(s) | ${lengthPerBar}m per bar | ${element}` + (label ? ` | ${label}` : '');
        } else {
            // Estimate length from area (assuming square area)
            const estimatedLength = Math.sqrt(area);
            totalLength = numberOfBars * estimatedLength;
            description = `${diameter}mm bars | Area: ${area}m² @ ${spacingCm}cm spacing | ${layers} layer(s) | Est. ${estimatedLength.toFixed(2)}m per bar | ${element}` + (label ? ` | ${label}` : '');
        }
    }

    // Universal formula: Weight (kg) = (D²/162) × Length (m)
    // Where D is diameter in mm
    // This is the standard formula used in construction
    const weightPerMeter = (diameter * diameter) / 162;
    const totalWeight = totalLength * weightPerMeter * (1 + wastage / 100);

    return {
        id: Date.now(),
        work_type: 'Rod / Steel Reinforcement',
        description,
        notes,
        materials: {
            steel_kg: roundValue(totalWeight),
            steel_diameter_mm: diameter,
            steel_bars: Math.ceil(numberOfBars),
            steel_length_m: roundValue(totalLength),
            steel_element: element,
        }
    };
}

function removeItem(id) {
    const index = results.findIndex(item => item.id === id);
    if (index >= 0) {
        results.splice(index, 1);
        renderSummary();
    }
}

function calculateCost(materials, unitCosts) {
    const breakdown = {
        cement: (materials.cement_bags || 0) * (unitCosts.cement_bag || 0),
        sand: (materials.sand_m3 || 0) * (unitCosts.sand_m3 || 0),
        aggregate: (materials.aggregate_m3 || 0) * (unitCosts.aggregate_m3 || 0),
        water: (materials.water_litres || 0) * (unitCosts.water_litre || 0),
        bricks: (materials.bricks_units || 0) * (unitCosts.brick_unit || 0),
        soling: ((materials.soling_volume_m3 || materials.material_volume_m3 || 0) * (unitCosts.soling_m3 || 0)),
        steel: (materials.steel_kg || 0) * (unitCosts.steel_kg || 0),
    };
    const total = Object.values(breakdown).reduce((sum, value) => sum + value, 0);
    return { breakdown, total };
}

function renderSummary() {
    const tbody = document.querySelector('#resultsTable tbody');
    tbody.innerHTML = '';

    if (!results.length) {
        const row = document.createElement('tr');
        row.classList.add('no-data-row');
        row.innerHTML = `<td colspan="11" class="text-center text-muted py-4">No work items added yet. Use the form above to calculate materials.</td>`;
        tbody.appendChild(row);
        document.getElementById('resultsFooter').classList.add('d-none');
        updateExportPayload();
        return;
    }

    const currency = document.getElementById('currency').value;
    const currencySymbol = currency === 'USD' ? '$' : currency === 'EUR' ? '€' : (currency === 'INR' ? '₹' : 'Rs');
    const unitCosts = getUnitCosts();
    const totals = {
        cement_bags: 0,
        sand_m3: 0,
        aggregate_m3: 0,
        bricks_units: 0,
        water_litres: 0,
        soling_volume_m3: 0,
        steel_kg: 0,
        total_cost: 0,
    };

    results.forEach((item, index) => {
        const cost = calculateCost(item.materials, unitCosts);
        totals.cement_bags += item.materials.cement_bags || 0;
        totals.sand_m3 += item.materials.sand_m3 || 0;
        totals.aggregate_m3 += item.materials.aggregate_m3 || 0;
        totals.bricks_units += item.materials.bricks_units || 0;
        totals.water_litres += item.materials.water_litres || 0;
        totals.soling_volume_m3 += item.materials.soling_volume_m3 || 0;
        totals.steel_kg += item.materials.steel_kg || 0;
        totals.total_cost += cost.total;

        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${index + 1}</td>
            <td>
                <div class="fw-semibold">${item.work_type}</div>
                <div class="small text-muted">${item.description}</div>
                ${item.notes ? `<div class="small text-info">${item.notes}</div>` : ''}
            </td>
            <td>${item.materials.cement_bags ?? '-'}</td>
            <td>${item.materials.sand_m3 ?? '-'}</td>
            <td>${item.materials.aggregate_m3 ?? '-'}</td>
            <td>${item.materials.bricks_units ?? '-'}</td>
            <td>${item.materials.water_litres ?? '-'}</td>
            <td>${item.materials.soling_volume_m3 ?? '-'}</td>
            <td>${item.materials.steel_kg ?? '-'}</td>
            <td>${cost.total ? currencySymbol + ' ' + roundValue(cost.total, 2) : '-'}</td>
            <td class="text-end">
                <button class="btn btn-sm btn-link text-danger" onclick="removeItem(${item.id})">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });

    document.getElementById('totalCement').innerText = roundValue(totals.cement_bags);
    document.getElementById('totalSand').innerText = roundValue(totals.sand_m3);
    document.getElementById('totalAggregate').innerText = roundValue(totals.aggregate_m3);
    document.getElementById('totalBricks').innerText = Math.round(totals.bricks_units);
    document.getElementById('totalWater').innerText = roundValue(totals.water_litres);
    document.getElementById('totalSoling').innerText = roundValue(totals.soling_volume_m3);
    document.getElementById('totalSteel').innerText = roundValue(totals.steel_kg);
    document.getElementById('totalCost').innerText = currencySymbol + ' ' + roundValue(totals.total_cost, 2);
    document.getElementById('resultsFooter').classList.remove('d-none');

    updateExportPayload(totals);
}

function updateExportPayload(totals = null) {
    const payloadItems = [];
    const unitCosts = getUnitCosts();
    const currency = document.getElementById('currency').value;

    results.forEach((item, index) => {
        const cost = calculateCost(item.materials, unitCosts);
        payloadItems.push({
            sn: index + 1,
            work_type: item.work_type,
            description: item.description,
            notes: item.notes,
            materials: item.materials,
            cost: { ...cost.breakdown, total: roundValue(cost.total, 2) },
        });
    });

    const summary = {
        totals: totals ? {
            cement_bags: roundValue(totals.cement_bags || 0),
            sand_m3: roundValue(totals.sand_m3 || 0),
            aggregate_m3: roundValue(totals.aggregate_m3 || 0),
            bricks_units: Math.round(totals.bricks_units || 0),
            water_litres: roundValue(totals.water_litres || 0),
            soling_volume_m3: roundValue(totals.soling_volume_m3 || 0),
            steel_kg: roundValue(totals.steel_kg || 0),
            total_cost: roundValue(totals.total_cost || 0, 2),
        } : {},
        unit_costs: unitCosts,
        currency,
    };

    document.querySelector('#excelExportForm input[name="calculations"]').value = JSON.stringify(payloadItems);
    document.querySelector('#excelExportForm input[name="summary"]').value = JSON.stringify(summary);
    document.querySelector('#pdfExportForm input[name="calculations"]').value = JSON.stringify(payloadItems);
    document.querySelector('#pdfExportForm input[name="summary"]').value = JSON.stringify(summary);
}

function exportData(type) {
    if (!results.length) {
        alert('Add at least one work item before exporting.');
        return;
    }
    if (type === 'excel') {
        document.getElementById('excelExportForm').submit();
    } else {
        document.getElementById('pdfExportForm').submit();
    }
}

function saveForFuture() {
    if (!results.length) {
        alert('Add at least one work item before saving.');
        return;
    }

    const name = prompt('Enter a name for this calculation set (e.g., Project A – Footing & Slab):');
    if (!name) {
        return;
    }

    // Ensure export payload is up to date
    renderSummary();

    const saveForm = document.getElementById('saveSetForm');
    saveForm.querySelector('input[name="name"]').value = name;
    saveForm.querySelector('input[name="calculations"]').value =
        document.querySelector('#excelExportForm input[name="calculations"]').value;
    saveForm.querySelector('input[name="summary"]').value =
        document.querySelector('#excelExportForm input[name="summary"]').value;

    saveForm.submit();
}

function showHistoryDetails(id) {
    const data = historySets[id];
    if (!data) return;

    const container = document.getElementById('historyDetails');
    if (!container) return;

    let html = `
        <div class="mb-3">
            <div class="fw-semibold">${data.name}</div>
            <div class="text-muted small">Saved by ${data.user} on ${data.created_at}</div>
        </div>
    `;

    if (data.summary?.totals) {
        html += '<h6>Totals</h6><ul>';
        Object.entries(data.summary.totals).forEach(([key, value]) => {
            html += `<li>${key.replace(/_/g,' ')}: <strong>${value}</strong></li>`;
        });
        html += '</ul>';
    }

    if (Array.isArray(data.calculations)) {
        html += '<h6 class="mt-3">Work Items</h6>';
        data.calculations.forEach(item => {
            html += `
                <div class="border rounded p-2 mb-2">
                    <div class="fw-semibold">${item.work_type ?? ''}</div>
                    <div class="text-muted small">${item.description ?? ''}</div>
                    ${item.notes ? `<div class="small text-info">${item.notes}</div>` : ''}
                    <div class="mt-2">
                        ${(item.materials ? Object.entries(item.materials).map(([k,v]) => `<div>${k.replace(/_/g,' ')}: <strong>${v}</strong></div>`).join('') : '')}
                    </div>
                </div>
            `;
        });
    }

    container.innerHTML = html;
    const modalEl = document.getElementById('historyModal');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
}
</script>
@endpush
@endsection

