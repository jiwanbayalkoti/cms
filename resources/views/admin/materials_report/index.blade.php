@extends('admin.layout')

@section('title', 'Materials Report')

@section('content')
<style>
    .material-report-card { transition: transform 0.2s, box-shadow 0.2s; }
    .material-report-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.1) !important; }
    .material-report-progress { height: 6px; border-radius: 3px; background: #e9ecef; overflow: hidden; }
    .material-report-progress-bar { height: 100%; border-radius: 3px; transition: width 0.4s ease; }
    .stat-card { border-left: 4px solid transparent; }
    .border-left-primary { border-left-color: #0d6efd !important; }
    .border-left-success { border-left-color: #198754 !important; }
    .border-left-warning { border-left-color: #ffc107 !important; }
    .border-left-info { border-left-color: #0dcaf0 !important; }
    .low-stock-badge { position: absolute; top: 8px; right: 8px; }
</style>

<div class="d-flex flex-wrap justify-content-between align-items-start mb-4 gap-2">
    <div>
        <h1 class="h3 mb-1 fw-bold">Materials Report</h1>
        <p class="text-muted mb-0 small">Advanced materials tracking and analytics</p>
    </div>
    <div class="d-flex gap-2">
        @if(isset($totalsByMaterial) && $totalsByMaterial->isNotEmpty())
        <a href="{{ route('admin.materials-report.export.excel', request()->query()) }}" class="btn btn-success btn-sm d-inline-flex align-items-center gap-1">
            <i class="bi bi-file-earmark-excel"></i>
            <span class="d-none d-sm-inline">Export Excel</span>
        </a>
        @endif
        <a href="{{ route('admin.construction-materials.index') }}" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1">
            <i class="bi bi-box-seam"></i>
            <span class="d-none d-sm-inline">Materials</span>
        </a>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-4">
    <div class="card-header">
        <strong>Filters</strong>
    </div>
    <div class="card-body">
        <form method="get" action="{{ route('admin.materials-report.index') }}" id="filterForm">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small">Project</label>
                    <select name="project_id" class="form-select form-select-sm">
                        <option value="">All Projects</option>
                        @foreach($projects ?? [] as $p)
                            <option value="{{ $p->id }}" {{ (isset($projectId) ? $projectId : request('project_id')) == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Start Date</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">End Date</label>
                    <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Category</label>
                    <select name="category" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        @foreach($categories ?? [] as $catName => $catName)
                            <option value="{{ $catName }}" {{ ($category ?? '') == $catName ? 'selected' : '' }}>{{ $catName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Supplier</label>
                    <select name="supplier_id" class="form-select form-select-sm">
                        <option value="">All Suppliers</option>
                        @foreach($suppliers ?? [] as $supId => $supName)
                            <option value="{{ $supId }}" {{ (isset($supplierId) && $supplierId == $supId) ? 'selected' : '' }}>{{ $supName }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row g-3 mt-2">
                <div class="col-md-4">
                    <label class="form-label small">Search</label>
                    <input type="text" name="search" class="form-control form-control-sm" value="{{ $search ?? '' }}" placeholder="Material name, bill number, work type...">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">&nbsp;</label>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="low_stock_only" id="lowStockOnly" value="1" {{ ($lowStockOnly ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label small" for="lowStockOnly">Low Stock Only</label>
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">&nbsp;</label>
                    <button type="button" class="btn btn-outline-secondary btn-sm w-100" id="resetFiltersBtn">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Content Area (will be updated via AJAX) --}}
<div id="materialsReportContent">
{{-- Statistics Summary --}}
@if(isset($totalsByMaterial) && $totalsByMaterial->isNotEmpty())
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card stat-card border-left-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Total Materials</div>
                        <div class="h4 mb-0">{{ $totalMaterials ?? 0 }}</div>
                    </div>
                    <i class="bi bi-box-seam fs-1 text-primary opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card border-left-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Total Received</div>
                        <div class="h4 mb-0">{{ number_format($totalReceived ?? 0, 2) }}</div>
                    </div>
                    <i class="bi bi-arrow-down-circle fs-1 text-success opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card border-left-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Total Used</div>
                        <div class="h4 mb-0">{{ number_format($totalUsed ?? 0, 2) }}</div>
                    </div>
                    <i class="bi bi-arrow-up-circle fs-1 text-warning opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card border-left-info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Total Remaining</div>
                        <div class="h4 mb-0">{{ number_format($totalRemaining ?? 0, 2) }}</div>
                    </div>
                    <i class="bi bi-check-circle fs-1 text-info opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Stock Status Statistics --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card stat-card border-left-danger">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Out of Stock</div>
                        <div class="h4 mb-0 text-danger">{{ $outOfStockCount ?? 0 }}</div>
                    </div>
                    <i class="bi bi-x-circle fs-1 text-danger opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card border-left-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Low Stock</div>
                        <div class="h4 mb-0 text-warning">{{ $lowStockCount ?? 0 }}</div>
                    </div>
                    <i class="bi bi-exclamation-triangle fs-1 text-warning opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card border-left-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">In Stock</div>
                        <div class="h4 mb-0 text-success">{{ $inStockCount ?? 0 }}</div>
                    </div>
                    <i class="bi bi-check-circle fs-1 text-success opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
</div>

@if((isset($outOfStockCount) && $outOfStockCount > 0) || (isset($lowStockCount) && $lowStockCount > 0))
<div class="alert alert-warning alert-dismissible fade show">
    <i class="bi bi-exclamation-triangle me-2"></i>
    @if(isset($outOfStockCount) && $outOfStockCount > 0)
        <strong>{{ $outOfStockCount }}</strong> material(s) are out of stock.
    @endif
    @if(isset($lowStockCount) && $lowStockCount > 0)
        @if(isset($outOfStockCount) && $outOfStockCount > 0) <br> @endif
        <strong>{{ $lowStockCount }}</strong> material(s) have low stock (less than 20% remaining).
    @endif
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@php
    $themes = [
        ['icon' => 'bi-boxes',       'bg' => 'rgba(13, 110, 253, 0.15)',  'color' => '#0d6efd', 'bar' => '#0d6efd'],
        ['icon' => 'bi-grid-3x3',    'bg' => 'rgba(253, 126, 20, 0.15)',   'color' => '#fd7e14', 'bar' => '#fd7e14'],
        ['icon' => 'bi-bag',         'bg' => 'rgba(13, 202, 240, 0.15)',   'color' => '#0dcaf0', 'bar' => '#0dcaf0'],
        ['icon' => 'bi-arrow-left-right', 'bg' => 'rgba(111, 66, 193, 0.15)', 'color' => '#6f42c1', 'bar' => '#6f42c1'],
        ['icon' => 'bi-droplet-half','bg' => 'rgba(255, 193, 7, 0.2)',    'color' => '#b8860b', 'bar' => '#ffc107'],
        ['icon' => 'bi-lightning',   'bg' => 'rgba(32, 201, 151, 0.15)',  'color' => '#20c997', 'bar' => '#20c997'],
    ];
    $iconMap = [
        'aggregate' => 0, 'brick' => 1, 'cement' => 2, 'rod' => 3, 'sand' => 4, 'wire' => 5,
    ];
@endphp

{{-- Materials Grid --}}
<div class="row g-4 mb-4">
    @foreach($totalsByMaterial as $index => $row)
    @php
        $nameLower = strtolower($row->material_name);
        $themeIndex = 0;
        foreach ($iconMap as $key => $i) {
            if (str_contains($nameLower, $key)) { $themeIndex = $i; break; }
        }
        if (!isset($themes[$themeIndex])) { $themeIndex = $index % count($themes); }
        $t = $themes[$themeIndex];
        $received = (float) $row->total_received;
        $remaining = (float) $row->total_remaining;
        $used = (float) $row->total_used;
        $cost = (float) ($row->total_cost ?? 0);
        $pct = $received > 0 ? min(100, round(($remaining / $received) * 100, 1)) : 0;
        $isOutOfStock = $remaining <= 0;
        $isLowStock = $received > 0 && $pct > 0 && $pct < 20;
        $isInStock = $received > 0 && $pct >= 20;
        $stockStatus = $isOutOfStock ? 'out' : ($isLowStock ? 'low' : 'in');
        $stockBadgeClass = $isOutOfStock ? 'bg-danger' : ($isLowStock ? 'bg-warning' : 'bg-success');
        $stockBadgeText = $isOutOfStock ? 'Out of Stock' : ($isLowStock ? 'Low Stock' : 'In Stock');
    @endphp
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="card material-report-card border-0 shadow-sm h-100 overflow-hidden position-relative {{ $isLowStock ? 'border-warning' : ($isOutOfStock ? 'border-danger' : '') }}">
            <span class="badge {{ $stockBadgeClass }} low-stock-badge">{{ $stockBadgeText }}</span>
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: {{ $t['bg'] }}; color: {{ $t['color'] }};">
                        <i class="bi {{ $t['icon'] }} fs-4"></i>
                    </span>
                    <div class="min-w-0 flex-grow-1">
                        <h6 class="mb-0 fw-semibold text-dark text-break" style="word-break: break-word;">{{ $row->material_name }}</h6>
                        <span class="small text-muted">{{ $row->unit ?? '–' }}</span>
                    </div>
                </div>
                <div class="small mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Received</span>
                        <span class="fw-semibold">{{ number_format($received, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Used</span>
                        <span class="fw-semibold" style="color: {{ $t['color'] }};">{{ number_format($used, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Remaining</span>
                        <span class="fw-semibold" style="color: {{ $t['color'] }};">{{ number_format($remaining, 2) }}</span>
                    </div>
                    @if($cost > 0)
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Total Cost</span>
                        <span class="fw-semibold text-success">Rs. {{ number_format($cost, 2) }}</span>
                    </div>
                    @endif
                </div>
                <div class="material-report-progress mt-2">
                    @php
                        $progressColor = $isOutOfStock ? '#dc3545' : ($isLowStock ? '#ffc107' : '#198754');
                    @endphp
                    <div class="material-report-progress-bar" style="width: {{ $pct }}%; background: {{ $progressColor }};"></div>
                </div>
                <div class="text-end mt-1">
                    @php
                        $statusColor = $isOutOfStock ? '#dc3545' : ($isLowStock ? '#ffc107' : '#198754');
                    @endphp
                    <span class="small fw-medium" style="color: {{ $statusColor }};">{{ $pct }}% remaining</span>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Detailed Breakdown by Project --}}
@if(isset($detailsByProject) && $detailsByProject->isNotEmpty())
<div class="card">
    <div class="card-header">
        <strong>Detailed Breakdown by Project</strong>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Material</th>
                        <th>Project</th>
                        <th class="text-end">Received</th>
                        <th class="text-end">Used</th>
                        <th class="text-end">Remaining</th>
                        <th>Unit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($detailsByProject as $materialName => $projectData)
                        @foreach($projectData as $item)
                        <tr>
                            <td><strong>{{ $materialName }}</strong></td>
                            <td>{{ $item->project?->name ?? 'No Project' }}</td>
                            <td class="text-end">{{ number_format((float)$item->total_received, 2) }}</td>
                            <td class="text-end">{{ number_format((float)$item->total_used, 2) }}</td>
                            <td class="text-end">{{ number_format((float)$item->total_remaining, 2) }}</td>
                            <td>{{ $item->unit ?? '–' }}</td>
                        </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Rod Size-wise Report --}}
@if(isset($rodSizeSummary) && $rodSizeSummary->isNotEmpty())
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <strong>Rod Size-wise Report</strong>
            <div class="text-muted small">Rod materials grouped by size (e.g. 8mm, 10mm)</div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Size</th>
                        <th>Unit</th>
                        <th class="text-end">Total Received</th>
                        <th class="text-end">Total Used</th>
                        <th class="text-end">Total Remaining</th>
                        <th class="text-end">Total Cost</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rodSizeSummary as $rod)
                        <tr>
                            <td><strong>{{ $rod->size }}</strong></td>
                            <td>{{ $rod->unit ?? '-' }}</td>
                            <td class="text-end">{{ number_format((float) $rod->total_received, 2) }}</td>
                            <td class="text-end">{{ number_format((float) $rod->total_used, 2) }}</td>
                            <td class="text-end">{{ number_format((float) $rod->total_remaining, 2) }}</td>
                            <td class="text-end">{{ number_format((float) $rod->total_cost, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endif

@else
<div class="card border-0 shadow-sm">
    <div class="card-body text-center text-muted py-5">
        <i class="bi bi-box-seam display-4 opacity-50"></i>
        <p class="mb-0 mt-3">No material data found. <a href="{{ route('admin.construction-materials.index') }}">Add materials</a> or adjust filters.</p>
    </div>
</div>
@endif
</div>

<script>
(function() {
    const form = document.getElementById('filterForm');
    const contentArea = document.getElementById('materialsReportContent');
    if (!form || !contentArea) return;
    
    let searchTimeout = null;
    let isLoading = false;
    
    function loadData() {
        if (isLoading) return;
        isLoading = true;
        
        const formData = new FormData(form);
        const params = new URLSearchParams();
        for (const [key, value] of formData.entries()) {
            if (value) params.append(key, value);
        }
        
        const url = form.action + (params.toString() ? '?' + params.toString() : '');
        
        // Update URL without reload
        window.history.pushState({}, '', url);
        
        // Show loading indicator
        contentArea.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2 text-muted">Loading materials report...</p></div>';
        
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
        .then(response => response.text())
        .then(html => {
            // Extract content from response
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newContent = doc.getElementById('materialsReportContent');
            if (newContent) {
                contentArea.innerHTML = newContent.innerHTML;
            } else {
                // Fallback: extract content sections manually
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;
                
                // Find all sections that should be in content area
                const statsRow = tempDiv.querySelector('.row.g-3.mb-4');
                const alertWarning = tempDiv.querySelector('.alert.alert-warning');
                const materialsRow = tempDiv.querySelector('.row.g-4.mb-4');
                const detailsCard = Array.from(tempDiv.querySelectorAll('.card')).find(card => 
                    card.querySelector('.table') && card.textContent.includes('Detailed Breakdown')
                );
                const emptyCard = tempDiv.querySelector('.card .text-center.text-muted')?.closest('.card');
                
                let extractedContent = '';
                
                // Statistics row
                if (statsRow) {
                    extractedContent += statsRow.outerHTML;
                }
                
                // Low stock alert
                if (alertWarning && alertWarning.textContent.includes('low stock')) {
                    extractedContent += alertWarning.outerHTML;
                }
                
                // Empty state or materials grid
                if (emptyCard) {
                    extractedContent += emptyCard.outerHTML;
                } else if (materialsRow) {
                    extractedContent += materialsRow.outerHTML;
                    if (detailsCard) {
                        extractedContent += detailsCard.outerHTML;
                    }
                }
                
                if (extractedContent) {
                    contentArea.innerHTML = extractedContent;
                } else {
                    contentArea.innerHTML = '<div class="alert alert-warning">Failed to load data. Please refresh the page.</div>';
                }
            }
            isLoading = false;
        })
        .catch(error => {
            console.error('Error loading data:', error);
            contentArea.innerHTML = '<div class="alert alert-danger">Error loading data. Please refresh the page.</div>';
            isLoading = false;
        });
    }
    
    // Reset button
    const resetBtn = document.getElementById('resetFiltersBtn');
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            form.reset();
            loadData();
        });
    }
    
    // Auto-submit on select/checkbox change
    form.querySelectorAll('select, input[type="checkbox"]').forEach(function(el) {
        el.addEventListener('change', function() {
            loadData();
        });
    });
    
    // Auto-submit on date change
    form.querySelectorAll('input[type="date"]').forEach(function(el) {
        el.addEventListener('change', function() {
            loadData();
        });
    });
    
    // Debounced auto-submit for search input
    const searchInput = form.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                loadData();
            }, 500);
        });
        
        // Submit on Enter key
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(searchTimeout);
                loadData();
            }
        });
    }
    
    // Handle browser back/forward
    window.addEventListener('popstate', function() {
        loadData();
    });
})();
</script>
@endsection
