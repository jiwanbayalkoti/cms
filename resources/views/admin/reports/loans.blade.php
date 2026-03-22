@extends('admin.layout')

@section('title', 'Loans Report')

@section('content')
<div class="mb-6 flex justify-between items-center gap-3">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Loans Report</h1>
        <p class="mt-2 text-gray-600">Loan received vs repaid (separate from Income).</p>
    </div>
    <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary">
        Back to Reports
    </a>
</div>

<div class="bg-white shadow-lg rounded-lg p-4 mb-6">
    <form method="GET" action="{{ route('admin.reports.loans') }}" class="row g-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label small">Start Date</label>
            <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}" required>
        </div>
        <div class="col-md-3">
            <label class="form-label small">End Date</label>
            <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}" disabled>
        </div>
        <div class="col-md-4">
            <label class="form-label small">Project</label>
            <select name="project_id" class="form-select form-select-sm">
                <option value="">All Projects</option>
                @foreach($projects ?? [] as $p)
                    <option value="{{ $p->id }}" {{ (int) $projectId === (int) $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary btn-sm w-100">
                Filter
            </button>
        </div>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white border rounded-lg p-4">
        <div class="text-sm text-muted">Total Received</div>
        <div class="text-2xl font-bold text-green-600">${{ number_format($totalReceived ?? 0, 2) }}</div>
    </div>
    <div class="bg-white border rounded-lg p-4">
        <div class="text-sm text-muted">Total Repaid</div>
        <div class="text-2xl font-bold text-red-600">${{ number_format($totalRepaid ?? 0, 2) }}</div>
    </div>
    <div class="bg-white border rounded-lg p-4">
        <div class="text-sm text-muted">Outstanding (Net)</div>
        <div class="text-2xl font-bold {{ ($netBalance ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">${{ number_format($netBalance ?? 0, 2) }}</div>
    </div>
</div>

<div class="bg-white shadow-lg rounded-lg overflow-hidden">
    <div class="p-4 border-b fw-bold">
        Loan Transactions
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-striped align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Direction</th>
                    <th>Party / Source</th>
                    <th>Project</th>
                    <th class="text-end">Amount</th>
                    <th class="text-end">Interest %</th>
                    <th class="text-end">Payable</th>
                    <th>Payment Method</th>
                    <th>Bank</th>
                </tr>
            </thead>
            <tbody>
                @forelse($loans ?? [] as $loan)
                    @php
                        $principal = (float) ($loan->amount ?? 0);
                        $rate = (float) ($loan->interest_rate ?? 0);
                        $interestAmount = $principal * $rate / 100;
                        $payableAmount = $principal + $interestAmount;
                    @endphp
                    <tr>
                        <td>{{ $loan->loan_date->format('M d, Y') }}</td>
                        <td>
                            @if($loan->direction === 'received')
                                <span class="badge bg-success">Received</span>
                            @else
                                <span class="badge bg-danger">Repaid</span>
                            @endif
                        </td>
                        <td>
                            {{
                                $loan->party_source
                                    ?? $loan->party_name
                                    ?? $loan->source
                                    ?? ($loan->supplier ? $loan->supplier->name : ($loan->staff ? $loan->staff->name : '—'))
                            }}
                        </td>
                        <td>{{ $loan->project ? $loan->project->name : '—' }}</td>
                        <td class="text-end">
                            @if($loan->direction === 'received')
                                <span class="text-success fw-bold">${{ number_format($loan->amount, 2) }}</span>
                            @else
                                <span class="text-danger fw-bold">${{ number_format($loan->amount, 2) }}</span>
                            @endif
                        </td>
                        <td class="text-end">{{ number_format($rate, 2) }}%</td>
                        <td class="text-end">
                            @if($loan->direction === 'received')
                                <span class="text-success fw-bold">${{ number_format($payableAmount, 2) }}</span>
                            @else
                                <span class="text-danger fw-bold">${{ number_format($payableAmount, 2) }}</span>
                            @endif
                        </td>
                        <td>{{ $loan->payment_method ?? '—' }}</td>
                        <td>{{ $loan->bankAccount ? $loan->bankAccount->account_name : '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">No loan data found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

