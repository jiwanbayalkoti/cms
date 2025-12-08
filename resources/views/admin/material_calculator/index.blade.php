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
                                <div class="col-md-3">
                                    <label class="form-label">Length</label>
                                    <input type="number" step="0.01" min="0" id="concreteLength" class="form-control" placeholder="e.g., 5" />
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label">&nbsp;</label>
                                    <select id="concreteLengthUnit" class="form-select">
                                        <option value="m" selected>m</option>
                                        <option value="cm">cm</option>
                                        <option value="mm">mm</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Width</label>
                                    <input type="number" step="0.01" min="0" id="concreteWidth" class="form-control" placeholder="e.g., 0.6" />
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label">&nbsp;</label>
                                    <select id="concreteWidthUnit" class="form-select">
                                        <option value="m" selected>m</option>
                                        <option value="cm">cm</option>
                                        <option value="mm">mm</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Height / Depth</label>
                                    <input type="number" step="0.01" min="0" id="concreteDepth" class="form-control" placeholder="e.g., 0.5" />
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label">&nbsp;</label>
                                    <select id="concreteDepthUnit" class="form-select">
                                        <option value="m" selected>m</option>
                                        <option value="cm">cm</option>
                                        <option value="mm">mm</option>
                                    </select>
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
                                <div class="col-md-3">
                                    <label class="form-label">Wall Length</label>
                                    <input type="number" step="0.01" min="0" id="wallLength" class="form-control" placeholder="e.g., 6" />
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label">&nbsp;</label>
                                    <select id="wallLengthUnit" class="form-select">
                                        <option value="m" selected>m</option>
                                        <option value="cm">cm</option>
                                        <option value="mm">mm</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Wall Height</label>
                                    <input type="number" step="0.01" min="0" id="wallHeight" class="form-control" placeholder="e.g., 3" oninput="autoCalculateWallThickness()" />
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label">&nbsp;</label>
                                    <select id="wallHeightUnit" class="form-select" onchange="autoCalculateWallThickness()">
                                        <option value="m" selected>m</option>
                                        <option value="cm">cm</option>
                                        <option value="mm">mm</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Wall Thickness <small class="text-muted">(Auto: 50% of height)</small></label>
                                    <input type="number" step="0.01" min="0" id="wallThickness" class="form-control" placeholder="Auto-calculated" oninput="manualThicknessChange()" />
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label">&nbsp;</label>
                                    <select id="wallThicknessUnit" class="form-select">
                                        <option value="m" selected>m</option>
                                        <option value="cm">cm</option>
                                        <option value="mm">mm</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Type</label>
                                    <select id="masonryType" class="form-select">
                                        <option value="brick">Brick</option>
                                        <option value="stone">Stone</option>
                                    </select>
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
                                <div class="col-md-3">
                                    <label class="form-label">Surface Area</label>
                                    <input type="number" step="0.01" min="0" id="plasterArea" class="form-control" placeholder="e.g., 40" />
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label">&nbsp;</label>
                                    <select id="plasterAreaUnit" class="form-select">
                                        <option value="m2" selected>m²</option>
                                        <option value="ft2">ft²</option>
                                        <option value="cm2">cm²</option>
                                        <option value="mm2">mm²</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Thickness</label>
                                    <input type="number" step="0.1" min="0" id="plasterThickness" class="form-control" placeholder="e.g., 1.5" />
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label">&nbsp;</label>
                                    <select id="plasterThicknessUnit" class="form-select">
                                        <option value="cm" selected>cm</option>
                                        <option value="mm">mm</option>
                                        <option value="m">m</option>
                                    </select>
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
                                <div class="col-md-3">
                                    <label class="form-label">Area</label>
                                    <input type="number" step="0.01" min="0" id="solingArea" class="form-control" placeholder="e.g., 50" />
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label">&nbsp;</label>
                                    <select id="solingAreaUnit" class="form-select">
                                        <option value="m2" selected>m²</option>
                                        <option value="ft2">ft²</option>
                                        <option value="cm2">cm²</option>
                                        <option value="mm2">mm²</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Thickness</label>
                                    <input type="number" step="0.1" min="0" id="solingThickness" class="form-control" placeholder="e.g., 15" />
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label">&nbsp;</label>
                                    <select id="solingThicknessUnit" class="form-select">
                                        <option value="cm" selected>cm</option>
                                        <option value="mm">mm</option>
                                        <option value="m">m</option>
                                    </select>
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
                                <div class="col-md-3">
                                    <label class="form-label">Length per Bar</label>
                                    <input type="number" step="0.01" min="0" id="steelLength" class="form-control" placeholder="e.g., 6" />
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label">&nbsp;</label>
                                    <select id="steelLengthUnit" class="form-select">
                                        <option value="m" selected>m</option>
                                        <option value="cm">cm</option>
                                        <option value="mm">mm</option>
                                    </select>
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
                                <div class="col-md-3">
                                    <label class="form-label">Area</label>
                                    <input type="number" step="0.01" min="0" id="steelArea" class="form-control" placeholder="e.g., 50" />
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label">&nbsp;</label>
                                    <select id="steelAreaUnit" class="form-select">
                                        <option value="m2" selected>m²</option>
                                        <option value="ft2">ft²</option>
                                        <option value="cm2">cm²</option>
                                        <option value="mm2">mm²</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Spacing</label>
                                    <input type="number" step="0.5" min="0" id="steelSpacing" class="form-control" placeholder="e.g., 15" />
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label">&nbsp;</label>
                                    <select id="steelSpacingUnit" class="form-select">
                                        <option value="cm" selected>cm</option>
                                        <option value="mm">mm</option>
                                        <option value="m">m</option>
                                    </select>
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
            <button type="button" class="btn btn-outline-info btn-sm" onclick="loadMyHistory()">
                <i class="bi bi-clock-history me-1"></i> My History
            </button>
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
                        <th>Bricks / Stone</th>
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


<!-- History Modal -->
<div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">My Calculation History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="historyLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading your history...</p>
                </div>
                <div id="historyContent" style="display: none;">
                    <div id="historyList"></div>
                    <div id="historyDetails" style="display: none;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.value-display {
    font-size: 0.9em;
    line-height: 1.4;
}
.value-display .small {
    font-size: 0.75em;
    display: block;
}
.value-display strong {
    font-size: 1em;
}
</style>
@endpush

@push('scripts')
<script>
const concreteRatios = @json(collect($concreteGrades)->keyBy('value')->map(fn($grade) => $grade['ratio'])->toArray());
const mortarRatios = @json(collect($mortarMixes)->keyBy('value')->map(fn($mix) => $mix['ratio'])->toArray());
const defaultCosts = @json($defaultCosts);

const results = [];
let historySets = {};

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

// Auto-calculate wall thickness for masonry (50% of height)
let masonryThicknessManuallyChanged = false;
const wallHeightEl = document.getElementById('wallHeight');
const wallThicknessEl = document.getElementById('wallThickness');

if (wallHeightEl && wallThicknessEl) {
    wallHeightEl.addEventListener('input', function() {
        if (!masonryThicknessManuallyChanged) {
            const height = parseFloat(this.value);
            if (!isNaN(height) && height > 0) {
                wallThicknessEl.value = (height / 2).toFixed(2);
            } else if (height === 0 || isNaN(height)) {
                wallThicknessEl.value = '';
            }
        }
    });

    wallThicknessEl.addEventListener('input', function() {
        masonryThicknessManuallyChanged = true;
    });

    // Reset flag when work type changes
    document.getElementById('workType').addEventListener('change', function() {
        masonryThicknessManuallyChanged = false;
        if (this.value !== 'masonry') {
            masonryThicknessManuallyChanged = false;
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
    // Reset masonry thickness manual change flag
    masonryThicknessManuallyChanged = false;
}

function roundValue(value, precision = 2) {
    return Math.round((value + Number.EPSILON) * Math.pow(10, precision)) / Math.pow(10, precision);
}

// Unit conversion functions - convert to meters (for length) or square meters (for area)
function convertToMeters(value, unit) {
    if (!value || isNaN(value)) return 0;
    switch(unit) {
        case 'mm': return value / 1000;
        case 'cm': return value / 100;
        case 'm': return value;
        default: return value;
    }
}

function convertToSquareMeters(value, unit) {
    if (!value || isNaN(value)) return 0;
    switch(unit) {
        case 'mm2': return value / 1000000; // mm² to m²
        case 'cm2': return value / 10000; // cm² to m²
        case 'ft2': return value * 0.092903; // ft² to m² (1 ft² = 0.092903 m²)
        case 'm2': return value;
        default: return value;
    }
}

function getUnitLabel(unit) {
    const labels = {
        'mm': 'mm',
        'cm': 'cm',
        'm': 'm',
        'mm2': 'mm²',
        'cm2': 'cm²',
        'ft2': 'ft²',
        'm2': 'm²'
    };
    return labels[unit] || unit;
}

function formatValueWithRounding(exactValue, roundedValue, unit = '', precision = 2) {
    if (exactValue === null || exactValue === undefined || exactValue === '') return '-';
    const exactFormatted = typeof exactValue === 'number' ? exactValue.toFixed(5) : exactValue;
    const roundedFormatted = typeof roundedValue === 'number' ? roundedValue.toFixed(precision) : roundedValue;
    return `<div class="value-display">
                <span class="text-muted small">Exact: ${exactFormatted}${unit}</span>
                <br><strong>Rounded: ${roundedFormatted}${unit}</strong>
            </div>`;
}

function formatIntegerWithRounding(exactValue, roundedValue) {
    if (exactValue === null || exactValue === undefined || exactValue === '') return '-';
    return `<div class="value-display">
                <span class="text-muted small">Exact: ${exactValue.toFixed(2)}</span>
                <br><strong>Rounded: ${Math.ceil(roundedValue)}</strong>
            </div>`;
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
    const lengthValue = parseFloat(document.getElementById('concreteLength').value);
    const widthValue = parseFloat(document.getElementById('concreteWidth').value);
    const depthValue = parseFloat(document.getElementById('concreteDepth').value);
    const lengthUnit = document.getElementById('concreteLengthUnit').value;
    const widthUnit = document.getElementById('concreteWidthUnit').value;
    const depthUnit = document.getElementById('concreteDepthUnit').value;
    const grade = document.getElementById('concreteGrade').value;
    const element = document.getElementById('concreteElement').value;

    if ([lengthValue, widthValue, depthValue].some(v => !v || v <= 0)) {
        alert('Provide concrete dimensions (length, width, depth).');
        return null;
    }

    // Convert all to meters
    const length = convertToMeters(lengthValue, lengthUnit);
    const width = convertToMeters(widthValue, widthUnit);
    const depth = convertToMeters(depthValue, depthUnit);

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

    const description = `${element} (${lengthValue}${getUnitLabel(lengthUnit)} × ${widthValue}${getUnitLabel(widthUnit)} × ${depthValue}${getUnitLabel(depthUnit)}) - Grade ${grade}` + (label ? ` | ${label}` : '');

    return {
        id: Date.now(),
        work_type: `Concrete - ${element}`,
        description,
        notes,
        materials: {
            cement_bags: roundValue(cementBags),
            cement_bags_exact: cementBags,
            sand_m3: roundValue(sandVolume),
            sand_m3_exact: sandVolume,
            aggregate_m3: roundValue(aggregateVolume),
            aggregate_m3_exact: aggregateVolume,
            water_litres: roundValue(waterLitres),
            water_litres_exact: waterLitres,
            concrete_volume_m3: roundValue(volume),
            concrete_volume_m3_exact: volume,
        }
    };
}

function calculateMasonry(wastage, label, notes) {
    const lengthValue = parseFloat(document.getElementById('wallLength').value);
    const heightValue = parseFloat(document.getElementById('wallHeight').value);
    const thicknessValue = parseFloat(document.getElementById('wallThickness').value);
    const lengthUnit = document.getElementById('wallLengthUnit').value;
    const heightUnit = document.getElementById('wallHeightUnit').value;
    const thicknessUnit = document.getElementById('wallThicknessUnit').value;
    const mix = document.getElementById('masonryMortar').value;
    const type = document.getElementById('masonryType').value; // 'brick' or 'stone'

    if ([lengthValue, heightValue, thicknessValue].some(v => !v || v <= 0)) {
        alert('Provide wall length, height and thickness.');
        return null;
    }

    // Convert all to meters
    const length = convertToMeters(lengthValue, lengthUnit);
    const height = convertToMeters(heightValue, heightUnit);
    const thickness = convertToMeters(thicknessValue, thicknessUnit);

    const ratio = mortarRatios[mix] || [1,6];
    const totalParts = ratio[0] + ratio[1];
    const wallVolume = length * height * thickness;
    
    // Mortar calculation (same for both brick and stone)
    const mortarWetVolume = wallVolume * 0.25;
    const dryVolume = mortarWetVolume * 1.33 * (1 + wastage / 100);
    const cementVolume = dryVolume * (ratio[0] / totalParts);
    const sandVolume = dryVolume * (ratio[1] / totalParts);
    const cementBags = cementVolume / 0.035;

    const materials = {
        cement_bags: roundValue(cementBags),
        cement_bags_exact: cementBags,
        sand_m3: roundValue(sandVolume),
        sand_m3_exact: sandVolume,
        wall_volume_m3: roundValue(wallVolume),
        wall_volume_m3_exact: wallVolume,
    };

    let description = `Wall ${lengthValue}${getUnitLabel(lengthUnit)} × ${heightValue}${getUnitLabel(heightUnit)} × ${thicknessValue}${getUnitLabel(thicknessUnit)} | ${type === 'brick' ? 'Brick' : 'Stone'} | Mortar ${mix}`;
    if (label) {
        description += ` | ${label}`;
    }

    if (type === 'brick') {
        // Calculate number of bricks
        const brickVolume = 0.19 * 0.09 * 0.09; // Standard brick size: 19cm x 9cm x 9cm
        const bricksNeeded = (wallVolume / brickVolume) * 1.05; // 5% wastage
        materials.bricks_units = Math.ceil(bricksNeeded);
        materials.bricks_units_exact = bricksNeeded;
    } else if (type === 'stone') {
        // Calculate stone volume in cubic meters (wall volume minus mortar volume)
        const stoneVolume = wallVolume - mortarWetVolume; // Stone volume = total volume - mortar volume
        const stoneVolumeWithWastage = stoneVolume * (1 + wastage / 100);
        materials.stone_volume_m3 = roundValue(stoneVolumeWithWastage); // Apply wastage
        materials.stone_volume_m3_exact = stoneVolumeWithWastage;
    }

    return {
        id: Date.now(),
        work_type: 'Masonry / Wall',
        description,
        notes,
        materials,
        masonry_type: type, // Store type for display purposes
    };
}

function calculatePlaster(wastage, label, notes) {
    const areaValue = parseFloat(document.getElementById('plasterArea').value);
    const thicknessValue = parseFloat(document.getElementById('plasterThickness').value);
    const areaUnit = document.getElementById('plasterAreaUnit').value;
    const thicknessUnit = document.getElementById('plasterThicknessUnit').value;
    const mix = document.getElementById('plasterMortar').value;

    if (!areaValue || areaValue <= 0 || !thicknessValue || thicknessValue <= 0) {
        alert('Provide plaster area and thickness.');
        return null;
    }

    // Convert to square meters and meters
    const area = convertToSquareMeters(areaValue, areaUnit);
    const thicknessM = convertToMeters(thicknessValue, thicknessUnit);

    const ratio = mortarRatios[mix] || [1,4];
    const totalParts = ratio[0] + ratio[1];
    const wetVolume = area * thicknessM;
    const dryVolume = wetVolume * 1.33 * (1 + wastage / 100);
    const cementVolume = dryVolume * (ratio[0] / totalParts);
    const sandVolume = dryVolume * (ratio[1] / totalParts);
    const cementBags = cementVolume / 0.035;

    const description = `Plaster ${areaValue}${getUnitLabel(areaUnit)} @ ${thicknessValue}${getUnitLabel(thicknessUnit)} | Mix ${mix}` + (label ? ` | ${label}` : '');

    return {
        id: Date.now(),
        work_type: 'Plaster / Finish',
        description,
        notes,
        materials: {
            cement_bags: roundValue(cementBags),
            cement_bags_exact: cementBags,
            sand_m3: roundValue(sandVolume),
            sand_m3_exact: sandVolume,
            plaster_area_m2: roundValue(area),
            plaster_area_m2_exact: area,
        }
    };
}

function calculateSoling(wastage, label, notes) {
    const areaValue = parseFloat(document.getElementById('solingArea').value);
    const thicknessValue = parseFloat(document.getElementById('solingThickness').value);
    const areaUnit = document.getElementById('solingAreaUnit').value;
    const thicknessUnit = document.getElementById('solingThicknessUnit').value;
    const material = document.getElementById('solingMaterial').value;

    if (!areaValue || areaValue <= 0 || !thicknessValue || thicknessValue <= 0) {
        alert('Provide soling area and thickness.');
        return null;
    }

    // Convert to square meters and meters
    const area = convertToSquareMeters(areaValue, areaUnit);
    const thicknessM = convertToMeters(thicknessValue, thicknessUnit);
    const volume = area * thicknessM;
    const materials = {
        soling_material: material,
    };

    let description = `Soling ${areaValue}${getUnitLabel(areaUnit)} @ ${thicknessValue}${getUnitLabel(thicknessUnit)} | ${material}`;
    if (label) {
        description += ` | ${label}`;
    }

    if (material === 'brick') {
        // Calculate number of bricks for soling
        // Standard brick size: 19cm x 9cm x 9cm (0.19m x 0.09m x 0.09m)
        // For soling, bricks are laid flat (19cm x 9cm face)
        const brickArea = 0.19 * 0.09; // Area covered by one brick in m²
        const bricksNeeded = (area / brickArea) * (1 + wastage / 100);
        materials.bricks_units = Math.ceil(bricksNeeded);
        materials.bricks_units_exact = bricksNeeded;
        materials.soling_volume_m3 = roundValue(volume); // Keep volume for reference
        materials.soling_volume_m3_exact = volume;
    } else {
        // For other materials (gravel, stone, sand), calculate volume
        const volumeWithWastage = volume * (1 + wastage / 100);
        materials.soling_volume_m3 = roundValue(volumeWithWastage);
        materials.soling_volume_m3_exact = volumeWithWastage;
    }

    return {
        id: Date.now(),
        work_type: 'Soling / Base',
        description,
        notes,
        materials,
        soling_type: material, // Store material type for display
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
        const lengthPerBarValue = parseFloat(document.getElementById('steelLength').value);
        const lengthUnit = document.getElementById('steelLengthUnit').value;

        if (!bars || bars <= 0 || !lengthPerBarValue || lengthPerBarValue <= 0) {
            alert('Provide number of bars and length per bar.');
            return null;
        }

        // Convert to meters
        const lengthPerBar = convertToMeters(lengthPerBarValue, lengthUnit);
        numberOfBars = bars;
        totalLength = bars * lengthPerBar;
        description = `${diameter}mm bars × ${bars} nos × ${lengthPerBarValue}${getUnitLabel(lengthUnit)} | ${element}` + (label ? ` | ${label}` : '');
    } else if (method === 'area') {
        const areaValue = parseFloat(document.getElementById('steelArea').value);
        const spacingValue = parseFloat(document.getElementById('steelSpacing').value);
        const areaUnit = document.getElementById('steelAreaUnit').value;
        const spacingUnit = document.getElementById('steelSpacingUnit').value;
        const layers = parseFloat(document.getElementById('steelLayers').value) || 1;
        const lengthPerBarValue = parseFloat(document.getElementById('steelLength').value);
        const lengthUnit = document.getElementById('steelLengthUnit').value;

        if (!areaValue || areaValue <= 0 || !spacingValue || spacingValue <= 0) {
            alert('Provide area and spacing for area-based calculation.');
            return null;
        }

        // Convert to square meters and meters
        const area = convertToSquareMeters(areaValue, areaUnit);
        const spacingM = convertToMeters(spacingValue, spacingUnit);
        
        // Calculate number of bars based on area and spacing
        const barsPerDirection = Math.ceil(Math.sqrt(area) / spacingM) + 1; // Approximate calculation
        numberOfBars = barsPerDirection * layers;
        
        // If length per bar is provided, use it; otherwise estimate from area
        if (lengthPerBarValue && lengthPerBarValue > 0) {
            const lengthPerBar = convertToMeters(lengthPerBarValue, lengthUnit);
            totalLength = numberOfBars * lengthPerBar;
            description = `${diameter}mm bars | Area: ${areaValue}${getUnitLabel(areaUnit)} @ ${spacingValue}${getUnitLabel(spacingUnit)} spacing | ${layers} layer(s) | ${lengthPerBarValue}${getUnitLabel(lengthUnit)} per bar | ${element}` + (label ? ` | ${label}` : '');
        } else {
            // Estimate length from area (assuming square area)
            const estimatedLength = Math.sqrt(area);
            totalLength = numberOfBars * estimatedLength;
            description = `${diameter}mm bars | Area: ${areaValue}${getUnitLabel(areaUnit)} @ ${spacingValue}${getUnitLabel(spacingUnit)} spacing | ${layers} layer(s) | Est. ${estimatedLength.toFixed(2)}m per bar | ${element}` + (label ? ` | ${label}` : '');
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
            steel_kg_exact: totalWeight,
            steel_diameter_mm: diameter,
            steel_bars: Math.ceil(numberOfBars),
            steel_bars_exact: numberOfBars,
            steel_length_m: roundValue(totalLength),
            steel_length_m_exact: totalLength,
            steel_element: element,
        }
    };
}

function getBricksStoneDisplay(item) {
    // Handle masonry types
    if (item.masonry_type === 'stone') {
        if (item.materials.stone_volume_m3_exact !== undefined) {
            return formatValueWithRounding(item.materials.stone_volume_m3_exact, item.materials.stone_volume_m3, ' m³', 3);
        }
        return item.materials.stone_volume_m3 ? item.materials.stone_volume_m3 + ' m³' : '-';
    }
    
    // Handle soling brick or masonry brick
    if (item.materials.bricks_units_exact !== undefined) {
        return formatIntegerWithRounding(item.materials.bricks_units_exact, item.materials.bricks_units);
    }
    
    // Default: show bricks_units if available
    return item.materials.bricks_units ?? '-';
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
        stone: (materials.stone_volume_m3 || 0) * (unitCosts.stone_m3 || 0), // Stone cost per m³
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
        cement_bags_exact: 0,
        sand_m3: 0,
        sand_m3_exact: 0,
        aggregate_m3: 0,
        aggregate_m3_exact: 0,
        bricks_units: 0,
        bricks_units_exact: 0,
        stone_volume_m3: 0,
        stone_volume_m3_exact: 0,
        water_litres: 0,
        water_litres_exact: 0,
        soling_volume_m3: 0,
        soling_volume_m3_exact: 0,
        steel_kg: 0,
        steel_kg_exact: 0,
        total_cost: 0,
        total_cost_exact: 0,
    };

    results.forEach((item, index) => {
        const cost = calculateCost(item.materials, unitCosts);
        
        // Sum exact values
        totals.cement_bags_exact += item.materials.cement_bags_exact || item.materials.cement_bags || 0;
        totals.sand_m3_exact += item.materials.sand_m3_exact || item.materials.sand_m3 || 0;
        totals.aggregate_m3_exact += item.materials.aggregate_m3_exact || item.materials.aggregate_m3 || 0;
        totals.bricks_units_exact += item.materials.bricks_units_exact || item.materials.bricks_units || 0;
        totals.stone_volume_m3_exact += item.materials.stone_volume_m3_exact || item.materials.stone_volume_m3 || 0;
        totals.water_litres_exact += item.materials.water_litres_exact || item.materials.water_litres || 0;
        totals.soling_volume_m3_exact += item.materials.soling_volume_m3_exact || item.materials.soling_volume_m3 || 0;
        totals.steel_kg_exact += item.materials.steel_kg_exact || item.materials.steel_kg || 0;
        totals.total_cost_exact += cost.total;
        
        // Sum rounded values (for display - calculated from exact values at the end)
        totals.cement_bags += item.materials.cement_bags || 0;
        totals.sand_m3 += item.materials.sand_m3 || 0;
        totals.aggregate_m3 += item.materials.aggregate_m3 || 0;
        totals.bricks_units += item.materials.bricks_units || 0;
        totals.stone_volume_m3 += item.materials.stone_volume_m3 || 0;
        totals.water_litres += item.materials.water_litres || 0;
        totals.soling_volume_m3 += item.materials.soling_volume_m3 || 0;
        totals.steel_kg += item.materials.steel_kg || 0;
        totals.total_cost += roundValue(cost.total, 2);

        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${index + 1}</td>
            <td>
                <div class="fw-semibold">${item.work_type}</div>
                <div class="small text-muted">${item.description}</div>
                ${item.notes ? `<div class="small text-info">${item.notes}</div>` : ''}
            </td>
            <td>${item.materials.cement_bags_exact !== undefined ? formatValueWithRounding(item.materials.cement_bags_exact, item.materials.cement_bags, ' bags', 2) : (item.materials.cement_bags ?? '-')}</td>
            <td>${item.materials.sand_m3_exact !== undefined ? formatValueWithRounding(item.materials.sand_m3_exact, item.materials.sand_m3, ' m³', 3) : (item.materials.sand_m3 ?? '-')}</td>
            <td>${item.materials.aggregate_m3_exact !== undefined ? formatValueWithRounding(item.materials.aggregate_m3_exact, item.materials.aggregate_m3, ' m³', 3) : (item.materials.aggregate_m3 ?? '-')}</td>
            <td>${getBricksStoneDisplay(item)}</td>
            <td>${item.materials.water_litres_exact !== undefined ? formatValueWithRounding(item.materials.water_litres_exact, item.materials.water_litres, ' L', 2) : (item.materials.water_litres ?? '-')}</td>
            <td>${item.materials.soling_volume_m3_exact !== undefined ? formatValueWithRounding(item.materials.soling_volume_m3_exact, item.materials.soling_volume_m3, ' m³', 3) : (item.materials.soling_volume_m3 ? (item.materials.soling_volume_m3 + ' m³') : '-')}</td>
            <td>${item.materials.steel_kg_exact !== undefined ? formatValueWithRounding(item.materials.steel_kg_exact, item.materials.steel_kg, ' kg', 2) : (item.materials.steel_kg ?? '-')}</td>
            <td>${cost.total ? formatValueWithRounding(cost.total, roundValue(cost.total, 2), ' ' + currencySymbol, 2) : '-'}</td>
            <td class="text-end">
                <button class="btn btn-sm btn-link text-danger" onclick="removeItem(${item.id})">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });

    // Display totals with exact and rounded values
    document.getElementById('totalCement').innerHTML = formatValueWithRounding(totals.cement_bags_exact, roundValue(totals.cement_bags_exact, 2), ' bags', 2);
    document.getElementById('totalSand').innerHTML = formatValueWithRounding(totals.sand_m3_exact, roundValue(totals.sand_m3_exact, 3), ' m³', 3);
    document.getElementById('totalAggregate').innerHTML = formatValueWithRounding(totals.aggregate_m3_exact, roundValue(totals.aggregate_m3_exact, 3), ' m³', 3);
    
    // Display bricks or stone in the same column
    let bricksText = '';
    let stoneText = '';
    if (totals.bricks_units_exact > 0) {
        bricksText = formatIntegerWithRounding(totals.bricks_units_exact, totals.bricks_units_exact);
    }
    if (totals.stone_volume_m3_exact > 0) {
        stoneText = formatValueWithRounding(totals.stone_volume_m3_exact, roundValue(totals.stone_volume_m3_exact, 3), ' m³', 3);
    }
    document.getElementById('totalBricks').innerHTML = bricksText + (bricksText && stoneText ? '<br> / <br>' : '') + stoneText || '-';
    
    document.getElementById('totalWater').innerHTML = formatValueWithRounding(totals.water_litres_exact, roundValue(totals.water_litres_exact, 2), ' L', 2);
    document.getElementById('totalSoling').innerHTML = formatValueWithRounding(totals.soling_volume_m3_exact, roundValue(totals.soling_volume_m3_exact, 3), ' m³', 3);
    document.getElementById('totalSteel').innerHTML = formatValueWithRounding(totals.steel_kg_exact, roundValue(totals.steel_kg_exact, 2), ' kg', 2);
    document.getElementById('totalCost').innerHTML = formatValueWithRounding(totals.total_cost_exact, roundValue(totals.total_cost_exact, 2), ' ' + currencySymbol, 2);
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
            stone_volume_m3: roundValue(totals.stone_volume_m3 || 0),
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

function loadMyHistory() {
    const modalEl = document.getElementById('historyModal');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();

    // Show loading, hide content
    document.getElementById('historyLoading').style.display = 'block';
    document.getElementById('historyContent').style.display = 'none';
    document.getElementById('historyList').innerHTML = '';
    document.getElementById('historyDetails').style.display = 'none';

    fetch('{{ route("admin.material-calculator.my-history") }}')
        .then(response => response.json())
        .then(data => {
            historySets = data.history || {};
            renderHistoryList(data.history || {}, data.count || 0);
        })
        .catch(error => {
            console.error('Error loading history:', error);
            document.getElementById('historyLoading').innerHTML = 
                '<div class="alert alert-danger">Error loading history. Please try again.</div>';
        });
}

function renderHistoryList(history, count) {
    const listContainer = document.getElementById('historyList');
    
    if (count === 0) {
        listContainer.innerHTML = '<div class="alert alert-info text-center">No saved calculations found.</div>';
        document.getElementById('historyLoading').style.display = 'none';
        document.getElementById('historyContent').style.display = 'block';
        return;
    }

    let html = `
        <div class="mb-3">
            <p class="text-muted">You have <strong>${count}</strong> saved calculation${count !== 1 ? 's' : ''}.</p>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Items</th>
                        <th>Saved On</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
    `;

    let index = 1;
    Object.entries(history).forEach(([id, data]) => {
        html += `
            <tr>
                <td>${index++}</td>
                <td><strong>${data.name}</strong></td>
                <td>${Array.isArray(data.calculations) ? data.calculations.length : 0} item(s)</td>
                <td><small class="text-muted">${data.created_at}</small></td>
                <td class="text-end">
                    <button class="btn btn-sm btn-outline-primary" onclick="showHistoryDetails(${id})">
                        <i class="bi bi-eye me-1"></i> View Details
                    </button>
                </td>
            </tr>
        `;
    });

    html += `
                </tbody>
            </table>
        </div>
    `;

    listContainer.innerHTML = html;
    document.getElementById('historyLoading').style.display = 'none';
    document.getElementById('historyContent').style.display = 'block';
}

function showHistoryDetails(id) {
    const data = historySets[id];
    if (!data) {
        alert('History details not found.');
        return;
    }

    const detailsContainer = document.getElementById('historyDetails');
    const listContainer = document.getElementById('historyList');
    
    // Hide list, show details
    listContainer.style.display = 'none';
    detailsContainer.style.display = 'block';

    let html = `
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <button class="btn btn-sm btn-outline-secondary" onclick="backToHistoryList()">
                    <i class="bi bi-arrow-left me-1"></i> Back to List
                </button>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">${data.name}</h6>
                <small class="text-muted">Saved on ${data.created_at}</small>
            </div>
            <div class="card-body">
    `;

    if (data.summary?.totals) {
        html += '<h6 class="mb-3">Summary Totals</h6>';
        html += '<div class="row g-3">';
        Object.entries(data.summary.totals).forEach(([key, value]) => {
            const label = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            html += `
                <div class="col-md-4">
                    <div class="border rounded p-2">
                        <small class="text-muted d-block">${label}</small>
                        <strong>${typeof value === 'number' ? value.toFixed(2) : value}</strong>
                    </div>
                </div>
            `;
        });
        html += '</div>';
    }

    if (data.summary?.unit_costs) {
        html += '<h6 class="mt-4 mb-3">Unit Costs</h6>';
        html += '<div class="row g-3">';
        Object.entries(data.summary.unit_costs).forEach(([key, value]) => {
            const label = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            if (value > 0) {
                html += `
                    <div class="col-md-4">
                        <div class="border rounded p-2">
                            <small class="text-muted d-block">${label}</small>
                            <strong>${value.toFixed(2)}</strong>
                        </div>
                    </div>
                `;
            }
        });
        html += '</div>';
    }

    html += '</div></div>';

    if (Array.isArray(data.calculations) && data.calculations.length > 0) {
        html += '<h6 class="mt-4 mb-3">Work Items</h6>';
        data.calculations.forEach((item, idx) => {
            html += `
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <strong>#${idx + 1} - ${item.work_type || 'N/A'}</strong>
                            </div>
                        </div>
                        <div class="text-muted small mb-2">${item.description || ''}</div>
                        ${item.notes ? `<div class="alert alert-info py-2 px-3 mb-2"><small>${item.notes}</small></div>` : ''}
                        <div class="row g-2 mt-2">
            `;
            if (item.materials) {
                Object.entries(item.materials).forEach(([k, v]) => {
                    const label = k.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                    html += `
                        <div class="col-md-4">
                            <small class="text-muted d-block">${label}</small>
                            <strong>${typeof v === 'number' ? v.toFixed(2) : v}</strong>
                        </div>
                    `;
                });
            }
            if (item.cost) {
                html += '<div class="col-12 mt-2"><hr></div>';
                html += '<div class="col-12"><strong>Cost Breakdown:</strong></div>';
                Object.entries(item.cost).forEach(([k, v]) => {
                    if (k !== 'total' && v > 0) {
                        const label = k.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                        html += `
                            <div class="col-md-4">
                                <small class="text-muted d-block">${label}</small>
                                <strong>${typeof v === 'number' ? v.toFixed(2) : v}</strong>
                            </div>
                        `;
                    }
                });
                if (item.cost.total) {
                    html += `
                        <div class="col-12 mt-2">
                            <div class="alert alert-success py-2 px-3 mb-0">
                                <strong>Total Cost: ${item.cost.total.toFixed(2)}</strong>
                            </div>
                        </div>
                    `;
                }
            }
            html += '</div></div></div>';
        });
    }

    detailsContainer.innerHTML = html;
}

function backToHistoryList() {
    document.getElementById('historyList').style.display = 'block';
    document.getElementById('historyDetails').style.display = 'none';
    document.getElementById('historyDetails').innerHTML = '';
}
</script>
@endpush
@endsection

