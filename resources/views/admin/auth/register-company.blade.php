@extends('admin.auth.layout')

@section('title', 'Register company')
@section('panel_width', 'max-w-2xl')

@section('hero_title', 'Start your company workspace.')
@section('hero_subtitle', 'Register once to create your company profile and the first admin account. Super Admin will enable modules for your team.')

@section('content')
    <div class="mb-6">
        <h2 class="text-2xl font-bold" style="color: var(--auth-ink);">Company registration</h2>
        <p class="mt-1 text-sm" style="color: var(--auth-muted);">Fill in company details and create the first admin account.</p>
    </div>

    @if($errors->any())
        <div class="auth-alert bg-red-50 border border-red-200 text-red-700 mb-5" role="alert">
            <button type="button" class="auth-alert-close" onclick="closeAuthAlert(this)" aria-label="Close">&times;</button>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.register.company.store') }}" class="space-y-7">
        @csrf

        <div>
            <div class="auth-section-title">
                <i class="bi bi-building"></i> Company details
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-1.5" style="color: var(--auth-ink);">Company name <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <i class="bi bi-building auth-field-icon"></i>
                        <input type="text" name="company_name" value="{{ old('company_name') }}" required
                               placeholder="Your company name"
                               class="auth-input">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5" style="color: var(--auth-ink);">Company email</label>
                    <div class="relative">
                        <i class="bi bi-envelope auth-field-icon"></i>
                        <input type="email" name="company_email" value="{{ old('company_email') }}"
                               placeholder="info@company.com"
                               class="auth-input">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5" style="color: var(--auth-ink);">Company phone</label>
                    <div class="relative">
                        <i class="bi bi-telephone auth-field-icon"></i>
                        <input type="text" name="company_phone" value="{{ old('company_phone') }}"
                               placeholder="98XXXXXXXX"
                               class="auth-input">
                    </div>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-1.5" style="color: var(--auth-ink);">Company address</label>
                    <div class="relative">
                        <i class="bi bi-geo-alt auth-field-icon"></i>
                        <input type="text" name="company_address" value="{{ old('company_address') }}"
                               placeholder="City, district"
                               class="auth-input">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5" style="color: var(--auth-ink);">PAN / Tax number</label>
                    <div class="relative">
                        <i class="bi bi-receipt auth-field-icon"></i>
                        <input type="text" name="tax_number" value="{{ old('tax_number') }}"
                               placeholder="Optional"
                               class="auth-input">
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="auth-section-title">
                <i class="bi bi-person-badge"></i> Admin account
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1.5" style="color: var(--auth-ink);">Full name <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <i class="bi bi-person auth-field-icon"></i>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               placeholder="Admin full name"
                               class="auth-input">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5" style="color: var(--auth-ink);">Phone</label>
                    <div class="relative">
                        <i class="bi bi-phone auth-field-icon"></i>
                        <input type="text" name="phone" value="{{ old('phone') }}"
                               placeholder="Contact number"
                               class="auth-input">
                    </div>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-1.5" style="color: var(--auth-ink);">Email <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <i class="bi bi-envelope auth-field-icon"></i>
                        <input type="email" name="email" value="{{ old('email') }}" required
                               placeholder="admin@company.com"
                               class="auth-input">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5" style="color: var(--auth-ink);">Password <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <i class="bi bi-lock auth-field-icon"></i>
                        <input type="password" name="password" id="reg-password" required
                               placeholder="Min. 8 characters"
                               class="auth-input pr-16">
                        <button type="button" onclick="togglePasswordVisibility('reg-password', this)"
                                class="absolute inset-y-0 right-0 px-3 text-xs font-semibold auth-link">
                            Show
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5" style="color: var(--auth-ink);">Confirm password <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <i class="bi bi-shield-lock auth-field-icon"></i>
                        <input type="password" name="password_confirmation" id="reg-password-confirm" required
                               placeholder="Repeat password"
                               class="auth-input pr-16">
                        <button type="button" onclick="togglePasswordVisibility('reg-password-confirm', this)"
                                class="absolute inset-y-0 right-0 px-3 text-xs font-semibold auth-link">
                            Show
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 pt-2">
            <a href="{{ route('admin.login') }}"
               class="inline-flex items-center justify-center gap-2 text-sm font-semibold px-1 auth-link">
                <i class="bi bi-arrow-left"></i> Back to login
            </a>
            <button type="submit" class="auth-btn-primary sm:!w-auto sm:min-w-[200px]">
                <i class="bi bi-check2-circle"></i>
                Register company
            </button>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    function closeAuthAlert(button) {
        const alert = button.closest('.auth-alert');
        if (alert) alert.remove();
    }

    function togglePasswordVisibility(inputId, button) {
        const input = document.getElementById(inputId);
        if (!input) return;
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        button.textContent = isPassword ? 'Hide' : 'Show';
    }
</script>
@endpush
