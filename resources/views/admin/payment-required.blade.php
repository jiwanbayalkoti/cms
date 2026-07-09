@extends('admin.layout')

@section('title', 'Payment Required')

@section('content')
@php($company = auth()->user()?->company)
<div class="max-w-6xl mx-auto">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="h-12 w-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center">
                    <i class="bi bi-lock-fill text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 mb-0">Payment Required</h1>
                    <p class="text-gray-600 mb-0">Please pay and submit details for super admin verification.</p>
                </div>
            </div>

            @if($company)
                <div class="bg-gray-50 rounded-lg p-4 text-left mb-6">
                    <p class="mb-1"><strong>Company:</strong> {{ $company->name }}</p>
                    @if($company->trial_ends_at)
                        <p class="mb-1"><strong>Trial Ended:</strong> {{ optional($company->effectiveTrialEndsAt())->format('Y-m-d') }}</p>
                    @endif
                    @if($company->paid_until)
                        <p class="mb-1"><strong>Last Paid Until:</strong> {{ $company->paid_until->format('Y-m-d') }}</p>
                    @endif
                    <p class="mb-0"><strong>Status:</strong> {{ ucfirst($company->subscription_status ?? 'locked') }}</p>
                </div>
            @endif

            <h2 class="h5 mb-3">Payment Details</h2>
            <p class="text-muted mb-0">Select Bank, eSewa, Khalti, or another enabled method in the form. The matching account details and QR will appear there.</p>

            @if(!empty($paymentSettings['payment_instructions']))
                <div class="alert alert-info whitespace-pre-line">{{ $paymentSettings['payment_instructions'] }}</div>
            @endif

            @if(!empty($paymentSettings['contact_phone']) || !empty($paymentSettings['contact_email']) || !empty($paymentSettings['contact_address']))
                <div class="bg-light rounded p-3 mt-4">
                    <h3 class="h6 mb-2">Contact Support</h3>
                    @if(!empty($paymentSettings['contact_phone']))
                        <p class="mb-1"><strong>Phone:</strong> {{ $paymentSettings['contact_phone'] }}</p>
                    @endif
                    @if(!empty($paymentSettings['contact_email']))
                        <p class="mb-1"><strong>Email:</strong> {{ $paymentSettings['contact_email'] }}</p>
                    @endif
                    @if(!empty($paymentSettings['contact_address']))
                        <p class="mb-0 whitespace-pre-line"><strong>Address:</strong> {{ $paymentSettings['contact_address'] }}</p>
                    @endif
                </div>
            @endif

            <div class="mt-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="h6 mb-0">Payment History</h3>
                    <a href="{{ route('admin.payment-requests.history.pdf') }}" target="_blank" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
                    </a>
                </div>

                @forelse($paymentRequests as $request)
                    <div class="border rounded p-3 mb-2">
                        <div class="d-flex justify-content-between gap-2">
                            <div>
                                <strong>{{ $request->amount ? 'Rs. ' . number_format((float) $request->amount, 2) : 'Payment request' }}</strong>
                                <div class="text-muted small">{{ $request->paid_for_days }} days · {{ $request->payment_method ?: 'Method not set' }}</div>
                            </div>
                            @php($badge = $request->status === 'verified' ? 'success' : ($request->status === 'rejected' ? 'danger' : 'warning'))
                            <span class="badge text-bg-{{ $badge }} align-self-start">{{ ucfirst($request->status) }}</span>
                        </div>
                        @if($request->transaction_reference)
                            <div class="small mt-1">Ref: {{ $request->transaction_reference }}</div>
                        @endif
                        @if($request->expires_at)
                            <div class="small text-muted">Active until {{ $request->expires_at->format('Y-m-d') }}</div>
                        @endif
                    </div>
                @empty
                    <p class="text-muted mb-0">No payment history yet.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="h5 mb-3">Submit Payment for Verification</h2>
            @if(empty($paymentSettings['enabled_payment_methods']))
                <div class="alert alert-warning">No payment method is enabled yet. Please contact super admin.</div>
            @endif
            @if($hasPendingPaymentRequest ?? false)
                <div class="alert alert-info">A payment request is already pending verification. Please wait for super admin approval before submitting another one.</div>
            @endif
            <form id="paymentVerificationForm" method="POST" action="{{ route('admin.payment-requests.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Payment For</label>
                    <select name="paid_for_days" id="paid-for-days" class="form-control" required onchange="updatePaymentAmount()">
                        @foreach($paymentSettings['year_options'] as $option)
                            <option value="{{ $option['days'] }}" data-amount="{{ $option['amount'] }}" data-subtotal="{{ $option['subtotal'] }}" data-discount="{{ $option['discount_percent'] }}" {{ (int) old('paid_for_days', 365) === (int) $option['days'] ? 'selected' : '' }}>
                                {{ $option['years'] }} {{ $option['years'] === 1 ? 'Year' : 'Years' }} - Rs. {{ number_format($option['amount'], 2) }}
                                @if($option['discount_percent'] > 0)
                                    ({{ rtrim(rtrim(number_format($option['discount_percent'], 2), '0'), '.') }}% discount)
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Amount to Pay</label>
                    <input type="text" id="payment-amount-display" class="form-control" readonly>
                    <small class="text-muted" id="payment-discount-summary"></small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Payment Method</label>
                    <div class="payment-method-toggle-grid">
                        @foreach($paymentSettings['enabled_payment_methods'] as $methodKey => $method)
                            @php($isSelectedMethod = old('payment_method') ? old('payment_method') === $methodKey : $loop->first)
                            <input class="payment-method-radio" type="radio" name="payment_method" value="{{ $methodKey }}" id="payment-method-{{ $methodKey }}" {{ $isSelectedMethod ? 'checked' : '' }} required onchange="updatePaymentMethodDetails()">
                            <label class="payment-method-toggle-card" for="payment-method-{{ $methodKey }}">
                                <span class="payment-method-toggle-dot"></span>
                                <span>{{ $method['label'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="mb-3" id="selected-method-details">
                    @foreach($paymentSettings['enabled_payment_methods'] as $methodKey => $method)
                        @php($isSelectedMethod = old('payment_method') ? old('payment_method') === $methodKey : $loop->first)
                        <div class="payment-method-panel {{ $isSelectedMethod ? '' : 'hidden' }}" data-method="{{ $methodKey }}">
                            @if(!empty($method['details']))
                                <div class="fw-semibold mb-1">{{ $method['label'] }} Details</div>
                                <div class="bg-light rounded p-3 whitespace-pre-line mb-3">{{ $method['details'] }}</div>
                            @endif
                            @if(!empty($method['qr_path']))
                                <div class="fw-semibold mb-1">{{ $method['label'] }} QR</div>
                                <img src="{{ asset('storage/' . $method['qr_path']) }}" alt="{{ $method['label'] }} QR" class="rounded border" style="max-height: 220px;">
                            @endif
                            @if(empty($method['details']) && empty($method['qr_path']))
                                <div class="alert alert-secondary mb-0">No details configured for {{ $method['label'] }} yet.</div>
                            @endif
                        </div>
                    @endforeach
                </div>
                <div class="mb-3">
                    <label class="form-label">Transaction Reference</label>
                    <input type="text" name="transaction_reference" class="form-control" value="{{ old('transaction_reference') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Payment Date</label>
                    <input type="date" name="payment_date" class="form-control" value="{{ old('payment_date', now()->format('Y-m-d')) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Payment Proof <span class="text-danger">*</span></label>
                    <input type="file" name="proof" class="form-control" accept="image/*,.pdf" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Note</label>
                    <textarea name="note" class="form-control" rows="3">{{ old('note') }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary" {{ ($hasPendingPaymentRequest ?? false) ? 'disabled' : '' }}>Submit for Verification</button>
            </form>

            <hr class="my-4">
            <h3 class="h6 mb-3">Recent Requests</h3>
            @forelse($paymentRequests as $request)
                <div class="border rounded p-3 mb-2">
                    <div class="d-flex justify-content-between">
                        <strong>{{ $request->amount ? 'Rs. ' . number_format((float) $request->amount, 2) : 'Payment request' }}</strong>
                        @php($badge = $request->status === 'verified' ? 'success' : ($request->status === 'rejected' ? 'danger' : 'warning'))
                        <span class="badge text-bg-{{ $badge }}">{{ ucfirst($request->status) }}</span>
                    </div>
                    <small class="text-muted">{{ $request->paid_for_days }} days · submitted {{ $request->created_at->format('Y-m-d H:i') }}</small>
                    @if($request->expires_at)
                        <div><small>Active until {{ $request->expires_at->format('Y-m-d') }}</small></div>
                    @endif
                    @if($request->admin_note)
                        <div class="mt-2 text-sm">{{ $request->admin_note }}</div>
                    @endif
                </div>
            @empty
                <p class="text-muted">No payment request submitted yet.</p>
            @endforelse

        </div>
    </div>
</div>
<style>
    .payment-method-toggle-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
        gap: 10px;
    }

    .payment-method-radio {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .payment-method-toggle-card {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 10px;
        background: #fff;
        cursor: pointer;
        font-weight: 600;
        color: #374151;
        transition: all 0.2s ease;
    }

    .payment-method-toggle-dot {
        width: 18px;
        height: 18px;
        border-radius: 999px;
        border: 2px solid #9ca3af;
        background: #fff;
        box-shadow: inset 0 0 0 3px #fff;
    }

    .payment-method-radio:checked + .payment-method-toggle-card {
        border-color: #16a34a;
        background: #dcfce7;
        color: #166534;
    }

    .payment-method-radio:checked + .payment-method-toggle-card .payment-method-toggle-dot {
        border-color: #16a34a;
        background: #16a34a;
    }

    #paymentVerificationForm label.error {
        display: block;
        margin-top: 4px;
        color: #dc3545;
        font-size: 0.875rem;
    }

    #paymentVerificationForm .is-invalid,
    #paymentVerificationForm input.error,
    #paymentVerificationForm select.error,
    #paymentVerificationForm textarea.error {
        border-color: #dc3545;
    }
</style>
<script>
    function updatePaymentAmount() {
        const select = document.getElementById('paid-for-days');
        const amountInput = document.getElementById('payment-amount-display');
        const summary = document.getElementById('payment-discount-summary');
        if (!select || !amountInput || !summary) return;

        const option = select.options[select.selectedIndex];
        const amount = Number(option.dataset.amount || 0);
        const subtotal = Number(option.dataset.subtotal || amount);
        const discount = Number(option.dataset.discount || 0);

        amountInput.value = 'Rs. ' + amount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        summary.textContent = discount > 0
            ? 'Original: Rs. ' + subtotal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' · Discount: ' + discount + '%'
            : 'No discount applied for this duration.';
    }

    function updatePaymentMethodDetails() {
        const selected = document.querySelector('input[name="payment_method"]:checked');
        if (!selected) return;

        document.querySelectorAll('.payment-method-panel').forEach(panel => {
            panel.classList.toggle('hidden', panel.dataset.method !== selected.value);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        updatePaymentAmount();
        updatePaymentMethodDetails();
    });
</script>
@push('modals')
<script>
    $(function () {
        if (!$.validator) {
            return;
        }

        $.validator.addMethod('proofFileType', function (value, element) {
            if (!value) {
                return false;
            }

            return /\.(jpe?g|png|pdf|webp)$/i.test(value);
        }, 'Only JPG, PNG, WEBP, or PDF files are allowed.');

        $.validator.addMethod('proofFileSize', function (value, element) {
            if (!element.files || !element.files.length) {
                return false;
            }

            return element.files[0].size <= 4096 * 1024;
        }, 'Payment proof must be 4MB or smaller.');

        $('#paymentVerificationForm').validate({
            ignore: [],
            rules: {
                paid_for_days: {
                    required: true
                },
                payment_method: {
                    required: true
                },
                proof: {
                    required: true,
                    proofFileType: true,
                    proofFileSize: true
                },
                payment_date: {
                    date: true
                }
            },
            messages: {
                paid_for_days: {
                    required: 'Please select payment duration.'
                },
                payment_method: {
                    required: 'Please select a payment method.'
                },
                proof: {
                    required: 'Please upload payment proof.'
                },
                payment_date: {
                    date: 'Please enter a valid payment date.'
                }
            },
            errorPlacement: function (error, element) {
                if (element.attr('name') === 'payment_method') {
                    error.insertAfter(element.closest('.payment-method-toggle-grid'));
                    return;
                }

                error.insertAfter(element);
            },
            highlight: function (element) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function (element) {
                $(element).removeClass('is-invalid');
            }
        });
    });
</script>
@endpush
@endsection
