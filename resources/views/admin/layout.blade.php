<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Panel') - {{ config('app.name', 'Laravel') }}</title>

    @php
        use App\Support\CompanyContext;
        $activeCompanyId = CompanyContext::getActiveCompanyId();
        $faviconUrl = asset('favicon.ico');
        if ($activeCompanyId) {
            try {
                $company = \App\Models\Company::find($activeCompanyId);
                if ($company) {
                    $faviconUrl = $company->getFaviconUrl();
                }
            } catch (\Exception $e) {
                // Fallback to default
            }
        }
    @endphp
    <link rel="icon" type="image/png" href="{{ $faviconUrl }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Preconnect for CDN -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Bootstrap CSS (for module screens using Bootstrap classes) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        [x-cloak] { display: none !important; }
        
        
        /* Sidebar Styles */
        .sidebar {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar.collapsed {
            width: 80px;
        }
        
        .sidebar.expanded {
            width: 256px;
        }
        
        .sidebar-content {
            transition: opacity 0.2s ease-in-out;
        }
        
        .sidebar.collapsed .sidebar-content {
            opacity: 0;
            pointer-events: none;
        }
        
        .sidebar-icon {
            transition: all 0.3s ease;
        }
        
        .sidebar.collapsed .nav-item-text {
            display: none;
        }
        
        .sidebar.collapsed .nav-item {
            justify-content: center;
            padding-left: 0;
            padding-right: 0;
        }
        
        .sidebar.collapsed .submenu {
            display: none !important;
        }
        
        .nav-item {
            position: relative;
            overflow: hidden;
        }
        
        .nav-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: linear-gradient(180deg, #3b82f6, #8b5cf6);
            transform: scaleY(0);
            transition: transform 0.2s ease;
        }
        
        .nav-item.active::before,
        .nav-item:hover::before {
            transform: scaleY(1);
        }
        
        /* Tooltip for collapsed sidebar */
        .sidebar.collapsed .nav-item {
            position: relative;
        }
        
        .sidebar.collapsed .nav-item::after {
            content: attr(data-tooltip);
            position: absolute;
            left: 100%;
            top: 50%;
            transform: translateY(-50%);
            margin-left: 12px;
            padding: 8px 12px;
            background: rgba(0, 0, 0, 0.9);
            color: white;
            border-radius: 6px;
            font-size: 14px;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease;
            z-index: 1000;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar.collapsed .nav-item:hover::after {
            opacity: 1;
        }
        
        .sidebar.collapsed .nav-item::before {
            content: '';
            position: absolute;
            left: 100%;
            top: 50%;
            transform: translateY(-50%);
            margin-left: 8px;
            border: 6px solid transparent;
            border-right-color: rgba(0, 0, 0, 0.9);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease;
            z-index: 1001;
        }
        
        .sidebar.collapsed .nav-item:hover::before {
            opacity: 1;
        }
        
        .sidebar-toggle-btn {
            transition: transform 0.3s ease;
        }
        
        .sidebar-toggle-btn:hover {
            transform: scale(1.1);
        }
        
        .menu-badge {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
        }
        
        .sidebar.collapsed .menu-badge {
            display: none;
        }
        
        /* Custom Scrollbar */
        #sidebar-nav::-webkit-scrollbar {
            width: 6px;
        }
        
        #sidebar-nav::-webkit-scrollbar-track {
            background: #1f2937;
        }
        
        #sidebar-nav::-webkit-scrollbar-thumb {
            background: #4b5563;
            border-radius: 3px;
        }
        
        #sidebar-nav::-webkit-scrollbar-thumb:hover {
            background: #6b7280;
        }
        
        /* Alert Messages */
        .alert-message,
        .alert.alert-success,
        .alert.alert-danger,
        .alert.alert-info,
        .alert.alert-warning {
            transition: all 0.3s ease-in-out;
            animation: slideIn 0.3s ease-out;
        }
        
        .alert-message.hiding,
        .alert.hiding {
            opacity: 0;
            transform: translateY(-10px);
            margin-bottom: 0 !important;
            padding-top: 0;
            padding-bottom: 0;
            max-height: 0;
            overflow: hidden;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .alert-close-btn {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            background: transparent;
            border: none;
            font-size: 1.25rem;
            font-weight: bold;
            line-height: 1;
            color: inherit;
            opacity: 0.7;
            cursor: pointer;
            padding: 0.25rem 0.5rem;
            transition: opacity 0.2s;
            z-index: 1;
        }
        
        .alert-close-btn:hover {
            opacity: 1;
        }
        
        /* Ensure Bootstrap alerts have relative positioning for close button */
        .alert {
            position: relative;
        }
        
        @media (max-width: 768px) {
            /* Full screen layout on mobile */
            body {
                margin: 0;
                padding: 0;
                width: 100vw;
                overflow-x: hidden;
            }
            
            .min-h-screen {
                min-height: 100vh;
                width: 100vw;
                margin: 0;
                padding: 0;
            }
            
            /* Sidebar - hidden by default, overlays when open */
            .sidebar {
                flex: 0 0 0 !important;
                width: 0 !important;
                max-width: 0 !important;
                padding: 0 !important;
                position: fixed;
                left: 0;
                top: 0;
                z-index: 1000;
                height: 100vh;
                transform: translateX(-100%);
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                box-shadow: 2px 0 20px rgba(0, 0, 0, 0.5);
                visibility: hidden;
                opacity: 0;
            }
            
            .sidebar.mobile-open {
                flex: 0 0 280px !important;
                width: 280px !important;
                max-width: 85vw !important;
                padding: inherit !important;
                transform: translateX(0);
                visibility: visible;
                opacity: 1;
            }
            
            .sidebar.collapsed,
            .sidebar.expanded {
                width: 0 !important;
                max-width: 0 !important;
            }
            
            .sidebar-toggle-btn {
                display: none !important;
            }
            
            /* Overlay - shows when sidebar is open */
            .sidebar-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.6);
                z-index: 999;
                transition: opacity 0.3s ease;
                backdrop-filter: blur(2px);
                opacity: 0;
            }
            
            .sidebar-overlay.active {
                display: block;
                opacity: 1;
            }
            
            /* Ensure sidebar content is always visible on mobile */
            .sidebar .sidebar-content {
                opacity: 1 !important;
                pointer-events: auto !important;
            }
            
            .sidebar .nav-item-text {
                display: block !important;
            }
            
            /* Submenu handling on mobile */
            .sidebar .submenu {
                max-height: 0;
                overflow: hidden;
                transition: max-height 0.3s ease-out;
            }
            
            .sidebar .submenu:not(.hidden) {
                max-height: 500px;
            }
            
            /* Prevent body scroll when sidebar is open */
            body.sidebar-open {
                overflow: hidden;
                position: fixed;
                width: 100%;
                height: 100%;
            }
            
            /* Main content - full width on mobile */
            .flex-1.flex.flex-col {
                width: 100vw;
                margin: 0;
                padding: 0;
            }
            
            /* Header adjustments */
            nav {
                width: 100vw;
                margin: 0;
                padding: 0 0.5rem;
            }
            
            /* Main content area */
            main {
                width: 100vw;
                margin: 0;
                padding: 0.5rem;
                overflow-x: hidden;
            }
            
            main .max-w-7xl {
                max-width: 100%;
                padding: 0;
                margin: 0;
            }
            
            /* Ensure cards and content fit mobile */
            .card, .bg-white {
                width: 100%;
                max-width: 100%;
            }
            
            /* Fix table overflow on mobile */
            .table-responsive {
                width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
        }
        
        @media (min-width: 769px) {
            /* Ensure sidebar is visible on desktop */
            .sidebar {
                display: block !important;
                position: relative !important;
                transform: none !important;
                visibility: visible !important;
                opacity: 1 !important;
            }
            
            .sidebar.mobile-open {
                transform: none !important;
            }
            
            .sidebar-overlay {
                display: none !important;
            }
            
            #sidebarToggleMobile {
                display: none !important;
            }
            
            /* Ensure main content has proper spacing on desktop */
            .flex-1.flex.flex-col {
                width: auto;
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>
<body class="antialiased bg-gray-100">
    <div class="min-h-screen flex">
        <!-- Sidebar Overlay (Mobile) -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        
        <!-- Sidebar -->
        <aside class="sidebar expanded bg-gradient-to-b from-gray-800 via-gray-800 to-gray-900 min-h-screen relative" id="sidebar">
            <div class="p-4 h-full flex flex-col">
                @php
                    $activeCompanyId = session('active_company_id') ?: Auth::user()->company_id;
                    $activeCompany = \App\Models\Company::find($activeCompanyId);
                @endphp

                <!-- Sidebar Header with Toggle -->
                <div class="flex items-center justify-between mb-6">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-2 sm:space-x-3 sidebar-content min-w-0 flex-1">
                        @if($activeCompany && $activeCompany->logo)
                            @php
                                $logoUrl = $activeCompany->getLogoUrl();
                            @endphp
                            @if($logoUrl)
                                <img src="{{ $logoUrl }}" alt="Company Logo" class="h-10 w-10 sm:h-12 sm:w-12 rounded-lg shadow-lg bg-white p-1 object-contain flex-shrink-0">
                            @endif
                        @endif
                        <span class="text-base sm:text-xl font-bold text-white truncate">{{ $activeCompany?->name ?? 'Admin Panel' }}</span>
                    </a>
                    <button id="sidebarToggle" class="sidebar-toggle-btn p-2 rounded-lg bg-gray-700 hover:bg-gray-600 text-white transition-all duration-200 flex-shrink-0" aria-label="Toggle Sidebar">
                        <svg id="sidebarToggleIcon" class="w-5 h-5 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                        </svg>
                    </button>
                </div>

                <nav class="space-y-1 flex-1 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-600 scrollbar-track-gray-800" id="sidebar-nav" style="scrollbar-width: thin; scrollbar-color: #4b5563 #1f2937;">
                    @php
                        $projectsOpen = $projectsOpen ?? request()->routeIs('admin.projects.*');
                        $materialsOpen = $materialsOpen ?? (request()->routeIs('admin.construction-materials.*') || request()->routeIs('admin.material-*') || request()->routeIs('admin.suppliers.*') || request()->routeIs('admin.work-types.*') || request()->routeIs('admin.payment-modes.*') || request()->routeIs('admin.purchased-bies.*'));
                        $billingOpen = $billingOpen ?? (request()->routeIs('admin.bill-*') || request()->routeIs('admin.completed-works.*'));
                        $staffOpen = $staffOpen ?? (request()->routeIs('admin.staff.*') || request()->routeIs('admin.positions.*') || request()->routeIs('admin.users.*'));
                        $financeOpen = $financeOpen ?? (request()->routeIs('admin.incomes.*') || request()->routeIs('admin.expenses.*') || request()->routeIs('admin.reports.*') || request()->routeIs('admin.categories.*') || request()->routeIs('admin.subcategories.*'));
                        $accountingOpen = $accountingOpen ?? (request()->routeIs('admin.chart-of-accounts.*') || request()->routeIs('admin.journal-entries.*') || request()->routeIs('admin.bank-accounts.*') || request()->routeIs('admin.purchase-invoices.*') || request()->routeIs('admin.sales-invoices.*') || request()->routeIs('admin.customers.*'));
                        $vehicleRentOpen = $vehicleRentOpen ?? request()->routeIs('admin.vehicle-rents.*');
                    @endphp
                    <a href="{{ route('admin.dashboard') }}" data-tooltip="Dashboard" class="nav-item flex items-center px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-lg' : '' }}">
                        <svg class="w-5 h-5 mr-3 sidebar-icon flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        <span class="nav-item-text">Admin Dashboard</span>
                    </a>
                    <a href="{{ route('admin.companies.profile') }}" data-tooltip="Company Profile" class="nav-item flex items-center px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 {{ request()->routeIs('admin.companies.profile*') ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-lg' : '' }}">
                        <svg class="w-5 h-5 mr-3 sidebar-icon flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <span class="nav-item-text">Company Profile</span>
                    </a>
                    @if(Auth::user()->role === 'super_admin')
                    <a href="{{ route('admin.companies.index') }}" data-tooltip="Companies" class="nav-item flex items-center px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 {{ request()->routeIs('admin.companies.index') || request()->routeIs('admin.companies.create') || request()->routeIs('admin.companies.edit') || request()->routeIs('admin.companies.show') ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-lg' : '' }}">
                        <svg class="w-5 h-5 mr-3 sidebar-icon flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18"></path>
                        </svg>
                        <span class="nav-item-text">Companies</span>
                    </a>
                    @endif
                    <button type="button" data-tooltip="Projects" class="nav-item w-full flex items-center justify-between px-4 py-3 text-gray-200 hover:bg-gray-700 rounded-lg transition-all duration-200 group-toggle" data-target="projects-menu" aria-expanded="{{ $projectsOpen ? 'true' : 'false' }}">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-3 sidebar-icon flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18"></path>
                            </svg>
                            <span class="nav-item-text">Projects</span>
                        </span>
                        <svg class="w-4 h-4 transition-transform flex-shrink-0 sidebar-content {{ $projectsOpen ? 'rotate-180' : '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" data-icon="chevron">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9l6 6 6-6"/>
                        </svg>
                    </button>
                    <div id="projects-menu" class="submenu space-y-1 pl-4 ml-4 border-l-2 border-gray-600 {{ $projectsOpen ? 'mt-2' : 'hidden' }}">
                        <a href="{{ route('admin.projects.index') }}" class="flex items-center px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 {{ request()->routeIs('admin.projects.*') ? 'bg-gray-700 text-white' : '' }}">
                            <span class="text-sm">All Projects</span>
                        </a>
                    </div>
                    <button type="button" data-tooltip="Materials & Procurement" class="nav-item w-full flex items-center justify-between px-4 py-3 text-gray-200 hover:bg-gray-700 rounded-lg transition-all duration-200 group-toggle" data-target="materials-menu" aria-expanded="{{ $materialsOpen ? 'true' : 'false' }}">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-3 sidebar-icon flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                            <span class="nav-item-text">Materials & Procurement</span>
                        </span>
                        <svg class="w-4 h-4 transition-transform flex-shrink-0 sidebar-content {{ $materialsOpen ? 'rotate-180' : '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" data-icon="chevron">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9l6 6 6-6"/>
                        </svg>
                    </button>
                    <div id="materials-menu" class="submenu space-y-1 pl-4 ml-4 border-l-2 border-gray-600 {{ $materialsOpen ? 'mt-2' : 'hidden' }}">
                    <a href="{{ route('admin.material-calculator.index') }}" class="flex items-center px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 {{ request()->routeIs('admin.material-calculator.*') ? 'bg-gray-700 text-white' : '' }}">
                            <span class="text-sm">Material Calculator</span>
                        </a>   
                    <a href="{{ route('admin.construction-materials.index') }}" class="flex items-center px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 {{ request()->routeIs('admin.construction-materials.*') ? 'bg-gray-700 text-white' : '' }}">
                            <span class="text-sm">Construction Materials</span>
                        </a>
                        <a href="{{ route('admin.material-categories.index') }}" class="flex items-center px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 {{ request()->routeIs('admin.material-categories.*') ? 'bg-gray-700 text-white' : '' }}">
                            <span class="text-sm">Material Categories</span>
                        </a>
                        <a href="{{ route('admin.material-names.index') }}" class="flex items-center px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 {{ request()->routeIs('admin.material-names.*') ? 'bg-gray-700 text-white' : '' }}">
                            <span class="text-sm">Material Names</span>
                        </a>
                        <a href="{{ route('admin.material-units.index') }}" class="flex items-center px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 {{ request()->routeIs('admin.material-units.*') ? 'bg-gray-700 text-white' : '' }}">
                            <span class="text-sm">Material Units</span>
                        </a>
                        <a href="{{ route('admin.suppliers.index') }}" class="flex items-center px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 {{ request()->routeIs('admin.suppliers.*') ? 'bg-gray-700 text-white' : '' }}">
                            <span class="text-sm">Suppliers</span>
                        </a>
                        <a href="{{ route('admin.work-types.index') }}" class="flex items-center px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 {{ request()->routeIs('admin.work-types.*') ? 'bg-gray-700 text-white' : '' }}">
                            <span class="text-sm">Work Types</span>
                        </a>
                        <a href="{{ route('admin.payment-modes.index') }}" class="flex items-center px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 {{ request()->routeIs('admin.payment-modes.*') ? 'bg-gray-700 text-white' : '' }}">
                            <span class="text-sm">Payment Modes</span>
                        </a>
                        <a href="{{ route('admin.purchased-bies.index') }}" class="flex items-center px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 {{ request()->routeIs('admin.purchased-bies.*') ? 'bg-gray-700 text-white' : '' }}">
                            <span class="text-sm">Stackholders</span>
                        </a>
                       
                    </div>
                    <button type="button" data-tooltip="Billing & Estimates" class="nav-item w-full flex items-center justify-between px-4 py-3 text-gray-200 hover:bg-gray-700 rounded-lg transition-all duration-200 group-toggle mt-2" data-target="billing-menu" aria-expanded="{{ $billingOpen ? 'true' : 'false' }}">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-3 sidebar-icon flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span class="nav-item-text">Billing & Estimates</span>
                        </span>
                        <svg class="w-4 h-4 transition-transform flex-shrink-0 sidebar-content {{ $billingOpen ? 'rotate-180' : '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" data-icon="chevron">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9l6 6 6-6"/>
                        </svg>
                    </button>
                    <div id="billing-menu" class="submenu space-y-1 pl-4 ml-4 border-l-2 border-gray-600 {{ $billingOpen ? 'mt-2' : 'hidden' }}">
                        <a href="{{ route('admin.bill-modules.index') }}" class="flex items-center px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 {{ request()->routeIs('admin.bill-modules.*') ? 'bg-gray-700 text-white' : '' }}">
                            <span class="text-sm">Final Bills / Estimates</span>
                        </a>
                        <a href="{{ route('admin.completed-works.index') }}" class="flex items-center px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 {{ request()->routeIs('admin.completed-works.*') ? 'bg-gray-700 text-white' : '' }}">
                            <span class="text-sm">Completed Works</span>
                        </a>
                        <a href="{{ route('admin.bill-categories.index') }}" class="flex items-center px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 {{ request()->routeIs('admin.bill-categories.*') ? 'bg-gray-700 text-white' : '' }}">
                            <span class="text-sm">Bill Categories</span>
                        </a>
                        <a href="{{ route('admin.bill-subcategories.index') }}" class="flex items-center px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 {{ request()->routeIs('admin.bill-subcategories.*') ? 'bg-gray-700 text-white' : '' }}">
                            <span class="text-sm">Bill Subcategories</span>
                        </a>
                    </div>
                    <button type="button" data-tooltip="Staff & Users" class="nav-item w-full flex items-center justify-between px-4 py-3 text-gray-200 hover:bg-gray-700 rounded-lg transition-all duration-200 group-toggle mt-2" data-target="staff-menu" aria-expanded="{{ $staffOpen ? 'true' : 'false' }}">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-3 sidebar-icon flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <span class="nav-item-text">Staff & Users</span>
                        </span>
                        <svg class="w-4 h-4 transition-transform flex-shrink-0 sidebar-content {{ $staffOpen ? 'rotate-180' : '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" data-icon="chevron">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9l6 6 6-6"/>
                        </svg>
                    </button>
                    <div id="staff-menu" class="submenu space-y-1 pl-4 ml-4 border-l-2 border-gray-600 {{ $staffOpen ? 'mt-2' : 'hidden' }}">
                        <a href="{{ route('admin.staff.index') }}" class="flex items-center px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 {{ request()->routeIs('admin.staff.*') ? 'bg-gray-700 text-white' : '' }}">
                            <span class="text-sm">Staff</span>
                        </a>
                        <a href="{{ route('admin.positions.index') }}" class="flex items-center px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 {{ request()->routeIs('admin.positions.*') ? 'bg-gray-700 text-white' : '' }}">
                            <span class="text-sm">Positions</span>
                        </a>
                        @if(Auth::user()->role === 'super_admin')
                        <a href="{{ route('admin.users.index') }}" class="flex items-center px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 {{ request()->routeIs('admin.users.*') ? 'bg-gray-700 text-white' : '' }}">
                            <span class="text-sm">Users</span>
                        </a>
                        @endif
                    </div>
                    <button type="button" data-tooltip="Finance & Reports" class="nav-item w-full flex items-center justify-between px-4 py-3 text-gray-200 hover:bg-gray-700 rounded-lg transition-all duration-200 group-toggle mt-2" data-target="finance-menu" aria-expanded="{{ $financeOpen ? 'true' : 'false' }}">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-3 sidebar-icon flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="nav-item-text">Finance & Reports</span>
                        </span>
                        <svg class="w-4 h-4 transition-transform flex-shrink-0 sidebar-content {{ $financeOpen ? 'rotate-180' : '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" data-icon="chevron">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9l6 6 6-6"/>
                        </svg>
                    </button>
                    <div id="finance-menu" class="submenu space-y-1 pl-4 ml-4 border-l-2 border-gray-600 {{ $financeOpen ? 'mt-2' : 'hidden' }}">
                        <a href="{{ route('admin.incomes.index') }}" class="flex items-center px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 {{ request()->routeIs('admin.incomes.*') ? 'bg-gray-700 text-white' : '' }}">
                            <span class="text-sm">Income</span>
                        </a>
                        <a href="{{ route('admin.expenses.index') }}" class="flex items-center px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 {{ request()->routeIs('admin.expenses.*') ? 'bg-gray-700 text-white' : '' }}">
                            <span class="text-sm">Expenses</span>
                        </a>
                        <a href="{{ route('admin.reports.index') }}" class="flex items-center px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 {{ request()->routeIs('admin.reports.*') ? 'bg-gray-700 text-white' : '' }}">
                            <span class="text-sm">Reports</span>
                        </a>
                        <a href="{{ route('admin.categories.index') }}" class="flex items-center px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 {{ request()->routeIs('admin.categories.*') ? 'bg-gray-700 text-white' : '' }}">
                            <span class="text-sm">Categories</span>
                        </a>
                        <a href="{{ route('admin.subcategories.index') }}" class="flex items-center px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 {{ request()->routeIs('admin.subcategories.*') ? 'bg-gray-700 text-white' : '' }}">
                            <span class="text-sm">Subcategories</span>
                        </a>
                    </div>
                    <!-- <button type="button" data-tooltip="Accounting System" class="nav-item w-full flex items-center justify-between px-4 py-3 text-gray-200 hover:bg-gray-700 rounded-lg transition-all duration-200 group-toggle mt-2" data-target="accounting-menu" aria-expanded="{{ $accountingOpen ? 'true' : 'false' }}">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-3 sidebar-icon flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span class="nav-item-text">Accounting System</span>
                        </span>
                        <svg class="w-4 h-4 transition-transform flex-shrink-0 sidebar-content {{ $accountingOpen ? 'rotate-180' : '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" data-icon="chevron">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9l6 6 6-6"/>
                        </svg>
                    </button>
                    <div id="accounting-menu" class="submenu space-y-1 pl-4 ml-4 border-l-2 border-gray-600 {{ $accountingOpen ? 'mt-2' : 'hidden' }}">
                        <a href="{{ route('admin.chart-of-accounts.index') }}" class="flex items-center px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 {{ request()->routeIs('admin.chart-of-accounts.*') ? 'bg-gray-700 text-white' : '' }}">
                            <span class="text-sm">Chart of Accounts</span>
                        </a>
                        <a href="{{ route('admin.journal-entries.index') }}" class="flex items-center px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 {{ request()->routeIs('admin.journal-entries.*') ? 'bg-gray-700 text-white' : '' }}">
                            <span class="text-sm">Journal Entries</span>
                        </a>
                        <a href="{{ route('admin.bank-accounts.index') }}" class="flex items-center px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 {{ request()->routeIs('admin.bank-accounts.*') ? 'bg-gray-700 text-white' : '' }}">
                            <span class="text-sm">Bank & Cash Accounts</span>
                        </a>
                        <a href="{{ route('admin.purchase-invoices.index') }}" class="flex items-center px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 {{ request()->routeIs('admin.purchase-invoices.*') ? 'bg-gray-700 text-white' : '' }}">
                            <span class="text-sm">Purchase Invoices</span>
                        </a>
                        <a href="{{ route('admin.sales-invoices.index') }}" class="flex items-center px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 {{ request()->routeIs('admin.sales-invoices.*') ? 'bg-gray-700 text-white' : '' }}">
                            <span class="text-sm">Sales Invoices</span>
                        </a>
                        <a href="{{ route('admin.customers.index') }}" class="flex items-center px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 {{ request()->routeIs('admin.customers.*') ? 'bg-gray-700 text-white' : '' }}">
                            <span class="text-sm">Customers</span>
                        </a>
                    </div> -->
                    <a href="{{ route('admin.vehicle-rents.index') }}" data-tooltip="Vehicle Rent" class="nav-item flex items-center px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 {{ request()->routeIs('admin.vehicle-rents.*') ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-lg' : '' }}">
                        <svg class="w-5 h-5 mr-3 sidebar-icon flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                        <span class="nav-item-text">Vehicle Rent</span>
                    </a>
                    <!-- <a href="{{ route('admin.material-calculator.index') }}" class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition duration-200 {{ request()->routeIs('admin.material-calculator.*') ? 'bg-gray-700 text-white' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6M9 16h3m4 5H8a2 2 0 01-2-2V5a2 2 0 012-2h6l4 4v12a2 2 0 01-2 2z"></path>
                        </svg>
                        Material Calculator
                    </a> -->
                   
                </nav>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col">
            <!-- Top Navigation -->
            <nav class="bg-white shadow-lg">
                <div class="px-2 sm:px-4 lg:px-8">
                    <div class="flex justify-between items-center h-16">
                        <div class="flex items-center space-x-2 sm:space-x-4 min-w-0 flex-1">
                            <button id="sidebarToggleMobile" class="lg:hidden p-2 rounded-lg hover:bg-gray-100 text-gray-600 transition-all duration-200 flex-shrink-0" aria-label="Toggle Sidebar">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                </svg>
                            </button>
                            <h2 class="text-base sm:text-lg font-semibold text-gray-800 truncate">@yield('title', 'Admin Panel')</h2>
                        </div>
                        <div class="flex items-center space-x-2 sm:space-x-4 flex-shrink-0">
                            @if(Auth::user()->role === 'super_admin' && Auth::user()->company_id == 1)
                                <form method="POST" action="{{ route('admin.companies.switch') }}" class="hidden sm:flex items-center space-x-2">
                                    @csrf
                                    <select name="company_id" onchange="this.form.submit()" class="border rounded px-2 py-1 text-sm">
                                        @php
                                            $activeCompanyId = session('active_company_id') ?: Auth::user()->company_id;
                                        @endphp
                                        @foreach(\App\Models\Company::orderBy('name')->get() as $company)
                                            <option value="{{ $company->id }}" {{ $activeCompanyId == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            @elseif(Auth::user()->role !== 'super_admin')
                                <span class="hidden sm:inline text-gray-700 text-sm">{{ optional(Auth::user()->company)->name }}</span>
                            @endif
                            
                            @php
                                use App\Support\ProjectContext;
                                
                                $activeCompanyId = \App\Support\CompanyContext::getActiveCompanyId();
                                $activeProjectId = ProjectContext::getActiveProjectId();
                                $headerProjects = collect([]);
                                
                                if (!empty($activeCompanyId)) {
                                    try {
                                        $headerProjects = \App\Models\Project::where('company_id', $activeCompanyId)
                                            ->where('status', '!=', 'cancelled')
                                            ->orderBy('name')
                                            ->get();
                                    } catch (\Exception $e) {
                                        $headerProjects = collect([]);
                                    }
                                }
                            @endphp
                            
                            @if(isset($headerProjects) && $headerProjects instanceof \Illuminate\Support\Collection && $headerProjects->count() > 0)
                                <form method="POST" action="{{ route('admin.projects.switch') }}" class="hidden sm:flex items-center space-x-2">
                                    @csrf
                                    <select name="project_id" onchange="this.form.submit()" class="border rounded px-2 py-1 text-sm">
                                        <option value="">All Projects</option>
                                        @foreach($headerProjects as $project)
                                            <option value="{{ $project->id }}" {{ $activeProjectId == $project->id ? 'selected' : '' }}>{{ $project->name }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            @endif
                            
                            <span class="hidden sm:inline text-gray-700 text-sm truncate max-w-[100px]">{{ Auth::user()->name }}</span>
                            <form method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 sm:px-4 py-2 rounded-lg transition duration-200 text-sm sm:text-base">
                                    <span class="hidden sm:inline">Logout</span>
                                    <svg class="sm:hidden w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="flex-1 py-6">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                @if(session('success'))
                    <div class="alert-message mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative pr-10" role="alert">
                        <button type="button" class="alert-close-btn" onclick="closeAlert(this)" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert-message mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative pr-10" role="alert">
                        <button type="button" class="alert-close-btn" onclick="closeAlert(this)" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert-message mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative pr-10" role="alert">
                        <button type="button" class="alert-close-btn" onclick="closeAlert(this)" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebarToggleMobile = document.getElementById('sidebarToggleMobile');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const toggleIcon = document.getElementById('sidebarToggleIcon');
            
            // Check if mobile
            function isMobile() {
                return window.innerWidth <= 768;
            }
            
            // Load sidebar state from localStorage (desktop only)
            if (!isMobile()) {
                const savedState = localStorage.getItem('sidebarCollapsed');
                if (savedState === 'true') {
                    sidebar.classList.remove('expanded');
                    sidebar.classList.add('collapsed');
                    if (toggleIcon) {
                        toggleIcon.style.transform = 'rotate(180deg)';
                    }
                }
            }
            
            // Toggle sidebar (desktop button)
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    toggleSidebar();
                });
            }
            
            // Toggle sidebar (mobile button)
            if (sidebarToggleMobile) {
                sidebarToggleMobile.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    toggleSidebarMobile();
                });
            }
            
            // Close sidebar when clicking overlay (mobile)
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', function () {
                    closeSidebarMobile();
                });
            }
            
            function toggleSidebar() {
                if (!sidebar) return;
                
                if (isMobile()) {
                    // On mobile, use mobile toggle instead
                    toggleSidebarMobile();
                    return;
                }
                
                // Desktop toggle
                const isCollapsed = sidebar.classList.contains('collapsed');
                
                if (isCollapsed) {
                    sidebar.classList.remove('collapsed');
                    sidebar.classList.add('expanded');
                    localStorage.setItem('sidebarCollapsed', 'false');
                    if (toggleIcon) {
                        toggleIcon.style.transform = 'rotate(0deg)';
                    }
                } else {
                    sidebar.classList.remove('expanded');
                    sidebar.classList.add('collapsed');
                    localStorage.setItem('sidebarCollapsed', 'true');
                    if (toggleIcon) {
                        toggleIcon.style.transform = 'rotate(180deg)';
                    }
                }
            }
            
            function toggleSidebarMobile() {
                if (!sidebar) return;
                
                const isOpen = sidebar.classList.contains('mobile-open');
                
                if (isOpen) {
                    closeSidebarMobile();
                } else {
                    openSidebarMobile();
                }
            }
            
            function openSidebarMobile() {
                if (!sidebar) return;
                
                sidebar.classList.add('mobile-open');
                if (sidebarOverlay) {
                    sidebarOverlay.classList.add('active');
                }
                document.body.classList.add('sidebar-open');
            }
            
            function closeSidebarMobile() {
                if (!sidebar) return;
                
                sidebar.classList.remove('mobile-open');
                if (sidebarOverlay) {
                    sidebarOverlay.classList.remove('active');
                }
                document.body.classList.remove('sidebar-open');
            }
            
            // Handle submenu toggles (works on both desktop and mobile)
            document.querySelectorAll('#sidebar-nav .group-toggle').forEach(function (button) {
                button.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const targetId = button.getAttribute('data-target');
                    const panel = document.getElementById(targetId);
                    const expanded = button.getAttribute('aria-expanded') === 'true';

                    if (panel) {
                        // Toggle hidden class
                        panel.classList.toggle('hidden');
                        
                        // Update aria-expanded
                        const newExpanded = !expanded;
                        button.setAttribute('aria-expanded', newExpanded ? 'true' : 'false');
                        
                        // Toggle chevron icon rotation
                        const icon = button.querySelector('[data-icon="chevron"]');
                        if (icon) {
                            if (newExpanded) {
                                icon.classList.add('rotate-180');
                            } else {
                                icon.classList.remove('rotate-180');
                            }
                        }
                    }
                });
            });
            
            // Close mobile sidebar when clicking outside or on overlay
            document.addEventListener('click', function(event) {
                if (isMobile() && sidebar && sidebar.classList.contains('mobile-open')) {
                    // Don't close if clicking inside sidebar
                    if (sidebar.contains(event.target)) {
                        return;
                    }
                    // Don't close if clicking the toggle button
                    if (sidebarToggleMobile && sidebarToggleMobile.contains(event.target)) {
                        return;
                    }
                    // Close if clicking overlay or outside
                    closeSidebarMobile();
                }
            });
            
            // Handle window resize
            let resizeTimer;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    if (sidebar) {
                        if (!isMobile() && sidebar.classList.contains('mobile-open')) {
                            closeSidebarMobile();
                        }
                    }
                }, 250);
            });
            
            // Debug: Log if elements are not found (remove in production)
            if (!sidebar) {
                console.error('Sidebar element not found');
            }
            if (!sidebarToggle && !isMobile()) {
                console.warn('Desktop sidebar toggle button not found');
            }
            if (!sidebarToggleMobile && isMobile()) {
                console.warn('Mobile sidebar toggle button not found');
            }
            
            // Handle window resize
            let resizeTimer;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    if (!isMobile()) {
                        // On desktop, close mobile sidebar if open
                        closeSidebarMobile();
                    } else {
                        // On mobile, ensure sidebar is closed by default
                        if (!sidebar.classList.contains('mobile-open')) {
                            closeSidebarMobile();
                        }
                    }
                }, 250);
            });
            
            // Close sidebar when navigating on mobile
            document.querySelectorAll('#sidebar-nav a').forEach(function(link) {
                link.addEventListener('click', function() {
                    if (isMobile()) {
                        closeSidebarMobile();
                    }
                });
            });
        });
        
        // Alert Messages Auto-dismiss and Close functionality
        function closeAlert(button) {
            const alert = button.closest('.alert-message, .alert');
            if (alert) {
                alert.classList.add('hiding');
                setTimeout(function() {
                    alert.remove();
                }, 300);
            }
        }
        
        // Auto-dismiss alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            // Handle both .alert-message and Bootstrap .alert classes
            const alerts = document.querySelectorAll('.alert-message, .alert.alert-success, .alert.alert-danger, .alert.alert-info, .alert.alert-warning');
            alerts.forEach(function(alert) {
                // Skip if already has close button
                if (alert.querySelector('.alert-close-btn')) {
                    return;
                }
                
                // Add alert-message class for styling if it's a Bootstrap alert
                if (alert.classList.contains('alert') && !alert.classList.contains('alert-message')) {
                    alert.classList.add('alert-message');
                }
                
                // Add close button if it doesn't have one
                if (!alert.querySelector('.alert-close-btn')) {
                    alert.style.position = 'relative';
                    alert.style.paddingRight = '2.5rem';
                    const closeBtn = document.createElement('button');
                    closeBtn.type = 'button';
                    closeBtn.className = 'alert-close-btn';
                    closeBtn.setAttribute('onclick', 'closeAlert(this)');
                    closeBtn.setAttribute('aria-label', 'Close');
                    closeBtn.innerHTML = '<span aria-hidden="true">&times;</span>';
                    alert.appendChild(closeBtn);
                }
                
                // Set timeout to auto-dismiss after 5 seconds
                const timeout = setTimeout(function() {
                    alert.classList.add('hiding');
                    setTimeout(function() {
                        alert.remove();
                    }, 300);
                }, 5000);
                
                // Clear timeout if user manually closes the alert
                const closeBtn = alert.querySelector('.alert-close-btn');
                if (closeBtn) {
                    closeBtn.addEventListener('click', function() {
                        clearTimeout(timeout);
                    });
                }
            });
        });
    </script>
    @stack('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

