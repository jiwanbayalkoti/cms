@if($vehicleRents->count() > 0)
<div class="card mb-4 shadow-sm">
    <div class="card-body py-3">
        <div class="row g-3 mb-0">
            <div class="col-md-3">
                <div class="d-flex align-items-center p-3 bg-light rounded">
                    <div class="flex-shrink-0">
                        <i class="bi bi-cash-stack text-primary fs-4"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <small class="text-muted d-block mb-1">Total Amount</small>
                        <h5 class="mb-0 text-primary fw-bold">{{ number_format($totalAmount, 2) }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="d-flex align-items-center p-3 bg-light rounded">
                    <div class="flex-shrink-0">
                        <i class="bi bi-check-circle-fill text-success fs-4"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <small class="text-muted d-block mb-1">Total Paid</small>
                        <h5 class="mb-0 text-success fw-bold">{{ number_format($totalPaid, 2) }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="d-flex align-items-center p-3 bg-light rounded">
                    <div class="flex-shrink-0">
                        <i class="bi bi-{{ $totalBalance > 0 ? 'exclamation-triangle-fill text-danger' : 'check-circle-fill text-success' }} fs-4"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <small class="text-muted d-block mb-1">Remaining Balance</small>
                        <h5 class="mb-0 text-{{ $totalBalance > 0 ? 'danger' : 'success' }} fw-bold">{{ number_format($totalBalance, 2) }}</h5>
                        @if($totalBalance > 0)
                            <small class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Outstanding</small>
                        @else
                            <small class="text-success"><i class="bi bi-check-circle me-1"></i>All Paid</small>
                        @endif
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
                        <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Total Advances</small>
                    </div>
                </div>
            </div>
        </div>
        @if(isset($netBalance))
        <div class="row mt-3">
            <div class="col-12">
                <div class="alert alert-{{ $netBalance > 0 ? 'warning' : ($netBalance < 0 ? 'success' : 'info') }} mb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong><i class="bi bi-calculator me-2"></i>Net Balance (After Advance Payments):</strong>
                            <span class="ms-2 fs-5 fw-bold">{{ number_format($netBalance, 2) }}</span>
                        </div>
                        <div>
                            @if($netBalance > 0)
                                <span class="badge bg-warning text-dark"><i class="bi bi-arrow-up-circle me-1"></i>Outstanding Amount</span>
                            @elseif($netBalance < 0)
                                <span class="badge bg-success"><i class="bi bi-arrow-down-circle me-1"></i>Overpaid/Advance Credit</span>
                            @else
                                <span class="badge bg-info"><i class="bi bi-check-circle me-1"></i>Balanced</span>
                            @endif
                        </div>
                    </div>
                    <small class="text-muted mt-2 d-block">Calculation: Remaining Balance ({{ number_format($totalBalance, 2) }}) - Advance Payments ({{ number_format($totalAdvancePayments ?? 0, 2) }}) = Net Balance</small>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endif

