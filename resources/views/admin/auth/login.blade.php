@extends('admin.auth.layout')

@section('title', 'Sign in')

@section('hero_title', 'Welcome back.')
@section('hero_subtitle', 'Sign in to continue managing your company projects, finances, and team from your admin dashboard.')

@section('content')
    <div class="mb-6">
        <h2 class="text-2xl font-bold" style="color: var(--auth-ink);">Sign in</h2>
        <p class="mt-1 text-sm" style="color: var(--auth-muted);">Enter your credentials to access the admin panel.</p>
    </div>

    @if(session('success'))
        <div class="auth-alert bg-emerald-50 border border-emerald-200 text-emerald-800 mb-5" role="alert">
            <button type="button" class="auth-alert-close" onclick="closeAuthAlert(this)" aria-label="Close">&times;</button>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="auth-alert bg-red-50 border border-red-200 text-red-700 mb-5" role="alert">
            <button type="button" class="auth-alert-close" onclick="closeAuthAlert(this)" aria-label="Close">&times;</button>
            {{ session('error') }}
        </div>
    @endif

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

    <form method="POST" action="{{ route('admin.login') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium mb-1.5" style="color: var(--auth-ink);">Email address</label>
            <div class="relative">
                <i class="bi bi-envelope auth-field-icon"></i>
                <input id="email" name="email" type="email" autocomplete="email" required
                       value="{{ old('email') }}"
                       placeholder="you@company.com"
                       class="auth-input @error('email') is-invalid @enderror">
            </div>
            @error('email')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium mb-1.5" style="color: var(--auth-ink);">Password</label>
            <div class="relative">
                <i class="bi bi-lock auth-field-icon"></i>
                <input id="password" name="password" type="password" autocomplete="current-password" required
                       placeholder="Enter your password"
                       class="auth-input pr-16 @error('password') is-invalid @enderror">
                <button type="button" onclick="togglePasswordVisibility('password', this)"
                        class="absolute inset-y-0 right-0 px-3 text-xs font-semibold auth-link">
                    Show
                </button>
            </div>
            @error('password')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center">
            <input id="remember" name="remember" type="checkbox"
                   class="h-4 w-4 rounded border-stone-300 text-brand-500 focus:ring-brand-500">
            <label for="remember" class="ml-2 text-sm" style="color: var(--auth-muted);">Remember me on this device</label>
        </div>

        <button type="submit" class="auth-btn-primary">
            <i class="bi bi-box-arrow-in-right"></i>
            Sign in
        </button>
    </form>

    <div class="mt-6 pt-6" style="border-top: 1px solid var(--auth-line);">
        <p class="text-center text-sm mb-3" style="color: var(--auth-muted);">Don't have an account yet?</p>
        <button type="button" onclick="openRegisterModal()" class="auth-btn-secondary">
            <i class="bi bi-building-add"></i>
            Register your company
        </button>
    </div>
@endsection

@push('modals')
<div id="registerModal" class="auth-modal" onclick="if (event.target === this) closeRegisterModal()">
    <div class="auth-modal-panel">
        <div class="flex items-center justify-between px-6 py-4" style="border-bottom: 1px solid #e5e7eb;">
            <div>
                <h3 class="text-lg font-bold" style="color: var(--auth-ink);">Register your company</h3>
                <p class="text-sm" style="color: var(--auth-muted);">Company details and first admin account</p>
            </div>
            <button type="button" onclick="closeRegisterModal()" class="h-9 w-9 rounded-lg text-slate-500 hover:bg-slate-100" aria-label="Close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <form id="registerCompanyForm" class="px-6 py-5 space-y-6" onsubmit="return submitRegisterForm(event)">
            @csrf
            <div id="register-form-errors" class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm" style="display:none;"></div>

            <div>
                <div class="auth-section-title"><i class="bi bi-building"></i> Company details</div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1.5" style="color: var(--auth-ink);">Company name <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <i class="bi bi-building auth-field-icon"></i>
                            <input type="text" name="company_name" required placeholder="Your company name" class="auth-input">
                        </div>
                        <div class="field-error text-red-600 text-sm mt-1" data-field="company_name" style="display:none;"></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1.5" style="color: var(--auth-ink);">Company email</label>
                        <div class="relative">
                            <i class="bi bi-envelope auth-field-icon"></i>
                            <input type="email" name="company_email" placeholder="info@company.com" class="auth-input">
                        </div>
                        <div class="field-error text-red-600 text-sm mt-1" data-field="company_email" style="display:none;"></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1.5" style="color: var(--auth-ink);">Company phone</label>
                        <div class="relative">
                            <i class="bi bi-telephone auth-field-icon"></i>
                            <input type="text" name="company_phone" placeholder="98XXXXXXXX" class="auth-input">
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1.5" style="color: var(--auth-ink);">Company address</label>
                        <div class="relative">
                            <i class="bi bi-geo-alt auth-field-icon"></i>
                            <input type="text" name="company_address" placeholder="City, district" class="auth-input">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1.5" style="color: var(--auth-ink);">PAN / Tax number</label>
                        <div class="relative">
                            <i class="bi bi-receipt auth-field-icon"></i>
                            <input type="text" name="tax_number" placeholder="Optional" class="auth-input">
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <div class="auth-section-title"><i class="bi bi-person-badge"></i> Admin account</div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1.5" style="color: var(--auth-ink);">Full name <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <i class="bi bi-person auth-field-icon"></i>
                            <input type="text" name="name" required placeholder="Admin full name" class="auth-input">
                        </div>
                        <div class="field-error text-red-600 text-sm mt-1" data-field="name" style="display:none;"></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1.5" style="color: var(--auth-ink);">Phone</label>
                        <div class="relative">
                            <i class="bi bi-phone auth-field-icon"></i>
                            <input type="text" name="phone" placeholder="Contact number" class="auth-input">
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1.5" style="color: var(--auth-ink);">Email <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <i class="bi bi-envelope auth-field-icon"></i>
                            <input type="email" name="email" required placeholder="admin@company.com" class="auth-input">
                        </div>
                        <div class="field-error text-red-600 text-sm mt-1" data-field="email" style="display:none;"></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1.5" style="color: var(--auth-ink);">Password <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <i class="bi bi-lock auth-field-icon"></i>
                            <input type="password" name="password" id="reg-password" required placeholder="Min. 8 characters" class="auth-input pr-16">
                            <button type="button" onclick="togglePasswordVisibility('reg-password', this)" class="absolute inset-y-0 right-0 px-3 text-xs font-semibold auth-link">Show</button>
                        </div>
                        <div class="field-error text-red-600 text-sm mt-1" data-field="password" style="display:none;"></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1.5" style="color: var(--auth-ink);">Confirm password <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <i class="bi bi-shield-lock auth-field-icon"></i>
                            <input type="password" name="password_confirmation" id="reg-password-confirm" required placeholder="Repeat password" class="auth-input pr-16">
                            <button type="button" onclick="togglePasswordVisibility('reg-password-confirm', this)" class="absolute inset-y-0 right-0 px-3 text-xs font-semibold auth-link">Show</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 pt-1 pb-1">
                <button type="button" onclick="closeRegisterModal()" class="inline-flex items-center justify-center gap-2 text-sm font-semibold px-1 auth-link">
                    <i class="bi bi-arrow-left"></i> Back to login
                </button>
                <button type="submit" id="register-submit-btn" class="auth-btn-primary sm:!w-auto sm:min-w-[200px]">
                    <i class="bi bi-check2-circle"></i>
                    Register company
                </button>
            </div>
        </form>
    </div>
</div>
@endpush

@push('scripts')
<script>
    const registerStoreUrl = @json(route('admin.register.company.store'));
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

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

    function openRegisterModal() {
        document.getElementById('registerModal').classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }

    function closeRegisterModal() {
        document.getElementById('registerModal').classList.remove('is-open');
        document.body.style.overflow = '';
    }

    function clearRegisterErrors() {
        const box = document.getElementById('register-form-errors');
        if (box) {
            box.style.display = 'none';
            box.textContent = '';
        }
        document.querySelectorAll('#registerCompanyForm .field-error').forEach(function (el) {
            el.style.display = 'none';
            el.textContent = '';
        });
    }

    function submitRegisterForm(e) {
        e.preventDefault();
        clearRegisterErrors();
        const form = document.getElementById('registerCompanyForm');
        const btn = document.getElementById('register-submit-btn');
        if (!form || !btn) return false;
        const original = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Saving...';

        fetch(registerStoreUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: new FormData(form)
        })
        .then(function (response) {
            const contentType = response.headers.get('content-type') || '';
            if (!contentType.includes('application/json')) {
                throw { message: 'Could not register. Please try again.' };
            }
            return response.json().then(function (data) {
                if (!response.ok || data.success === false) {
                    throw data;
                }
                return data;
            });
        })
        .then(function (data) {
            window.location.href = data.redirect || '/admin/dashboard';
        })
        .catch(function (error) {
            if (error && error.errors) {
                Object.keys(error.errors).forEach(function (field) {
                    const el = document.querySelector('#registerCompanyForm .field-error[data-field="' + field + '"]');
                    if (el) {
                        el.textContent = error.errors[field][0];
                        el.style.display = 'block';
                    }
                });
            }
            const box = document.getElementById('register-form-errors');
            const first = error && error.errors ? Object.values(error.errors)[0] : null;
            if (box) {
                box.textContent = (error && error.message) || (first && first[0]) || 'Could not register. Please check the form.';
                box.style.display = 'block';
            }
            btn.disabled = false;
            btn.innerHTML = original;
        });

        return false;
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeRegisterModal();
    });

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.auth-card > .auth-alert').forEach(function (alert) {
            setTimeout(function () { alert.remove(); }, 6000);
        });
    });
</script>
@endpush
