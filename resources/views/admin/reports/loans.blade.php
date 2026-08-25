@extends('admin.layout')

@section('title', 'Loans Report')

@section('content')
<div class="mb-4 flex justify-between items-center gap-3">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Loans Report</h1>
        <p class="mt-1 text-gray-600 text-sm">Taken vs Given loans with repaid and remaining totals.</p>
    </div>
    <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary btn-sm btn-keep-text">
        <i class="bi bi-arrow-left"></i>
        <span>Back to Reports</span>
    </a>
</div>

<div id="loanSummaryCards" class="grid grid-cols-1 lg:grid-cols-2 gap-3 mb-4">
    <div class="bg-white border rounded-lg p-3">
        <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500 mb-2">Taken loans (company borrowed)</div>
        <div class="grid grid-cols-3 gap-2">
            <div class="rounded-md bg-green-50 border border-green-100 px-2.5 py-2">
                <div class="text-xs text-muted leading-tight">Total Taken</div>
                <div class="text-lg font-bold text-green-700 leading-tight mt-0.5">${{ number_format($totalTaken ?? $totalReceived ?? 0, 2) }}</div>
            </div>
            <div class="rounded-md bg-amber-50 border border-amber-100 px-2.5 py-2">
                <div class="text-xs text-muted leading-tight">Total Repaid</div>
                <div class="text-lg font-bold text-amber-700 leading-tight mt-0.5">${{ number_format($totalRepaid ?? 0, 2) }}</div>
            </div>
            <div class="rounded-md bg-slate-50 border border-slate-100 px-2.5 py-2">
                <div class="text-xs text-muted leading-tight">Remaining</div>
                <div class="text-lg font-bold {{ ($takenRemaining ?? $netBalance ?? 0) >= 0 ? 'text-slate-800' : 'text-red-600' }} leading-tight mt-0.5">${{ number_format($takenRemaining ?? $netBalance ?? 0, 2) }}</div>
            </div>
        </div>
    </div>
    <div class="bg-white border rounded-lg p-3">
        <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500 mb-2">Given loans (company lent)</div>
        <div class="grid grid-cols-3 gap-2">
            <div class="rounded-md bg-red-50 border border-red-100 px-2.5 py-2">
                <div class="text-xs text-muted leading-tight">Total Given</div>
                <div class="text-lg font-bold text-red-700 leading-tight mt-0.5">${{ number_format($totalGiven ?? 0, 2) }}</div>
            </div>
            <div class="rounded-md bg-blue-50 border border-blue-100 px-2.5 py-2">
                <div class="text-xs text-muted leading-tight">Returned / Repaid</div>
                <div class="text-lg font-bold text-blue-700 leading-tight mt-0.5">${{ number_format($totalGivenReturned ?? 0, 2) }}</div>
            </div>
            <div class="rounded-md bg-slate-50 border border-slate-100 px-2.5 py-2">
                <div class="text-xs text-muted leading-tight">Remaining</div>
                <div class="text-lg font-bold {{ ($givenRemaining ?? 0) >= 0 ? 'text-slate-800' : 'text-red-600' }} leading-tight mt-0.5">${{ number_format($givenRemaining ?? 0, 2) }}</div>
            </div>
        </div>
    </div>
</div>

<div class="bg-white shadow-lg rounded-lg p-3 mb-4">
    <form method="GET" action="{{ route('admin.reports.loans') }}" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small mb-1">Start Date</label>
            <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}">
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-1">End Date</label>
            <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}" max="{{ date('Y-m-d') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label small mb-1">Project</label>
            <select name="project_id" class="form-select form-select-sm">
                <option value="">All Projects</option>
                @foreach($projects ?? [] as $p)
                    <option value="{{ $p->id }}" {{ (int) $projectId === (int) $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary btn-sm btn-keep-text w-100">
                <i class="bi bi-funnel"></i>
                <span>Filter</span>
            </button>
        </div>
    </form>
</div>

<div class="bg-white shadow-lg rounded-lg overflow-hidden">
    <div class="p-4 border-b fw-bold">
        Loan Transactions & Repayments
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-striped align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Party / Source</th>
                    <th>Project</th>
                    <th class="text-end">Principal</th>
                    <th class="text-end">Interest %</th>
                    <th class="text-end">Payable / Paid</th>
                    <th>Payment Method</th>
                    <th>Bank</th>
                    <th>Expenses</th>
                </tr>
            </thead>
            <tbody>
                @forelse($loans ?? [] as $loan)
                    @php
                        $principal = (float) ($loan->amount ?? 0);
                        $rate = (float) ($loan->interest_rate ?? 0);
                        $payableAmount = (float) ($loan->payable_amount ?? $principal);
                        $partyLabel = $loan->party_source
                            ?? $loan->party_name
                            ?? $loan->source
                            ?? ($loan->supplier ? $loan->supplier->name : ($loan->staff ? $loan->staff->name : '—'));
                    @endphp
                    <tr>
                        <td>{{ $loan->loan_date ? $loan->loan_date->format('M d, Y') : '—' }}</td>
                        <td>
                            @if(($loan->row_type ?? 'loan') === 'payment' || ($loan->direction ?? '') === 'repayment')
                                <span class="badge bg-warning text-dark">Repayment</span>
                            @elseif(($loan->direction ?? '') === 'received')
                                <span class="badge bg-success">Taken</span>
                            @elseif(($loan->direction ?? '') === 'repaid')
                                <span class="badge bg-danger">Given</span>
                            @else
                                <span class="badge bg-secondary">Legacy</span>
                            @endif
                        </td>
                        <td>{{ $partyLabel }}</td>
                        <td>{{ $loan->project ? $loan->project->name : '—' }}</td>
                        <td class="text-end">
                            @if(in_array($loan->direction ?? '', ['received', 'repaid'], true))
                                <span class="{{ ($loan->direction ?? '') === 'received' ? 'text-success' : 'text-danger' }} fw-bold">${{ number_format($principal, 2) }}</span>
                            @else
                                <span class="text-danger fw-bold">${{ number_format($principal, 2) }}</span>
                            @endif
                        </td>
                        <td class="text-end">{{ number_format($rate, 2) }}%</td>
                        <td class="text-end">
                            @if(($loan->direction ?? '') === 'received')
                                <span class="text-success fw-bold">${{ number_format($payableAmount, 2) }}</span>
                            @else
                                <span class="text-danger fw-bold">${{ number_format($payableAmount, 2) }}</span>
                            @endif
                        </td>
                        <td>{{ $loan->payment_method ? ucfirst(str_replace('_', ' ', $loan->payment_method)) : '—' }}</td>
                        <td>{{ $loan->bankAccount ? $loan->bankAccount->account_name : '—' }}</td>
                        <td>
                            @if(!empty($loan->in_expenses))
                                <span class="badge bg-orange-100 text-orange-800">Yes</span>
                            @elseif(($loan->direction ?? '') === 'repaid' && empty($loan->is_approved ?? true))
                                <span class="badge bg-secondary">After approval</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">No loan data found for this period.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
