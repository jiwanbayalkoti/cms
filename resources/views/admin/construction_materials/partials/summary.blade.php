<div class="row g-3 mb-0">
    <div class="col-md-3">
        <div class="d-flex align-items-center p-3 bg-light rounded">
            <div class="flex-shrink-0">
                <i class="bi bi-cash-stack text-primary fs-4"></i>
            </div>
            <div class="flex-grow-1 ms-3">
                <small class="text-muted d-block mb-1">Total Cost</small>
                <h5 class="mb-0 text-primary fw-bold">{{ number_format($totalCost, 2) }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="d-flex align-items-center p-3 bg-light rounded">
            <div class="flex-shrink-0">
                <i class="bi bi-wallet-fill text-info fs-4"></i>
            </div>
            <div class="flex-grow-1 ms-3">
                <small class="text-muted d-block mb-1">Advance Payments</small>
                <h5 class="mb-0 text-info fw-bold">{{ number_format($totalAdvancePayments ?? 0, 2) }}</h5>
                <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Material Payments</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="d-flex align-items-center p-3 bg-light rounded">
            <div class="flex-shrink-0">
                <i class="bi bi-{{ $netBalance > 0 ? 'exclamation-triangle-fill text-danger' : 'check-circle-fill text-success' }} fs-4"></i>
            </div>
            <div class="flex-grow-1 ms-3">
                <small class="text-muted d-block mb-1">Remaining Balance</small>
                <h5 class="mb-0 text-{{ ($netBalance ?? 0) > 0 ? 'danger' : 'success' }} fw-bold">{{ number_format($netBalance ?? $totalCost, 2) }}</h5>
                @if(($netBalance ?? 0) > 0)
                    <small class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Outstanding</small>
                @else
                    <small class="text-success"><i class="bi bi-check-circle me-1"></i>All Paid</small>
                @endif
            </div>
        </div>
    </div>
</div>

