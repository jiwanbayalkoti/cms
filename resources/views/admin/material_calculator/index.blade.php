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
                                    <option value="soling">Soling</option>
                                    <option value="plaster">Plaster</option>
                                    <option value="floor_slab">Floor Slab / Concrete</option>
                                    <option value="footing">Footing (Multiple Sizes)</option>
                                    <option value="rod_calculation">Rod Calculation</option>
                                    <option value="wall_brick_calculation">Wall Brick Calculation</option>
                                    <option value="wall_plaster_calculation">Wall Plaster Calculation</option>
                                    <option value="masonry_wall_calculation">Masonry Wall Calculation</option>
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

                        {{-- Soling Type Selection --}}
                        <div class="mt-4 d-none" data-section="soling">
                            <h6 class="fw-semibold text-primary">Soling</h6>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Soling Type <span class="text-red-500">*</span></label>
                                    <select id="solingType" class="form-select" onchange="handleSolingTypeChange()">
                                        <option value="">-- Select Soling Type --</option>
                                        <option value="brick_soling">Brick Soling</option>
                                        <option value="stone_soling">Stone Soling</option>
                                        <option value="pcc_soling">PCC Soling</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Brick Soling Inputs --}}
                            <div id="brickSolingSection" class="d-none">
                                <h6 class="fw-semibold text-secondary mb-3">Brick Soling Details</h6>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Area (m²)</label>
                                        <input type="number" step="0.01" min="0" id="brickSolingArea" class="form-control" placeholder="e.g., 7.4" />
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Bricks per m²</label>
                                        <input type="number" step="0.01" min="0" id="brickSolingBricksPerSqm" class="form-control" value="50" placeholder="50" />
                                        <small class="text-muted">Default: 50 bricks per square meter</small>
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
                            <div id="stoneSolingSection" class="d-none">
                                <h6 class="fw-semibold text-secondary mb-3">Stone Soling Details</h6>
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
                            <div id="pccSolingSection" class="d-none">
                                <h6 class="fw-semibold text-secondary mb-3">PCC Soling Details</h6>
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
                        </div>

                        {{-- Legacy Brick Soling Inputs (hidden, kept for backward compatibility) --}}
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

                        {{-- Legacy Stone Soling Inputs (hidden, kept for backward compatibility) --}}
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

                        {{-- Legacy PCC Soling Inputs (hidden, kept for backward compatibility) --}}
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
                            <h6 class="fw-semibold text-primary">Footing Calculation</h6>
                            
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Footing Type <span class="text-red-500">*</span></label>
                                    <select id="footingType" class="form-select" onchange="handleFootingTypeChange()">
                                        <option value="">-- Select Footing Type --</option>
                                        <option value="soling">Soling</option>
                                        <option value="pcc">PCC</option>
                                        <option value="hattipaile">Hattipaile (Stepped footing with details size)</option>
                                    </select>
                                </div>
                            </div>
                            
                            {{-- Concrete Grade --}}
                            <div id="footingGradeSection" class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Concrete Grade</label>
                                    <select id="footingGrade" class="form-select">
                                        @foreach($concreteGrades as $grade)
                                            <option value="{{ $grade['value'] }}">{{ $grade['label'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            {{-- Soling Sub-section --}}
                            <div id="footingSolingSection" class="d-none">
                                <h6 class="fw-semibold text-secondary mb-3">Soling Details</h6>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label">Material Type <span class="text-red-500">*</span></label>
                                        <select id="footingSolingMaterial" class="form-select" onchange="handleFootingSolingMaterialChange()">
                                            @foreach($solingMaterials as $material)
                                                @if($material['value'] !== 'gravel' && $material['value'] !== 'sand')
                                                    <option value="{{ $material['value'] }}">{{ $material['label'] }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <small class="text-muted">Add multiple footing sizes below to calculate soling for each size.</small>
                                    </div>
                                </div>
                                
                                {{-- Brick Size Fields (shown only for brick material) --}}
                                <div id="footingBrickSizeSection" class="row g-3 mt-3 d-none">
                                    <div class="col-md-12">
                                        <h6 class="fw-semibold text-secondary mb-2">Brick Specifications</h6>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Brick Length (m)</label>
                                        <input type="number" step="0.01" min="0" id="footingBrickLength" class="form-control" value="0.2" placeholder="0.2">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Brick Width (m)</label>
                                        <input type="number" step="0.01" min="0" id="footingBrickWidth" class="form-control" value="0.1" placeholder="0.1">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Brick Height (m)</label>
                                        <input type="number" step="0.01" min="0" id="footingBrickHeight" class="form-control" value="0.1" placeholder="0.1">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Bricks per m²</label>
                                        <input type="number" step="0.01" min="0" id="footingBricksPerSqm" class="form-control" value="50" placeholder="50">
                                        <small class="text-muted">Default: 50 bricks per square meter</small>
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="footingIncludeSand" checked>
                                            <label class="form-check-label" for="footingIncludeSand">
                                                Include Sand
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- PCC Sub-section --}}
                            <div id="footingPCCSection" class="d-none">
                                <h6 class="fw-semibold text-secondary mb-3">PCC Details</h6>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <small class="text-muted">Add multiple footing sizes below to calculate PCC for each size.</small>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Rod Calculate Sub-section --}}
                            <div id="footingRodSection" class="d-none">
                                <h6 class="fw-semibold text-secondary mb-3">Steel / Rod Details</h6>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Element Type</label>
                                        <select id="footingSteelElement" class="form-select" onchange="handleFootingSteelElementChange()">
                                            <option value="Foundation">Foundation</option>
                                            <option value="Column / Pillar">Column / Pillar</option>
                                            <option value="Beam">Beam</option>
                                            <option value="Slab">Slab</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4" id="footingSteelDiameterCol">
                                        <label class="form-label">Bar Diameter (mm)</label>
                                        <select id="footingSteelDiameter" class="form-select">
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
                                        <label class="form-label">Calculation Method</label>
                                        <select id="footingSteelMethod" class="form-select" onchange="handleFootingSteelMethodChange()">
                                            <option value="area">By Area (m²)</option>
                                            <option value="length">By Total Length (m)</option>
                                        </select>
                                    </div>
                                </div>
                                
                                {{-- Separate Bar Diameters for Slab --}}
                                <div id="footingSlabBarDiameters" class="row g-3 mt-2 d-none">
                                    <div class="col-md-6">
                                        <label class="form-label">Main Bar Diameter (mm)</label>
                                        <select id="footingSteelMainDiameter" class="form-select">
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
                                    <div class="col-md-6">
                                        <label class="form-label">Distribution Bar Diameter (mm)</label>
                                        <select id="footingSteelDistDiameter" class="form-select">
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
                                </div>
                                
                                {{-- Beam Layout Section --}}
                                <div id="footingBeamLayoutSection" class="row g-3 mt-2 d-none">
                                    <div class="col-md-12">
                                        <h6 class="fw-semibold text-secondary mb-3">Beam Layout & Grid</h6>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Horizontal Grid Distances (mm)</label>
                                        <input type="text" id="beamHorizontalGrids" class="form-control" placeholder="e.g., 3500, 1500, 4000" />
                                        <small class="text-muted">Comma-separated values</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Vertical Grid Distances (mm)</label>
                                        <input type="text" id="beamVerticalGrids" class="form-control" placeholder="e.g., 3500, 3500, 4000" />
                                        <small class="text-muted">Comma-separated values</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Number of Horizontal Beam Lines</label>
                                        <input type="number" step="1" min="1" id="beamHorizontalLines" class="form-control" placeholder="e.g., 4" value="1" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Number of Vertical Beam Lines</label>
                                        <input type="number" step="1" min="1" id="beamVerticalLines" class="form-control" placeholder="e.g., 4" value="1" />
                                    </div>
                                    <div class="col-md-12">
                                        <h6 class="fw-semibold text-secondary mb-3 mt-3">Beam Section</h6>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Beam Width (b) (mm)</label>
                                        <input type="number" step="1" min="0" id="beamWidth" class="form-control" placeholder="e.g., 300" />
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Beam Depth (D) (mm)</label>
                                        <input type="number" step="1" min="0" id="beamDepth" class="form-control" placeholder="e.g., 450" />
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Clear Cover (C) (mm)</label>
                                        <input type="number" step="1" min="0" id="beamCover" class="form-control" placeholder="e.g., 40" value="40" />
                                    </div>
                                    <div class="col-md-12">
                                        <h6 class="fw-semibold text-secondary mb-3 mt-3">Main Reinforcement</h6>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Bottom Bar Diameter (mm)</label>
                                        <select id="beamBottomBarDia" class="form-select">
                                            <option value="12">12 mm</option>
                                            <option value="16">16 mm</option>
                                            <option value="20">20 mm</option>
                                            <option value="25">25 mm</option>
                                            <option value="32">32 mm</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Bottom Bar Quantity per Beam</label>
                                        <input type="number" step="1" min="1" id="beamBottomBarQty" class="form-control" placeholder="e.g., 3" value="2" />
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Top Bar Diameter (mm)</label>
                                        <select id="beamTopBarDia" class="form-select">
                                            <option value="12">12 mm</option>
                                            <option value="16">16 mm</option>
                                            <option value="20">20 mm</option>
                                            <option value="25">25 mm</option>
                                            <option value="32">32 mm</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Top Bar Quantity per Beam</label>
                                        <input type="number" step="1" min="1" id="beamTopBarQty" class="form-control" placeholder="e.g., 2" value="2" />
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Extra Top Bar at Supports?</label>
                                        <select id="beamExtraTopBar" class="form-select" onchange="handleBeamExtraTopBarChange()">
                                            <option value="no">No</option>
                                            <option value="yes">Yes</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Extra Bar Diameter (mm)</label>
                                        <select id="beamExtraBarDia" class="form-select">
                                            <option value="12">12 mm</option>
                                            <option value="16">16 mm</option>
                                            <option value="20">20 mm</option>
                                            <option value="25">25 mm</option>
                                        </select>
                                    </div>
                                    <div id="beamExtraBarSection" class="col-md-4 d-none">
                                        <label class="form-label">Extra Bar Length per Support (m)</label>
                                        <input type="number" step="0.01" min="0" id="beamExtraBarLength" class="form-control" placeholder="e.g., 1.5" />
                                    </div>
                                    <div class="col-md-12">
                                        <h6 class="fw-semibold text-secondary mb-3 mt-3">Stirrups</h6>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Stirrup Diameter (mm)</label>
                                        <select id="beamStirrupDia" class="form-select">
                                            <option value="6">6 mm</option>
                                            <option value="8">8 mm</option>
                                            <option value="10">10 mm</option>
                                            <option value="12">12 mm</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Stirrup Spacing - Mid Span (mm)</label>
                                        <input type="number" step="1" min="0" id="beamStirrupSpacingMid" class="form-control" placeholder="e.g., 200" value="200" />
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Stirrup Spacing - Near Supports (mm)</label>
                                        <input type="number" step="1" min="0" id="beamStirrupSpacingSupport" class="form-control" placeholder="e.g., 100" value="100" />
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Close Spacing Zone Length (m)</label>
                                        <input type="number" step="0.01" min="0" id="beamCloseSpacingLength" class="form-control" placeholder="e.g., 0.5" value="0.5" />
                                    </div>
                                </div>
                                
                                <div id="footingSteelAreaMethod" class="row g-3 mt-2">
                                    <div class="col-md-6">
                                        <label class="form-label">Clear Cover (mm)</label>
                                        <input type="number" step="1" min="0" id="footingSteelCover" class="form-control" placeholder="e.g., 100" value="100" />
                                        <small class="text-muted">Cover on all sides</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Main Bar Direction</label>
                                        <select id="footingSteelMainDirection" class="form-select">
                                            <option value="length">Along Length</option>
                                            <option value="width">Along Width</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Main Bar Spacing (mm)</label>
                                        <input type="number" step="1" min="0" id="footingSteelMainSpacing" class="form-control" placeholder="e.g., 125" value="125" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Distribution Bar Spacing (mm)</label>
                                        <input type="number" step="1" min="0" id="footingSteelDistSpacing" class="form-control" placeholder="e.g., 125" value="125" />
                                    </div>
                                    <div class="col-md-12">
                                        <small class="text-muted">Length and width will be taken from footing sizes below.</small>
                                    </div>
                                </div>
                                <div id="footingSteelLengthMethod" class="row g-3 mt-2 d-none">
                                    <div class="col-md-6">
                                        <label class="form-label">Total Length (m)</label>
                                        <input type="number" step="0.01" min="0" id="footingSteelTotalLength" class="form-control" placeholder="e.g., 100" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Number of Bars</label>
                                        <input type="number" step="1" min="1" id="footingSteelBars" class="form-control" placeholder="e.g., 10" value="1" />
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Multiple Footing Sizes Section (Common for all types) --}}
                            <div id="footingMultipleSizesSection" class="mt-4">
                                <h6 class="fw-semibold text-secondary mb-3">Multiple Footing Sizes (Optional)</h6>
                                <div class="alert alert-info mb-3">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Add different footing sizes (F1, F2, F3, etc.) with their dimensions and quantities.
                                </div>
                                
                                <div id="footingHattipaileList" class="mb-3">
                                    <!-- Footing items will be added here dynamically -->
                                </div>
                                
                                <button type="button" class="btn btn-sm btn-primary mb-3" onclick="addFootingItem()">
                                    <i class="bi bi-plus-circle me-1"></i> Add Footing Size
                                </button>
                            </div>
                            
                            {{-- Rod Calculation Section --}}
                        </div>
                        
                        <div class="mt-4 d-none" data-section="rod_calculation">
                            <h6 class="fw-semibold text-primary">Rod Calculation</h6>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">For What? <span class="text-red-500">*</span></label>
                                    <select id="rodCalculationType" class="form-select" onchange="handleRodCalculationTypeChange()">
                                        <option value="">-- Select Type --</option>
                                        <option value="footing">Footing</option>
                                        <option value="beam">Beam</option>
                                        <option value="pillar">Pillar / Column</option>
                                        <option value="slab">Slab</option>
                                    </select>
                                </div>
                            </div>
                            
                            {{-- Footing Rod Calculation --}}
                            <div id="rodFootingSection" class="d-none">
                                <h6 class="fw-semibold text-secondary mb-3">Footing Rod Details</h6>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Bar Diameter (mm)</label>
                                        <select id="rodFootingDiameter" class="form-select">
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
                                        <label class="form-label">Calculation Method</label>
                                        <select id="rodFootingMethod" class="form-select">
                                            <option value="area">By Area (m²)</option>
                                            <option value="length">By Total Length (m)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Clear Cover (mm)</label>
                                        <input type="number" step="1" min="0" id="rodFootingCover" class="form-control" placeholder="e.g., 100" value="100" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Main Bar Direction</label>
                                        <select id="rodFootingMainDirection" class="form-select">
                                            <option value="length">Along Length</option>
                                            <option value="width">Along Width</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Main Bar Spacing (mm)</label>
                                        <input type="number" step="1" min="0" id="rodFootingMainSpacing" class="form-control" placeholder="e.g., 125" value="125" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Distribution Bar Spacing (mm)</label>
                                        <input type="number" step="1" min="0" id="rodFootingDistSpacing" class="form-control" placeholder="e.g., 125" value="125" />
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <h6 class="fw-semibold text-secondary mb-3">Footing Sizes</h6>
                                    <div id="rodFootingSizesList"></div>
                                    <button type="button" class="btn btn-sm btn-primary mt-2" onclick="addRodFootingSize()">
                                        <i class="bi bi-plus-circle me-1"></i> Add Footing Size
                                    </button>
                                </div>
                            </div>
                            
                            {{-- Beam Rod Calculation --}}
                            <div id="rodBeamSection" class="d-none">
                                <h6 class="fw-semibold text-secondary mb-3">Beam Layout & Grid</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Horizontal Grid Distances (mm)</label>
                                        <input type="text" id="rodBeamHorizontalGrids" class="form-control" placeholder="e.g., 3500, 1500, 4000" />
                                        <small class="text-muted">Comma-separated values</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Vertical Grid Distances (mm)</label>
                                        <input type="text" id="rodBeamVerticalGrids" class="form-control" placeholder="e.g., 3500, 3500, 4000" />
                                        <small class="text-muted">Comma-separated values</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Number of Horizontal Beam Lines</label>
                                        <input type="number" step="1" min="1" id="rodBeamHorizontalLines" class="form-control" placeholder="e.g., 4" value="1" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Number of Vertical Beam Lines</label>
                                        <input type="number" step="1" min="1" id="rodBeamVerticalLines" class="form-control" placeholder="e.g., 4" value="1" />
                                    </div>
                                    <div class="col-md-12">
                                        <h6 class="fw-semibold text-secondary mb-3 mt-3">Beam Section</h6>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Beam Width (b) (mm)</label>
                                        <input type="number" step="1" min="0" id="rodBeamWidth" class="form-control" placeholder="e.g., 300" />
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Beam Depth (D) (mm)</label>
                                        <input type="number" step="1" min="0" id="rodBeamDepth" class="form-control" placeholder="e.g., 450" />
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Clear Cover (C) (mm)</label>
                                        <input type="number" step="1" min="0" id="rodBeamCover" class="form-control" placeholder="e.g., 40" value="40" />
                                    </div>
                                    <div class="col-md-12">
                                        <h6 class="fw-semibold text-secondary mb-3 mt-3">Main Reinforcement</h6>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Bottom Bar Diameter (mm)</label>
                                        <select id="rodBeamBottomBarDia" class="form-select">
                                            <option value="12">12 mm</option>
                                            <option value="16">16 mm</option>
                                            <option value="20">20 mm</option>
                                            <option value="25">25 mm</option>
                                            <option value="32">32 mm</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Bottom Bar Quantity per Beam</label>
                                        <input type="number" step="1" min="1" id="rodBeamBottomBarQty" class="form-control" placeholder="e.g., 3" value="2" />
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Top Bar Diameter (mm)</label>
                                        <select id="rodBeamTopBarDia" class="form-select">
                                            <option value="12">12 mm</option>
                                            <option value="16">16 mm</option>
                                            <option value="20">20 mm</option>
                                            <option value="25">25 mm</option>
                                            <option value="32">32 mm</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Top Bar Quantity per Beam</label>
                                        <input type="number" step="1" min="1" id="rodBeamTopBarQty" class="form-control" placeholder="e.g., 2" value="2" />
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Extra Top Bar at Supports?</label>
                                        <select id="rodBeamExtraTopBar" class="form-select" onchange="handleRodBeamExtraTopBarChange()">
                                            <option value="no">No</option>
                                            <option value="yes">Yes</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Extra Bar Diameter (mm)</label>
                                        <select id="rodBeamExtraBarDia" class="form-select">
                                            <option value="12">12 mm</option>
                                            <option value="16">16 mm</option>
                                            <option value="20">20 mm</option>
                                            <option value="25">25 mm</option>
                                        </select>
                                    </div>
                                    <div id="rodBeamExtraBarSection" class="col-md-4 d-none">
                                        <label class="form-label">Extra Bar Length per Support (m)</label>
                                        <input type="number" step="0.01" min="0" id="rodBeamExtraBarLength" class="form-control" placeholder="e.g., 1.5" />
                                    </div>
                                    <div class="col-md-12">
                                        <h6 class="fw-semibold text-secondary mb-3 mt-3">Stirrups</h6>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Stirrup Diameter (mm)</label>
                                        <select id="rodBeamStirrupDia" class="form-select">
                                            <option value="6">6 mm</option>
                                            <option value="8">8 mm</option>
                                            <option value="10">10 mm</option>
                                            <option value="12">12 mm</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Stirrup Spacing - Mid Span (mm)</label>
                                        <input type="number" step="1" min="0" id="rodBeamStirrupSpacingMid" class="form-control" placeholder="e.g., 200" value="200" />
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Stirrup Spacing - Near Supports (mm)</label>
                                        <input type="number" step="1" min="0" id="rodBeamStirrupSpacingSupport" class="form-control" placeholder="e.g., 100" value="100" />
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Close Spacing Zone Length (m)</label>
                                        <input type="number" step="0.01" min="0" id="rodBeamCloseSpacingLength" class="form-control" placeholder="e.g., 0.5" value="0.5" />
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Pillar/Column Rod Calculation --}}
                            <div id="rodPillarSection" class="d-none">
                                <h6 class="fw-semibold text-secondary mb-3">Pillar / Column Rod Details</h6>
                                <div class="alert alert-info mb-3">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Add multiple pillar types (C1, C2, C3, etc.) with different rod configurations. Each pillar type can have multiple bar groups (e.g., 4 bars @ 12mm, 4 bars @ 16mm).
                                </div>
                                <div id="rodPillarTypesList"></div>
                                <button type="button" class="btn btn-sm btn-primary mt-2" onclick="addRodPillarType()">
                                    <i class="bi bi-plus-circle me-1"></i> Add Pillar Type
                                </button>
                            </div>
                            
                            {{-- Slab Rod Calculation --}}
                            <div id="rodSlabSection" class="d-none">
                                <h6 class="fw-semibold text-secondary mb-3">Slab Rod Details</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Main Bar Diameter (mm)</label>
                                        <select id="rodSlabMainDiameter" class="form-select">
                                            <option value="6">6 mm</option>
                                            <option value="8">8 mm</option>
                                            <option value="10">10 mm</option>
                                            <option value="12">12 mm</option>
                                            <option value="16">16 mm</option>
                                            <option value="20">20 mm</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Distribution Bar Diameter (mm)</label>
                                        <select id="rodSlabDistDiameter" class="form-select">
                                            <option value="6">6 mm</option>
                                            <option value="8">8 mm</option>
                                            <option value="10">10 mm</option>
                                            <option value="12">12 mm</option>
                                            <option value="16">16 mm</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Main Bar Spacing (mm)</label>
                                        <input type="number" step="1" min="0" id="rodSlabMainSpacing" class="form-control" placeholder="e.g., 125" value="125" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Distribution Bar Spacing (mm)</label>
                                        <input type="number" step="1" min="0" id="rodSlabDistSpacing" class="form-control" placeholder="e.g., 125" value="125" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Slab Length (m)</label>
                                        <input type="number" step="0.01" min="0" id="rodSlabLength" class="form-control" placeholder="e.g., 5" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Slab Width (m)</label>
                                        <input type="number" step="0.01" min="0" id="rodSlabWidth" class="form-control" placeholder="e.g., 4" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Clear Cover (mm)</label>
                                        <input type="number" step="1" min="0" id="rodSlabCover" class="form-control" placeholder="e.g., 20" value="20" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Soling Inputs (Legacy) --}}
                        <div class="mt-4 d-none" data-section="soling_legacy">
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

                        {{-- Wall Brick Calculation Section --}}
                        <div class="mt-4 d-none" data-section="wall_brick_calculation">
                            <h6 class="fw-semibold text-primary">Wall Brick Calculation (One Floor)</h6>
                            <div class="alert alert-info mb-3">
                                <i class="bi bi-info-circle me-2"></i>
                                Calculate total bricks, sand, and cement required for all walls of one floor based on wall layout.
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Horizontal Wall Distances (mm)</label>
                                    <input type="text" id="wallBrickHorizontalGrids" class="form-control" placeholder="e.g., 3500, 1500, 4000" />
                                    <small class="text-muted">Comma-separated values</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Vertical Wall Distances (mm)</label>
                                    <input type="text" id="wallBrickVerticalGrids" class="form-control" placeholder="e.g., 3500, 3500, 4000" />
                                    <small class="text-muted">Comma-separated values</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Number of Horizontal Wall Lines</label>
                                    <input type="number" step="1" min="1" id="wallBrickHorizontalLines" class="form-control" placeholder="e.g., 4" value="1" />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Number of Vertical Wall Lines</label>
                                    <input type="number" step="1" min="1" id="wallBrickVerticalLines" class="form-control" placeholder="e.g., 4" value="1" />
                                </div>
                                <div class="col-md-12">
                                    <h6 class="fw-semibold text-secondary mb-3 mt-3">Wall Dimensions</h6>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Wall Height (m)</label>
                                    <input type="number" step="0.01" min="0" id="wallBrickHeight" class="form-control" placeholder="e.g., 3" />
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Wall Thickness (mm)</label>
                                    <input type="number" step="1" min="0" id="wallBrickThickness" class="form-control" placeholder="e.g., 230" value="230" />
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Opening Deduction (%)</label>
                                    <input type="number" step="0.1" min="0" max="100" id="wallBrickOpeningDeduction" class="form-control" placeholder="e.g., 15" value="15" />
                                    <small class="text-muted">Percentage of wall area for doors/windows</small>
                                </div>
                                <div class="col-md-12">
                                    <h6 class="fw-semibold text-secondary mb-3 mt-3">Brick Details</h6>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Brick Length (m)</label>
                                    <input type="number" step="0.01" min="0" id="wallBrickLength" class="form-control" value="0.2" placeholder="0.2" />
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Brick Width (m)</label>
                                    <input type="number" step="0.01" min="0" id="wallBrickWidth" class="form-control" value="0.1" placeholder="0.1" />
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Brick Height (m)</label>
                                    <input type="number" step="0.01" min="0" id="wallBrickHeightSize" class="form-control" value="0.1" placeholder="0.1" />
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Mortar Thickness (mm)</label>
                                    <input type="number" step="1" min="0" id="wallBrickMortarThickness" class="form-control" value="10" placeholder="10" />
                                </div>
                                <div class="col-md-12">
                                    <h6 class="fw-semibold text-secondary mb-3 mt-3">Mortar Ratio</h6>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Mortar Mix</label>
                                    <select id="wallBrickMortarMix" class="form-select">
                                        @foreach($mortarMixes as $mix)
                                            <option value="{{ $mix['value'] }}">{{ $mix['label'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Cement-Sand Ratio</label>
                                    <input type="text" id="wallBrickMortarRatio" class="form-control" placeholder="e.g., 1:6" value="1:6" readonly>
                                </div>
                            </div>
                        </div>

                        {{-- Wall Plaster Calculation Section --}}
                        <div class="mt-4 d-none" data-section="wall_plaster_calculation">
                            <h6 class="fw-semibold text-primary">Wall Plaster Calculation (One Floor)</h6>
                            <div class="alert alert-info mb-3">
                                <i class="bi bi-info-circle me-2"></i>
                                Calculate total plaster materials required for inside and outside walls of one floor. You can calculate combined or individual (inside/outside) plaster.
                            </div>
                            
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Calculation Type <span class="text-red-500">*</span></label>
                                    <select id="wallPlasterCalculationType" class="form-select" onchange="handleWallPlasterCalculationTypeChange()">
                                        <option value="">-- Select Type --</option>
                                        <option value="combined">Combined (Inside + Outside)</option>
                                        <option value="inside">Inside Only</option>
                                        <option value="outside">Outside Only</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Combined Calculation Section --}}
                            <div id="wallPlasterCombinedSection" class="d-none">
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Horizontal Wall Distances (mm)</label>
                                        <input type="text" id="wallPlasterCombinedHorizontalGrids" class="form-control" placeholder="e.g., 3500, 1500, 4000" />
                                        <small class="text-muted">Comma-separated values</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Vertical Wall Distances (mm)</label>
                                        <input type="text" id="wallPlasterCombinedVerticalGrids" class="form-control" placeholder="e.g., 3500, 3500, 4000" />
                                        <small class="text-muted">Comma-separated values</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Number of Horizontal Wall Lines</label>
                                        <input type="number" step="1" min="1" id="wallPlasterCombinedHorizontalLines" class="form-control" placeholder="e.g., 4" value="1" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Number of Vertical Wall Lines</label>
                                        <input type="number" step="1" min="1" id="wallPlasterCombinedVerticalLines" class="form-control" placeholder="e.g., 4" value="1" />
                                    </div>
                                    <div class="col-md-12">
                                        <h6 class="fw-semibold text-secondary mb-3 mt-3">Wall Dimensions</h6>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Wall Height (m)</label>
                                        <input type="number" step="0.01" min="0" id="wallPlasterCombinedHeight" class="form-control" placeholder="e.g., 3" />
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Opening Deduction (%)</label>
                                        <input type="number" step="0.1" min="0" max="100" id="wallPlasterCombinedOpeningDeduction" class="form-control" placeholder="e.g., 15" value="15" />
                                        <small class="text-muted">Percentage of wall area for doors/windows</small>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Plaster Thickness (mm)</label>
                                        <input type="number" step="0.1" min="0" id="wallPlasterCombinedThickness" class="form-control" placeholder="e.g., 12" value="12" />
                                    </div>
                                    <div class="col-md-12">
                                        <h6 class="fw-semibold text-secondary mb-3 mt-3">Inside Wall Plaster</h6>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Inside Plaster Thickness (mm)</label>
                                        <input type="number" step="0.1" min="0" id="wallPlasterCombinedInsideThickness" class="form-control" placeholder="e.g., 12" value="12" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Inside Mortar Mix</label>
                                        <select id="wallPlasterCombinedInsideMix" class="form-select">
                                            @foreach($mortarMixes as $mix)
                                                <option value="{{ $mix['value'] }}">{{ $mix['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-12">
                                        <h6 class="fw-semibold text-secondary mb-3 mt-3">Outside Wall Plaster</h6>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Outside Plaster Thickness (mm)</label>
                                        <input type="number" step="0.1" min="0" id="wallPlasterCombinedOutsideThickness" class="form-control" placeholder="e.g., 15" value="15" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Outside Mortar Mix</label>
                                        <select id="wallPlasterCombinedOutsideMix" class="form-select">
                                            @foreach($mortarMixes as $mix)
                                                <option value="{{ $mix['value'] }}">{{ $mix['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- Inside Only Section --}}
                            <div id="wallPlasterInsideSection" class="d-none">
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Horizontal Wall Distances (mm)</label>
                                        <input type="text" id="wallPlasterInsideHorizontalGrids" class="form-control" placeholder="e.g., 3500, 1500, 4000" />
                                        <small class="text-muted">Comma-separated values</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Vertical Wall Distances (mm)</label>
                                        <input type="text" id="wallPlasterInsideVerticalGrids" class="form-control" placeholder="e.g., 3500, 3500, 4000" />
                                        <small class="text-muted">Comma-separated values</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Number of Horizontal Wall Lines</label>
                                        <input type="number" step="1" min="1" id="wallPlasterInsideHorizontalLines" class="form-control" placeholder="e.g., 4" value="1" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Number of Vertical Wall Lines</label>
                                        <input type="number" step="1" min="1" id="wallPlasterInsideVerticalLines" class="form-control" placeholder="e.g., 4" value="1" />
                                    </div>
                                    <div class="col-md-12">
                                        <h6 class="fw-semibold text-secondary mb-3 mt-3">Wall Dimensions</h6>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Wall Height (m)</label>
                                        <input type="number" step="0.01" min="0" id="wallPlasterInsideHeight" class="form-control" placeholder="e.g., 3" />
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Opening Deduction (%)</label>
                                        <input type="number" step="0.1" min="0" max="100" id="wallPlasterInsideOpeningDeduction" class="form-control" placeholder="e.g., 15" value="15" />
                                        <small class="text-muted">Percentage of wall area for doors/windows</small>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Plaster Thickness (mm)</label>
                                        <input type="number" step="0.1" min="0" id="wallPlasterInsideThickness" class="form-control" placeholder="e.g., 12" value="12" />
                                    </div>
                                    <div class="col-md-12">
                                        <h6 class="fw-semibold text-secondary mb-3 mt-3">Mortar Ratio</h6>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Mortar Mix</label>
                                        <select id="wallPlasterInsideMix" class="form-select">
                                            @foreach($mortarMixes as $mix)
                                                <option value="{{ $mix['value'] }}">{{ $mix['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Cement-Sand Ratio</label>
                                        <input type="text" id="wallPlasterInsideRatio" class="form-control" placeholder="e.g., 1:4" value="1:4" readonly>
                                    </div>
                                </div>
                            </div>

                            {{-- Outside Only Section --}}
                            <div id="wallPlasterOutsideSection" class="d-none">
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Horizontal Wall Distances (mm)</label>
                                        <input type="text" id="wallPlasterOutsideHorizontalGrids" class="form-control" placeholder="e.g., 3500, 1500, 4000" />
                                        <small class="text-muted">Comma-separated values</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Vertical Wall Distances (mm)</label>
                                        <input type="text" id="wallPlasterOutsideVerticalGrids" class="form-control" placeholder="e.g., 3500, 3500, 4000" />
                                        <small class="text-muted">Comma-separated values</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Number of Horizontal Wall Lines</label>
                                        <input type="number" step="1" min="1" id="wallPlasterOutsideHorizontalLines" class="form-control" placeholder="e.g., 4" value="1" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Number of Vertical Wall Lines</label>
                                        <input type="number" step="1" min="1" id="wallPlasterOutsideVerticalLines" class="form-control" placeholder="e.g., 4" value="1" />
                                    </div>
                                    <div class="col-md-12">
                                        <h6 class="fw-semibold text-secondary mb-3 mt-3">Wall Dimensions</h6>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Wall Height (m)</label>
                                        <input type="number" step="0.01" min="0" id="wallPlasterOutsideHeight" class="form-control" placeholder="e.g., 3" />
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Opening Deduction (%)</label>
                                        <input type="number" step="0.1" min="0" max="100" id="wallPlasterOutsideOpeningDeduction" class="form-control" placeholder="e.g., 15" value="15" />
                                        <small class="text-muted">Percentage of wall area for doors/windows</small>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Plaster Thickness (mm)</label>
                                        <input type="number" step="0.1" min="0" id="wallPlasterOutsideThickness" class="form-control" placeholder="e.g., 15" value="15" />
                                    </div>
                                    <div class="col-md-12">
                                        <h6 class="fw-semibold text-secondary mb-3 mt-3">Mortar Ratio</h6>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Mortar Mix</label>
                                        <select id="wallPlasterOutsideMix" class="form-select">
                                            @foreach($mortarMixes as $mix)
                                                <option value="{{ $mix['value'] }}">{{ $mix['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Cement-Sand Ratio</label>
                                        <input type="text" id="wallPlasterOutsideRatio" class="form-control" placeholder="e.g., 1:4" value="1:4" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Masonry Wall Calculation Section (Retaining Wall) --}}
                        <div class="mt-4 d-none" data-section="masonry_wall_calculation">
                            <h6 class="fw-semibold text-primary">Masonry Wall Calculation (Retaining Wall)</h6>
                            <div class="alert alert-info mb-3">
                                <i class="bi bi-info-circle me-2"></i>
                                Calculate materials for stone masonry retaining wall based on thumb-rule design. Values are for site estimation - final design must be approved by structural engineer.
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Wall Height (H) (m) <span class="text-red-500">*</span></label>
                                    <input type="number" step="0.01" min="0" id="masonryWallHeight" class="form-control" placeholder="e.g., 2.5" oninput="calculateMasonryWallDimensions()" />
                                    <small class="text-muted">Height of retaining wall</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Wall Length (L) (m) <span class="text-red-500">*</span></label>
                                    <input type="number" step="0.01" min="0" id="masonryWallLength" class="form-control" placeholder="e.g., 10" oninput="calculateMasonryWallDimensions()" />
                                    <small class="text-muted">Length of retaining wall</small>
                                </div>
                                <div class="col-md-12">
                                    <div id="masonryWallDimensionsDisplay" class="alert alert-light border d-none">
                                        <h6 class="fw-semibold mb-2">Calculated Wall Dimensions:</h6>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <strong>Base Width (B):</strong> <span id="masonryWallBaseWidth" class="text-primary">-</span> m
                                            </div>
                                            <div class="col-md-4">
                                                <strong>Top Width (T):</strong> <span id="masonryWallTopWidth" class="text-primary">-</span> m
                                            </div>
                                            <div class="col-md-4">
                                                <strong>Wall Volume:</strong> <span id="masonryWallVolume" class="text-primary">-</span> m³
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Masonry Type</label>
                                    <select id="masonryWallType" class="form-select">
                                        <option value="stone">Stone Masonry</option>
                                        <option value="brick">Brick Masonry</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Mortar Ratio <span class="text-red-500">*</span></label>
                                    <select id="masonryWallMortarRatio" class="form-select">
                                        <option value="1:4">1:4</option>
                                        <option value="1:5" selected>1:5</option>
                                        <option value="1:6">1:6</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Soil Type (Optional)</label>
                                    <select id="masonryWallSoilType" class="form-select">
                                        <option value="">-- Select Soil Type --</option>
                                        <option value="hard">Hard</option>
                                        <option value="medium">Medium</option>
                                        <option value="soft">Soft</option>
                                    </select>
                                    <small class="text-muted">For reference only</small>
                                </div>
                            </div>
                            <div class="alert alert-warning mb-3">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>Important Notes:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>Values are thumb-rule based for site estimation</li>
                                    <li>Final design must be approved by structural engineer</li>
                                    <li>Weep holes and drainage are mandatory for retaining walls</li>
                                </ul>
                            </div>
                        </div>

                        <div class="row g-3 mt-4">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Wastage Allowance (%)</label>
                                <input type="number" step="0.1" min="0" max="15" id="wastagePercent" class="form-control" value="0">
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
            <table class="table table-striped table-hover table-sm align-middle mb-0" id="resultsTable" style="table-layout: auto;">
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
    /* Improve table row spacing and Work & Description column */
    #resultsTable tbody tr {
        min-height: 80px;
        vertical-align: top;
    }
    
    #resultsTable tbody td {
        padding: 12px 8px;
        vertical-align: middle;
    }
    
    #resultsTable tbody td:nth-child(2) {
        min-width: 300px;
        max-width: 400px;
        padding: 12px;
        vertical-align: top;
    }
    
    #resultsTable tbody td:nth-child(2) .fw-semibold {
        font-size: 0.95rem;
        line-height: 1.4;
        margin-bottom: 8px;
        color: #212529;
    }
    
    #resultsTable tbody td:nth-child(2) .small {
        line-height: 1.5;
        word-wrap: break-word;
        display: block;
        margin-bottom: 4px;
    }
    
    #resultsTable tbody td:nth-child(2) .text-info {
        margin-top: 6px;
    }
    
    #resultsTable thead th {
        padding: 12px 8px;
        font-weight: 600;
        white-space: nowrap;
    }
    
    #resultsTable thead th:nth-child(2) {
        min-width: 300px;
    }
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
            'soling': 'soling',
            'brick_soling': 'brick_soling',
            'stone_soling': 'stone_soling',
            'pcc_soling': 'pcc_soling',
            'plaster': 'plaster',
            'concrete': 'concrete',
            'floor_slab': 'concrete', // Floor slab uses concrete section
            'footing': 'footing',
            'rod_calculation': 'rod_calculation',
            'wall_brick_calculation': 'wall_brick_calculation',
            'wall_plaster_calculation': 'wall_plaster_calculation',
            'masonry_wall_calculation': 'masonry_wall_calculation',
        };
        
        if (sectionMap[value] === sectionType) {
            section.classList.remove('d-none');
            // Initialize footing section if selected
            if (value === 'footing' && sectionType === 'footing') {
                // Reset footing type selection
                const footingTypeEl = document.getElementById('footingType');
                if (footingTypeEl) {
                    footingTypeEl.value = '';
                    handleFootingTypeChange();
                }
            }
            // Initialize rod calculation section if selected
            if (value === 'rod_calculation' && sectionType === 'rod_calculation') {
                // Reset rod calculation type selection
                const rodTypeEl = document.getElementById('rodCalculationType');
                if (rodTypeEl) {
                    rodTypeEl.value = '';
                    handleRodCalculationTypeChange();
                }
            }
            // Initialize wall plaster calculation section if selected
            if (value === 'wall_plaster_calculation' && sectionType === 'wall_plaster_calculation') {
                // Reset wall plaster calculation type selection
                const plasterTypeEl = document.getElementById('wallPlasterCalculationType');
                if (plasterTypeEl) {
                    plasterTypeEl.value = '';
                    handleWallPlasterCalculationTypeChange();
                }
            }
            // Initialize soling section if selected
            if (value === 'soling' && sectionType === 'soling') {
                // Reset soling type selection and hide all detail sections
                const solingTypeEl = document.getElementById('solingType');
                if (solingTypeEl) {
                    solingTypeEl.value = '';
                }
                // Hide all soling detail sections
                document.getElementById('brickSolingSection')?.classList.add('d-none');
                document.getElementById('stoneSolingSection')?.classList.add('d-none');
                document.getElementById('pccSolingSection')?.classList.add('d-none');
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

// Update wall brick mortar ratio display when mix changes
const wallBrickMortarMixEl = document.getElementById('wallBrickMortarMix');
const wallBrickMortarRatioEl = document.getElementById('wallBrickMortarRatio');
if (wallBrickMortarMixEl && wallBrickMortarRatioEl) {
    wallBrickMortarMixEl.addEventListener('change', (event) => {
        const mix = event.target.value;
        const ratio = mortarRatios[mix] || [1, 6];
        wallBrickMortarRatioEl.value = `${ratio[0]}:${ratio[1]}`;
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

// Initialize cost inputs with default values (0)
document.querySelectorAll('.cost-input').forEach(input => {
    // Ensure all cost inputs default to 0
    if (!input.value || input.value === '') {
        input.value = '0';
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
    const footingHattipaileList = document.getElementById('footingHattipaileList');
    if (footingHattipaileList) {
        footingHattipaileList.innerHTML = '';
    }
    // Reset footing type selection
    const footingTypeEl = document.getElementById('footingType');
    if (footingTypeEl) {
        footingTypeEl.value = '';
        handleFootingTypeChange();
    }
    // Reset soling type selection
    const solingTypeEl = document.getElementById('solingType');
    if (solingTypeEl) {
        solingTypeEl.value = '';
        handleSolingTypeChange();
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

    if (type === 'floor_slab') {
        calculation = calculateConcrete(wastage, label, notes, true);
    } else if (type === 'masonry') {
        calculation = calculateMasonry(wastage, label, notes);
    } else if (type === 'plaster') {
        calculation = calculatePlaster(wastage, label, notes);
    } else if (type === 'soling') {
        const solingType = document.getElementById('solingType')?.value || '';
        if (!solingType) {
            alert('Please select a soling type (Brick, Stone, or PCC).');
            return;
        }
        if (solingType === 'brick_soling') {
            calculation = calculateBrickSoling(wastage, label, notes);
        } else if (solingType === 'stone_soling') {
            calculation = calculateStoneSoling(wastage, label, notes);
        } else if (solingType === 'pcc_soling') {
            calculation = calculatePCCSoling(wastage, label, notes);
        }
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
    } else if (type === 'rod_calculation') {
        calculation = calculateRodCalculation(wastage, label, notes);
    } else if (type === 'wall_brick_calculation') {
        calculation = calculateWallBrick(wastage, label, notes);
    } else if (type === 'wall_plaster_calculation') {
        calculation = calculateWallPlaster(wastage, label, notes);
    } else if (type === 'masonry_wall_calculation') {
        calculation = calculateMasonryWall(wastage, label, notes);
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

// Calculate Brick Soling (Using standard method: 50 bricks per m² - same as footing)
function calculateBrickSoling(wastage, label, notes) {
    const area = parseFloat(document.getElementById('brickSolingArea').value);
    const bricksPerSqm = parseFloat(document.getElementById('brickSolingBricksPerSqm')?.value) || 50;
    const useSand = document.getElementById('brickSolingSand').value === 'yes';

    if (!area || area <= 0) {
        alert('Provide brick soling area in m².');
        return null;
    }

    // Calculate bricks needed using bricks per square meter (standard method - same as footing)
    const bricksNeeded = (area * bricksPerSqm) * (1 + wastage / 100);
    
    // Sand for joint filling (0.003 m³ per m² is standard)
    const sandVolume = useSand ? (area * 0.003) * (1 + wastage / 100) : 0;

    // Labor calculation: approximately 0.25 man-day per m² for skilled, 0.5 man-day per m² for unskilled
    const skilledManDays = area * 0.25;
    const unskilledManDays = area * 0.5;

    const description = `Brick Soling ${area} m² | ${bricksPerSqm} bricks/m²` + (label ? ` | ${label}` : '');

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
    const container = document.getElementById('footingHattipaileList') || document.getElementById('footingList');
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

// Handle footing type change
function handleFootingTypeChange() {
    const footingType = document.getElementById('footingType')?.value || '';
    
    // Hide all sub-sections
    document.getElementById('footingSolingSection')?.classList.add('d-none');
    document.getElementById('footingPCCSection')?.classList.add('d-none');
    document.getElementById('footingRodSection')?.classList.add('d-none');
    
    // Show/hide concrete grade section (hide for soling, show for others or when empty)
    const gradeSection = document.getElementById('footingGradeSection');
    if (gradeSection) {
        if (footingType === 'soling') {
            gradeSection.classList.add('d-none');
        } else {
            gradeSection.classList.remove('d-none');
        }
    }
    
    // Show multiple footing sizes section (but hide for beam element type)
    const multipleSizesSection = document.getElementById('footingMultipleSizesSection');
    const elementType = document.getElementById('footingSteelElement')?.value || '';
    if (multipleSizesSection && footingType) {
        // Hide for beam, show for others
        if (footingType === 'rod' && elementType === 'Beam') {
            multipleSizesSection.classList.add('d-none');
        } else {
            multipleSizesSection.classList.remove('d-none');
        }
    } else if (multipleSizesSection) {
        multipleSizesSection.classList.add('d-none');
    }
    
    // Show selected sub-section
    if (footingType === 'soling') {
        document.getElementById('footingSolingSection')?.classList.remove('d-none');
        // Check material type and show brick size fields if needed
        setTimeout(() => {
            handleFootingSolingMaterialChange();
        }, 100);
    } else if (footingType === 'pcc') {
        document.getElementById('footingPCCSection')?.classList.remove('d-none');
    } else if (footingType === 'rod') {
        document.getElementById('footingRodSection')?.classList.remove('d-none');
        handleFootingSteelMethodChange();
        setTimeout(() => {
            handleFootingSteelElementChange();
        }, 100);
    } else if (footingType === 'hattipaile') {
        // For hattipaile, we can optionally hide the type-specific sections
        // and only show multiple sizes
    }
    
    // Initialize footing items if empty and type is selected
    if (footingType) {
        setTimeout(() => {
            if (footingItems.length === 0 && typeof addFootingItem === 'function') {
                // Only auto-add for hattipaile, others can add manually
                if (footingType === 'hattipaile') {
                    addFootingItem();
                }
            }
        }, 100);
    }
}

// Handle footing soling material change
function handleFootingSolingMaterialChange() {
    const material = document.getElementById('footingSolingMaterial')?.value || '';
    const brickSizeSection = document.getElementById('footingBrickSizeSection');
    
    if (brickSizeSection) {
        if (material === 'brick') {
            brickSizeSection.classList.remove('d-none');
        } else {
            brickSizeSection.classList.add('d-none');
        }
    }
}

// Handle footing steel element type change
function handleFootingSteelElementChange() {
    const element = document.getElementById('footingSteelElement')?.value || '';
    const slabBarDiameters = document.getElementById('footingSlabBarDiameters');
    const beamLayoutSection = document.getElementById('footingBeamLayoutSection');
    const areaMethodSection = document.getElementById('footingSteelAreaMethod');
    const generalDiameterCol = document.getElementById('footingSteelDiameterCol');
    
    // Show/hide general bar diameter field (only needed for Foundation and Column/Pillar)
    if (generalDiameterCol) {
        if (element === 'Slab' || element === 'Beam') {
            generalDiameterCol.classList.add('d-none');
        } else {
            generalDiameterCol.classList.remove('d-none');
        }
    }
    
    // Show/hide slab bar diameters
    if (slabBarDiameters) {
        if (element === 'Slab') {
            slabBarDiameters.classList.remove('d-none');
        } else {
            slabBarDiameters.classList.add('d-none');
        }
    }
    
    // Show/hide beam layout section
    if (beamLayoutSection && areaMethodSection) {
        if (element === 'Beam') {
            beamLayoutSection.classList.remove('d-none');
            areaMethodSection.classList.add('d-none');
            // Hide multiple footing sizes section for beam (not needed)
            const multipleSizesSection = document.getElementById('footingMultipleSizesSection');
            if (multipleSizesSection) {
                multipleSizesSection.classList.add('d-none');
            }
        } else {
            beamLayoutSection.classList.add('d-none');
            areaMethodSection.classList.remove('d-none');
            // Show multiple footing sizes section for other element types
            const multipleSizesSection = document.getElementById('footingMultipleSizesSection');
            const footingType = document.getElementById('footingType')?.value || '';
            if (multipleSizesSection && footingType === 'rod') {
                multipleSizesSection.classList.remove('d-none');
            }
        }
    }
}

// Handle beam extra top bar change
function handleBeamExtraTopBarChange() {
    const hasExtra = document.getElementById('beamExtraTopBar')?.value === 'yes';
    const extraBarSection = document.getElementById('beamExtraBarSection');
    
    if (extraBarSection) {
        if (hasExtra) {
            extraBarSection.classList.remove('d-none');
        } else {
            extraBarSection.classList.add('d-none');
        }
    }
}

// Handle soling type change
function handleSolingTypeChange() {
    const solingType = document.getElementById('solingType')?.value || '';
    
    // Hide all soling sections
    document.getElementById('brickSolingSection')?.classList.add('d-none');
    document.getElementById('stoneSolingSection')?.classList.add('d-none');
    document.getElementById('pccSolingSection')?.classList.add('d-none');
    
    // Show selected section
    if (solingType === 'brick_soling') {
        document.getElementById('brickSolingSection')?.classList.remove('d-none');
    } else if (solingType === 'stone_soling') {
        document.getElementById('stoneSolingSection')?.classList.remove('d-none');
    } else if (solingType === 'pcc_soling') {
        document.getElementById('pccSolingSection')?.classList.remove('d-none');
    }
}

// Handle rod calculation type change
function handleRodCalculationTypeChange() {
    const rodType = document.getElementById('rodCalculationType')?.value || '';
    
    // Hide all rod sections
    document.getElementById('rodFootingSection')?.classList.add('d-none');
    document.getElementById('rodBeamSection')?.classList.add('d-none');
    document.getElementById('rodPillarSection')?.classList.add('d-none');
    document.getElementById('rodSlabSection')?.classList.add('d-none');
    
    // Show selected section
    if (rodType === 'footing') {
        document.getElementById('rodFootingSection')?.classList.remove('d-none');
    } else if (rodType === 'beam') {
        document.getElementById('rodBeamSection')?.classList.remove('d-none');
    } else if (rodType === 'pillar') {
        document.getElementById('rodPillarSection')?.classList.remove('d-none');
    } else if (rodType === 'slab') {
        document.getElementById('rodSlabSection')?.classList.remove('d-none');
    }
}

// Handle rod beam extra top bar change
function handleRodBeamExtraTopBarChange() {
    const hasExtra = document.getElementById('rodBeamExtraTopBar')?.value === 'yes';
    const extraBarSection = document.getElementById('rodBeamExtraBarSection');
    
    if (extraBarSection) {
        if (hasExtra) {
            extraBarSection.classList.remove('d-none');
        } else {
            extraBarSection.classList.add('d-none');
        }
    }
}

// Calculate and display masonry wall dimensions
function calculateMasonryWallDimensions() {
    const height = parseFloat(document.getElementById('masonryWallHeight')?.value) || 0;
    const length = parseFloat(document.getElementById('masonryWallLength')?.value) || 0;
    const displayDiv = document.getElementById('masonryWallDimensionsDisplay');
    const baseWidthSpan = document.getElementById('masonryWallBaseWidth');
    const topWidthSpan = document.getElementById('masonryWallTopWidth');
    const volumeSpan = document.getElementById('masonryWallVolume');
    
    if (!displayDiv || !baseWidthSpan || !topWidthSpan || !volumeSpan) {
        return;
    }
    
    if (height > 0) {
        // Calculate base width (B) = 0.6 × Height
        const baseWidth = 0.6 * height;
        
        // Calculate top width (T) based on height
        let topWidth;
        if (height <= 2.0) {
            topWidth = 0.45;
        } else if (height > 2.0 && height <= 3.0) {
            topWidth = 0.50;
        } else {
            topWidth = 0.60;
        }
        
        // Calculate wall volume
        let wallVolume = 0;
        if (length > 0) {
            wallVolume = ((topWidth + baseWidth) / 2) * height * length;
        }
        
        // Display values
        baseWidthSpan.textContent = roundValue(baseWidth, 2);
        topWidthSpan.textContent = roundValue(topWidth, 2);
        if (length > 0) {
            volumeSpan.textContent = roundValue(wallVolume, 2);
        } else {
            volumeSpan.textContent = '-';
        }
        
        // Show display div
        displayDiv.classList.remove('d-none');
    } else {
        // Hide display div if height is not entered
        displayDiv.classList.add('d-none');
        baseWidthSpan.textContent = '-';
        topWidthSpan.textContent = '-';
        volumeSpan.textContent = '-';
    }
}

// Handle wall plaster calculation type change
function handleWallPlasterCalculationTypeChange() {
    const calcType = document.getElementById('wallPlasterCalculationType')?.value || '';
    
    // Hide all sections
    document.getElementById('wallPlasterCombinedSection')?.classList.add('d-none');
    document.getElementById('wallPlasterInsideSection')?.classList.add('d-none');
    document.getElementById('wallPlasterOutsideSection')?.classList.add('d-none');
    
    // Show selected section
    if (calcType === 'combined') {
        document.getElementById('wallPlasterCombinedSection')?.classList.remove('d-none');
    } else if (calcType === 'inside') {
        document.getElementById('wallPlasterInsideSection')?.classList.remove('d-none');
    } else if (calcType === 'outside') {
        document.getElementById('wallPlasterOutsideSection')?.classList.remove('d-none');
    }
}

// Update wall plaster mortar ratio displays
const wallPlasterInsideMixEl = document.getElementById('wallPlasterInsideMix');
const wallPlasterInsideRatioEl = document.getElementById('wallPlasterInsideRatio');
if (wallPlasterInsideMixEl && wallPlasterInsideRatioEl) {
    wallPlasterInsideMixEl.addEventListener('change', (event) => {
        const mix = event.target.value;
        const ratio = mortarRatios[mix] || [1, 4];
        wallPlasterInsideRatioEl.value = `${ratio[0]}:${ratio[1]}`;
    });
}

const wallPlasterOutsideMixEl = document.getElementById('wallPlasterOutsideMix');
const wallPlasterOutsideRatioEl = document.getElementById('wallPlasterOutsideRatio');
if (wallPlasterOutsideMixEl && wallPlasterOutsideRatioEl) {
    wallPlasterOutsideMixEl.addEventListener('change', (event) => {
        const mix = event.target.value;
        const ratio = mortarRatios[mix] || [1, 4];
        wallPlasterOutsideRatioEl.value = `${ratio[0]}:${ratio[1]}`;
    });
}

const wallPlasterCombinedInsideMixEl = document.getElementById('wallPlasterCombinedInsideMix');
const wallPlasterCombinedOutsideMixEl = document.getElementById('wallPlasterCombinedOutsideMix');
if (wallPlasterCombinedInsideMixEl && wallPlasterCombinedOutsideMixEl) {
    // These don't need ratio display updates as they're in combined section
}

// Rod footing sizes management
let rodFootingItems = [];
let rodFootingItemCounter = 0;

// Rod pillar types management
let rodPillarTypes = [];
let rodPillarTypeCounter = 0;

function addRodFootingSize() {
    rodFootingItemCounter++;
    const item = {
        id: 'rod-footing-' + rodFootingItemCounter,
        label: 'F' + rodFootingItemCounter,
        length: '',
        width: '',
        lengthUnit: 'm',
        widthUnit: 'm',
        quantity: 1
    };
    rodFootingItems.push(item);
    renderRodFootingSizes();
}

function removeRodFootingItem(id) {
    rodFootingItems = rodFootingItems.filter(item => item.id !== id);
    renderRodFootingSizes();
}

function renderRodFootingSizes() {
    const container = document.getElementById('rodFootingSizesList');
    if (!container) return;
    
    if (rodFootingItems.length === 0) {
        container.innerHTML = '<div class="alert alert-secondary mb-0">No footing sizes added yet. Click "Add Footing Size" to add.</div>';
        return;
    }
    
    container.innerHTML = rodFootingItems.map((item, index) => {
        return `
            <div class="card mb-3" data-rod-footing-id="${item.id}">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <strong>${item.label}</strong>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeRodFootingItem('${item.id}')">
                        <i class="bi bi-trash"></i> Remove
                    </button>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label">Label</label>
                            <input type="text" class="form-control rod-footing-label" data-id="${item.id}" value="${item.label}" placeholder="F1">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Length</label>
                            <input type="number" step="0.01" min="0" class="form-control rod-footing-length" data-id="${item.id}" value="${item.length}" placeholder="e.g., 1.5">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <select class="form-select rod-footing-length-unit" data-id="${item.id}">
                                <option value="m" ${item.lengthUnit === 'm' ? 'selected' : ''}>m</option>
                                <option value="cm" ${item.lengthUnit === 'cm' ? 'selected' : ''}>cm</option>
                                <option value="mm" ${item.lengthUnit === 'mm' ? 'selected' : ''}>mm</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Width</label>
                            <input type="number" step="0.01" min="0" class="form-control rod-footing-width" data-id="${item.id}" value="${item.width}" placeholder="e.g., 1.5">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <select class="form-select rod-footing-width-unit" data-id="${item.id}">
                                <option value="m" ${item.widthUnit === 'm' ? 'selected' : ''}>m</option>
                                <option value="cm" ${item.widthUnit === 'cm' ? 'selected' : ''}>cm</option>
                                <option value="mm" ${item.widthUnit === 'mm' ? 'selected' : ''}>mm</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Quantity</label>
                            <input type="number" step="1" min="1" class="form-control rod-footing-quantity" data-id="${item.id}" value="${item.quantity}" placeholder="1">
                        </div>
                    </div>
                </div>
            </div>
        `;
    }).join('');
    
    // Add event listeners
    container.querySelectorAll('.rod-footing-label').forEach(input => {
        input.addEventListener('input', (e) => {
            const id = e.target.dataset.id;
            const item = rodFootingItems.find(i => i.id === id);
            if (item) item.label = e.target.value;
        });
    });
    
    container.querySelectorAll('.rod-footing-length, .rod-footing-width, .rod-footing-quantity').forEach(input => {
        input.addEventListener('input', (e) => {
            const id = e.target.dataset.id;
            const item = rodFootingItems.find(i => i.id === id);
            if (item) {
                if (e.target.classList.contains('rod-footing-length')) item.length = e.target.value;
                if (e.target.classList.contains('rod-footing-width')) item.width = e.target.value;
                if (e.target.classList.contains('rod-footing-quantity')) item.quantity = parseInt(e.target.value) || 1;
            }
        });
    });
    
    container.querySelectorAll('.rod-footing-length-unit, .rod-footing-width-unit').forEach(select => {
        select.addEventListener('change', (e) => {
            const id = e.target.dataset.id;
            const item = rodFootingItems.find(i => i.id === id);
            if (item) {
                if (e.target.classList.contains('rod-footing-length-unit')) item.lengthUnit = e.target.value;
                if (e.target.classList.contains('rod-footing-width-unit')) item.widthUnit = e.target.value;
            }
        });
    });
}

// Rod pillar types management functions
function addRodPillarType() {
    rodPillarTypeCounter++;
    const pillarType = {
        id: 'rod-pillar-' + rodPillarTypeCounter,
        label: 'C' + rodPillarTypeCounter,
        height: '',
        quantity: 1,
        width: '',
        depth: '',
        clearCover: 40,
        barGroups: [{
            id: 'bar-group-' + Date.now(),
            bars: 4,
            diameter: 12
        }],
        stirrups: {
            diameter: 8,
            spacing: 150
        }
    };
    rodPillarTypes.push(pillarType);
    renderRodPillarTypes();
}

function removeRodPillarType(id) {
    rodPillarTypes = rodPillarTypes.filter(type => type.id !== id);
    renderRodPillarTypes();
}

function addBarGroupToPillar(pillarId) {
    const pillarType = rodPillarTypes.find(t => t.id === pillarId);
    if (pillarType) {
        pillarType.barGroups.push({
            id: 'bar-group-' + Date.now(),
            bars: 4,
            diameter: 12
        });
        renderRodPillarTypes();
    }
}

function removeBarGroupFromPillar(pillarId, groupId) {
    const pillarType = rodPillarTypes.find(t => t.id === pillarId);
    if (pillarType) {
        pillarType.barGroups = pillarType.barGroups.filter(g => g.id !== groupId);
        renderRodPillarTypes();
    }
}

function renderRodPillarTypes() {
    const container = document.getElementById('rodPillarTypesList');
    if (!container) return;
    
    if (rodPillarTypes.length === 0) {
        container.innerHTML = '<div class="alert alert-secondary mb-0">No pillar types added yet. Click "Add Pillar Type" to add.</div>';
        return;
    }
    
    container.innerHTML = rodPillarTypes.map((pillarType, index) => {
        const barGroupsHtml = pillarType.barGroups.map((group, gIndex) => {
            return `
                <div class="row g-2 mb-2 align-items-end" data-group-id="${group.id}">
                    <div class="col-md-4">
                        <label class="form-label small">Number of Bars</label>
                        <input type="number" step="1" min="1" class="form-control form-control-sm pillar-bar-count" 
                               data-pillar-id="${pillarType.id}" data-group-id="${group.id}" 
                               value="${group.bars}" placeholder="e.g., 4">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Bar Diameter (mm)</label>
                        <select class="form-select form-select-sm pillar-bar-diameter" 
                                data-pillar-id="${pillarType.id}" data-group-id="${group.id}">
                            <option value="6" ${group.diameter == 6 ? 'selected' : ''}>6 mm</option>
                            <option value="8" ${group.diameter == 8 ? 'selected' : ''}>8 mm</option>
                            <option value="10" ${group.diameter == 10 ? 'selected' : ''}>10 mm</option>
                            <option value="12" ${group.diameter == 12 ? 'selected' : ''}>12 mm</option>
                            <option value="16" ${group.diameter == 16 ? 'selected' : ''}>16 mm</option>
                            <option value="20" ${group.diameter == 20 ? 'selected' : ''}>20 mm</option>
                            <option value="25" ${group.diameter == 25 ? 'selected' : ''}>25 mm</option>
                            <option value="32" ${group.diameter == 32 ? 'selected' : ''}>32 mm</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-sm btn-danger" 
                                onclick="removeBarGroupFromPillar('${pillarType.id}', '${group.id}')">
                            <i class="bi bi-trash"></i> Remove Group
                        </button>
                    </div>
                </div>
            `;
        }).join('');
        
        return `
            <div class="card mb-3" data-pillar-type-id="${pillarType.id}">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <strong>${pillarType.label}</strong>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeRodPillarType('${pillarType.id}')">
                        <i class="bi bi-trash"></i> Remove Pillar Type
                    </button>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Pillar Label</label>
                            <input type="text" class="form-control pillar-label" data-id="${pillarType.id}" 
                                   value="${pillarType.label}" placeholder="C1">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Column Height (m)</label>
                            <input type="number" step="0.01" min="0" class="form-control pillar-height" 
                                   data-id="${pillarType.id}" value="${pillarType.height}" placeholder="e.g., 3">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Quantity</label>
                            <input type="number" step="1" min="1" class="form-control pillar-quantity" 
                                   data-id="${pillarType.id}" value="${pillarType.quantity}" placeholder="e.g., 4">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Column Width (mm)</label>
                            <input type="number" step="1" min="0" class="form-control pillar-width" 
                                   data-id="${pillarType.id}" value="${pillarType.width || ''}" placeholder="e.g., 300">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Column Depth (mm)</label>
                            <input type="number" step="1" min="0" class="form-control pillar-depth" 
                                   data-id="${pillarType.id}" value="${pillarType.depth || ''}" placeholder="e.g., 300">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Clear Cover (mm)</label>
                            <input type="number" step="1" min="0" class="form-control pillar-cover" 
                                   data-id="${pillarType.id}" value="${pillarType.clearCover || 40}" placeholder="e.g., 40">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-12">
                            <h6 class="fw-semibold text-secondary">Stirrups (Churi/Ring)</h6>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Stirrup Diameter (mm)</label>
                            <select class="form-select pillar-stirrup-dia" data-id="${pillarType.id}">
                                <option value="6" ${pillarType.stirrups?.diameter == 6 ? 'selected' : ''}>6 mm</option>
                                <option value="8" ${pillarType.stirrups?.diameter == 8 ? 'selected' : ''}>8 mm</option>
                                <option value="10" ${pillarType.stirrups?.diameter == 10 ? 'selected' : ''}>10 mm</option>
                                <option value="12" ${pillarType.stirrups?.diameter == 12 ? 'selected' : ''}>12 mm</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Stirrup Spacing (mm)</label>
                            <input type="number" step="1" min="0" class="form-control pillar-stirrup-spacing" 
                                   data-id="${pillarType.id}" value="${pillarType.stirrups?.spacing || 150}" placeholder="e.g., 150">
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0">Bar Groups</label>
                            <button type="button" class="btn btn-sm btn-success" 
                                    onclick="addBarGroupToPillar('${pillarType.id}')">
                                <i class="bi bi-plus-circle me-1"></i> Add Bar Group
                            </button>
                        </div>
                        ${barGroupsHtml}
                    </div>
                </div>
            </div>
        `;
    }).join('');
    
    // Add event listeners
    container.querySelectorAll('.pillar-label').forEach(input => {
        input.addEventListener('input', (e) => {
            const id = e.target.dataset.id;
            const pillarType = rodPillarTypes.find(t => t.id === id);
            if (pillarType) pillarType.label = e.target.value;
        });
    });
    
    container.querySelectorAll('.pillar-height, .pillar-quantity, .pillar-width, .pillar-depth, .pillar-cover').forEach(input => {
        input.addEventListener('input', (e) => {
            const id = e.target.dataset.id;
            const pillarType = rodPillarTypes.find(t => t.id === id);
            if (pillarType) {
                if (e.target.classList.contains('pillar-height')) pillarType.height = e.target.value;
                if (e.target.classList.contains('pillar-quantity')) pillarType.quantity = parseInt(e.target.value) || 1;
                if (e.target.classList.contains('pillar-width')) pillarType.width = e.target.value;
                if (e.target.classList.contains('pillar-depth')) pillarType.depth = e.target.value;
                if (e.target.classList.contains('pillar-cover')) pillarType.clearCover = parseFloat(e.target.value) || 40;
            }
        });
    });
    
    container.querySelectorAll('.pillar-stirrup-dia, .pillar-stirrup-spacing').forEach(input => {
        input.addEventListener('change', (e) => {
            const id = e.target.dataset.id;
            const pillarType = rodPillarTypes.find(t => t.id === id);
            if (pillarType) {
                if (!pillarType.stirrups) pillarType.stirrups = {};
                if (e.target.classList.contains('pillar-stirrup-dia')) {
                    pillarType.stirrups.diameter = parseInt(e.target.value);
                }
                if (e.target.classList.contains('pillar-stirrup-spacing')) {
                    pillarType.stirrups.spacing = parseFloat(e.target.value) || 150;
                }
            }
        });
    });
    
    container.querySelectorAll('.pillar-stirrup-spacing').forEach(input => {
        input.addEventListener('input', (e) => {
            const id = e.target.dataset.id;
            const pillarType = rodPillarTypes.find(t => t.id === id);
            if (pillarType) {
                if (!pillarType.stirrups) pillarType.stirrups = {};
                pillarType.stirrups.spacing = parseFloat(e.target.value) || 150;
            }
        });
    });
    
    container.querySelectorAll('.pillar-bar-count').forEach(input => {
        input.addEventListener('input', (e) => {
            const pillarId = e.target.dataset.pillarId;
            const groupId = e.target.dataset.groupId;
            const pillarType = rodPillarTypes.find(t => t.id === pillarId);
            if (pillarType) {
                const group = pillarType.barGroups.find(g => g.id === groupId);
                if (group) group.bars = parseInt(e.target.value) || 1;
            }
        });
    });
    
    container.querySelectorAll('.pillar-bar-diameter').forEach(select => {
        select.addEventListener('change', (e) => {
            const pillarId = e.target.dataset.pillarId;
            const groupId = e.target.dataset.groupId;
            const pillarType = rodPillarTypes.find(t => t.id === pillarId);
            if (pillarType) {
                const group = pillarType.barGroups.find(g => g.id === groupId);
                if (group) group.diameter = parseInt(e.target.value);
            }
        });
    });
}

// Handle footing steel method change
function handleFootingSteelMethodChange() {
    const method = document.getElementById('footingSteelMethod')?.value || 'area';
    const areaMethod = document.getElementById('footingSteelAreaMethod');
    const lengthMethod = document.getElementById('footingSteelLengthMethod');
    
    if (areaMethod && lengthMethod) {
        if (method === 'area') {
            areaMethod.classList.remove('d-none');
            lengthMethod.classList.add('d-none');
        } else {
            areaMethod.classList.add('d-none');
            lengthMethod.classList.remove('d-none');
        }
    }
}

// Calculate Footing (Multiple Sizes)
function calculateFooting(wastage, label, notes) {
    const footingType = document.getElementById('footingType')?.value || '';
    
    if (!footingType) {
        alert('Please select a footing type.');
        return null;
    }
    
    if (footingType === 'soling') {
        // Use soling calculation based on multiple footing sizes
        const material = document.getElementById('footingSolingMaterial').value;
        
        if (footingItems.length === 0) {
            alert('Add at least one footing size for soling calculation.');
            return null;
        }
        
        // Calculate soling for multiple footing sizes
        return calculateSolingForFootingMultiple(wastage, label, notes, material);
    } else if (footingType === 'pcc') {
        // Use PCC calculation based on multiple footing sizes
        const grade = document.getElementById('footingGrade')?.value;
        
        if (!grade) {
            alert('Please select a concrete grade for PCC.');
            return null;
        }
        
        if (footingItems.length === 0) {
            alert('Add at least one footing size for PCC calculation.');
            return null;
        }
        
        // Calculate PCC for multiple footing sizes using concrete grade
        return calculatePCCForFootingMultiple(wastage, label, notes, grade);
    } else if (footingType === 'rod') {
        // Use steel calculation
        const method = document.getElementById('footingSteelMethod').value;
        const element = document.getElementById('footingSteelElement').value;
        
        // Check if element is Beam - use beam layout calculation
        if (element === 'Beam') {
            return calculateBeamLayout(wastage, label, notes);
        }
        
        // Get diameters based on element type
        let mainDiameter, distDiameter, diameter;
        if (element === 'Slab') {
            mainDiameter = parseFloat(document.getElementById('footingSteelMainDiameter').value);
            distDiameter = parseFloat(document.getElementById('footingSteelDistDiameter').value);
            diameter = mainDiameter; // Default for backward compatibility
        } else {
            diameter = parseFloat(document.getElementById('footingSteelDiameter').value);
            mainDiameter = diameter;
            distDiameter = diameter;
        }
        
        let totalLength = 0;
        let numberOfBars = 0;
        
        if (method === 'area') {
            // Get input parameters
            const clearCover = parseFloat(document.getElementById('footingSteelCover').value) || 100; // mm
            const mainDirection = document.getElementById('footingSteelMainDirection').value || 'length';
            const mainSpacing = parseFloat(document.getElementById('footingSteelMainSpacing').value) || 125; // mm
            const distSpacing = parseFloat(document.getElementById('footingSteelDistSpacing').value) || 125; // mm
            
            if (!mainSpacing || mainSpacing <= 0 || !distSpacing || distSpacing <= 0) {
                alert('Please provide spacing for both main and distribution bars.');
                return null;
            }
            
            if (footingItems.length === 0) {
                alert('Please add at least one footing size to calculate rod requirements.');
                return null;
            }
            
            // Convert spacing from mm to meters
            const mainSpacingM = mainSpacing / 1000;
            const distSpacingM = distSpacing / 1000;
            const coverM = clearCover / 1000; // Convert cover from mm to meters
            
            let totalMainBars = 0;
            let totalMainLength = 0;
            let totalDistBars = 0;
            let totalDistLength = 0;
            let footingDetails = [];
            
            // Process each footing item separately
            footingItems.forEach((item) => {
                const lengthValue = parseFloat(item.length);
                const widthValue = parseFloat(item.width);
                const quantity = parseInt(item.quantity) || 1;
                
                if (lengthValue && lengthValue > 0 && widthValue && widthValue > 0) {
                    // Convert to meters
                    const lengthM = convertToMeters(lengthValue, item.lengthUnit);
                    const widthM = convertToMeters(widthValue, item.widthUnit);
                    
                    // Calculate clear dimensions after deducting cover (cover on both sides)
                    const clearLength = lengthM - (2 * coverM);
                    const clearWidth = widthM - (2 * coverM);
                    
                    // Determine dimensions based on main bar direction
                    let mainBarClearDimension, distBarClearDimension;
                    if (mainDirection === 'length') {
                        // Main bars run along length, distributor bars run along width
                        mainBarClearDimension = clearLength;
                        distBarClearDimension = clearWidth;
                    } else {
                        // Main bars run along width, distributor bars run along length
                        mainBarClearDimension = clearWidth;
                        distBarClearDimension = clearLength;
                    }
                    
                    // Calculate number of bars
                    // Formula: Number of bars = (clear dimension perpendicular to bar direction / spacing) + 1
                    const mainBarsPerFooting = Math.floor(distBarClearDimension / mainSpacingM) + 1;
                    const distBarsPerFooting = Math.floor(mainBarClearDimension / distSpacingM) + 1;
                    
                    // Calculate cutting length of one bar (including 90° bends at both ends)
                    // Bend length = 10d per bend, so 20d total per bar
                    const mainBarDiameterM = mainDiameter / 1000; // Convert mm to meters
                    const distBarDiameterM = distDiameter / 1000; // Convert mm to meters
                    
                    const mainBarBendLength = 20 * mainBarDiameterM; // 20d total (10d per bend × 2)
                    const distBarBendLength = 20 * distBarDiameterM; // 20d total (10d per bend × 2)
                    
                    const oneMainBarLength = mainBarClearDimension + mainBarBendLength;
                    const oneDistBarLength = distBarClearDimension + distBarBendLength;
                    
                    // Total length for this footing (multiply by quantity)
                    const mainLengthPerFooting = mainBarsPerFooting * oneMainBarLength * quantity;
                    const distLengthPerFooting = distBarsPerFooting * oneDistBarLength * quantity;
                    
                    // Add to totals
                    totalMainBars += mainBarsPerFooting * quantity;
                    totalMainLength += mainLengthPerFooting;
                    totalDistBars += distBarsPerFooting * quantity;
                    totalDistLength += distLengthPerFooting;
                    
                    footingDetails.push({
                        label: item.label,
                        length: lengthValue,
                        width: widthValue,
                        lengthUnit: item.lengthUnit,
                        widthUnit: item.widthUnit,
                        quantity: quantity,
                        mainBars: mainBarsPerFooting,
                        distBars: distBarsPerFooting,
                        mainBarLength: oneMainBarLength,
                        distBarLength: oneDistBarLength,
                        area: lengthM * widthM * quantity
                    });
                }
            });
            
            if (totalMainBars === 0 || totalDistBars === 0) {
                alert('Please provide valid footing dimensions.');
                return null;
            }
            
            // Combined totals
            const totalBars = totalMainBars + totalDistBars;
            totalLength = totalMainLength + totalDistLength;
            numberOfBars = totalBars;
            
            // For description, use average dimensions
            const avgLength = footingItems.reduce((sum, item) => {
                const lengthM = convertToMeters(parseFloat(item.length), item.lengthUnit);
                return sum + lengthM * (parseInt(item.quantity) || 1);
            }, 0) / footingItems.reduce((sum, item) => sum + (parseInt(item.quantity) || 1), 0);
            
            const avgWidth = footingItems.reduce((sum, item) => {
                const widthM = convertToMeters(parseFloat(item.width), item.widthUnit);
                return sum + widthM * (parseInt(item.quantity) || 1);
            }, 0) / footingItems.reduce((sum, item) => sum + (parseInt(item.quantity) || 1), 0);
            
            const length = avgLength || 0;
            const width = avgWidth || 0;
            const mainBars = totalMainBars;
            const mainTotalLength = totalMainLength;
            const distBars = totalDistBars;
            const distTotalLength = totalDistLength;
            
            // Pass spacing info for description (mainSpacing and distSpacing as comma-separated string)
            const spacingInfo = `${mainSpacing},${distSpacing}`;
            
            return calculateSteelForFooting(wastage, label, notes, diameter, totalLength, numberOfBars, element, method, spacingInfo, length, width, mainBars, mainTotalLength, distBars, distTotalLength, footingDetails, mainDiameter, distDiameter, clearCover, mainDirection);
        } else {
            totalLength = parseFloat(document.getElementById('footingSteelTotalLength').value);
            numberOfBars = parseInt(document.getElementById('footingSteelBars').value) || 1;
            if (!totalLength || totalLength <= 0) {
                alert('Please provide total length for steel calculation.');
                return null;
            }
            
            return calculateSteelForFooting(wastage, label, notes, diameter, totalLength, numberOfBars, element, method, null, null, null, null, null, null, null, null, mainDiameter, distDiameter, null, null);
        }
    } else if (footingType === 'hattipaile') {
        // Use existing hattipaile calculation (original footing calculation)
        if (footingItems.length === 0) {
            alert('Add at least one footing size.');
            return null;
        }
        return calculateFootingHattipaile(wastage, label, notes);
    }
    
    return null;
}


// Helper functions for footing calculations
// Calculate soling for multiple footing sizes
function calculateSolingForFootingMultiple(wastage, label, notes, material) {
    if (footingItems.length === 0) {
        alert('Add at least one footing size.');
        return null;
    }
    
    let totalArea = 0;
    let totalVolume = 0;
    let footingDetails = [];
    let hasError = false;
    
    // Calculate area and volume for each footing item
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
        
        const area = length * width * quantity;
        const volume = area * depth;
        
        totalArea += area;
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
            area: area,
            volume: volume
        });
    });
    
    if (hasError || totalArea <= 0) {
        return null;
    }
    
    // Calculate based on material type
    if (material === 'brick') {
        return calculateBrickSolingForFootingMultiple(wastage, label, notes, totalArea, totalVolume, footingDetails);
    } else if (material === 'stone') {
        return calculateStoneSolingForFootingMultiple(wastage, label, notes, totalArea, totalVolume, footingDetails);
    }
    return null;
}

function calculateBrickSolingForFootingMultiple(wastage, label, notes, totalArea, totalVolume, footingDetails) {
    // Get custom brick dimensions or use defaults
    const brickLength = parseFloat(document.getElementById('footingBrickLength')?.value) || 0.2;
    const brickWidth = parseFloat(document.getElementById('footingBrickWidth')?.value) || 0.1;
    const brickHeight = parseFloat(document.getElementById('footingBrickHeight')?.value) || 0.1;
    const bricksPerSqm = parseFloat(document.getElementById('footingBricksPerSqm')?.value) || 50;
    const includeSand = document.getElementById('footingIncludeSand')?.checked ?? true;
    
    // Calculate bricks needed using bricks per square meter
    const bricksNeeded = (totalArea * bricksPerSqm) * (1 + wastage / 100);
    
    // Calculate sand volume (0.003 m³ per m² is standard) - only if includeSand is checked
    const sandVolume = includeSand ? (totalArea * 0.003) * (1 + wastage / 100) : 0;
    
    const footingDesc = footingDetails.map(f => {
        return `${f.label}: ${f.length}${getUnitLabel(f.lengthUnit)} × ${f.width}${getUnitLabel(f.widthUnit)} × ${f.depth}${getUnitLabel(f.depthUnit)} (Qty: ${f.quantity})`;
    }).join(' | ');
    
    const brickSizeInfo = `Brick: ${brickLength}m × ${brickWidth}m × ${brickHeight}m | ${bricksPerSqm} bricks/m²`;
    const description = `Footing - Brick Soling | ${brickSizeInfo} | ${footingDesc}` + (label ? ` | ${label}` : '');
    
    // Labor calculation: approximately 0.25 man-day per m² for skilled, 0.5 man-day per m² for unskilled
    const skilledManDays = totalArea * 0.25;
    const unskilledManDays = totalArea * 0.5;
    
    const materials = {
        bricks_units: Math.ceil(bricksNeeded),
        bricks_units_exact: bricksNeeded,
        soling_area_m2: roundValue(totalArea),
        soling_area_m2_exact: totalArea,
        skilled_labor_days: roundValue(skilledManDays, 2),
        unskilled_labor_days: roundValue(unskilledManDays, 2),
        footing_details: footingDetails,
    };
    
    // Add sand only if includeSand is checked
    if (includeSand) {
        materials.sand_m3 = roundValue(sandVolume);
        materials.sand_m3_exact = sandVolume;
    }
    
    return {
        id: Date.now(),
        work_type: 'Footing - Brick Soling',
        description,
        notes,
        materials: materials,
    };
}

function calculateStoneSolingForFootingMultiple(wastage, label, notes, totalArea, totalVolume, footingDetails) {
    const volumeWithWastage = totalVolume * (1 + wastage / 100);
    const sandVolume = (totalVolume * 0.2) * (1 + wastage / 100);
    
    const footingDesc = footingDetails.map(f => {
        return `${f.label}: ${f.length}${getUnitLabel(f.lengthUnit)} × ${f.width}${getUnitLabel(f.widthUnit)} × ${f.depth}${getUnitLabel(f.depthUnit)} (Qty: ${f.quantity})`;
    }).join(' | ');
    
    const description = `Footing - Stone Soling | ${footingDesc}` + (label ? ` | ${label}` : '');
    
    // Labor calculation: approximately 0.4 man-day per m² for skilled, 0.8 man-day per m² for unskilled
    const skilledManDays = totalArea * 0.4;
    const unskilledManDays = totalArea * 0.8;
    
    return {
        id: Date.now(),
        work_type: 'Footing - Stone Soling',
        description,
        notes,
        materials: {
            stone_volume_m3: roundValue(volumeWithWastage),
            stone_volume_m3_exact: volumeWithWastage,
            sand_m3: roundValue(sandVolume),
            sand_m3_exact: sandVolume,
            soling_area_m2: roundValue(totalArea),
            soling_area_m2_exact: totalArea,
            skilled_labor_days: roundValue(skilledManDays, 2),
            unskilled_labor_days: roundValue(unskilledManDays, 2),
            footing_details: footingDetails,
        }
    };
}

function calculateSolingForFooting(wastage, label, notes, areaValue, areaUnit, thicknessValue, thicknessUnit, material) {
    const area = convertToSquareMeters(areaValue, areaUnit);
    const thicknessM = convertToMeters(thicknessValue, thicknessUnit);
    const volume = area * thicknessM;
    
    if (material === 'brick') {
        return calculateBrickSolingForFooting(wastage, label, notes, area, volume);
    } else if (material === 'stone') {
        return calculateStoneSolingForFooting(wastage, label, notes, area, volume);
    }
    return null;
}

function calculateBrickSolingForFooting(wastage, label, notes, area, volume) {
    // Get custom brick dimensions or use defaults
    const brickLength = parseFloat(document.getElementById('footingBrickLength')?.value) || 0.2;
    const brickWidth = parseFloat(document.getElementById('footingBrickWidth')?.value) || 0.1;
    const brickHeight = parseFloat(document.getElementById('footingBrickHeight')?.value) || 0.1;
    const bricksPerSqm = parseFloat(document.getElementById('footingBricksPerSqm')?.value) || 50;
    const includeSand = document.getElementById('footingIncludeSand')?.checked ?? true;
    
    // Calculate bricks needed using bricks per square meter
    const bricksNeeded = (area * bricksPerSqm) * (1 + wastage / 100);
    
    // Calculate sand volume (0.003 m³ per m² is standard) - only if includeSand is checked
    const sandVolume = includeSand ? (area * 0.003) * (1 + wastage / 100) : 0;
    
    const brickSizeInfo = `Brick: ${brickLength}m × ${brickWidth}m × ${brickHeight}m | ${bricksPerSqm} bricks/m²`;
    const description = `Footing - Brick Soling ${area.toFixed(2)} m² | ${brickSizeInfo}` + (label ? ` | ${label}` : '');
    
    const materials = {
        bricks_units: Math.ceil(bricksNeeded),
        bricks_units_exact: bricksNeeded,
        soling_area_m2: roundValue(area),
        soling_area_m2_exact: area,
    };
    
    // Add sand only if includeSand is checked
    if (includeSand) {
        materials.sand_m3 = roundValue(sandVolume);
        materials.sand_m3_exact = sandVolume;
    }
    
    return {
        id: Date.now(),
        work_type: 'Footing - Brick Soling',
        description,
        notes,
        materials: materials,
    };
}

function calculateStoneSolingForFooting(wastage, label, notes, area, volume) {
    const volumeWithWastage = volume * (1 + wastage / 100);
    const sandVolume = (volume * 0.2) * (1 + wastage / 100);
    
    const description = `Footing - Stone Soling ${area.toFixed(2)} m²` + (label ? ` | ${label}` : '');
    
    return {
        id: Date.now(),
        work_type: 'Footing - Stone Soling',
        description,
        notes,
        materials: {
            stone_volume_m3: roundValue(volumeWithWastage),
            stone_volume_m3_exact: volumeWithWastage,
            sand_m3: roundValue(sandVolume),
            sand_m3_exact: sandVolume,
            soling_area_m2: roundValue(area),
            soling_area_m2_exact: area,
        }
    };
}

// Calculate PCC for multiple footing sizes
function calculatePCCForFootingMultiple(wastage, label, notes, grade) {
    if (footingItems.length === 0) {
        alert('Add at least one footing size.');
        return null;
    }
    
    let totalVolume = 0;
    let footingDetails = [];
    let hasError = false;
    
    // Calculate volume for each footing item
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
    
    // Use concrete grade ratio
    if (!grade || !concreteRatios[grade]) {
        alert('Please select a valid concrete grade.');
        return null;
    }
    
    const ratio = concreteRatios[grade];
    const totalParts = ratio[0] + ratio[1] + ratio[2];
    
    const dryVolume = totalVolume * 1.54;
    const wastageFactor = 1 + wastage / 100;
    
    const cementVolume = dryVolume * (ratio[0] / totalParts) * wastageFactor;
    const sandVolume = dryVolume * (ratio[1] / totalParts) * wastageFactor;
    const aggregateVolume = dryVolume * (ratio[2] / totalParts) * wastageFactor;
    const cementBags = cementVolume / 0.035;
    const waterLitres = cementBags * 50 * 0.5;
    
    const footingDesc = footingDetails.map(f => {
        return `${f.label}: ${f.length}${getUnitLabel(f.lengthUnit)} × ${f.width}${getUnitLabel(f.widthUnit)} × ${f.depth}${getUnitLabel(f.depthUnit)} (Qty: ${f.quantity})`;
    }).join(' | ');
    
    const description = `Footing - PCC | Grade ${grade} (${ratio[0]}:${ratio[1]}:${ratio[2]}) | ${footingDesc}` + (label ? ` | ${label}` : '');
    
    // Labor calculation: approximately 0.5 man-day per m³ for skilled, 1.0 man-day per m³ for unskilled
    const skilledManDays = totalVolume * 0.5;
    const unskilledManDays = totalVolume * 1.0;
    
    return {
        id: Date.now(),
        work_type: 'Footing - PCC',
        description,
        notes,
        materials: {
            cement_bags: Math.ceil(cementBags), // Round up to next whole number
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
            footing_details: footingDetails,
        }
    };
}

// Legacy function (kept for backward compatibility)
function calculatePCCForFooting(wastage, label, notes, areaValue, areaUnit, thicknessValue, thicknessUnit, ratioStr) {
    const area = convertToSquareMeters(areaValue, areaUnit);
    const thicknessM = convertToMeters(thicknessValue, thicknessUnit);
    const volume = area * thicknessM;
    
    const ratioParts = ratioStr.split(':').map(r => parseFloat(r.trim()));
    const ratio = ratioParts.length === 3 ? ratioParts : [1, 3, 6];
    const totalParts = ratio[0] + ratio[1] + ratio[2];
    
    const dryVolume = volume * 1.54;
    const wastageFactor = 1 + wastage / 100;
    
    const cementVolume = dryVolume * (ratio[0] / totalParts) * wastageFactor;
    const sandVolume = dryVolume * (ratio[1] / totalParts) * wastageFactor;
    const aggregateVolume = dryVolume * (ratio[2] / totalParts) * wastageFactor;
    const cementBags = cementVolume / 0.035;
    const waterLitres = cementBags * 50 * 0.5;
    
    const description = `Footing - PCC ${area.toFixed(2)} m² @ ${thicknessValue}${getUnitLabel(thicknessUnit)} | Ratio ${ratioStr}` + (label ? ` | ${label}` : '');
    
    return {
        id: Date.now(),
        work_type: 'Footing - PCC',
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

// Calculate Beam Layout Reinforcement
// Calculate Rod Calculation based on type
function calculateRodCalculation(wastage, label, notes) {
    const rodType = document.getElementById('rodCalculationType')?.value || '';
    
    if (!rodType) {
        alert('Please select what the rod calculation is for (Footing, Beam, Pillar, or Slab).');
        return null;
    }
    
    if (rodType === 'footing') {
        return calculateRodFooting(wastage, label, notes);
    } else if (rodType === 'beam') {
        return calculateRodBeam(wastage, label, notes);
    } else if (rodType === 'pillar') {
        return calculateRodPillar(wastage, label, notes);
    } else if (rodType === 'slab') {
        return calculateRodSlab(wastage, label, notes);
    }
    
    return null;
}

// Calculate Rod for Footing
function calculateRodFooting(wastage, label, notes) {
    if (rodFootingItems.length === 0) {
        alert('Add at least one footing size for rod calculation.');
        return null;
    }
    
    const diameter = parseFloat(document.getElementById('rodFootingDiameter').value);
    const method = document.getElementById('rodFootingMethod').value;
    const clearCover = parseFloat(document.getElementById('rodFootingCover').value) || 100;
    const mainDirection = document.getElementById('rodFootingMainDirection').value;
    const mainSpacing = parseFloat(document.getElementById('rodFootingMainSpacing').value) || 125;
    const distSpacing = parseFloat(document.getElementById('rodFootingDistSpacing').value) || 125;
    
    let totalLength = 0;
    let mainBars = 0;
    let distBars = 0;
    let mainTotalLength = 0;
    let distTotalLength = 0;
    
    if (method === 'area') {
        rodFootingItems.forEach(item => {
            const length = convertToMeters(parseFloat(item.length) || 0, item.lengthUnit);
            const width = convertToMeters(parseFloat(item.width) || 0, item.widthUnit);
            const quantity = parseInt(item.quantity) || 1;
            
            if (length > 0 && width > 0) {
                const clearLength = length - (2 * clearCover / 1000);
                const clearWidth = width - (2 * clearCover / 1000);
                
                const mainDim = mainDirection === 'length' ? clearLength : clearWidth;
                const distDim = mainDirection === 'length' ? clearWidth : clearLength;
                
                const mainBarsCount = Math.floor(mainDim / (mainSpacing / 1000)) + 1;
                const distBarsCount = Math.floor(distDim / (distSpacing / 1000)) + 1;
                
                const mainBarLength = mainDim + (2 * clearCover / 1000);
                const distBarLength = distDim + (2 * clearCover / 1000);
                
                mainBars += mainBarsCount * quantity;
                distBars += distBarsCount * quantity;
                mainTotalLength += mainBarsCount * mainBarLength * quantity;
                distTotalLength += distBarsCount * distBarLength * quantity;
            }
        });
        
        totalLength = mainTotalLength + distTotalLength;
    }
    
    const weightPerM = (diameter * diameter) / 162;
    const totalWeight = totalLength * weightPerM * (1 + wastage / 100);
    
    let description = `Footing Rod Calculation | ${diameter}mm bars | Method: ${method}`;
    if (method === 'area') {
        description += ` | Main: ${mainBars} bars | Dist: ${distBars} bars`;
    }
    if (label) description += ` | ${label}`;
    
    return {
        id: Date.now(),
        work_type: 'Rod Calculation - Footing',
        description,
        notes,
        materials: {
            steel_kg: roundValue(totalWeight),
            steel_kg_exact: totalWeight,
            steel_length_m: roundValue(totalLength),
            steel_length_m_exact: totalLength,
            main_bars: mainBars,
            distribution_bars: distBars,
        }
    };
}

// Calculate Rod for Beam (reuse existing calculateBeamLayout but with rodBeam fields)
function calculateRodBeam(wastage, label, notes) {
    // Get rod beam field values
    const horizontalGridsStr = document.getElementById('rodBeamHorizontalGrids').value;
    const verticalGridsStr = document.getElementById('rodBeamVerticalGrids').value;
    const horizontalLines = parseInt(document.getElementById('rodBeamHorizontalLines').value) || 1;
    const verticalLines = parseInt(document.getElementById('rodBeamVerticalLines').value) || 1;
    const beamWidth = parseFloat(document.getElementById('rodBeamWidth').value);
    const beamDepth = parseFloat(document.getElementById('rodBeamDepth').value);
    const clearCover = parseFloat(document.getElementById('rodBeamCover').value) || 40;
    const bottomBarDia = parseFloat(document.getElementById('rodBeamBottomBarDia').value);
    const bottomBarQty = parseInt(document.getElementById('rodBeamBottomBarQty').value) || 2;
    const topBarDia = parseFloat(document.getElementById('rodBeamTopBarDia').value);
    const topBarQty = parseInt(document.getElementById('rodBeamTopBarQty').value) || 2;
    const hasExtraTopBar = document.getElementById('rodBeamExtraTopBar').value === 'yes';
    const extraBarDia = hasExtraTopBar ? parseFloat(document.getElementById('rodBeamExtraBarDia').value) : 0;
    const extraBarLength = hasExtraTopBar ? parseFloat(document.getElementById('rodBeamExtraBarLength').value) : 0;
    const stirrupDia = parseFloat(document.getElementById('rodBeamStirrupDia').value);
    const stirrupSpacingMid = parseFloat(document.getElementById('rodBeamStirrupSpacingMid').value) || 200;
    const stirrupSpacingSupport = parseFloat(document.getElementById('rodBeamStirrupSpacingSupport').value) || 100;
    const closeSpacingLength = parseFloat(document.getElementById('rodBeamCloseSpacingLength').value) || 0.5;
    
    // Temporarily set beam fields to use rod beam values
    const tempSetField = (id, value) => {
        const el = document.getElementById(id);
        if (el) el.value = value;
    };
    
    tempSetField('beamHorizontalGrids', horizontalGridsStr);
    tempSetField('beamVerticalGrids', verticalGridsStr);
    tempSetField('beamHorizontalLines', horizontalLines);
    tempSetField('beamVerticalLines', verticalLines);
    tempSetField('beamWidth', beamWidth);
    tempSetField('beamDepth', beamDepth);
    tempSetField('beamCover', clearCover);
    tempSetField('beamBottomBarDia', bottomBarDia);
    tempSetField('beamBottomBarQty', bottomBarQty);
    tempSetField('beamTopBarDia', topBarDia);
    tempSetField('beamTopBarQty', topBarQty);
    tempSetField('beamExtraTopBar', hasExtraTopBar ? 'yes' : 'no');
    if (hasExtraTopBar) {
        tempSetField('beamExtraBarDia', extraBarDia);
        tempSetField('beamExtraBarLength', extraBarLength);
    }
    tempSetField('beamStirrupDia', stirrupDia);
    tempSetField('beamStirrupSpacingMid', stirrupSpacingMid);
    tempSetField('beamStirrupSpacingSupport', stirrupSpacingSupport);
    tempSetField('beamCloseSpacingLength', closeSpacingLength);
    
    // Call existing beam calculation
    const result = calculateBeamLayout(wastage, label, notes);
    
    // Update work type
    if (result) {
        result.work_type = 'Rod Calculation - Beam';
    }
    
    return result;
}

// Calculate Rod for Pillar/Column
function calculateRodPillar(wastage, label, notes) {
    if (rodPillarTypes.length === 0) {
        alert('Add at least one pillar type for rod calculation.');
        return null;
    }
    
    let totalWeight = 0;
    let totalLength = 0;
    let totalPillars = 0;
    let totalStirrupWeight = 0;
    let totalStirrupLength = 0;
    const wastageFactor = 1 + wastage / 100;
    const pillarDetails = [];
    
    rodPillarTypes.forEach(pillarType => {
        const height = parseFloat(pillarType.height);
        const quantity = parseInt(pillarType.quantity) || 1;
        const width = parseFloat(pillarType.width) || 0;
        const depth = parseFloat(pillarType.depth) || 0;
        const clearCover = parseFloat(pillarType.clearCover) || 40;
        
        if (!height || height <= 0) {
            alert(`${pillarType.label}: Please provide column height.`);
            return;
        }
        
        if (pillarType.barGroups.length === 0) {
            alert(`${pillarType.label}: Please add at least one bar group.`);
            return;
        }
        
        let pillarWeight = 0;
        let pillarLength = 0;
        const barGroupsDetail = [];
        
        // Calculate main bars
        pillarType.barGroups.forEach(group => {
            const bars = parseInt(group.bars) || 1;
            const diameter = parseInt(group.diameter) || 12;
            
            const groupLength = bars * height * quantity;
            const weightPerM = (diameter * diameter) / 162;
            const groupWeight = groupLength * weightPerM;
            
            pillarLength += groupLength;
            pillarWeight += groupWeight;
            
            barGroupsDetail.push(`${bars}@${diameter}mm`);
        });
        
        // Calculate stirrups if dimensions provided
        let stirrupWeight = 0;
        let stirrupLength = 0;
        if (width > 0 && depth > 0 && pillarType.stirrups) {
            const stirrupDia = parseInt(pillarType.stirrups.diameter) || 8;
            const stirrupSpacing = parseFloat(pillarType.stirrups.spacing) || 150;
            
            // Stirrup cutting length = 2(b - 2C) + 2(D - 2C) + hooks (10d per hook, 2 hooks = 20d)
            const widthM = width / 1000; // Convert mm to meters
            const depthM = depth / 1000; // Convert mm to meters
            const coverM = clearCover / 1000; // Convert mm to meters
            const stirrupDiaM = stirrupDia / 1000; // Convert mm to meters
            
            const stirrupHookLength = 20 * stirrupDiaM; // 20d total (10d per hook × 2)
            const stirrupCuttingLength = (2 * (widthM - 2 * coverM)) + (2 * (depthM - 2 * coverM)) + stirrupHookLength;
            
            // Number of stirrups = (height / spacing) + 1
            const numberOfStirrups = Math.ceil(height / (stirrupSpacing / 1000)) + 1;
            const totalStirrups = numberOfStirrups * quantity;
            
            stirrupLength = totalStirrups * stirrupCuttingLength;
            const stirrupWeightPerM = (stirrupDia * stirrupDia) / 162;
            stirrupWeight = stirrupLength * stirrupWeightPerM;
            
            barGroupsDetail.push(`Stirrups: ${totalStirrups}@${stirrupDia}mm`);
        }
        
        totalWeight += (pillarWeight + stirrupWeight) * wastageFactor;
        totalLength += pillarLength;
        totalStirrupWeight += stirrupWeight * wastageFactor;
        totalStirrupLength += stirrupLength;
        totalPillars += quantity;
        
        pillarDetails.push(`${quantity}×${pillarType.label} (${barGroupsDetail.join(', ')})`);
    });
    
    if (totalWeight === 0) {
        return null;
    }
    
    let description = `Pillar/Column Rod Calculation | Total: ${totalPillars} pillars | `;
    description += pillarDetails.join(' | ');
    if (label) description += ` | ${label}`;
    
    return {
        id: Date.now(),
        work_type: 'Rod Calculation - Pillar/Column',
        description,
        notes,
        materials: {
            steel_kg: roundValue(totalWeight),
            steel_kg_exact: totalWeight,
            steel_length_m: roundValue(totalLength),
            steel_length_m_exact: totalLength,
            steel_bars: totalPillars,
            stirrups_kg: roundValue(totalStirrupWeight),
            stirrups_kg_exact: totalStirrupWeight,
            stirrups_length_m: roundValue(totalStirrupLength),
            stirrups_length_m_exact: totalStirrupLength,
            pillar_details: pillarDetails.join('; '),
        }
    };
}

// Calculate Rod for Slab
function calculateRodSlab(wastage, label, notes) {
    const mainDiameter = parseFloat(document.getElementById('rodSlabMainDiameter').value);
    const distDiameter = parseFloat(document.getElementById('rodSlabDistDiameter').value);
    const mainSpacing = parseFloat(document.getElementById('rodSlabMainSpacing').value) || 125;
    const distSpacing = parseFloat(document.getElementById('rodSlabDistSpacing').value) || 125;
    const length = parseFloat(document.getElementById('rodSlabLength').value);
    const width = parseFloat(document.getElementById('rodSlabWidth').value);
    const clearCover = parseFloat(document.getElementById('rodSlabCover').value) || 20;
    
    if (!mainDiameter || !distDiameter || !length || !width || length <= 0 || width <= 0) {
        alert('Please provide slab dimensions and bar diameters.');
        return null;
    }
    
    const clearLength = length - (2 * clearCover / 1000);
    const clearWidth = width - (2 * clearCover / 1000);
    
    const mainBars = Math.floor(clearWidth / (mainSpacing / 1000)) + 1;
    const distBars = Math.floor(clearLength / (distSpacing / 1000)) + 1;
    
    const mainBarLength = clearLength;
    const distBarLength = clearWidth;
    
    const mainTotalLength = mainBars * mainBarLength;
    const distTotalLength = distBars * distBarLength;
    
    const mainWeightPerM = (mainDiameter * mainDiameter) / 162;
    const distWeightPerM = (distDiameter * distDiameter) / 162;
    
    const mainWeight = mainTotalLength * mainWeightPerM;
    const distWeight = distTotalLength * distWeightPerM;
    const totalWeight = (mainWeight + distWeight) * (1 + wastage / 100);
    
    let description = `Slab Rod Calculation | Main: ${mainBars}@${mainDiameter}mm | Dist: ${distBars}@${distDiameter}mm | ${length}m × ${width}m`;
    if (label) description += ` | ${label}`;
    
    return {
        id: Date.now(),
        work_type: 'Rod Calculation - Slab',
        description,
        notes,
        materials: {
            steel_kg: roundValue(totalWeight),
            steel_kg_exact: totalWeight,
            steel_length_m: roundValue(mainTotalLength + distTotalLength),
            steel_length_m_exact: mainTotalLength + distTotalLength,
            main_bars: mainBars,
            distribution_bars: distBars,
        }
    };
}

// Calculate Wall Brick for One Floor
function calculateWallBrick(wastage, label, notes) {
    // Get wall layout inputs
    const horizontalGridsStr = document.getElementById('wallBrickHorizontalGrids').value;
    const verticalGridsStr = document.getElementById('wallBrickVerticalGrids').value;
    const horizontalLines = parseInt(document.getElementById('wallBrickHorizontalLines').value) || 1;
    const verticalLines = parseInt(document.getElementById('wallBrickVerticalLines').value) || 1;
    
    if (!horizontalGridsStr || !verticalGridsStr) {
        alert('Please provide horizontal and vertical wall distances.');
        return null;
    }
    
    // Parse grid distances
    const horizontalGrids = horizontalGridsStr.split(',').map(v => parseFloat(v.trim())).filter(v => !isNaN(v) && v > 0);
    const verticalGrids = verticalGridsStr.split(',').map(v => parseFloat(v.trim())).filter(v => !isNaN(v) && v > 0);
    
    if (horizontalGrids.length === 0 || verticalGrids.length === 0) {
        alert('Please provide valid wall distances.');
        return null;
    }
    
    // Get wall dimensions
    const wallHeight = parseFloat(document.getElementById('wallBrickHeight').value);
    const wallThickness = parseFloat(document.getElementById('wallBrickThickness').value) || 230; // mm
    const openingDeduction = parseFloat(document.getElementById('wallBrickOpeningDeduction').value) || 15; // %
    
    if (!wallHeight || wallHeight <= 0) {
        alert('Please provide wall height.');
        return null;
    }
    
    // Get brick details
    const brickLength = parseFloat(document.getElementById('wallBrickLength').value) || 0.2; // m
    const brickWidth = parseFloat(document.getElementById('wallBrickWidth').value) || 0.1; // m
    const brickHeight = parseFloat(document.getElementById('wallBrickHeightSize').value) || 0.1; // m
    const mortarThickness = parseFloat(document.getElementById('wallBrickMortarThickness').value) || 10; // mm
    
    // Get mortar ratio
    const mortarMix = document.getElementById('wallBrickMortarMix').value;
    const ratio = mortarRatios[mortarMix] || [1, 6];
    
    // Calculate total wall length
    const totalHorizontalSpan = horizontalGrids.reduce((sum, val) => sum + (val / 1000), 0); // m
    const totalVerticalSpan = verticalGrids.reduce((sum, val) => sum + (val / 1000), 0); // m
    
    const horizontalWallLength = totalHorizontalSpan * horizontalLines; // m
    const verticalWallLength = totalVerticalSpan * verticalLines; // m
    const totalWallLength = horizontalWallLength + verticalWallLength; // m
    
    // Calculate wall volume
    const wallThicknessM = wallThickness / 1000; // Convert mm to meters
    const totalWallVolume = totalWallLength * wallHeight * wallThicknessM; // m³
    
    // Apply opening deduction
    const netWallVolume = totalWallVolume * (1 - openingDeduction / 100); // m³
    const netWallArea = totalWallLength * wallHeight * (1 - openingDeduction / 100); // m²
    
    // Calculate number of bricks
    // Volume of one brick with mortar
    const mortarThicknessM = mortarThickness / 1000; // Convert mm to meters
    const brickVolumeWithMortar = (brickLength + mortarThicknessM) * (brickWidth + mortarThicknessM) * (brickHeight + mortarThicknessM); // m³
    
    // Number of bricks = wall volume / volume of one brick with mortar
    const numberOfBricks = Math.ceil(netWallVolume / brickVolumeWithMortar);
    
    // Calculate mortar volume
    const brickVolume = brickLength * brickWidth * brickHeight; // m³
    const totalBrickVolume = numberOfBricks * brickVolume; // m³
    const mortarVolume = netWallVolume - totalBrickVolume; // m³
    
    // Calculate cement and sand for mortar
    const dryVolume = mortarVolume * 1.33; // 33% more for dry volume
    const wastageFactor = 1 + wastage / 100;
    const totalParts = ratio[0] + ratio[1];
    
    const cementVolume = (dryVolume * (ratio[0] / totalParts)) * wastageFactor; // m³
    const sandVolume = (dryVolume * (ratio[1] / totalParts)) * wastageFactor; // m³
    
    const cementBags = cementVolume / 0.035; // 1 bag = 0.035 m³
    const waterLitres = cementBags * 50 * 0.5; // Standard water calculation
    
    // Calculate bricks with wastage
    const bricksWithWastage = Math.ceil(numberOfBricks * wastageFactor);
    
    let description = `Wall Brick Calculation | Total Wall Length: ${roundValue(totalWallLength, 2)}m | Height: ${wallHeight}m | Thickness: ${wallThickness}mm`;
    description += ` | Opening Deduction: ${openingDeduction}% | Bricks: ${bricksWithWastage} nos | Mortar: ${mortarMix}`;
    if (label) description += ` | ${label}`;
    
    return {
        id: Date.now(),
        work_type: 'Wall Brick Calculation',
        description,
        notes,
        materials: {
            bricks_units: bricksWithWastage,
            bricks_units_exact: bricksWithWastage,
            cement_bags: roundValue(cementBags),
            cement_bags_exact: cementBags,
            sand_m3: roundValue(sandVolume),
            sand_m3_exact: sandVolume,
            water_litres: roundValue(waterLitres),
            water_litres_exact: waterLitres,
            wall_area_m2: roundValue(netWallArea),
            wall_area_m2_exact: netWallArea,
            wall_volume_m3: roundValue(netWallVolume),
            wall_volume_m3_exact: netWallVolume,
            mortar_volume_m3: roundValue(mortarVolume),
            mortar_volume_m3_exact: mortarVolume,
        }
    };
}

// Calculate Wall Plaster for One Floor
// Example: 11m × 9m building with 3m height
// Input: Horizontal Grids = 11000mm (11m), Vertical Grids = 9000mm (9m)
//        Horizontal Lines = 2, Vertical Lines = 2
// Calculation:
//   - Total Horizontal Span = 11m
//   - Total Vertical Span = 9m
//   - Horizontal Wall Length = 11m × 2 = 22m (top + bottom walls)
//   - Vertical Wall Length = 9m × 2 = 18m (left + right walls)
//   - Total Wall Length = 22m + 18m = 40m (perimeter)
//   - Gross Wall Area = 40m × 3m = 120 m²
//   - Net Wall Area (15% opening deduction) = 120 × 0.85 = 102 m²
//   - For Combined: Inside = 102 m², Outside = 102 m², Total = 204 m²
//   - Inside Plaster (12mm, 1:4 mix):
//     * Wet Volume = 102 × 0.012 = 1.224 m³
//     * Dry Volume = 1.224 × 1.33 = 1.628 m³
//     * Cement = (1.628 × 1/5) / 0.035 = 9.3 bags
//     * Sand = 1.628 × 4/5 = 1.302 m³
//   - Outside Plaster (15mm, 1:4 mix):
//     * Wet Volume = 102 × 0.015 = 1.53 m³
//     * Dry Volume = 1.53 × 1.33 = 2.035 m³
//     * Cement = (2.035 × 1/5) / 0.035 = 11.6 bags
//     * Sand = 2.035 × 4/5 = 1.628 m³
//   - Total: Cement = 20.9 bags, Sand = 2.93 m³ (without wastage)
function calculateWallPlaster(wastage, label, notes) {
    const calcType = document.getElementById('wallPlasterCalculationType')?.value || '';
    
    if (!calcType) {
        alert('Please select calculation type (Combined, Inside, or Outside).');
        return null;
    }
    
    let insideArea = 0;
    let outsideArea = 0;
    let insideThickness = 0;
    let outsideThickness = 0;
    let insideMix = '';
    let outsideMix = '';
    let openingDeduction = 15;
    
    // Get common wall layout data
    let horizontalGridsStr, verticalGridsStr, horizontalLines, verticalLines, wallHeight;
    
    if (calcType === 'combined') {
        horizontalGridsStr = document.getElementById('wallPlasterCombinedHorizontalGrids').value;
        verticalGridsStr = document.getElementById('wallPlasterCombinedVerticalGrids').value;
        horizontalLines = parseInt(document.getElementById('wallPlasterCombinedHorizontalLines').value) || 1;
        verticalLines = parseInt(document.getElementById('wallPlasterCombinedVerticalLines').value) || 1;
        wallHeight = parseFloat(document.getElementById('wallPlasterCombinedHeight').value);
        openingDeduction = parseFloat(document.getElementById('wallPlasterCombinedOpeningDeduction').value) || 15;
        insideThickness = parseFloat(document.getElementById('wallPlasterCombinedInsideThickness').value) || 12;
        outsideThickness = parseFloat(document.getElementById('wallPlasterCombinedOutsideThickness').value) || 15;
        insideMix = document.getElementById('wallPlasterCombinedInsideMix').value;
        outsideMix = document.getElementById('wallPlasterCombinedOutsideMix').value;
    } else if (calcType === 'inside') {
        horizontalGridsStr = document.getElementById('wallPlasterInsideHorizontalGrids').value;
        verticalGridsStr = document.getElementById('wallPlasterInsideVerticalGrids').value;
        horizontalLines = parseInt(document.getElementById('wallPlasterInsideHorizontalLines').value) || 1;
        verticalLines = parseInt(document.getElementById('wallPlasterInsideVerticalLines').value) || 1;
        wallHeight = parseFloat(document.getElementById('wallPlasterInsideHeight').value);
        openingDeduction = parseFloat(document.getElementById('wallPlasterInsideOpeningDeduction').value) || 15;
        insideThickness = parseFloat(document.getElementById('wallPlasterInsideThickness').value) || 12;
        insideMix = document.getElementById('wallPlasterInsideMix').value;
    } else if (calcType === 'outside') {
        horizontalGridsStr = document.getElementById('wallPlasterOutsideHorizontalGrids').value;
        verticalGridsStr = document.getElementById('wallPlasterOutsideVerticalGrids').value;
        horizontalLines = parseInt(document.getElementById('wallPlasterOutsideHorizontalLines').value) || 1;
        verticalLines = parseInt(document.getElementById('wallPlasterOutsideVerticalLines').value) || 1;
        wallHeight = parseFloat(document.getElementById('wallPlasterOutsideHeight').value);
        openingDeduction = parseFloat(document.getElementById('wallPlasterOutsideOpeningDeduction').value) || 15;
        outsideThickness = parseFloat(document.getElementById('wallPlasterOutsideThickness').value) || 15;
        outsideMix = document.getElementById('wallPlasterOutsideMix').value;
    }
    
    if (!horizontalGridsStr || !verticalGridsStr) {
        alert('Please provide horizontal and vertical wall distances.');
        return null;
    }
    
    if (!wallHeight || wallHeight <= 0) {
        alert('Please provide wall height.');
        return null;
    }
    
    // Parse grid distances
    const horizontalGrids = horizontalGridsStr.split(',').map(v => parseFloat(v.trim())).filter(v => !isNaN(v) && v > 0);
    const verticalGrids = verticalGridsStr.split(',').map(v => parseFloat(v.trim())).filter(v => !isNaN(v) && v > 0);
    
    if (horizontalGrids.length === 0 || verticalGrids.length === 0) {
        alert('Please provide valid wall distances.');
        return null;
    }
    
    // Calculate total wall length
    const totalHorizontalSpan = horizontalGrids.reduce((sum, val) => sum + (val / 1000), 0); // m
    const totalVerticalSpan = verticalGrids.reduce((sum, val) => sum + (val / 1000), 0); // m
    
    const horizontalWallLength = totalHorizontalSpan * horizontalLines; // m
    const verticalWallLength = totalVerticalSpan * verticalLines; // m
    const totalWallLength = horizontalWallLength + verticalWallLength; // m
    
    // Calculate wall area (both sides for combined, single side for individual)
    const grossWallArea = totalWallLength * wallHeight; // m²
    const netWallArea = grossWallArea * (1 - openingDeduction / 100); // m² (after opening deduction)
    
    if (calcType === 'combined') {
        // Both inside and outside
        insideArea = netWallArea;
        outsideArea = netWallArea;
    } else if (calcType === 'inside') {
        insideArea = netWallArea;
    } else if (calcType === 'outside') {
        outsideArea = netWallArea;
    }
    
    // Calculate materials for inside plaster
    let insideCementBags = 0;
    let insideSandM3 = 0;
    if (insideArea > 0 && insideThickness > 0 && insideMix) {
        const insideThicknessM = insideThickness / 1000; // Convert mm to meters
        const insideWetVolume = insideArea * insideThicknessM; // m³
        const insideRatio = mortarRatios[insideMix] || [1, 4];
        const insideTotalParts = insideRatio[0] + insideRatio[1];
        const insideDryVolume = insideWetVolume * 1.33 * (1 + wastage / 100);
        const insideCementVolume = insideDryVolume * (insideRatio[0] / insideTotalParts);
        const insideSandVolume = insideDryVolume * (insideRatio[1] / insideTotalParts);
        insideCementBags = insideCementVolume / 0.035;
        insideSandM3 = insideSandVolume;
    }
    
    // Calculate materials for outside plaster
    let outsideCementBags = 0;
    let outsideSandM3 = 0;
    if (outsideArea > 0 && outsideThickness > 0 && outsideMix) {
        const outsideThicknessM = outsideThickness / 1000; // Convert mm to meters
        const outsideWetVolume = outsideArea * outsideThicknessM; // m³
        const outsideRatio = mortarRatios[outsideMix] || [1, 4];
        const outsideTotalParts = outsideRatio[0] + outsideRatio[1];
        const outsideDryVolume = outsideWetVolume * 1.33 * (1 + wastage / 100);
        const outsideCementVolume = outsideDryVolume * (outsideRatio[0] / outsideTotalParts);
        const outsideSandVolume = outsideDryVolume * (outsideRatio[1] / outsideTotalParts);
        outsideCementBags = outsideCementVolume / 0.035;
        outsideSandM3 = outsideSandVolume;
    }
    
    // Total materials
    const totalCementBags = insideCementBags + outsideCementBags;
    const totalSandM3 = insideSandM3 + outsideSandM3;
    const totalPlasterArea = insideArea + outsideArea;
    
    // Labor calculation: approximately 0.15 man-day per m² for skilled, 0.3 man-day per m² for unskilled
    const skilledManDays = totalPlasterArea * 0.15;
    const unskilledManDays = totalPlasterArea * 0.3;
    
    // Build description
    let description = `Wall Plaster Calculation | Type: ${calcType.charAt(0).toUpperCase() + calcType.slice(1)}`;
    description += ` | Total Wall Length: ${roundValue(totalWallLength, 2)}m | Height: ${wallHeight}m`;
    description += ` | Opening Deduction: ${openingDeduction}%`;
    
    if (calcType === 'combined') {
        description += ` | Inside: ${roundValue(insideArea, 2)}m² @ ${insideThickness}mm (${insideMix})`;
        description += ` | Outside: ${roundValue(outsideArea, 2)}m² @ ${outsideThickness}mm (${outsideMix})`;
    } else if (calcType === 'inside') {
        description += ` | Inside: ${roundValue(insideArea, 2)}m² @ ${insideThickness}mm (${insideMix})`;
    } else if (calcType === 'outside') {
        description += ` | Outside: ${roundValue(outsideArea, 2)}m² @ ${outsideThickness}mm (${outsideMix})`;
    }
    
    if (label) description += ` | ${label}`;
    
    return {
        id: Date.now(),
        work_type: 'Wall Plaster Calculation',
        description,
        notes,
        materials: {
            cement_bags: roundValue(totalCementBags),
            cement_bags_exact: totalCementBags,
            sand_m3: roundValue(totalSandM3),
            sand_m3_exact: totalSandM3,
            plaster_area_m2: roundValue(totalPlasterArea),
            plaster_area_m2_exact: totalPlasterArea,
            inside_plaster_area_m2: roundValue(insideArea),
            inside_plaster_area_m2_exact: insideArea,
            outside_plaster_area_m2: roundValue(outsideArea),
            outside_plaster_area_m2_exact: outsideArea,
            skilled_labor_days: roundValue(skilledManDays, 2),
            unskilled_labor_days: roundValue(unskilledManDays, 2),
            wall_length_m: roundValue(totalWallLength, 2),
            wall_length_m_exact: totalWallLength,
        }
    };
}

// Calculate Masonry Wall (Retaining Wall)
function calculateMasonryWall(wastage, label, notes) {
    const height = parseFloat(document.getElementById('masonryWallHeight').value);
    const length = parseFloat(document.getElementById('masonryWallLength').value);
    const masonryType = document.getElementById('masonryWallType')?.value || 'stone';
    const mortarRatioStr = document.getElementById('masonryWallMortarRatio')?.value || '1:5';
    const soilType = document.getElementById('masonryWallSoilType')?.value || '';
    
    if (!height || height <= 0) {
        alert('Please provide wall height (H) in meters.');
        return null;
    }
    
    if (!length || length <= 0) {
        alert('Please provide wall length (L) in meters.');
        return null;
    }
    
    // Design assumptions for Stone Masonry Retaining Wall
    // Base width (B) = 0.6 × Height (H)
    const baseWidth = 0.6 * height;
    
    // Top width (T) based on height
    let topWidth;
    if (height <= 2.0) {
        topWidth = 0.45;
    } else if (height > 2.0 && height <= 3.0) {
        topWidth = 0.50;
    } else {
        topWidth = 0.60;
    }
    
    // Wall volume calculation (trapezoidal section)
    // Volume = ((Top width + Base width) / 2) × Height × Length
    const wallVolume = ((topWidth + baseWidth) / 2) * height * length;
    
    // Material breakup for stone masonry
    // Stone volume = 70% of masonry volume
    // Mortar volume = 30% of masonry volume
    const stoneVolume = wallVolume * 0.70;
    const mortarWetVolume = wallVolume * 0.30;
    
    // Mortar calculation
    // Parse mortar ratio (e.g., "1:5" -> [1, 5])
    const ratioParts = mortarRatioStr.split(':').map(v => parseFloat(v.trim()));
    const cementRatio = ratioParts[0] || 1;
    const sandRatio = ratioParts[1] || 5;
    const totalRatio = cementRatio + sandRatio;
    
    // Dry volume factor = 1.33
    const dryVolume = mortarWetVolume * 1.33 * (1 + wastage / 100);
    
    // Cement calculation
    // Cement volume = (dry mortar volume × cement ratio) / total ratio
    const cementVolume = dryVolume * (cementRatio / totalRatio);
    
    // Convert cement volume to bags
    // Cement density = 1440 kg/m³
    // 1 cement bag = 50 kg
    // Cement weight = cementVolume × 1440 kg
    // Cement bags = cementWeight / 50
    const cementWeight = cementVolume * 1440; // kg
    const cementBags = cementWeight / 50;
    
    // Sand calculation
    // Sand volume = (dry mortar volume × sand ratio) / total ratio
    const sandVolume = dryVolume * (sandRatio / totalRatio);
    
    // Build description
    let description = `Masonry Wall (Retaining Wall) | ${masonryType === 'stone' ? 'Stone' : 'Brick'} Masonry`;
    description += ` | H: ${roundValue(height, 2)}m | L: ${roundValue(length, 2)}m`;
    description += ` | Base: ${roundValue(baseWidth, 2)}m | Top: ${roundValue(topWidth, 2)}m`;
    description += ` | Mortar: ${mortarRatioStr}`;
    if (soilType) {
        description += ` | Soil: ${soilType.charAt(0).toUpperCase() + soilType.slice(1)}`;
    }
    if (label) {
        description += ` | ${label}`;
    }
    
    return {
        id: Date.now(),
        work_type: 'Masonry Wall Calculation',
        description,
        notes,
        materials: {
            // Wall dimensions
            wall_height_m: roundValue(height, 2),
            wall_height_m_exact: height,
            wall_length_m: roundValue(length, 2),
            wall_length_m_exact: length,
            base_width_m: roundValue(baseWidth, 2),
            base_width_m_exact: baseWidth,
            top_width_m: roundValue(topWidth, 2),
            top_width_m_exact: topWidth,
            // Volumes
            wall_volume_m3: roundValue(wallVolume, 2),
            wall_volume_m3_exact: wallVolume,
            stone_volume_m3: roundValue(stoneVolume, 2),
            stone_volume_m3_exact: stoneVolume,
            mortar_volume_m3: roundValue(mortarWetVolume, 2),
            mortar_volume_m3_exact: mortarWetVolume,
            // Materials
            cement_bags: roundValue(cementBags, 2),
            cement_bags_exact: cementBags,
            sand_m3: roundValue(sandVolume, 2),
            sand_m3_exact: sandVolume,
            // Additional info
            masonry_type: masonryType,
            mortar_ratio: mortarRatioStr,
            soil_type: soilType || null,
        }
    };
}

function calculateBeamLayout(wastage, label, notes) {
    // Get grid layout inputs
    const horizontalGridsStr = document.getElementById('beamHorizontalGrids').value;
    const verticalGridsStr = document.getElementById('beamVerticalGrids').value;
    const horizontalLines = parseInt(document.getElementById('beamHorizontalLines').value) || 1;
    const verticalLines = parseInt(document.getElementById('beamVerticalLines').value) || 1;
    
    if (!horizontalGridsStr || !verticalGridsStr) {
        alert('Please provide horizontal and vertical grid distances.');
        return null;
    }
    
    // Parse grid distances
    const horizontalGrids = horizontalGridsStr.split(',').map(v => parseFloat(v.trim())).filter(v => !isNaN(v) && v > 0);
    const verticalGrids = verticalGridsStr.split(',').map(v => parseFloat(v.trim())).filter(v => !isNaN(v) && v > 0);
    
    if (horizontalGrids.length === 0 || verticalGrids.length === 0) {
        alert('Please provide valid grid distances.');
        return null;
    }
    
    // Get beam section data
    const beamWidth = parseFloat(document.getElementById('beamWidth').value); // mm
    const beamDepth = parseFloat(document.getElementById('beamDepth').value); // mm
    const clearCover = parseFloat(document.getElementById('beamCover').value) || 40; // mm
    
    if (!beamWidth || beamWidth <= 0 || !beamDepth || beamDepth <= 0) {
        alert('Please provide beam width and depth.');
        return null;
    }
    
    // Get main reinforcement data
    const bottomBarDia = parseFloat(document.getElementById('beamBottomBarDia').value); // mm
    const bottomBarQty = parseInt(document.getElementById('beamBottomBarQty').value) || 2;
    const topBarDia = parseFloat(document.getElementById('beamTopBarDia').value); // mm
    const topBarQty = parseInt(document.getElementById('beamTopBarQty').value) || 2;
    const hasExtraTopBar = document.getElementById('beamExtraTopBar').value === 'yes';
    const extraBarDia = hasExtraTopBar ? parseFloat(document.getElementById('beamExtraBarDia').value) : 0;
    const extraBarLength = hasExtraTopBar ? parseFloat(document.getElementById('beamExtraBarLength').value) : 0;
    
    // Get stirrup data
    const stirrupDia = parseFloat(document.getElementById('beamStirrupDia').value); // mm
    const stirrupSpacingMid = parseFloat(document.getElementById('beamStirrupSpacingMid').value) || 200; // mm
    const stirrupSpacingSupport = parseFloat(document.getElementById('beamStirrupSpacingSupport').value) || 100; // mm
    const closeSpacingLength = parseFloat(document.getElementById('beamCloseSpacingLength').value) || 0.5; // m
    
    // Convert all to meters
    const totalHorizontalSpan = horizontalGrids.reduce((sum, val) => sum + (val / 1000), 0); // m
    const totalVerticalSpan = verticalGrids.reduce((sum, val) => sum + (val / 1000), 0); // m
    
    // Calculate total beam length
    // Each grid span becomes a beam segment
    const horizontalBeamSegments = horizontalGrids.length; // Number of spans = number of segments
    const verticalBeamSegments = verticalGrids.length; // Number of spans = number of segments
    
    // Total beam length = sum of all grid distances × number of beam lines
    const horizontalBeamLength = horizontalGrids.reduce((sum, val) => sum + (val / 1000), 0) * horizontalLines; // m
    const verticalBeamLength = verticalGrids.reduce((sum, val) => sum + (val / 1000), 0) * verticalLines; // m
    const totalBeamLength = horizontalBeamLength + verticalBeamLength; // m
    const totalBeams = (horizontalBeamSegments * horizontalLines) + (verticalBeamSegments * verticalLines);
    
    // Calculate beam clear length for each grid span (assuming column width of 0.3m for deduction at each end)
    const avgColumnWidth = 0.3; // m (assumed column width)
    
    // Process horizontal beams
    let totalBottomBarLength = 0;
    let totalTopBarLength = 0;
    let totalStirrupLength = 0;
    
    // Development length (Ld = 40d)
    const bottomBarLd = 40 * (bottomBarDia / 1000); // m
    const topBarLd = 40 * (topBarDia / 1000); // m
    const extraBarLd = hasExtraTopBar ? 40 * (extraBarDia / 1000) : 0; // m
    
    // Calculate stirrups
    const beamWidthM = beamWidth / 1000; // m
    const beamDepthM = beamDepth / 1000; // m
    const coverM = clearCover / 1000; // m
    const stirrupDiaM = stirrupDia / 1000; // m
    
    // Stirrup cutting length = 2(b - 2C) + 2(D - 2C) + hooks (10d per hook, 2 hooks = 20d)
    const stirrupHookLength = 20 * stirrupDiaM; // m (10d per hook × 2)
    const stirrupCuttingLength = (2 * (beamWidthM - 2 * coverM)) + (2 * (beamDepthM - 2 * coverM)) + stirrupHookLength; // m
    
    // Process each horizontal grid span
    horizontalGrids.forEach((gridDist, index) => {
        const gridDistM = gridDist / 1000; // m
        const beamClearLength = gridDistM - (2 * avgColumnWidth); // Clear length after deducting column widths
        
        if (beamClearLength > 0) {
            // Calculate cutting length of one bar for this span
            const oneBottomBarLength = beamClearLength + (2 * bottomBarLd); // m
            const oneTopBarLength = beamClearLength + (2 * topBarLd); // m
            
            // Total bars for this span (multiply by number of horizontal beam lines)
            totalBottomBarLength += horizontalLines * bottomBarQty * oneBottomBarLength; // m
            totalTopBarLength += horizontalLines * topBarQty * oneTopBarLength; // m
            
            // Number of stirrups for this beam span
            // Support zone: calculate stirrups for one zone, then multiply by 2 (one at each end)
            const supportZoneSpacingM = stirrupSpacingSupport / 1000; // m
            const stirrupsPerSupportZone = Math.ceil(closeSpacingLength / supportZoneSpacingM); // stirrups in one support zone
            const supportZoneStirrups = 2 * stirrupsPerSupportZone; // 2 support zones (one at each end)
            
            const midZoneLength = beamClearLength - (2 * closeSpacingLength); // m
            const midZoneSpacingM = stirrupSpacingMid / 1000; // m
            const midZoneStirrups = midZoneLength > 0 ? Math.ceil(midZoneLength / midZoneSpacingM) : 0;
            const stirrupsPerSpan = supportZoneStirrups + midZoneStirrups;
            
            totalStirrupLength += horizontalLines * stirrupsPerSpan * stirrupCuttingLength; // m
        }
    });
    
    // Process each vertical grid span
    verticalGrids.forEach((gridDist, index) => {
        const gridDistM = gridDist / 1000; // m
        const beamClearLength = gridDistM - (2 * avgColumnWidth); // Clear length after deducting column widths
        
        if (beamClearLength > 0) {
            // Calculate cutting length of one bar for this span
            const oneBottomBarLength = beamClearLength + (2 * bottomBarLd); // m
            const oneTopBarLength = beamClearLength + (2 * topBarLd); // m
            
            // Total bars for this span (multiply by number of vertical beam lines)
            totalBottomBarLength += verticalLines * bottomBarQty * oneBottomBarLength; // m
            totalTopBarLength += verticalLines * topBarQty * oneTopBarLength; // m
            
            // Number of stirrups for this beam span
            // Support zone: calculate stirrups for one zone, then multiply by 2 (one at each end)
            const supportZoneSpacingM = stirrupSpacingSupport / 1000; // m
            const stirrupsPerSupportZone = Math.ceil(closeSpacingLength / supportZoneSpacingM); // stirrups in one support zone
            const supportZoneStirrups = 2 * stirrupsPerSupportZone; // 2 support zones (one at each end)
            
            const midZoneLength = beamClearLength - (2 * closeSpacingLength); // m
            const midZoneSpacingM = stirrupSpacingMid / 1000; // m
            const midZoneStirrups = midZoneLength > 0 ? Math.ceil(midZoneLength / midZoneSpacingM) : 0;
            const stirrupsPerSpan = supportZoneStirrups + midZoneStirrups;
            
            totalStirrupLength += verticalLines * stirrupsPerSpan * stirrupCuttingLength; // m
        }
    });
    
    // Calculate extra top bars (2 supports per beam span)
    const totalBeamSpans = totalBeams;
    const oneExtraBarLength = hasExtraTopBar ? (extraBarLength + (2 * extraBarLd)) : 0; // m
    const totalExtraBarLength = hasExtraTopBar ? (totalBeamSpans * 2 * oneExtraBarLength) : 0; // 2 supports per beam span
    
    // Calculate weights using D²/162 formula
    const bottomBarWeightPerM = (bottomBarDia * bottomBarDia) / 162; // kg/m
    const topBarWeightPerM = (topBarDia * topBarDia) / 162; // kg/m
    const extraBarWeightPerM = hasExtraTopBar ? (extraBarDia * extraBarDia) / 162 : 0; // kg/m
    const stirrupWeightPerM = (stirrupDia * stirrupDia) / 162; // kg/m
    
    const bottomBarWeight = totalBottomBarLength * bottomBarWeightPerM; // kg
    const topBarWeight = totalTopBarLength * topBarWeightPerM; // kg
    const extraBarWeight = totalExtraBarLength * extraBarWeightPerM; // kg
    const stirrupWeight = totalStirrupLength * stirrupWeightPerM; // kg
    
    // Total weight with wastage
    const wastageFactor = 1 + wastage / 100;
    const totalWeight = (bottomBarWeight + topBarWeight + extraBarWeight + stirrupWeight) * wastageFactor; // kg
    
    // Create description
    let description = `Beam Layout | H-Grids: ${horizontalGrids.join(', ')}mm (${horizontalLines} lines) | V-Grids: ${verticalGrids.join(', ')}mm (${verticalLines} lines)`;
    description += ` | Total Beam Length: ${roundValue(totalBeamLength, 2)}m (${totalBeams} beams)`;
    description += ` | Section: ${beamWidth}×${beamDepth}mm, Cover: ${clearCover}mm`;
    description += ` | Bottom: ${bottomBarQty}@${bottomBarDia}mm | Top: ${topBarQty}@${topBarDia}mm`;
    if (hasExtraTopBar) {
        description += ` | Extra: ${extraBarDia}mm@${extraBarLength}m`;
    }
    description += ` | Stirrups: ${stirrupDia}mm @ ${stirrupSpacingSupport}mm/${stirrupSpacingMid}mm`;
    if (label) {
        description += ` | ${label}`;
    }
    
    return {
        id: Date.now(),
        work_type: 'Footing - Beam Layout',
        description,
        notes,
        materials: {
            steel_kg: roundValue(totalWeight),
            steel_kg_exact: totalWeight,
            steel_length_m: roundValue(totalBeamLength),
            steel_length_m_exact: totalBeamLength,
            steel_bars: totalBeams,
            // Beam specific details
            beam_total_length_m: roundValue(totalBeamLength),
            beam_total_length_m_exact: totalBeamLength,
            beam_count: totalBeams,
            // Bottom bars
            bottom_bars_count: totalBeams * bottomBarQty,
            bottom_bars_diameter_mm: bottomBarDia,
            bottom_bars_length_m: roundValue(totalBottomBarLength),
            bottom_bars_length_m_exact: totalBottomBarLength,
            bottom_bars_kg: roundValue(bottomBarWeight * wastageFactor),
            bottom_bars_kg_exact: bottomBarWeight * wastageFactor,
            // Top bars
            top_bars_count: totalBeams * topBarQty,
            top_bars_diameter_mm: topBarDia,
            top_bars_length_m: roundValue(totalTopBarLength),
            top_bars_length_m_exact: totalTopBarLength,
            top_bars_kg: roundValue(topBarWeight * wastageFactor),
            top_bars_kg_exact: topBarWeight * wastageFactor,
            // Extra bars
            extra_bars_count: hasExtraTopBar ? totalBeams * 2 : 0,
            extra_bars_diameter_mm: extraBarDia,
            extra_bars_length_m: roundValue(totalExtraBarLength),
            extra_bars_length_m_exact: totalExtraBarLength,
            extra_bars_kg: roundValue(extraBarWeight * wastageFactor),
            extra_bars_kg_exact: extraBarWeight * wastageFactor,
            // Stirrups
            stirrups_count: Math.round(totalStirrupLength / stirrupCuttingLength),
            stirrups_diameter_mm: stirrupDia,
            stirrups_length_m: roundValue(totalStirrupLength),
            stirrups_length_m_exact: totalStirrupLength,
            stirrups_kg: roundValue(stirrupWeight * wastageFactor),
            stirrups_kg_exact: stirrupWeight * wastageFactor,
        }
    };
}

function calculateSteelForFooting(wastage, label, notes, diameter, totalLength, numberOfBars, element, method, spacing, length, width, mainBars, mainTotalLength, distBars, distTotalLength, footingDetails, mainDiameter, distDiameter, clearCover, mainDirection) {
    // Use separate diameters for main and distribution bars if provided (for Slab), otherwise use single diameter
    const mainBarDiameter = mainDiameter || diameter;
    const distBarDiameter = distDiameter || diameter;
    
    // Calculate weight per meter: D² / 162 (kg/m) - standard formula
    const mainWeightPerMeter = (mainBarDiameter * mainBarDiameter) / 162;
    const distWeightPerMeter = (distBarDiameter * distBarDiameter) / 162;
    const wastageFactor = 1 + wastage / 100;
    
    let materials = {
        steel_diameter_mm: diameter, // Keep for backward compatibility
    };
    
    if (method === 'area' && mainBars !== undefined && distBars !== undefined) {
        // Calculate main bars weight using main bar diameter
        const mainWeight = mainTotalLength * mainWeightPerMeter * wastageFactor;
        // Calculate distribution bars weight using distribution bar diameter
        const distWeight = distTotalLength * distWeightPerMeter * wastageFactor;
        // Combined total weight
        const totalWeight = mainWeight + distWeight;
        
        materials.main_bars = mainBars;
        materials.main_bars_diameter_mm = mainBarDiameter;
        materials.main_bars_length_m = roundValue(mainTotalLength);
        materials.main_bars_length_m_exact = mainTotalLength;
        materials.main_bars_kg = roundValue(mainWeight);
        materials.main_bars_kg_exact = mainWeight;
        
        materials.distribution_bars = distBars;
        materials.distribution_bars_diameter_mm = distBarDiameter;
        materials.distribution_bars_length_m = roundValue(distTotalLength);
        materials.distribution_bars_length_m_exact = distTotalLength;
        materials.distribution_bars_kg = roundValue(distWeight);
        materials.distribution_bars_kg_exact = distWeight;
        
        materials.steel_kg = roundValue(totalWeight);
        materials.steel_kg_exact = totalWeight;
        materials.steel_length_m = roundValue(totalLength);
        materials.steel_length_m_exact = totalLength;
        materials.steel_bars = numberOfBars;
        
        let description = `Footing - Steel`;
        if (element === 'Slab' && mainBarDiameter !== distBarDiameter) {
            description += ` Main: ${mainBarDiameter}mm, Dist: ${distBarDiameter}mm`;
        } else {
            description += ` Main: ${mainBarDiameter}mm, Dist: ${distBarDiameter}mm`;
        }
        
        // Get spacing values for description (from parameter or DOM)
        let mainSpacing, distSpacing;
        if (spacing && spacing.includes(',')) {
            const spacingParts = spacing.split(',');
            mainSpacing = spacingParts[0] || '';
            distSpacing = spacingParts[1] || '';
        } else {
            mainSpacing = document.getElementById('footingSteelMainSpacing')?.value || '';
            distSpacing = document.getElementById('footingSteelDistSpacing')?.value || '';
        }
        const coverValue = clearCover || document.getElementById('footingSteelCover')?.value || '';
        const directionValue = mainDirection || document.getElementById('footingSteelMainDirection')?.value || 'length';
        
        description += ` | L: ${roundValue(length, 2)}m × W: ${roundValue(width, 2)}m`;
        if (coverValue) {
            description += ` | Cover: ${coverValue}mm`;
        }
        description += ` | Main: ${directionValue === 'length' ? 'Along Length' : 'Along Width'} @ ${mainSpacing}mm`;
        description += ` | Dist: ${directionValue === 'length' ? 'Along Width' : 'Along Length'} @ ${distSpacing}mm`;
        
        if (footingDetails && footingDetails.length > 0) {
            const footingDesc = footingDetails.map(f => {
                return `${f.label}: ${f.length}${getUnitLabel(f.lengthUnit)} × ${f.width}${getUnitLabel(f.widthUnit)} (Qty: ${f.quantity})`;
            }).join(' | ');
            description += ` | ${footingDesc}`;
        }
        
        // Show cutting length per bar and total
        const oneMainBarLength = footingDetails && footingDetails.length > 0 ? footingDetails[0].mainBarLength : 0;
        const oneDistBarLength = footingDetails && footingDetails.length > 0 ? footingDetails[0].distBarLength : 0;
        
        description += ` | Main: ${mainBars} bars @ ${mainBarDiameter}mm (${roundValue(oneMainBarLength, 3)}m/bar, ${roundValue(mainTotalLength, 2)}m total, ${roundValue(mainWeight, 2)}kg)`;
        description += ` | Dist: ${distBars} bars @ ${distBarDiameter}mm (${roundValue(oneDistBarLength, 3)}m/bar, ${roundValue(distTotalLength, 2)}m total, ${roundValue(distWeight, 2)}kg)`;
        description += ` | Total: ${numberOfBars} bars, ${roundValue(totalLength, 2)}m, ${roundValue(totalWeight, 2)}kg`;
        description += ` | ${element}`;
        if (label) {
            description += ` | ${label}`;
        }
        
        return {
            id: Date.now(),
            work_type: 'Footing - Steel / Rod',
            description,
            notes,
            materials: materials,
        };
    } else {
        // For length method, use simple calculation
        const totalWeight = totalLength * weightPerMeter * wastageFactor;
        
        materials.steel_kg = roundValue(totalWeight);
        materials.steel_kg_exact = totalWeight;
        materials.steel_length_m = roundValue(totalLength);
        materials.steel_length_m_exact = totalLength;
        materials.steel_bars = numberOfBars || 1;
        
        let description = `Footing - Steel ${diameter}mm | Length: ${totalLength}m (${numberOfBars} bars) | ${element}`;
        if (label) {
            description += ` | ${label}`;
        }
        
        return {
            id: Date.now(),
            work_type: 'Footing - Steel / Rod',
            description,
            notes,
            materials: materials,
        };
    }
}

function calculateFootingHattipaile(wastage, label, notes) {
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
    
    const skilledManDays = totalVolume * 0.5;
    const unskilledManDays = totalVolume * 1.0;
    
    const footingDesc = footingDetails.map(f => {
        return `${f.label}: ${f.length}${getUnitLabel(f.lengthUnit)} × ${f.width}${getUnitLabel(f.widthUnit)} × ${f.depth}${getUnitLabel(f.depthUnit)} (Qty: ${f.quantity})`;
    }).join(' | ');
    
    const description = `Footing - Hattipaile (Stepped) - Grade ${grade} | ${footingDesc}` + (label ? ` | ${label}` : '');
    
    return {
        id: Date.now(),
        work_type: 'Footing - Hattipaile (Stepped Footing)',
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
            footing_details: footingDetails,
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
        row.style.minHeight = '80px';
        row.innerHTML = `
            <td class="align-middle">${index + 1}</td>
            <td class="align-top" style="min-width: 300px; padding: 12px;">
                <div class="fw-semibold mb-2" style="font-size: 0.95rem; line-height: 1.4;">${item.work_type}</div>
                <div class="small text-muted mb-1" style="line-height: 1.5; word-wrap: break-word;">${item.description}</div>
                ${item.notes ? `<div class="small text-info mt-1" style="line-height: 1.4;"><i class="bi bi-info-circle me-1"></i>${item.notes}</div>` : ''}
            </td>
            <td>${item.materials.cement_bags_exact !== undefined ? formatValueWithRounding(item.materials.cement_bags_exact, item.materials.cement_bags, ' bags', 2) : (item.materials.cement_bags ?? '-')}</td>
            <td>${item.materials.sand_m3_exact !== undefined ? formatValueWithRounding(item.materials.sand_m3_exact, item.materials.sand_m3, ' m³', 3) : (item.materials.sand_m3 ?? '-')}</td>
            <td>${item.materials.aggregate_m3_exact !== undefined ? formatValueWithRounding(item.materials.aggregate_m3_exact, item.materials.aggregate_m3, ' m³', 3) : (item.materials.aggregate_m3 ?? '-')}</td>
            <td>${getBricksStoneDisplay(item)}</td>
            <td>${item.materials.water_litres_exact !== undefined ? formatValueWithRounding(item.materials.water_litres_exact, item.materials.water_litres, ' L', 2) : (item.materials.water_litres ?? '-')}</td>
            <td>${item.materials.soling_volume_m3_exact !== undefined ? formatValueWithRounding(item.materials.soling_volume_m3_exact, item.materials.soling_volume_m3, ' m³', 3) : (item.materials.soling_volume_m3 ? (item.materials.soling_volume_m3 + ' m³') : '-')}</td>
            <td>
                ${item.materials.steel_kg_exact !== undefined ? formatValueWithRounding(item.materials.steel_kg_exact, item.materials.steel_kg, ' kg', 2) : (item.materials.steel_kg ?? '-')}
                ${item.materials.main_bars !== undefined && item.materials.distribution_bars !== undefined ? 
                    `<br><small class="text-muted">Main: ${item.materials.main_bars} bars | Dist: ${item.materials.distribution_bars} bars</small>` : ''}
                ${item.materials.bottom_bars_count !== undefined ? 
                    `<br><small class="text-muted">Bottom: ${item.materials.bottom_bars_count}@${item.materials.bottom_bars_diameter_mm}mm | Top: ${item.materials.top_bars_count}@${item.materials.top_bars_diameter_mm}mm</small>` : ''}
                ${item.materials.stirrups_count !== undefined ? 
                    `<br><small class="text-muted">Stirrups: ${item.materials.stirrups_count}@${item.materials.stirrups_diameter_mm}mm</small>` : ''}
            </td>
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

