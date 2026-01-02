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
                                    <option value="brick_soling">Brick Soling</option>
                                    <option value="stone_soling">Stone Soling</option>
                                    <option value="pcc_soling">PCC Soling</option>
                                    <option value="plaster">Plaster</option>
                                    <option value="concrete">Concrete</option>
                                    <option value="floor_slab">Floor Slab</option>
                                    <option value="footing">Footing (Multiple Sizes)</option>
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

                        {{-- Brick Soling Inputs --}}
                        <div class="mt-4 d-none" data-section="brick_soling">
                            <h6 class="fw-semibold text-primary">Brick Soling</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Area (m²)</label>
                                    <input type="number" step="0.01" min="0" id="brickSolingArea" class="form-control" placeholder="e.g., 7.4" />
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Brick Size (mm)</label>
                                    <select id="brickSize" class="form-select">
                                        <option value="230x110x75">230 × 110 × 75 mm (Standard)</option>
                                        <option value="230x110x75">Custom</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Sand for Joint Filling</label>
                                    <select id="brickSolingSand" class="form-select">
                                        <option value="yes" selected>Yes</option>
                                        <option value="no">No</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Stone Soling Inputs --}}
                        <div class="mt-4 d-none" data-section="stone_soling">
                            <h6 class="fw-semibold text-primary">Stone Soling</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Area (m²)</label>
                                    <input type="number" step="0.01" min="0" id="stoneSolingArea" class="form-control" placeholder="e.g., 50" />
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Thickness (cm)</label>
                                    <input type="number" step="0.1" min="0" id="stoneSolingThickness" class="form-control" placeholder="e.g., 15" />
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Sand for Joint Filling</label>
                                    <select id="stoneSolingSand" class="form-select">
                                        <option value="yes" selected>Yes</option>
                                        <option value="no">No</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- PCC Soling Inputs --}}
                        <div class="mt-4 d-none" data-section="pcc_soling">
                            <h6 class="fw-semibold text-primary">PCC Soling</h6>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Area (m²)</label>
                                    <input type="number" step="0.01" min="0" id="pccSolingArea" class="form-control" placeholder="e.g., 50" />
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Thickness (cm)</label>
                                    <input type="number" step="0.1" min="0" id="pccSolingThickness" class="form-control" placeholder="e.g., 10" />
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Concrete Grade</label>
                                    <select id="pccSolingGrade" class="form-select">
                                        <option value="M10">M10 (1:3:6)</option>
                                        <option value="M15" selected>M15 (1:2:4)</option>
                                        <option value="M20">M20 (1:1.5:3)</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Cement-Sand-Aggregate Ratio</label>
                                    <input type="text" id="pccSolingRatio" class="form-control" placeholder="e.g., 1:2:4" value="1:2:4" readonly>
                                </div>
                            </div>
                        </div>

                        {{-- Footing Inputs (Multiple Sizes) --}}
                        <div class="mt-4 d-none" data-section="footing">
                            <h6 class="fw-semibold text-primary">Footing Calculation (Multiple Sizes)</h6>
                            <div class="alert alert-info mb-3">
                                <i class="bi bi-info-circle me-2"></i>
                                Add different footing sizes (F1, F2, F3, etc.) with their dimensions and quantities.
                            </div>
                            
                            <div id="footingList" class="mb-3">
                                <!-- Footing items will be added here dynamically -->
                            </div>
                            
                            <button type="button" class="btn btn-sm btn-primary mb-3" onclick="addFootingItem()">
                                <i class="bi bi-plus-circle me-1"></i> Add Footing Size
                            </button>
                            
                            <div class="row g-3 mt-3">
                                <div class="col-md-6">
                                    <label class="form-label">Concrete Grade</label>
                                    <select id="footingGrade" class="form-select">
                                        @foreach($concreteGrades as $grade)
                                            <option value="{{ $grade['value'] }}">{{ $grade['label'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Soling Inputs (Legacy) --}}
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
                            <select id="currency" class="form-select" onchange="renderSummary(); renderToolsMachines();">
                                <option value="NPR">NPR (Rs)</option>
                                <option value="INR">INR (₹)</option>
                                <option value="USD">USD ($)</option>
                                <option value="EUR">EUR (€)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cement / bag</label>
                            <input type="number" min="0" step="0.01" class="form-control cost-input" data-key="cement_bag" placeholder="0.00" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Sand / m³</label>
                            <input type="number" min="0" step="0.01" class="form-control cost-input" data-key="sand_m3" placeholder="0.00" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Aggregate / Stone / m³</label>
                            <input type="number" min="0" step="0.01" class="form-control cost-input" data-key="aggregate_m3" placeholder="0.00" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Water / litre</label>
                            <input type="number" min="0" step="0.01" class="form-control cost-input" data-key="water_litre" placeholder="0.00" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Brick / block</label>
                            <input type="number" min="0" step="0.01" class="form-control cost-input" data-key="brick_unit" placeholder="0.00" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Soling / m³</label>
                            <input type="number" min="0" step="0.01" class="form-control cost-input" data-key="soling_m3" placeholder="0.00" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Steel / Rod (per kg)</label>
                            <input type="number" min="0" step="0.01" class="form-control cost-input" data-key="steel_kg" placeholder="0.00" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Skilled Labor Rate (per day)</label>
                            <input type="number" min="0" step="0.01" class="form-control cost-input" data-key="skilled_labor_day" placeholder="0.00" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Unskilled Labor Rate (per day)</label>
                            <input type="number" min="0" step="0.01" class="form-control cost-input" data-key="unskilled_labor_day" placeholder="0.00" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Labour Cost (per m³) <small class="text-muted">(Optional - for backward compatibility)</small></label>
                            <input type="number" min="0" step="0.01" class="form-control cost-input" data-key="labour_m3" placeholder="0.00" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Labour Cost (per m²) <small class="text-muted">(Optional - for backward compatibility)</small></label>
                            <input type="number" min="0" step="0.01" class="form-control cost-input" data-key="labour_m2" placeholder="0.00" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Centering Rate (per m²)</label>
                            <input type="number" min="0" step="0.01" class="form-control cost-input" data-key="centering_m2" placeholder="0.00" value="0">
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
                        <th>Labor (days)</th>
                        <th>Cost</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="no-data-row">
                        <td colspan="12" class="text-center text-muted py-4">
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
                        <td id="totalLabor">0</td>
                        <td id="totalCost">0</td>
                        <td></td>
                    </tr>
                    <tr class="fw-semibold table-secondary d-none" id="toolsMachinesCostRow">
                        <td colspan="10" class="text-end">Tools & Machines Cost:</td>
                        <td id="toolsMachinesCostInTable">0</td>
                        <td></td>
                    </tr>
                    <tr class="fw-bold table-success d-none" id="grandTotalRow">
                        <td colspan="10" class="text-end">Grand Total:</td>
                        <td id="grandTotalCost">0</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Tools and Machines Section -->
<div class="card shadow-sm mt-4">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0">Tools & Machines <small class="text-white-50">(Optional)</small></h5>
    </div>
    <div class="card-body">
        <form id="toolMachineForm" onsubmit="event.preventDefault(); addToolMachine();">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Equipment Name</label>
                    <input type="text" id="equipmentName" class="form-control" placeholder="e.g., Excavator, Mixer, Crane" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Rental Rate</label>
                    <div class="input-group">
                        <input type="number" step="0.01" min="0" id="rentalRate" class="form-control" placeholder="0.00">
                        <select id="rentalUnit" class="form-select" style="max-width: 80px;">
                            <option value="day">/day</option>
                            <option value="hour">/hour</option>
                            <option value="week">/week</option>
                            <option value="month">/month</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Usage Duration</label>
                    <input type="number" step="0.01" min="0" id="usageDuration" class="form-control" placeholder="0" value="1">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Fuel Cost</label>
                    <div class="input-group">
                        <input type="number" step="0.01" min="0" id="fuelCost" class="form-control" placeholder="0.00">
                        <select id="fuelUnit" class="form-select" style="max-width: 80px;">
                            <option value="total">Total</option>
                            <option value="day">/day</option>
                            <option value="hour">/hour</option>
                            <option value="litre">/litre</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Operator Cost</label>
                    <div class="input-group">
                        <input type="number" step="0.01" min="0" id="operatorCost" class="form-control" placeholder="0.00">
                        <select id="operatorUnit" class="form-select" style="max-width: 80px;">
                            <option value="day">/day</option>
                            <option value="hour">/hour</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-1">
                    <label class="form-label fw-semibold">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">Add</button>
                </div>
            </div>
            <div class="row g-3 mt-2">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Maintenance/Repair Cost</label>
                    <input type="number" step="0.01" min="0" id="maintenanceCost" class="form-control" placeholder="0.00">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Transportation Cost</label>
                    <input type="number" step="0.01" min="0" id="transportCost" class="form-control" placeholder="0.00">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Other Costs</label>
                    <input type="number" step="0.01" min="0" id="otherCost" class="form-control" placeholder="0.00">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Notes (optional)</label>
                    <input type="text" id="equipmentNotes" class="form-control" placeholder="Any remarks">
                </div>
            </div>
        </form>

        <div class="table-responsive mt-4">
            <table class="table table-sm table-hover align-middle mb-0" id="toolsMachinesTable">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Equipment</th>
                        <th>Rental</th>
                        <th>Fuel</th>
                        <th>Operator</th>
                        <th>Maintenance</th>
                        <th>Transport</th>
                        <th>Other</th>
                        <th>Total Cost</th>
                        <th>Notes</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="no-tools-row">
                        <td colspan="11" class="text-center text-muted py-4">
                            No tools or machines added yet.
                        </td>
                    </tr>
                </tbody>
                <tfoot class="table-light d-none" id="toolsFooter">
                    <tr class="fw-semibold table-info">
                        <td colspan="8">Total Tools & Machines Cost</td>
                        <td id="totalToolsCost" class="fw-bold">0</td>
                        <td colspan="2"></td>
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
const toolsMachines = [];
let historySets = {};
// Footing management variables
let footingItems = [];
let footingItemCounter = 0;

document.getElementById('workType').addEventListener('change', (event) => {
    const value = event.target.value;
    document.querySelectorAll('[data-section]').forEach(section => {
        const sectionType = section.getAttribute('data-section');
        // Map work types to sections
        const sectionMap = {
            'brick_soling': 'brick_soling',
            'stone_soling': 'stone_soling',
            'pcc_soling': 'pcc_soling',
            'plaster': 'plaster',
            'concrete': 'concrete',
            'floor_slab': 'concrete', // Floor slab uses concrete section
            'footing': 'footing',
        };
        
        if (sectionMap[value] === sectionType || (value === 'soling' && sectionType === 'soling')) {
            section.classList.remove('d-none');
            // Initialize footing section if selected
            if (value === 'footing' && sectionType === 'footing') {
                setTimeout(() => {
                    if (footingItems.length === 0 && typeof addFootingItem === 'function') {
                        addFootingItem(); // Add first footing item automatically
                    }
                }, 100);
            }
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

// Update PCC soling ratio display when grade changes
const pccSolingGradeEl = document.getElementById('pccSolingGrade');
const pccSolingRatioEl = document.getElementById('pccSolingRatio');
if (pccSolingGradeEl && pccSolingRatioEl) {
    pccSolingGradeEl.addEventListener('change', (event) => {
        const grade = event.target.value;
        const ratio = concreteRatios[grade] || [1, 2, 4];
        pccSolingRatioEl.value = `${ratio[0]}:${ratio[1]}:${ratio[2]}`;
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

// Initialize cost inputs with default values if empty
document.querySelectorAll('.cost-input').forEach(input => {
    const key = input.dataset.key;
    const currentValue = parseFloat(input.value) || 0;
    
    // If input is empty or 0, and defaultCosts has a value for this key, set it
    if ((!input.value || currentValue === 0) && defaultCosts[key] && defaultCosts[key] > 0) {
        input.value = defaultCosts[key];
    }
    
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
    // Reset footing items
    footingItems = [];
    footingItemCounter = 0;
    const footingList = document.getElementById('footingList');
    if (footingList) {
        footingList.innerHTML = '';
    }
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

    if (type === 'concrete' || type === 'floor_slab') {
        calculation = calculateConcrete(wastage, label, notes, type === 'floor_slab');
    } else if (type === 'masonry') {
        calculation = calculateMasonry(wastage, label, notes);
    } else if (type === 'plaster') {
        calculation = calculatePlaster(wastage, label, notes);
    } else if (type === 'brick_soling') {
        calculation = calculateBrickSoling(wastage, label, notes);
    } else if (type === 'stone_soling') {
        calculation = calculateStoneSoling(wastage, label, notes);
    } else if (type === 'pcc_soling') {
        calculation = calculatePCCSoling(wastage, label, notes);
    } else if (type === 'footing') {
        calculation = calculateFooting(wastage, label, notes);
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

function calculateConcrete(wastage, label, notes, isFloorSlab = false) {
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

    // Calculate formwork area for centering (assuming all sides except bottom)
    // For rectangular elements: 2 × (length × depth + width × depth) + (length × width) [top]
    const formworkArea = 2 * (length * depth + width * depth) + (length * width);

    // Labor calculation: approximately 0.5 man-day per m³ for skilled, 1.0 man-day per m³ for unskilled
    const skilledManDays = volume * 0.5;
    const unskilledManDays = volume * 1.0;

    const workTypeLabel = isFloorSlab ? 'Floor Slab' : `Concrete - ${element}`;

    return {
        id: Date.now(),
        work_type: workTypeLabel,
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
            formwork_area_m2: roundValue(formworkArea),
            formwork_area_m2_exact: formworkArea,
            skilled_labor_days: roundValue(skilledManDays, 2),
            unskilled_labor_days: roundValue(unskilledManDays, 2),
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

    // Labor calculation: approximately 0.15 man-day per m² for skilled, 0.3 man-day per m² for unskilled
    const skilledManDays = area * 0.15;
    const unskilledManDays = area * 0.3;

    return {
        id: Date.now(),
        work_type: 'Plaster',
        description,
        notes,
        materials: {
            cement_bags: roundValue(cementBags),
            cement_bags_exact: cementBags,
            sand_m3: roundValue(sandVolume),
            sand_m3_exact: sandVolume,
            plaster_area_m2: roundValue(area),
            plaster_area_m2_exact: area,
            skilled_labor_days: roundValue(skilledManDays, 2),
            unskilled_labor_days: roundValue(unskilledManDays, 2),
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

// Calculate Brick Soling (Nepali style)
function calculateBrickSoling(wastage, label, notes) {
    const area = parseFloat(document.getElementById('brickSolingArea').value);
    const brickSize = document.getElementById('brickSize').value;
    const useSand = document.getElementById('brickSolingSand').value === 'yes';

    if (!area || area <= 0) {
        alert('Provide brick soling area in m².');
        return null;
    }

    // Standard brick size: 230mm x 110mm x 75mm
    // For soling, bricks are laid flat (230mm x 110mm face)
    const brickArea = 0.23 * 0.11; // Area covered by one brick in m²
    const bricksNeeded = (area / brickArea) * (1 + wastage / 100);
    
    // Sand for joint filling (approximately 0.15 m³ per 50 m² or 0.003 m³ per m²)
    const sandVolume = useSand ? (area * 0.003) * (1 + wastage / 100) : 0;

    // Labor calculation: approximately 0.25 man-day per m² for skilled, 0.5 man-day per m² for unskilled
    const skilledManDays = area * 0.25;
    const unskilledManDays = area * 0.5;

    const description = `Brick Soling ${area} m²` + (label ? ` | ${label}` : '');

    return {
        id: Date.now(),
        work_type: 'Brick Soling',
        description,
        notes,
        materials: {
            bricks_units: Math.ceil(bricksNeeded),
            bricks_units_exact: bricksNeeded,
            sand_m3: roundValue(sandVolume),
            sand_m3_exact: sandVolume,
            soling_area_m2: roundValue(area),
            soling_area_m2_exact: area,
            skilled_labor_days: roundValue(skilledManDays, 2),
            unskilled_labor_days: roundValue(unskilledManDays, 2),
        }
    };
}

// Calculate Stone Soling (Nepali style)
function calculateStoneSoling(wastage, label, notes) {
    const area = parseFloat(document.getElementById('stoneSolingArea').value);
    const thickness = parseFloat(document.getElementById('stoneSolingThickness').value) / 100; // Convert cm to m
    const useSand = document.getElementById('stoneSolingSand').value === 'yes';

    if (!area || area <= 0 || !thickness || thickness <= 0) {
        alert('Provide stone soling area and thickness.');
        return null;
    }

    const volume = area * thickness;
    const volumeWithWastage = volume * (1 + wastage / 100);
    
    // Sand for joint filling (approximately 0.2 m³ per m³ of stone)
    const sandVolume = useSand ? (volume * 0.2) * (1 + wastage / 100) : 0;

    // Labor calculation: approximately 0.3 man-day per m² for skilled, 0.6 man-day per m² for unskilled
    const skilledManDays = area * 0.3;
    const unskilledManDays = area * 0.6;

    const description = `Stone Soling ${area} m² @ ${(thickness * 100).toFixed(1)} cm` + (label ? ` | ${label}` : '');

    return {
        id: Date.now(),
        work_type: 'Stone Soling',
        description,
        notes,
        materials: {
            stone_volume_m3: roundValue(volumeWithWastage),
            stone_volume_m3_exact: volumeWithWastage,
            sand_m3: roundValue(sandVolume),
            sand_m3_exact: sandVolume,
            soling_area_m2: roundValue(area),
            soling_area_m2_exact: area,
            soling_volume_m3: roundValue(volume),
            soling_volume_m3_exact: volume,
            skilled_labor_days: roundValue(skilledManDays, 2),
            unskilled_labor_days: roundValue(unskilledManDays, 2),
        }
    };
}

// Calculate PCC Soling (Nepali style)
function calculatePCCSoling(wastage, label, notes) {
    const area = parseFloat(document.getElementById('pccSolingArea').value);
    const thickness = parseFloat(document.getElementById('pccSolingThickness').value) / 100; // Convert cm to m
    const grade = document.getElementById('pccSolingGrade').value;

    if (!area || area <= 0 || !thickness || thickness <= 0) {
        alert('Provide PCC soling area and thickness.');
        return null;
    }

    const volume = area * thickness;
    const ratio = concreteRatios[grade] || [1, 2, 4];
    const totalParts = ratio[0] + ratio[1] + ratio[2];
    const dryVolume = volume * 1.54;
    const wastageFactor = 1 + wastage / 100;

    const cementVolume = dryVolume * (ratio[0] / totalParts) * wastageFactor;
    const sandVolume = dryVolume * (ratio[1] / totalParts) * wastageFactor;
    const aggregateVolume = dryVolume * (ratio[2] / totalParts) * wastageFactor;
    const cementBags = cementVolume / 0.035;
    const waterLitres = cementBags * 50 * 0.5;

    // Labor calculation: approximately 0.4 man-day per m² for skilled, 0.8 man-day per m² for unskilled
    const skilledManDays = area * 0.4;
    const unskilledManDays = area * 0.8;

    const description = `PCC Soling ${area} m² @ ${(thickness * 100).toFixed(1)} cm - Grade ${grade}` + (label ? ` | ${label}` : '');

    return {
        id: Date.now(),
        work_type: 'PCC Soling',
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
            soling_area_m2: roundValue(area),
            soling_area_m2_exact: area,
            skilled_labor_days: roundValue(skilledManDays, 2),
            unskilled_labor_days: roundValue(unskilledManDays, 2),
        }
    };
}

// Footing management functions
function addFootingItem() {
    footingItemCounter++;
    const itemId = `footing_${footingItemCounter}`;
    const item = {
        id: itemId,
        label: `F${footingItemCounter}`,
        length: '',
        width: '',
        depth: '',
        quantity: 1,
        lengthUnit: 'm',
        widthUnit: 'm',
        depthUnit: 'm'
    };
    footingItems.push(item);
    renderFootingItems();
}

function removeFootingItem(itemId) {
    footingItems = footingItems.filter(item => item.id !== itemId);
    renderFootingItems();
}

function renderFootingItems() {
    const container = document.getElementById('footingList');
    if (!container) return;
    
    if (footingItems.length === 0) {
        container.innerHTML = '<div class="alert alert-secondary mb-0">No footing sizes added yet. Click "Add Footing Size" to add.</div>';
        return;
    }
    
    container.innerHTML = footingItems.map((item, index) => {
        return `
            <div class="card mb-3" data-footing-id="${item.id}">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <strong>${item.label}</strong>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeFootingItem('${item.id}')">
                        <i class="bi bi-trash"></i> Remove
                    </button>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label">Label</label>
                            <input type="text" class="form-control footing-label" data-id="${item.id}" value="${item.label}" placeholder="F1">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Length</label>
                            <input type="number" step="0.01" min="0" class="form-control footing-length" data-id="${item.id}" value="${item.length}" placeholder="e.g., 1.5">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <select class="form-select footing-length-unit" data-id="${item.id}">
                                <option value="m" ${item.lengthUnit === 'm' ? 'selected' : ''}>m</option>
                                <option value="cm" ${item.lengthUnit === 'cm' ? 'selected' : ''}>cm</option>
                                <option value="mm" ${item.lengthUnit === 'mm' ? 'selected' : ''}>mm</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Width</label>
                            <input type="number" step="0.01" min="0" class="form-control footing-width" data-id="${item.id}" value="${item.width}" placeholder="e.g., 1.5">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <select class="form-select footing-width-unit" data-id="${item.id}">
                                <option value="m" ${item.widthUnit === 'm' ? 'selected' : ''}>m</option>
                                <option value="cm" ${item.widthUnit === 'cm' ? 'selected' : ''}>cm</option>
                                <option value="mm" ${item.widthUnit === 'mm' ? 'selected' : ''}>mm</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Depth</label>
                            <input type="number" step="0.01" min="0" class="form-control footing-depth" data-id="${item.id}" value="${item.depth}" placeholder="e.g., 0.5">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <select class="form-select footing-depth-unit" data-id="${item.id}">
                                <option value="m" ${item.depthUnit === 'm' ? 'selected' : ''}>m</option>
                                <option value="cm" ${item.depthUnit === 'cm' ? 'selected' : ''}>cm</option>
                                <option value="mm" ${item.depthUnit === 'mm' ? 'selected' : ''}>mm</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">Qty</label>
                            <input type="number" step="1" min="1" class="form-control footing-quantity" data-id="${item.id}" value="${item.quantity}" placeholder="1">
                        </div>
                    </div>
                </div>
            </div>
        `;
    }).join('');
    
    // Add event listeners for input changes
    container.querySelectorAll('.footing-label').forEach(input => {
        input.addEventListener('input', (e) => {
            const id = e.target.dataset.id;
            const item = footingItems.find(i => i.id === id);
            if (item) item.label = e.target.value;
        });
    });
    
    container.querySelectorAll('.footing-length, .footing-width, .footing-depth, .footing-quantity').forEach(input => {
        input.addEventListener('input', (e) => {
            const id = e.target.dataset.id;
            const item = footingItems.find(i => i.id === id);
            if (item) {
                if (e.target.classList.contains('footing-length')) item.length = e.target.value;
                if (e.target.classList.contains('footing-width')) item.width = e.target.value;
                if (e.target.classList.contains('footing-depth')) item.depth = e.target.value;
                if (e.target.classList.contains('footing-quantity')) item.quantity = parseInt(e.target.value) || 1;
            }
        });
    });
    
    container.querySelectorAll('.footing-length-unit, .footing-width-unit, .footing-depth-unit').forEach(select => {
        select.addEventListener('change', (e) => {
            const id = e.target.dataset.id;
            const item = footingItems.find(i => i.id === id);
            if (item) {
                if (e.target.classList.contains('footing-length-unit')) item.lengthUnit = e.target.value;
                if (e.target.classList.contains('footing-width-unit')) item.widthUnit = e.target.value;
                if (e.target.classList.contains('footing-depth-unit')) item.depthUnit = e.target.value;
            }
        });
    });
}

// Calculate Footing (Multiple Sizes)
function calculateFooting(wastage, label, notes) {
    if (footingItems.length === 0) {
        alert('Add at least one footing size.');
        return null;
    }
    
    const grade = document.getElementById('footingGrade').value;
    const ratio = concreteRatios[grade] || [1, 2, 4];
    const totalParts = ratio[0] + ratio[1] + ratio[2];
    
    let totalVolume = 0;
    let footingDetails = [];
    let hasError = false;
    
    footingItems.forEach((item, index) => {
        const lengthValue = parseFloat(item.length);
        const widthValue = parseFloat(item.width);
        const depthValue = parseFloat(item.depth);
        const quantity = parseInt(item.quantity) || 1;
        
        if (!lengthValue || lengthValue <= 0 || !widthValue || widthValue <= 0 || !depthValue || depthValue <= 0) {
            alert(`${item.label}: Please provide all dimensions (length, width, depth).`);
            hasError = true;
            return;
        }
        
        const length = convertToMeters(lengthValue, item.lengthUnit);
        const width = convertToMeters(widthValue, item.widthUnit);
        const depth = convertToMeters(depthValue, item.depthUnit);
        
        const volume = length * width * depth * quantity;
        totalVolume += volume;
        
        footingDetails.push({
            label: item.label,
            length: lengthValue,
            width: widthValue,
            depth: depthValue,
            lengthUnit: item.lengthUnit,
            widthUnit: item.widthUnit,
            depthUnit: item.depthUnit,
            quantity: quantity,
            volume: volume
        });
    });
    
    if (hasError || totalVolume <= 0) {
        return null;
    }
    
    const dryVolume = totalVolume * 1.54;
    const wastageFactor = 1 + wastage / 100;
    
    const cementVolume = dryVolume * (ratio[0] / totalParts) * wastageFactor;
    const sandVolume = dryVolume * (ratio[1] / totalParts) * wastageFactor;
    const aggregateVolume = dryVolume * (ratio[2] / totalParts) * wastageFactor;
    const cementBags = cementVolume / 0.035;
    const waterLitres = cementBags * 50 * 0.5;
    
    // Labor calculation: approximately 0.5 man-day per m³ for skilled, 1.0 man-day per m³ for unskilled
    const skilledManDays = totalVolume * 0.5;
    const unskilledManDays = totalVolume * 1.0;
    
    // Create description with all footing details
    const footingDesc = footingDetails.map(f => {
        return `${f.label}: ${f.length}${getUnitLabel(f.lengthUnit)} × ${f.width}${getUnitLabel(f.widthUnit)} × ${f.depth}${getUnitLabel(f.depthUnit)} (Qty: ${f.quantity})`;
    }).join(' | ');
    
    const description = `Footing - Grade ${grade} | ${footingDesc}` + (label ? ` | ${label}` : '');
    
    return {
        id: Date.now(),
        work_type: 'Footing (Multiple Sizes)',
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
            concrete_volume_m3: roundValue(totalVolume),
            concrete_volume_m3_exact: totalVolume,
            skilled_labor_days: roundValue(skilledManDays, 2),
            unskilled_labor_days: roundValue(unskilledManDays, 2),
            footing_details: footingDetails, // Store details for reference
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

function addToolMachine() {
    const name = document.getElementById('equipmentName').value.trim();
    if (!name) {
        alert('Enter equipment name.');
        return;
    }

    const rentalRate = parseFloat(document.getElementById('rentalRate').value) || 0;
    const rentalUnit = document.getElementById('rentalUnit').value;
    const usageDuration = parseFloat(document.getElementById('usageDuration').value) || 0;
    const fuelCost = parseFloat(document.getElementById('fuelCost').value) || 0;
    const fuelUnit = document.getElementById('fuelUnit').value;
    const operatorCost = parseFloat(document.getElementById('operatorCost').value) || 0;
    const operatorUnit = document.getElementById('operatorUnit').value;
    const maintenanceCost = parseFloat(document.getElementById('maintenanceCost').value) || 0;
    const transportCost = parseFloat(document.getElementById('transportCost').value) || 0;
    const otherCost = parseFloat(document.getElementById('otherCost').value) || 0;
    const notes = document.getElementById('equipmentNotes').value.trim() || '';

    // Calculate rental cost based on unit and duration
    let rentalTotal = 0;
    if (rentalRate > 0 && usageDuration > 0) {
        let multiplier = 1;
        if (rentalUnit === 'hour') {
            multiplier = usageDuration;
        } else if (rentalUnit === 'day') {
            multiplier = usageDuration;
        } else if (rentalUnit === 'week') {
            multiplier = usageDuration * 7;
        } else if (rentalUnit === 'month') {
            multiplier = usageDuration * 30;
        }
        rentalTotal = rentalRate * multiplier;
    }

    // Calculate fuel cost
    let fuelTotal = 0;
    if (fuelCost > 0) {
        if (fuelUnit === 'total') {
            fuelTotal = fuelCost;
        } else if (fuelUnit === 'day') {
            fuelTotal = fuelCost * (usageDuration || 1);
        } else if (fuelUnit === 'hour') {
            fuelTotal = fuelCost * (usageDuration || 1);
        } else if (fuelUnit === 'litre') {
            fuelTotal = fuelCost; // Assume fuel cost is already per litre total
        }
    }

    // Calculate operator cost
    let operatorTotal = 0;
    if (operatorCost > 0 && usageDuration > 0) {
        if (operatorUnit === 'hour') {
            operatorTotal = operatorCost * usageDuration;
        } else if (operatorUnit === 'day') {
            operatorTotal = operatorCost * usageDuration;
        }
    }

    const totalCost = rentalTotal + fuelTotal + operatorTotal + maintenanceCost + transportCost + otherCost;

    const toolMachine = {
        id: Date.now(),
        name,
        rental_rate: rentalRate,
        rental_unit: rentalUnit,
        rental_total: rentalTotal,
        usage_duration: usageDuration,
        fuel_cost: fuelCost,
        fuel_unit: fuelUnit,
        fuel_total: fuelTotal,
        operator_cost: operatorCost,
        operator_unit: operatorUnit,
        operator_total: operatorTotal,
        maintenance_cost: maintenanceCost,
        transport_cost: transportCost,
        other_cost: otherCost,
        total_cost: totalCost,
        notes
    };

    toolsMachines.push(toolMachine);
    renderToolsMachines();
    
    // Reset form
    document.getElementById('toolMachineForm').reset();
    document.getElementById('usageDuration').value = '1';
}

function removeToolMachine(id) {
    const index = toolsMachines.findIndex(item => item.id === id);
    if (index >= 0) {
        toolsMachines.splice(index, 1);
        renderToolsMachines();
    }
}

function renderToolsMachines() {
    const tbody = document.querySelector('#toolsMachinesTable tbody');
    tbody.innerHTML = '';

    if (!toolsMachines.length) {
        const row = document.createElement('tr');
        row.classList.add('no-tools-row');
        row.innerHTML = `<td colspan="11" class="text-center text-muted py-4">No tools or machines added yet.</td>`;
        tbody.appendChild(row);
        document.getElementById('toolsFooter').classList.add('d-none');
        renderSummary();
        return;
    }

    const currency = document.getElementById('currency').value;
    const currencySymbol = currency === 'USD' ? '$' : currency === 'EUR' ? '€' : (currency === 'INR' ? '₹' : 'Rs');
    let totalToolsCost = 0;

    toolsMachines.forEach((item, index) => {
        totalToolsCost += item.total_cost;
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${index + 1}</td>
            <td><strong>${item.name}</strong></td>
            <td>${item.rental_total > 0 ? formatValueWithRounding(item.rental_total, roundValue(item.rental_total, 2), ' ' + currencySymbol, 2) : '-'}</td>
            <td>${item.fuel_total > 0 ? formatValueWithRounding(item.fuel_total, roundValue(item.fuel_total, 2), ' ' + currencySymbol, 2) : '-'}</td>
            <td>${item.operator_total > 0 ? formatValueWithRounding(item.operator_total, roundValue(item.operator_total, 2), ' ' + currencySymbol, 2) : '-'}</td>
            <td>${item.maintenance_cost > 0 ? formatValueWithRounding(item.maintenance_cost, roundValue(item.maintenance_cost, 2), ' ' + currencySymbol, 2) : '-'}</td>
            <td>${item.transport_cost > 0 ? formatValueWithRounding(item.transport_cost, roundValue(item.transport_cost, 2), ' ' + currencySymbol, 2) : '-'}</td>
            <td>${item.other_cost > 0 ? formatValueWithRounding(item.other_cost, roundValue(item.other_cost, 2), ' ' + currencySymbol, 2) : '-'}</td>
            <td><strong>${formatValueWithRounding(item.total_cost, roundValue(item.total_cost, 2), ' ' + currencySymbol, 2)}</strong></td>
            <td><small class="text-muted">${item.notes || '-'}</small></td>
            <td class="text-end">
                <button class="btn btn-sm btn-link text-danger" onclick="removeToolMachine(${item.id})">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });

    document.getElementById('totalToolsCost').innerHTML = formatValueWithRounding(totalToolsCost, roundValue(totalToolsCost, 2), ' ' + currencySymbol, 2);
    document.getElementById('toolsFooter').classList.remove('d-none');
    renderSummary();
}

function calculateCost(materials, unitCosts) {
    const breakdown = {
        cement: (materials.cement_bags || 0) * (unitCosts.cement_bag || 0),
        sand: (materials.sand_m3 || 0) * (unitCosts.sand_m3 || 0),
        aggregate: (materials.aggregate_m3 || 0) * (unitCosts.aggregate_m3 || 0),
        water: (materials.water_litres || 0) * (unitCosts.water_litre || 0),
        bricks: (materials.bricks_units || 0) * (unitCosts.brick_unit || 0),
        stone: (materials.stone_volume_m3 || 0) * (unitCosts.aggregate_m3 || 0), // Use aggregate rate for stone
        soling: ((materials.soling_volume_m3 || materials.material_volume_m3 || 0) * (unitCosts.soling_m3 || 0)),
        steel: (materials.steel_kg || 0) * (unitCosts.steel_kg || 0),
    };
    
    // Calculate skilled and unskilled labor costs (new method - per day)
    if (materials.skilled_labor_days !== undefined && materials.skilled_labor_days > 0) {
        breakdown.skilled_labor = materials.skilled_labor_days * (unitCosts.skilled_labor_day || 0);
    }
    
    if (materials.unskilled_labor_days !== undefined && materials.unskilled_labor_days > 0) {
        breakdown.unskilled_labor = materials.unskilled_labor_days * (unitCosts.unskilled_labor_day || 0);
    }
    
    // Calculate labour cost based on volume (m³) for concrete, masonry, soling (legacy method)
    let labourVolume = 0;
    if (materials.concrete_volume_m3_exact !== undefined) {
        labourVolume = materials.concrete_volume_m3_exact;
    } else if (materials.wall_volume_m3_exact !== undefined) {
        labourVolume = materials.wall_volume_m3_exact;
    } else if (materials.soling_volume_m3_exact !== undefined) {
        labourVolume = materials.soling_volume_m3_exact;
    }
    
    if (labourVolume > 0 && !breakdown.skilled_labor && !breakdown.unskilled_labor) {
        breakdown.labour = labourVolume * (unitCosts.labour_m3 || 0);
    }
    
    // Calculate labour cost based on area (m²) for plaster (legacy method)
    if (materials.plaster_area_m2_exact !== undefined && !breakdown.skilled_labor && !breakdown.unskilled_labor) {
        breakdown.labour = (breakdown.labour || 0) + (materials.plaster_area_m2_exact * (unitCosts.labour_m2 || 0));
    }
    
    // Calculate centering cost for concrete works (formwork area)
    if (materials.formwork_area_m2_exact !== undefined) {
        breakdown.centering = materials.formwork_area_m2_exact * (unitCosts.centering_m2 || 0);
    }
    
    const total = Object.values(breakdown).reduce((sum, value) => sum + value, 0);
    return { breakdown, total };
}

function renderSummary() {
    const tbody = document.querySelector('#resultsTable tbody');
    tbody.innerHTML = '';

    if (!results.length) {
        const row = document.createElement('tr');
        row.classList.add('no-data-row');
        row.innerHTML = `<td colspan="12" class="text-center text-muted py-4">No work items added yet. Use the form above to calculate materials.</td>`;
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
        skilled_labor_days: 0,
        skilled_labor_days_exact: 0,
        unskilled_labor_days: 0,
        unskilled_labor_days_exact: 0,
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
        totals.skilled_labor_days_exact += item.materials.skilled_labor_days || 0;
        totals.unskilled_labor_days_exact += item.materials.unskilled_labor_days || 0;
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
        totals.skilled_labor_days += item.materials.skilled_labor_days || 0;
        totals.unskilled_labor_days += item.materials.unskilled_labor_days || 0;
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
            <td>
                ${(item.materials.skilled_labor_days || item.materials.unskilled_labor_days) ? 
                    (item.materials.skilled_labor_days ? `Skilled: ${roundValue(item.materials.skilled_labor_days, 2)}<br>` : '') + 
                    (item.materials.unskilled_labor_days ? `Unskilled: ${roundValue(item.materials.unskilled_labor_days, 2)}` : '') : '-'}
            </td>
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
    
    // Display labor totals
    let laborText = '';
    if (totals.skilled_labor_days_exact > 0 || totals.unskilled_labor_days_exact > 0) {
        if (totals.skilled_labor_days_exact > 0) {
            laborText += `Skilled: ${roundValue(totals.skilled_labor_days_exact, 2)}`;
        }
        if (totals.unskilled_labor_days_exact > 0) {
            if (laborText) laborText += '<br>';
            laborText += `Unskilled: ${roundValue(totals.unskilled_labor_days_exact, 2)}`;
        }
    }
    document.getElementById('totalLabor').innerHTML = laborText || '-';
    
    document.getElementById('totalCost').innerHTML = formatValueWithRounding(totals.total_cost_exact, roundValue(totals.total_cost_exact, 2), ' ' + currencySymbol, 2);
    
    // Add tools and machines cost to total
    const toolsMachinesTotal = toolsMachines.reduce((sum, item) => sum + item.total_cost, 0);
    const grandTotal = totals.total_cost_exact + toolsMachinesTotal;
    
    if (toolsMachinesTotal > 0) {
        document.getElementById('toolsMachinesCostInTable').innerHTML = formatValueWithRounding(toolsMachinesTotal, roundValue(toolsMachinesTotal, 2), ' ' + currencySymbol, 2);
        document.getElementById('grandTotalCost').innerHTML = formatValueWithRounding(grandTotal, roundValue(grandTotal, 2), ' ' + currencySymbol, 2);
        document.getElementById('toolsMachinesCostRow').classList.remove('d-none');
        document.getElementById('grandTotalRow').classList.remove('d-none');
    } else {
        document.getElementById('toolsMachinesCostRow').classList.add('d-none');
        document.getElementById('grandTotalRow').classList.add('d-none');
    }
    
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

    const toolsMachinesTotal = toolsMachines.reduce((sum, item) => sum + item.total_cost, 0);
    const grandTotal = totals ? (totals.total_cost || 0) + toolsMachinesTotal : toolsMachinesTotal;

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
            tools_machines_cost: roundValue(toolsMachinesTotal, 2),
            grand_total: roundValue(grandTotal, 2),
        } : {
            tools_machines_cost: roundValue(toolsMachinesTotal, 2),
            grand_total: roundValue(grandTotal, 2),
        },
        unit_costs: unitCosts,
        currency,
        tools_machines: toolsMachines.map(item => ({
            name: item.name,
            rental_total: roundValue(item.rental_total, 2),
            fuel_total: roundValue(item.fuel_total, 2),
            operator_total: roundValue(item.operator_total, 2),
            maintenance_cost: roundValue(item.maintenance_cost, 2),
            transport_cost: roundValue(item.transport_cost, 2),
            other_cost: roundValue(item.other_cost, 2),
            total_cost: roundValue(item.total_cost, 2),
            notes: item.notes,
        })),
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

