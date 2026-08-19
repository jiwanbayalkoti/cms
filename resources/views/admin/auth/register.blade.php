@extends('admin.auth.layout')

@section('title', 'Create account')

@section('hero_title', 'Create your company account.')
@section('hero_subtitle', 'Set up your organization and start managing construction projects with a dedicated admin workspace.')

@section('content')
    <div class="mb-6 text-center lg:text-left">
        <h2 class="text-2xl font-bold" style="color: var(--auth-ink);">Get started</h2>
        <p class="mt-1 text-sm" style="color: var(--auth-muted);">Choose how you want to register.</p>
    </div>

    <a href="{{ route('admin.register.company') }}"
       class="group block rounded-2xl p-6 transition"
       style="border: 1px solid var(--auth-line); background: linear-gradient(180deg, #fffdf9 0%, #f3f7f6 100%);">
        <div class="flex items-start gap-4">
            <div class="h-12 w-12 rounded-xl text-white flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform" style="background: var(--auth-mid);">
                <i class="bi bi-building-add text-xl"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold" style="color: var(--auth-ink);">Register as company</h3>
                <p class="mt-1 text-sm leading-relaxed" style="color: var(--auth-muted);">
                    Add your company to the system and create the first admin account in one step.
                </p>
                <span class="mt-3 inline-flex items-center gap-1 text-sm font-semibold auth-link group-hover:gap-2 transition-all">
                    Continue <i class="bi bi-arrow-right"></i>
                </span>
            </div>
        </div>
    </a>
@endsection

@section('footer')
    <a href="{{ route('admin.login') }}" class="text-sm auth-link">
        Already have an account? Sign in
    </a>
@endsection
