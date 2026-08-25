<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Auth') — {{ config('app.name', 'CMS') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'system-ui', 'sans-serif'] },
                    colors: {
                        brand: {
                            50: '#f3f7f6',
                            100: '#dceae8',
                            500: '#1a5c5f',
                            600: '#164c4f',
                            700: '#123c3e',
                            900: '#0c2b2d',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        :root {
            --auth-deep: #123c3e;
            --auth-mid: #1a5c5f;
            --auth-soft: #d5e6e4;
            --auth-cream: #ece7de;
            --auth-card: #ffffff;
            --auth-ink: #111827;
            --auth-muted: #4b5563;
            --auth-line: #c5beb3;
            --auth-input-border: #9ca3af;
            --auth-copper: #c47a3a;
        }
        body { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; background: var(--auth-cream); color: var(--auth-ink); }
        .auth-panel {
            background-color: #123c3e !important;
            background-image:
                linear-gradient(rgba(255,255,255,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.04) 1px, transparent 1px),
                radial-gradient(circle at 18% 18%, rgba(196, 122, 58, 0.22) 0%, transparent 42%),
                linear-gradient(160deg, #0c2b2d 0%, #123c3e 52%, #1a5c5f 100%);
            background-size: 32px 32px, 32px 32px, auto, auto;
            color: #ffffff !important;
        }
        .auth-card {
            background: #ffffff !important;
            border: 1px solid #cfd8d7 !important;
            box-shadow: 0 16px 40px rgba(18, 60, 62, 0.16);
        }
        .auth-input {
            width: 100%;
            border: 1.5px solid var(--auth-input-border) !important;
            border-radius: 0.75rem;
            padding: 0.78rem 0.9rem 0.78rem 2.65rem;
            font-size: 0.95rem;
            font-weight: 500;
            color: #111827 !important;
            background: #f8faf9 !important;
            transition: border-color 0.15s, box-shadow 0.15s, background 0.15s;
        }
        .auth-input::placeholder {
            color: #6b7280 !important;
            opacity: 1;
        }
        .auth-input:focus {
            outline: none;
            background: #ffffff !important;
            border-color: var(--auth-mid) !important;
            box-shadow: 0 0 0 4px rgba(26, 92, 95, 0.18);
        }
        .auth-input.is-invalid { border-color: #c2410c !important; }
        .auth-input:-webkit-autofill,
        .auth-input:-webkit-autofill:hover,
        .auth-input:-webkit-autofill:focus {
            -webkit-text-fill-color: #111827;
            -webkit-box-shadow: 0 0 0 1000px #f8faf9 inset;
            transition: background-color 9999s ease-in-out 0s;
        }
        .auth-field-icon {
            position: absolute;
            left: 0.85rem;
            top: 50%;
            transform: translateY(-50%);
            color: #1a5c5f;
            font-size: 1.05rem;
            pointer-events: none;
        }
        .auth-btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.78rem 1rem;
            border-radius: 0.75rem;
            font-weight: 600;
            font-size: 0.95rem;
            color: #fff;
            background: var(--auth-mid);
            box-shadow: 0 10px 22px -12px rgba(18, 60, 62, 0.7);
            transition: transform 0.15s, box-shadow 0.15s, background 0.15s;
        }
        .auth-btn-primary:hover {
            background: var(--auth-deep);
            box-shadow: 0 14px 26px -12px rgba(18, 60, 62, 0.8);
            transform: translateY(-1px);
        }
        .auth-btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            width: 100%;
            padding: 0.72rem 1rem;
            border-radius: 0.75rem;
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--auth-deep);
            background: #ffffff;
            border: 1.5px solid #1a5c5f;
            transition: background 0.15s, border-color 0.15s;
        }
        .auth-btn-secondary:hover { background: #e8f3f2; border-color: #123c3e; }
        .auth-alert {
            border-radius: 0.75rem;
            padding: 0.85rem 2.25rem 0.85rem 0.95rem;
            font-size: 0.875rem;
            position: relative;
            animation: authSlideIn 0.25s ease-out;
        }
        @keyframes authSlideIn {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .auth-alert-close {
            position: absolute;
            top: 0.55rem;
            right: 0.55rem;
            background: transparent;
            border: none;
            font-size: 1.1rem;
            line-height: 1;
            opacity: 0.65;
            cursor: pointer;
        }
        .auth-alert-close:hover { opacity: 1; }
        .auth-alert.hiding {
            opacity: 0;
            transform: translateY(-6px);
            transition: all 0.25s ease-in-out;
            margin-bottom: 0 !important;
            padding-top: 0;
            padding-bottom: 0;
            max-height: 0;
            overflow: hidden;
        }
        .auth-section-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #1a5c5f;
            margin-bottom: 1rem;
        }
        .auth-section-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #cfd8d7;
        }
        .auth-link { color: #164c4f; font-weight: 700; }
        .auth-link:hover { color: #0c2b2d; }
        .auth-modal {
            position: fixed;
            inset: 0;
            z-index: 80;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background: rgba(12, 43, 45, 0.55);
        }
        .auth-modal.is-open { display: flex; }
        .auth-modal-panel {
            width: 100%;
            max-width: 44rem;
            max-height: 92vh;
            overflow: auto;
            background: #ffffff;
            border-radius: 1rem;
            box-shadow: 0 24px 60px rgba(12, 43, 45, 0.28);
        }
    </style>
    @stack('styles')
</head>
<body class="antialiased">
    <div class="min-h-screen lg:grid lg:grid-cols-2">
        {{-- Brand panel --}}
        <aside class="auth-panel relative hidden lg:flex flex-col justify-between p-10 xl:p-14 overflow-hidden min-h-screen">
            <div>
                <div class="mb-10">
                    @include('admin.partials.cms-logo', ['logoId' => 'authLeft', 'logoWidth' => 220, 'logoHeight' => 72])
                </div>
                <h1 class="text-3xl xl:text-4xl font-extrabold leading-tight max-w-md" style="color: #ffffff;">
                    @yield('hero_title', 'Manage projects, finance & teams in one place.')
                </h1>
                <p class="mt-4 text-sm xl:text-base max-w-md leading-relaxed" style="color: #e4f0ee;">
                    @yield('hero_subtitle', 'Track income, expenses, materials, staff, vehicles and billing from a single dashboard.')
                </p>
            </div>

            <ul class="space-y-3 text-sm" style="color: #e8f1ef;">
                <li class="flex items-center gap-3">
                    <span class="h-8 w-8 rounded-lg flex items-center justify-center shrink-0" style="background: rgba(196,122,58,0.2); color: #f3d7b5;"><i class="bi bi-kanban"></i></span>
                    Project & milestone tracking
                </li>
                <li class="flex items-center gap-3">
                    <span class="h-8 w-8 rounded-lg flex items-center justify-center shrink-0" style="background: rgba(196,122,58,0.2); color: #f3d7b5;"><i class="bi bi-cash-stack"></i></span>
                    Income, expense & salary reports
                </li>
                <li class="flex items-center gap-3">
                    <span class="h-8 w-8 rounded-lg flex items-center justify-center shrink-0" style="background: rgba(196,122,58,0.2); color: #f3d7b5;"><i class="bi bi-shield-check"></i></span>
                    Role-based secure access
                </li>
            </ul>

            <p class="text-xs" style="color: #b7c9c7;">&copy; {{ date('Y') }} {{ config('app.name', 'CMS') }}. All rights reserved.</p>
        </aside>

        {{-- Form panel --}}
        <main class="flex items-center justify-center px-4 py-10 sm:px-6 lg:px-10 xl:px-16" style="background: var(--auth-cream);">
            <div class="w-full @yield('panel_width', 'max-w-md')">
                {{-- Mobile brand --}}
                <div class="lg:hidden text-center mb-8">
                    <div class="inline-flex items-center justify-center rounded-xl px-4 py-3" style="background: #1f2937;">
                        @include('admin.partials.cms-logo', ['logoId' => 'authMobile', 'logoWidth' => 170, 'logoHeight' => 56])
                    </div>
                </div>

                <div class="auth-card rounded-2xl p-6 sm:p-8">
                    <div class="hidden lg:flex justify-center mb-6 pb-5" style="border-bottom: 1px solid #e5e7eb;">
                        <div class="inline-flex items-center justify-center rounded-xl px-4 py-3" style="background: #1f2937;">
                            @include('admin.partials.cms-logo', ['logoId' => 'authForm', 'logoWidth' => 170, 'logoHeight' => 56])
                        </div>
                    </div>
                    @yield('content')
                </div>

                @hasSection('footer')
                    <div class="mt-6 text-center">
                        @yield('footer')
                    </div>
                @endif
            </div>
        </main>
    </div>

    @stack('modals')
    @stack('scripts')
    <script>
        (function () {
            function dismissAuthAlert(alert) {
                if (!alert || !alert.isConnected) return;
                alert.classList.add('hiding');
                setTimeout(function () {
                    if (alert.isConnected) alert.remove();
                }, 250);
            }

            window.closeAuthAlert = function (button) {
                const alert = button && button.closest ? button.closest('.auth-alert') : null;
                if (!alert) return;
                if (alert._autoDismissTimer) {
                    clearTimeout(alert._autoDismissTimer);
                    alert._autoDismissTimer = null;
                }
                dismissAuthAlert(alert);
            };

            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('.auth-alert').forEach(function (alert) {
                    if (alert.dataset.autoDismissBound === '1') return;
                    alert.dataset.autoDismissBound = '1';
                    alert._autoDismissTimer = setTimeout(function () {
                        dismissAuthAlert(alert);
                    }, 5000);
                });
            });
        })();
    </script>
</body>
</html>
