@extends('admin.layout')

@section('title', 'Super Admin Dashboard')

@section('content')
<div class="mb-6">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Super Admin Dashboard</h1>
            <p class="mt-2 text-gray-600">Platform control center. Manage system settings here, then open a company to work inside its ERP.</p>
        </div>
        <a href="{{ route('admin.settings.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
            Open System Settings
        </a>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800 text-sm">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800 text-sm">{{ session('error') }}</div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-lg shadow p-5">
        <p class="text-sm text-gray-500">System Name</p>
        <p class="text-lg font-semibold text-gray-900 mt-1">{{ $systemSettings['system_name'] }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-5">
        <p class="text-sm text-gray-500">Common Trial</p>
        <p class="text-lg font-semibold text-gray-900 mt-1">{{ $systemSettings['trial_days'] }} days</p>
    </div>
    <div class="bg-white rounded-lg shadow p-5">
        <p class="text-sm text-gray-500">Yearly Rate</p>
        <p class="text-lg font-semibold text-gray-900 mt-1">Rs. {{ number_format((float) $systemSettings['payment_yearly_rate'], 2) }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-5">
        <p class="text-sm text-gray-500">Pending Payments</p>
        <p class="text-lg font-semibold {{ ($platformStats['pending_payments'] ?? 0) > 0 ? 'text-amber-600' : 'text-gray-900' }} mt-1">{{ $platformStats['pending_payments'] ?? 0 }}</p>
    </div>
</div>

<div class="mb-8">
    <h2 class="text-lg font-semibold text-gray-900 mb-3">System Settings & Platform Tools</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <a href="{{ route('admin.settings.index') }}" class="bg-white rounded-lg shadow hover:shadow-md transition p-5 border border-transparent hover:border-indigo-500">
            <h3 class="font-semibold text-gray-900">System Settings</h3>
            <p class="text-sm text-gray-600 mt-2">Branding, trial days, payment methods, pricing, and payment verification.</p>
        </a>
        <a href="{{ route('admin.companies.index') }}" class="bg-white rounded-lg shadow hover:shadow-md transition p-5 border border-transparent hover:border-indigo-500">
            <h3 class="font-semibold text-gray-900">Manage Companies</h3>
            <p class="text-sm text-gray-600 mt-2">Create, edit, lock, trial period, and assign admin modules.</p>
        </a>
        <a href="{{ route('admin.users.index') }}" class="bg-white rounded-lg shadow hover:shadow-md transition p-5 border border-transparent hover:border-indigo-500">
            <h3 class="font-semibold text-gray-900">All Users</h3>
            <p class="text-sm text-gray-600 mt-2">View every login account across all companies ({{ $platformStats['users'] ?? 0 }} total).</p>
        </a>
        <a href="{{ route('admin.user-activities.index') }}" class="bg-white rounded-lg shadow hover:shadow-md transition p-5 border border-transparent hover:border-indigo-500">
            <h3 class="font-semibold text-gray-900">Activity Logs</h3>
            <p class="text-sm text-gray-600 mt-2">Audit user actions across the whole platform.</p>
        </a>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
    <div class="bg-white rounded-lg shadow p-5">
        <p class="text-sm text-gray-500">Companies</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $platformStats['companies'] ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-5">
        <p class="text-sm text-gray-500">Projects</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $platformStats['projects'] ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-5">
        <p class="text-sm text-gray-500">Email API Usage</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $platformStats['email_api_usage'] ?? 0 }} / {{ $platformStats['email_api_limit'] ?? 0 }}</p>
        <p class="text-xs text-gray-500 mt-1">Account emails sent: {{ $platformStats['email_notifications'] ?? 0 }}</p>
    </div>
</div>

<div class="mb-4 flex items-center justify-between gap-3">
    <div>
        <h2 class="text-lg font-semibold text-gray-900">Companies</h2>
        <p class="text-sm text-gray-600">Click a company to switch into its dashboard and ERP modules.</p>
    </div>
</div>

@if($companies->isEmpty())
    <div class="bg-white shadow-lg rounded-lg p-8 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No companies found</h3>
        <p class="mt-1 text-sm text-gray-500">Get started by creating a new company.</p>
        <a href="{{ route('admin.companies.index') }}" class="inline-flex mt-4 px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition">Manage companies</a>
    </div>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach($companies as $company)
            <form action="{{ route('admin.companies.switch') }}" method="POST" class="h-full">
                @csrf
                <input type="hidden" name="company_id" value="{{ $company->id }}">
                <input type="hidden" name="redirect_to" value="dashboard">
                <button type="submit"
                        class="w-full bg-white overflow-hidden shadow-lg rounded-lg hover:shadow-xl transition-all duration-200 cursor-pointer group border-2 border-transparent hover:border-indigo-500 text-left">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex-shrink-0">
                            @if($company->logo)
                                <img src="{{ $company->getLogoUrl() }}" alt="{{ $company->name }}" class="h-12 w-12 rounded-lg object-cover">
                            @else
                                <div class="h-12 w-12 rounded-lg bg-indigo-100 flex items-center justify-center">
                                    <span class="text-indigo-600 font-bold text-lg">{{ strtoupper(substr($company->name, 0, 1)) }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-gray-400 group-hover:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </div>

                    <h3 class="text-lg font-semibold text-gray-900 mb-2 group-hover:text-indigo-600 transition-colors">
                        {{ $company->name }}
                    </h3>

                    <div class="flex items-center mt-4">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                        </div>
                        <div class="ml-4 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Projects</dt>
                                <dd class="text-2xl font-bold text-gray-900">{{ $company->projects_count }}</dd>
                            </dl>
                        </div>
                    </div>

                    @if($company->email || $company->phone)
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <div class="text-xs text-gray-500 space-y-1">
                                @if($company->email)
                                    <div class="flex items-center">
                                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        <span class="truncate">{{ $company->email }}</span>
                                    </div>
                                @endif
                                @if($company->phone)
                                    <div class="flex items-center">
                                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5a2 2 0 012-2z"></path>
                                        </svg>
                                        <span>{{ $company->phone }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
                </button>
            </form>
        @endforeach
    </div>
@endif

@endsection
