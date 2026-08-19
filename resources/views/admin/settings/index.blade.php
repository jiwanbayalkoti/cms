@extends('admin.layout')

@section('title', 'System Settings')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">System Settings</h1>
            <p class="text-muted mb-0">Manage trial days, branding, payment details, and company payment verification.</p>
        </div>
        @if(Auth::user()->isSuperAdmin())
            <a href="{{ route('admin.companies.index') }}" class="btn btn-outline-secondary">Back to Companies</a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="row g-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <strong>General & Payment Settings</strong>
                </div>
                <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="card-body">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">System Name</label>
                        <input type="text" name="system_name" class="form-control" value="{{ old('system_name', $settings['system_name']) }}" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Contact Phone</label>
                            <input type="text" name="contact_phone" class="form-control" value="{{ old('contact_phone', $settings['contact_phone']) }}" placeholder="Support phone number">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact Email</label>
                            <input type="email" name="contact_email" class="form-control" value="{{ old('contact_email', $settings['contact_email']) }}" placeholder="support@example.com">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Contact Address</label>
                            <textarea name="contact_address" class="form-control" rows="2" placeholder="Office address or support contact note">{{ old('contact_address', $settings['contact_address']) }}</textarea>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Common Trial Period (days)</label>
                        <input type="number" name="trial_days" class="form-control" min="1" max="365" value="{{ old('trial_days', $settings['trial_days']) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payment Rate Per Year</label>
                        <input type="number" step="0.01" name="payment_yearly_rate" class="form-control" min="0" value="{{ old('payment_yearly_rate', $settings['payment_yearly_rate']) }}" required>
                        <small class="text-muted">Base yearly amount. Multi-year prices are calculated from this rate and discount below.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Year-wise Discount</label>
                        <div class="row g-2">
                            @foreach($settings['payment_year_options'] as $option)
                                <div class="col-6">
                                    <label class="form-label small mb-1">{{ $option['years'] }} {{ $option['years'] === 1 ? 'Year' : 'Years' }} Discount (%)</label>
                                    <input type="number" step="0.01" name="payment_discounts[{{ $option['years'] }}]" class="form-control" min="0" max="100" value="{{ old('payment_discounts.' . $option['years'], $settings['payment_discounts'][$option['years']] ?? 0) }}">
                                    <small class="text-muted">Current: Rs. {{ number_format($option['amount'], 2) }}</small>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">System Logo</label>
                        <input type="file" name="system_logo" class="form-control" accept="image/*">
                        @if($settings['system_logo'])
                            <img src="{{ asset('storage/' . $settings['system_logo']) }}" alt="System logo" class="mt-2 rounded border" style="max-height: 70px;">
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payment Methods</label>
                        <div class="vstack gap-3">
                            @foreach($settings['payment_methods'] as $methodKey => $method)
                                <div class="border rounded p-3 bg-light">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <strong>{{ $method['label'] }}</strong>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge {{ old('payment_methods.' . $methodKey . '.enabled', $method['enabled']) ? 'text-bg-success' : 'text-bg-secondary' }}" data-method-status="{{ $methodKey }}">
                                                {{ old('payment_methods.' . $methodKey . '.enabled', $method['enabled']) ? 'Active' : 'Inactive' }}
                                            </span>
                                            <input type="hidden" name="payment_methods[{{ $methodKey }}][enabled]" value="0">
                                            <input class="payment-method-toggle" type="checkbox" name="payment_methods[{{ $methodKey }}][enabled]" value="1" id="method-enabled-{{ $methodKey }}" data-method-key="{{ $methodKey }}" {{ old('payment_methods.' . $methodKey . '.enabled', $method['enabled']) ? 'checked' : '' }}>
                                            <label class="payment-method-toggle-label" for="method-enabled-{{ $methodKey }}" title="Enable or disable {{ $method['label'] }}"></label>
                                        </div>
                                    </div>
                                    <div class="payment-method-fields {{ old('payment_methods.' . $methodKey . '.enabled', $method['enabled']) ? '' : 'd-none' }}" data-method-fields="{{ $methodKey }}">
                                        <div class="mb-3">
                                            <label class="form-label">Display Name</label>
                                            <input type="text" name="payment_methods[{{ $methodKey }}][label]" class="form-control" value="{{ old('payment_methods.' . $methodKey . '.label', $method['label']) }}">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Details</label>
                                            <textarea name="payment_methods[{{ $methodKey }}][details]" class="form-control" rows="4" placeholder="Account ID, number, receiver name, branch, phone, or instructions">{{ old('payment_methods.' . $methodKey . '.details', $method['details']) }}</textarea>
                                        </div>
                                        <div class="mb-0">
                                            <label class="form-label">QR Image</label>
                                            <input type="file" name="payment_method_qr[{{ $methodKey }}]" class="form-control" accept="image/*">
                                            @if(!empty($method['qr_path']))
                                                <img src="{{ asset('storage/' . $method['qr_path']) }}" alt="{{ $method['label'] }} QR" class="mt-2 rounded border" style="max-height: 140px;">
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payment Instructions</label>
                        <textarea name="payment_instructions" class="form-control" rows="4" placeholder="Instructions companies should follow after payment">{{ old('payment_instructions', $settings['payment_instructions']) }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Settings</button>
                </form>
            </div>
        </div>

        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <strong>Company Payment Requests</strong>
                    <span class="badge text-bg-warning">{{ $paymentRequests->where('status', 'pending')->count() }} pending on this page</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Company</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                    <th>Proof</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($paymentRequests as $request)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $request->company?->name ?? 'Deleted company' }}</div>
                                            <small class="text-muted">Submitted {{ $request->created_at->format('Y-m-d H:i') }}</small>
                                        </td>
                                        <td>
                                            <div>{{ $request->amount ? 'Rs. ' . number_format((float) $request->amount, 2) : 'Amount not set' }}</div>
                                            <small class="text-muted">{{ $request->paid_for_days }} days · {{ $request->payment_method ?: 'Method not set' }}</small>
                                            @if($request->transaction_reference)
                                                <div><small>Ref: {{ $request->transaction_reference }}</small></div>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $badge = $request->status === 'verified' ? 'success' : ($request->status === 'rejected' ? 'danger' : 'warning');
                                            @endphp
                                            <span class="badge text-bg-{{ $badge }}">{{ ucfirst($request->status) }}</span>
                                            @if($request->expires_at)
                                                <div><small class="text-muted">Expires {{ $request->expires_at->format('Y-m-d') }}</small></div>
                                            @endif
                                        </td>
                                        <td>
                                            @if($request->proof_path)
                                                <a href="{{ asset('storage/' . $request->proof_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary">View</a>
                                            @else
                                                <span class="text-muted">No file</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($request->status === 'pending')
                                                <form method="POST" action="{{ route('admin.payment-requests.verify', $request) }}" class="d-flex gap-2 mb-2">
                                                    @csrf
                                                    <input type="number" name="paid_for_days" class="form-control form-control-sm" min="1" max="3650" value="{{ $request->paid_for_days }}" style="width: 90px;">
                                                    <button type="submit" class="btn btn-sm btn-success" title="Verify Payment">
                                                        <i class="bi bi-check-circle me-1"></i> Verify
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.payment-requests.reject', $request) }}" onsubmit="return confirm('Reject this payment request?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Reject Payment">
                                                        <i class="bi bi-x-circle me-1"></i> Reject
                                                    </button>
                                                </form>
                                            @else
                                                <small class="text-muted">By {{ $request->verifier?->name ?: 'super admin' }}</small>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">No payment requests yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($paymentRequests->hasPages())
                    <div class="card-footer bg-white">{{ $paymentRequests->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
<style>
    .payment-method-toggle {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .payment-method-toggle-label {
        position: relative;
        display: inline-block;
        width: 54px;
        height: 28px;
        margin: 0;
        cursor: pointer;
        background: #adb5bd;
        border-radius: 999px;
        transition: background-color 0.2s ease;
    }

    .payment-method-toggle-label::after {
        content: '';
        position: absolute;
        top: 3px;
        left: 3px;
        width: 22px;
        height: 22px;
        background: #fff;
        border-radius: 50%;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.25);
        transition: transform 0.2s ease;
    }

    .payment-method-toggle:checked + .payment-method-toggle-label {
        background: #198754;
    }

    .payment-method-toggle:checked + .payment-method-toggle-label::after {
        transform: translateX(26px);
    }
</style>
<script>
    document.querySelectorAll('.payment-method-toggle').forEach(toggle => {
        toggle.addEventListener('change', function () {
            const status = document.querySelector(`[data-method-status="${this.dataset.methodKey}"]`);
            if (!status) return;

            status.textContent = this.checked ? 'Active' : 'Inactive';
            status.classList.toggle('text-bg-success', this.checked);
            status.classList.toggle('text-bg-secondary', !this.checked);

            const fields = document.querySelector(`[data-method-fields="${this.dataset.methodKey}"]`);
            if (fields) {
                fields.classList.toggle('d-none', !this.checked);
            }
        });
    });
</script>
@endsection
