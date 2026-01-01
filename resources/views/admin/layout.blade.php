<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Panel') - {{ config('app.name', 'Laravel') }}</title>

    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='32' height='32' viewBox='0 0 32 32'><circle cx='16' cy='16' r='16' fill='%2329a7f6'/><text x='16' y='22' font-family='Arial Rounded MT Bold, Arial, sans-serif' font-weight='bold' font-size='16' text-anchor='middle' fill='%23fff'>cms</text></svg>">

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
        
        /* =========================
           GLOBAL FIXES - REMOVE ALL TOP SPACING
        ========================= */
        * {
            margin-top: 0;
        }
        
        html {
            margin: 0 !important;
            padding: 0 !important;
            height: 100%;
        }
        
        body {
            width: 100%;
            overflow-x: hidden;
            margin: 0 !important;
            padding: 0 !important;
            position: relative;
            height: 100%;
        }
        
        @media (max-width: 768px) {
            html, body {
                margin: 0 !important;
                padding: 0 !important;
                height: auto !important;
                min-height: 100vh;
            }
        }
        
        /* =========================
           SIDEBAR OVERLAY
        ========================= */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 40;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
            backdrop-filter: blur(2px);
            display: none;
        }

        .sidebar-overlay.active {
            opacity: 1;
            pointer-events: auto;
            display: block;
        }
        
        /* =========================
           SIDEBAR BASE
        ========================= */
        .sidebar {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), width 0.3s ease;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            z-index: 50;
            height: 100vh;
            min-height: 100vh;
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

        /* =========================
           MOBILE FIX (IMPORTANT)
        ========================= */
        @media (max-width: 768px) {
            /* CRITICAL: Layout wrapper should NOT take full height on mobile */
            .layout-wrapper {
                flex-direction: row !important; /* Keep row to prevent sidebar stacking */
                min-height: auto !important;
                height: auto !important;
                position: relative;
                top: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            
            /* Sidebar should NOT take any space in flex layout on mobile */
            .layout-wrapper > .sidebar {
                width: 0 !important;
                min-width: 0 !important;
                flex: 0 0 0 !important;
                overflow: hidden;
            }

            /* Sidebar is fixed and hidden - takes NO space in layout flow */
            .sidebar {
                position: fixed !important;
                top: 0;
                left: -100% !important;
                height: 100vh;
                z-index: 50;
                width: 260px !important;
                max-width: 85vw;
                transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                box-shadow: 2px 0 20px rgba(0, 0, 0, 0.3);
                /* Sidebar doesn't take space when hidden - removed from flow */
                margin: 0 !important;
                padding: 0 !important;
                /* CRITICAL: Remove from document flow so it doesn't push content */
                visibility: hidden;
                /* Remove from flex layout - takes zero width in flex */
                flex: 0 0 0 !important;
                min-width: 0 !important;
            }
            
            /* When mobile-open, sidebar should be visible and positioned */
            .sidebar.mobile-open {
                left: 0 !important;
                visibility: visible !important;
                width: 260px !important;
                max-width: 85vw !important;
                /* Override flex width when open */
                flex: 0 0 260px !important;
                min-width: 260px !important;
            }
            
            .sidebar.collapsed,
            .sidebar.expanded {
                width: 0 !important; /* Hidden on mobile - takes no space */
                max-width: 0 !important;
            }

            /* Main content starts at TOP - no space above, takes full width */
            .main-content {
                width: 100% !important;
                max-width: 100vw !important;
                margin: 0 !important;
                padding: 0 !important;
                position: relative;
                top: 0 !important;
                min-height: auto !important;
                flex: 1 1 100% !important;
                /* Ensure it's not pushed by sidebar */
                margin-left: 0 !important;
            }
            
            .sidebar-toggle-btn {
                display: none !important;
            }
            
            /* Ensure sidebar content is always visible on mobile */
            .sidebar .sidebar-content {
                opacity: 1 !important;
                pointer-events: auto !important;
            }
            
            .sidebar .nav-item-text {
                display: block !important;
            }
            
            /* Allow submenus to be toggled on mobile - respect hidden class */
            .sidebar .submenu {
                /* Don't force display - allow toggle functionality */
            }
            
            /* Ensure submenus respect hidden class on mobile */
            .sidebar .submenu.hidden {
                display: none !important;
            }
            
            /* Show submenus that are not hidden */
            .sidebar .submenu:not(.hidden) {
                display: block;
            }
            
            /* Prevent body scroll when sidebar is open */
            body.sidebar-open {
                overflow: hidden;
                position: fixed;
                width: 100%;
            }

            /* Make main content full width on mobile */
            .max-w-7xl {
                max-width: 100% !important;
                padding-left: 1rem;
                padding-right: 1rem;
            }
            
            /* Ensure main content doesn't shift on mobile */
            .flex-1.flex.flex-col {
                width: 100%;
                max-width: 100vw;
            }
            
            /* Prevent horizontal overflow */
            .min-h-screen {
                overflow-x: hidden;
            }
            
            /* CRITICAL FIX: Remove full height on mobile to prevent blank space */
            .layout-wrapper {
                min-height: auto !important;
                height: auto !important;
                margin: 0 !important;
                padding: 0 !important;
                position: relative !important;
                top: 0 !important;
            }
            
            body {
                min-height: auto !important;
                height: auto !important;
                padding: 0 !important;
                margin: 0 !important;
                overflow-x: hidden;
                position: relative !important;
            }
            
            html {
                padding: 0 !important;
                margin: 0 !important;
                overflow-x: hidden;
                height: auto !important;
            }
            
            /* Ensure sidebar overlay doesn't create space */
            .sidebar-overlay {
                top: 0 !important;
                left: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                display: none !important;
            }
            
            .sidebar-overlay.active {
                display: block !important;
            }
            
            /* CRITICAL: Main content must start at absolute top on mobile */
            .main-content {
                position: relative !important;
                top: 0 !important;
                margin-top: 0 !important;
                padding-top: 0 !important;
                min-height: auto !important;
            }
            
            /* Navigation bar at top */
            .main-content > nav {
                position: relative !important;
                top: 0 !important;
                margin-top: 0 !important;
                padding-top: 0 !important;
            }
            
            /* Main content area */
            .main-content > main {
                position: relative !important;
                top: 0 !important;
                margin-top: 0 !important;
                padding-top: 0.5rem !important;
            }
            
            /* Reduce padding on mobile for main content */
            main.flex-1 {
                padding-top: 0.5rem !important;
                padding-bottom: 1rem !important;
                margin-top: 0 !important;
            }
            
            /* Remove top padding from main content wrapper */
            main.flex-1 > div {
                padding-top: 0 !important;
            }
            
            /* Remove extra margin from content on mobile */
            .mb-12 {
                margin-bottom: 1.5rem !important;
            }
            
            /* Ensure main content starts at top on mobile */
            .main-content {
                min-height: auto !important;
            }
            
            /* Fix top navigation spacing on mobile */
            nav.bg-white {
                position: relative;
                z-index: 10;
                margin-top: 0 !important;
                padding-top: 0 !important;
            }
            
            /* Remove any top margin/padding that might push content down */
            .main-content {
                margin-top: 0 !important;
                padding-top: 0 !important;
            }
            
            .main-content > main {
                margin-top: 0 !important;
                padding-top: 0.5rem !important;
            }
            
            /* Ensure no extra space at top */
            .layout-wrapper {
                margin-top: 0 !important;
                padding-top: 0 !important;
            }
            
            /* Ensure main content starts immediately after nav */
            .main-content {
                margin-top: 0 !important;
                padding-top: 0 !important;
            }
            
            /* Fix navigation bar positioning */
            nav.bg-white.shadow-lg {
                margin-top: 0 !important;
                padding-top: 0 !important;
                position: relative;
                top: 0 !important;
            }
            
            /* Ensure nav bar doesn't create space */
            nav.bg-white.shadow-lg > div {
                margin-top: 0 !important;
                padding-top: 0 !important;
            }
            
            /* Remove any default browser spacing */
            * {
                box-sizing: border-box;
            }
            
            /* Table scrolling on mobile */
            table {
                width: 100%;
                display: table;
            }
            
            /* Ensure tables can scroll horizontally on mobile */
            .overflow-x-auto {
                -webkit-overflow-scrolling: touch;
                scrollbar-width: thin;
            }
            
            /* Table container for mobile scrolling */
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                width: 100%;
            }
            
            /* Mobile table styling - ensure all tables are scrollable */
            @media (max-width: 768px) {
                /* Wrap all tables in scrollable containers */
                table {
                    min-width: 600px; /* Minimum width to enable scrolling */
                    display: table;
                }
                
                /* Ensure table cells don't break */
                table td,
                table th {
                    white-space: nowrap;
                }
                
                /* Allow some cells to wrap if needed */
                table td.text-wrap,
                table th.text-wrap {
                    white-space: normal;
                }
                
                /* Force horizontal scroll for common table containers on mobile */
                .card-body,
                .card,
                .bg-white.rounded,
                .bg-white.shadow-lg,
                .bg-white.rounded.shadow {
                    overflow-x: auto !important;
                    -webkit-overflow-scrolling: touch;
                    scrollbar-width: thin;
                }
                
                /* Ensure table containers show scrollbar */
                .card-body::-webkit-scrollbar,
                .card::-webkit-scrollbar,
                .bg-white.rounded::-webkit-scrollbar,
                .bg-white.shadow-lg::-webkit-scrollbar,
                .bg-white.rounded.shadow::-webkit-scrollbar {
                    height: 8px;
                }
                
                .card-body::-webkit-scrollbar-track,
                .card::-webkit-scrollbar-track,
                .bg-white.rounded::-webkit-scrollbar-track,
                .bg-white.shadow-lg::-webkit-scrollbar-track,
                .bg-white.rounded.shadow::-webkit-scrollbar-track {
                    background: #f1f1f1;
                    border-radius: 4px;
                }
                
                .card-body::-webkit-scrollbar-thumb,
                .card::-webkit-scrollbar-thumb,
                .bg-white.rounded::-webkit-scrollbar-thumb,
                .bg-white.shadow-lg::-webkit-scrollbar-thumb,
                .bg-white.rounded.shadow::-webkit-scrollbar-thumb {
                    background: #888;
                    border-radius: 4px;
                }
                
                .card-body::-webkit-scrollbar-thumb:hover,
                .card::-webkit-scrollbar-thumb:hover,
                .bg-white.rounded::-webkit-scrollbar-thumb:hover,
                .bg-white.shadow-lg::-webkit-scrollbar-thumb:hover,
                .bg-white.rounded.shadow::-webkit-scrollbar-thumb:hover {
                    background: #555;
                }
            }
            
            /* Button sizing fix for all views - icon only */
            .btn-sm {
                font-size: 0.75rem !important;
                padding: 0.5rem !important;
                line-height: 1 !important;
                min-width: 38px !important;
                width: 38px !important;
                height: 38px !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
            }
            
            /* Hide text in all btn-sm buttons, show only icons */
            .btn-sm {
                font-size: 0 !important; /* Hide all text nodes */
                line-height: 0 !important;
            }
            
            .btn-sm .bi,
            .btn-sm i {
                font-size: 1rem !important;
                margin: 0 !important;
                padding: 0 !important;
                display: inline-block !important;
                line-height: 1 !important;
                vertical-align: middle;
            }
            
            /* Restore color for icons */
            .btn-sm .bi,
            .btn-sm i {
                color: inherit;
            }
            
            /* Hide any text or spans after icons */
            .btn-sm .bi + *,
            .btn-sm i + *,
            .btn-sm span:not(.bi):not(i),
            .btn-sm .bi ~ span,
            .btn-sm i ~ span,
            .btn-sm .bi ~ *:not(.bi):not(i) {
                display: none !important;
                font-size: 0 !important;
            }
            
            /* Ensure buttons in tables are icon-only */
            table .btn-sm {
                padding: 0.5rem !important;
                min-width: 38px !important;
                width: 38px !important;
                height: 38px !important;
            }
            
            table .btn-sm .bi,
            table .btn-sm i {
                font-size: 1rem !important;
                margin: 0 !important;
            }
            
            /* Mobile button optimization - show icons only or smaller text */
            @media (max-width: 768px) {
                /* Hide text in small buttons, show only icons */
                .btn-sm .bi + *,
                .btn-sm span:not(.bi),
                .btn-sm:not(:has(.bi)) {
                    display: none !important;
                }
                
                /* Show icons in buttons */
                .btn-sm .bi {
                    margin: 0 !important;
                    font-size: 1rem;
                }
                
                /* Make buttons more compact - icon only */
                .btn-sm {
                    padding: 0.375rem !important;
                    min-width: 36px;
                    width: 36px;
                    height: 36px;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                }
                
                /* Hide text after icons in small buttons */
                .btn-sm i + span,
                .btn-sm .bi ~ span {
                    display: none !important;
                }
                
                /* Primary action buttons - show icon + short text or icon only */
                a[href*="create"] span.mobile\:hidden,
                a[href*="add"] span.mobile\:hidden {
                    display: none !important;
                }
                
                a[href*="create"] span.mobile\:inline,
                a[href*="add"] span.mobile\:inline {
                    display: inline !important;
                }
                
                /* Reduce font size for primary buttons */
                a[href*="create"],
                a[href*="add"] {
                    font-size: 0.75rem !important;
                    padding: 0.5rem 0.75rem !important;
                }
                
                /* Table action buttons - icon only */
                table .btn-sm {
                    padding: 0.25rem 0.375rem !important;
                    min-width: 32px;
                    width: 32px;
                    height: 32px;
                }
                
                table .btn-sm .bi {
                    font-size: 0.875rem;
                }
            }
            
            /* Force content to start at very top */
            .main-content {
                position: relative;
                top: 0 !important;
            }
            
            .main-content > nav {
                margin: 0 !important;
                padding: 0 !important;
                position: relative;
                top: 0 !important;
            }
            
            .main-content > nav > div {
                margin: 0 !important;
                padding-top: 0 !important;
                padding-bottom: 0 !important;
            }
            
            .main-content > nav > div > div {
                margin: 0 !important;
                padding-top: 0 !important;
                height: 64px !important;
            }
            
            /* Remove any space before main content */
            .main-content::before {
                display: none !important;
                content: none !important;
            }
            
            /* Ensure layout wrapper starts at top */
            .layout-wrapper::before {
                display: none !important;
                content: none !important;
            }
        }
        
        @media (min-width: 769px) {
            .sidebar-overlay {
                display: none !important;
            }
            
            #sidebarToggleMobile {
                display: none !important;
            }
            
            .layout-wrapper {
                flex-direction: row;
                height: 100vh;
                min-height: 100vh;
            }
            
            .sidebar {
                height: 100vh;
                min-height: 100vh;
            }
        }
    </style>
</head>
    <body class="antialiased bg-gray-100" style="margin: 0 !important; padding: 0 !important;">
    <div class="flex layout-wrapper" style="margin: 0 !important; padding: 0 !important; position: relative; top: 0 !important;">
        <!-- Sidebar Overlay (Mobile) -->
        <div class="sidebar-overlay" id="sidebarOverlay" style="display: none !important;"></div>
        
        <!-- Sidebar -->
        <aside class="sidebar expanded bg-gradient-to-b from-gray-800 via-gray-800 to-gray-900 relative" id="sidebar">
            <div class="p-4 h-full flex flex-col">
                @php
                    $activeCompanyId = session('active_company_id') ?: Auth::user()->company_id;
                    $activeCompany = \App\Models\Company::find($activeCompanyId);
                @endphp

                <!-- Sidebar Header with Toggle -->
<div class="flex flex-col items-center justify-center mb-6 gap-1">
  <!-- Top row: logo and toggle button -->
  <div class="flex flex-row items-center justify-center gap-2 w-full">
    <!-- <a href="{{ route('admin.dashboard') }}" class="block">
      <img src="{{ asset('logo.svg') }}" alt="Company Logo" class="w-full max-w-[120px] h-auto rounded-lg shadow-lg bg-white p-1 object-contain" style="object-fit:contain;">
                    </a> -->
                    <svg width="150" height="50" viewBox="0 0 340 100" fill="none" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="cmsBlue" x1="0" y1="0" x2="340" y2="0" gradientUnits="userSpaceOnUse">
      <stop stop-color="#1176d7"/>
      <stop offset="1" stop-color="#43bafc"/>
    </linearGradient>
    <linearGradient id="cmsLight" x1="25" y1="75" x2="330" y2="100" gradientUnits="userSpaceOnUse">
      <stop stop-color="#63d4ff"/>
      <stop offset="1" stop-color="#3aafea"/>
    </linearGradient>
    <filter id="shadow" x="-10%" y="-10%" width="120%" height="120%">
      <feDropShadow dx="0" dy="2" stdDeviation="2" flood-color="#1c3045" flood-opacity=".2"/>
    </filter>
  </defs>
  <!-- Main CMS letters with slant and gradient -->
  <text x="0" y="73"
        font-family="'Arial Black', Impact, Arial, sans-serif"
        font-size="85" font-weight="bold"
        font-style="italic"
        fill="url(#cmsBlue)"
        letter-spacing="2"
        filter="url(#shadow)">CMS</text>
  <!-- The wave underline with gradient and highlight -->
  <path d="M22 81
           Q90 95 170 81
           Q255 65 320 81
           Q300 98 170 97
           Q55 94 22 81
           Z"
        fill="url(#cmsLight)"
        stroke="#49bdff" stroke-width="1"/>
  <!-- Optional: subtle highlight outline for the top edge of the wave -->
  <path d="M24 83
           Q90 95 170 80
           Q255 65 318 83"
        fill="none"
        stroke="#ffffff"
        stroke-width="2"
        opacity="0.2"/>
  <!-- Vertical subtitle -->
  <text x="230" y="25" font-size="17" font-family="Arial, sans-serif" fill="#ffffff">construction</text>
  <text x="230" y="48" font-size="17" font-family="Arial, sans-serif" fill="#ffffff">management</text>
  <text x="230" y="71" font-size="17" font-family="Arial, sans-serif" fill="#ffffff">system</text>
</svg>

    <button id="sidebarToggle" class="sidebar-toggle-btn h-[44px] px-2 rounded-lg bg-gray-700 hover:bg-gray-600 text-white transition-all duration-200 flex items-center justify-center flex-shrink-0" aria-label="Toggle Sidebar">
      <svg id="sidebarToggleIcon" class="w-6 h-6 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
        <rect y="4" width="24" height="2" rx="1" fill="currentColor"/>
        <rect y="10" width="24" height="2" rx="1" fill="currentColor"/>
        <rect y="16" width="24" height="2" rx="1" fill="currentColor"/>
                        </svg>
                    </button>
  </div>
  <!-- Second row: company name -->
  <span class="block text-base sm:text-xl font-bold text-white text-center truncate w-full mt-1">{{ $activeCompany?->name ?? 'Admin Panel' }}</span>
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
                    @if(Auth::user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" data-tooltip="Dashboard" class="nav-item flex items-center px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-lg' : '' }}">
                        <svg class="w-5 h-5 mr-3 sidebar-icon flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        <span class="nav-item-text">Admin Dashboard</span>
                    </a>
                    @endif
                    @if(Auth::user()->isAdmin())
                    <a href="{{ route('admin.companies.profile') }}" data-tooltip="Company Profile" class="nav-item flex items-center px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 {{ request()->routeIs('admin.companies.profile*') ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-lg' : '' }}">
                        <svg class="w-5 h-5 mr-3 sidebar-icon flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <span class="nav-item-text">Company Profile</span>
                    </a>
                    @endif
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
                    <div id="projects-menu" class="submenu space-y-1 pl-4 ml-4 border-l-2 border-gray-600 {{ $projectsOpen ? 'mt-2' : 'mt-2 hidden' }}">
                        <a href="{{ route('admin.projects.index') }}" class="flex items-center px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 {{ request()->routeIs('admin.projects.*') ? 'bg-gray-700 text-white' : '' }}">
                            <span class="text-sm">All Projects</span>
                        </a>
                    </div>
                    @if(Auth::user()->role !== 'site_engineer')
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
                    <div id="materials-menu" class="submenu space-y-1 pl-4 ml-4 border-l-2 border-gray-600 {{ $materialsOpen ? 'mt-2' : 'mt-2 hidden' }}">
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
                    @endif
                    @if(Auth::user()->isAdmin())
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
                    <div id="billing-menu" class="submenu space-y-1 pl-4 ml-4 border-l-2 border-gray-600 {{ $billingOpen ? 'mt-2' : 'mt-2 hidden' }}">
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
                    @endif
                    @if(Auth::user()->isAdmin())
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
                    <div id="staff-menu" class="submenu space-y-1 pl-4 ml-4 border-l-2 border-gray-600 {{ $staffOpen ? 'mt-2' : 'mt-2 hidden' }}">
                        <a href="{{ route('admin.staff.index') }}" class="flex items-center px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 {{ request()->routeIs('admin.staff.*') ? 'bg-gray-700 text-white' : '' }}">
                            <span class="text-sm">Staff</span>
                        </a>
                        <a href="{{ route('admin.positions.index') }}" class="flex items-center px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 {{ request()->routeIs('admin.positions.*') ? 'bg-gray-700 text-white' : '' }}">
                            <span class="text-sm">Positions</span>
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="flex items-center px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 {{ request()->routeIs('admin.users.*') ? 'bg-gray-700 text-white' : '' }}">
                            <span class="text-sm">Users</span>
                        </a>
                    </div>
                    @endif
                    @if(Auth::user()->role !== 'site_engineer')
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
                    <div id="finance-menu" class="submenu space-y-1 pl-4 ml-4 border-l-2 border-gray-600 {{ $financeOpen ? 'mt-2' : 'mt-2 hidden' }}">
                        @if(Auth::user()->isAdmin())
                        <a href="{{ route('admin.incomes.index') }}" class="flex items-center px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 {{ request()->routeIs('admin.incomes.*') ? 'bg-gray-700 text-white' : '' }}">
                            <span class="text-sm">Income</span>
                        </a>
                        @endif
                        <a href="{{ route('admin.expenses.index') }}" class="flex items-center px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 {{ request()->routeIs('admin.expenses.*') ? 'bg-gray-700 text-white' : '' }}">
                            <span class="text-sm">Expenses</span>
                        </a>
                        @if(Auth::user()->isAdmin())
                        <a href="{{ route('admin.reports.index') }}" class="flex items-center px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 {{ request()->routeIs('admin.reports.*') ? 'bg-gray-700 text-white' : '' }}">
                            <span class="text-sm">Reports</span>
                        </a>
                        @endif
                        <a href="{{ route('admin.categories.index') }}" class="flex items-center px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 {{ request()->routeIs('admin.categories.*') ? 'bg-gray-700 text-white' : '' }}">
                            <span class="text-sm">Categories</span>
                        </a>
                        <a href="{{ route('admin.subcategories.index') }}" class="flex items-center px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 {{ request()->routeIs('admin.subcategories.*') ? 'bg-gray-700 text-white' : '' }}">
    <span class="text-sm">Subcategories</span>
</a>
<a href="{{ route('admin.expense-types.index') }}" class="flex items-center px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 {{ request()->routeIs('admin.expense-types.*') ? 'bg-gray-700 text-white' : '' }}">
    <span class="text-sm">Expense Types</span>
</a>
<a href="{{ route('admin.payment-types.index') }}" class="flex items-center px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 {{ request()->routeIs('admin.payment-types.*') ? 'bg-gray-700 text-white' : '' }}">
    <span class="text-sm">Payment Types</span>
</a>
                        @if(Auth::user()->isAdmin())
<a href="{{ route('admin.salary-payments.index') }}" class="flex items-center px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 {{ request()->routeIs('admin.salary-payments.*') ? 'bg-gray-700 text-white' : '' }}">
    <span class="text-sm">Salary Payments</span>
</a>
                        @endif
                    </div>
                    @endif
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
                    <div id="accounting-menu" class="submenu space-y-1 pl-4 ml-4 border-l-2 border-gray-600 {{ $accountingOpen ? 'mt-2' : 'mt-2 hidden' }}">
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
                    @if(Auth::user()->role !== 'site_engineer')
                    <a href="{{ route('admin.vehicle-rents.index') }}" data-tooltip="Vehicle Rent" class="nav-item flex items-center px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 {{ request()->routeIs('admin.vehicle-rents.*') ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-lg' : '' }}">
                        <svg class="w-5 h-5 mr-3 sidebar-icon flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                        <span class="nav-item-text">Vehicle Rent</span>
                    </a>
                    @endif
                    @if(Auth::user()->isAdmin())
                    <a href="{{ route('admin.advance-payments.index') }}" data-tooltip="Advance Payments" class="nav-item flex items-center px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-all duration-200 {{ request()->routeIs('admin.advance-payments.*') ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-lg' : '' }}">
                        <svg class="w-5 h-5 mr-3 sidebar-icon flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="nav-item-text">Advance Payments</span>
                    </a>
                    @endif
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
        <div class="flex-1 flex flex-col main-content">
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
                        <div class="flex items-center space-x-1 sm:space-x-4 flex-shrink-0">
                            @if(Auth::user()->role === 'super_admin' && Auth::user()->company_id == 1)
                                <form method="POST" action="{{ route('admin.companies.switch') }}" class="flex items-center space-x-1 sm:space-x-2">
                                    @csrf
                                    <select name="company_id" onchange="this.form.submit()" class="border rounded px-1 sm:px-2 py-1 text-xs sm:text-sm max-w-[80px] sm:max-w-none">
                                        @php
                                            $activeCompanyId = session('active_company_id') ?: Auth::user()->company_id;
                                        @endphp
                                        @foreach(\App\Models\Company::orderBy('name')->get() as $company)
                                            <option value="{{ $company->id }}" {{ $activeCompanyId == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            @elseif(Auth::user()->role !== 'super_admin')
                                <span class="inline text-gray-700 text-xs sm:text-sm truncate max-w-[60px] sm:max-w-none">{{ optional(Auth::user()->company)->name }}</span>
                            @endif
                            
                            @php
                                use App\Support\ProjectContext;
                                
                                $activeCompanyId = \App\Support\CompanyContext::getActiveCompanyId();
                                $activeProjectId = ProjectContext::getActiveProjectId();
                                $headerProjects = collect([]);
                                
                                if (!empty($activeCompanyId)) {
                                    try {
                                        $user = Auth::user();
                                        $query = \App\Models\Project::where('company_id', $activeCompanyId)
                                            ->where('status', '!=', 'cancelled')
                                            ->orderBy('name');
                                        
                                        // Apply project access restrictions if user has specific project assignments
                                        $accessibleProjectIds = $user->getAccessibleProjectIds();
                                        if ($accessibleProjectIds !== null) {
                                            $query->whereIn('id', $accessibleProjectIds);
                                        }
                                        
                                        $headerProjects = $query->get();
                                    } catch (\Exception $e) {
                                        $headerProjects = collect([]);
                                    }
                                }
                            @endphp
                            
                            @if(isset($headerProjects) && $headerProjects instanceof \Illuminate\Support\Collection && $headerProjects->count() > 0)
                                <form method="POST" action="{{ route('admin.projects.switch') }}" class="flex items-center space-x-1 sm:space-x-2">
                                    @csrf
                                    <select name="project_id" onchange="this.form.submit()" class="border rounded px-1 sm:px-2 py-1 text-xs sm:text-sm max-w-[80px] sm:max-w-none">
                                        <option value="">All Projects</option>
                                        @foreach($headerProjects as $project)
                                            <option value="{{ $project->id }}" {{ $activeProjectId == $project->id ? 'selected' : '' }}>{{ $project->name }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            @endif
                            
                            <span class="inline text-gray-700 text-xs sm:text-sm truncate max-w-[60px] sm:max-w-[100px]">{{ Auth::user()->name }}</span>
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
            <main class="flex-1 py-6 mobile:py-2">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" id="main-content-container">
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

                    <div id="page-content">
                        @yield('content')
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script>
        // Global CSRF token - initialize once to avoid redeclaration errors
        if (typeof window.csrfToken === 'undefined') {
            window.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        }
        
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
            
            // Define functions first before using them
            function closeSidebarMobile() {
                if (!sidebar) return;
                sidebar.classList.remove('mobile-open');
                if (sidebarOverlay) {
                    sidebarOverlay.classList.remove('active');
                }
                document.body.classList.remove('sidebar-open');
            }
            
            function toggleSidebar() {
                if (!sidebar) return;
                if (isMobile()) return; // Don't allow desktop collapse on mobile
                
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
                    sidebar.classList.add('mobile-open');
                    if (sidebarOverlay) {
                        sidebarOverlay.classList.add('active');
                    }
                    document.body.classList.add('sidebar-open');
                }
            }
            
            // Load sidebar state from localStorage (desktop only)
            if (sidebar) {
                if (!isMobile()) {
                    const savedState = localStorage.getItem('sidebarCollapsed');
                    if (savedState === 'true') {
                        sidebar.classList.remove('expanded');
                        sidebar.classList.add('collapsed');
                        if (toggleIcon) {
                            toggleIcon.style.transform = 'rotate(180deg)';
                        }
                    }
                } else {
                    // Hide sidebar on initial load for mobile
                    closeSidebarMobile();
                }
            }
            
            // Toggle sidebar (desktop only)
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (isMobile()) {
                        return;
                    }
                    toggleSidebar();
                });
            }
            
            // Toggle sidebar (mobile)
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
            
            // Close sidebar on initial load for mobile
            if (isMobile() && sidebar) {
                closeSidebarMobile();
            }
            
            // Function to handle submenu toggle (using event delegation for better performance)
            function handleSubmenuToggle(e) {
                const button = e.target.closest('.group-toggle');
                if (!button) return;
                
                e.preventDefault();
                e.stopPropagation();
                
                const targetId = button.getAttribute('data-target');
                const panel = document.getElementById(targetId);
                const expanded = button.getAttribute('aria-expanded') === 'true';

                if (panel) {
                    // Toggle hidden class
                    if (panel.classList.contains('hidden')) {
                        panel.classList.remove('hidden');
                        button.setAttribute('aria-expanded', 'true');
                    } else {
                        panel.classList.add('hidden');
                        button.setAttribute('aria-expanded', 'false');
                    }
                    
                    // Toggle chevron icon rotation
                    const icon = button.querySelector('[data-icon="chevron"]');
                    if (icon) {
                        if (panel.classList.contains('hidden')) {
                            icon.classList.remove('rotate-180');
                        } else {
                            icon.classList.add('rotate-180');
                        }
                    }
                }
            }
            
            // Use event delegation on sidebar nav for submenu toggles
            const sidebarNav = document.getElementById('sidebar-nav');
            if (sidebarNav) {
                sidebarNav.addEventListener('click', handleSubmenuToggle);
            }
            
            // Function to attach submenu toggle listeners (for compatibility)
            // Make it globally accessible for AJAX-loaded content
            window.attachSubmenuToggleListeners = function() {
                // Event delegation is already set up, so this is just for compatibility
                // No need to do anything as event delegation handles it
            };
            
            // Also create a local reference for backward compatibility
            function attachSubmenuToggleListeners() {
                window.attachSubmenuToggleListeners();
            }
            
            // Initial call (for compatibility)
            attachSubmenuToggleListeners();
            
            // Close mobile sidebar when clicking outside
            document.addEventListener('click', function(event) {
                if (isMobile() && 
                    sidebar.classList.contains('mobile-open') &&
                    !sidebar.contains(event.target) && 
                    sidebarToggleMobile && 
                    !sidebarToggleMobile.contains(event.target)) {
                    closeSidebarMobile();
                }
            });
            
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
            
            // AJAX Navigation - Intercept sidebar link clicks
            document.querySelectorAll('#sidebar-nav a[href]').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    
                    // Skip if it's an external link, mailto, tel, or hash link
                    if (!href || href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:') || href.startsWith('http://') || href.startsWith('https://')) {
                        return;
                    }
                    
                    // Skip if it's a logout link or form submission
                    if (href.includes('logout') || this.closest('form')) {
                        return;
                    }
                    
                    e.preventDefault();
                    loadPageViaAjax(href);
                });
            });
            
        });
        
        
        // Global debounce utility for performance optimization
        window.debounce = function(func, wait, immediate) {
            let timeout;
            return function executedFunction() {
                const context = this;
                const args = arguments;
                const later = function() {
                    timeout = null;
                    if (!immediate) func.apply(context, args);
                };
                const callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
                if (callNow) func.apply(context, args);
            };
        };
        
        // Request cache for AJAX calls (5 minute TTL)
        window.requestCache = {
            cache: new Map(),
            ttl: 5 * 60 * 1000, // 5 minutes
            get: function(key) {
                const item = this.cache.get(key);
                if (!item) return null;
                if (Date.now() > item.expiry) {
                    this.cache.delete(key);
                    return null;
                }
                return item.data;
            },
            set: function(key, data) {
                this.cache.set(key, {
                    data: data,
                    expiry: Date.now() + this.ttl
                });
            },
            clear: function() {
                this.cache.clear();
            }
        };
        
        // AJAX Page Loading Function (make it globally accessible)
        window.loadPageViaAjax = function(url) {
            const contentContainer = document.getElementById('page-content');
            if (!contentContainer) return;
            
            // Check cache first
            const cacheKey = 'page_' + url;
            const cached = window.requestCache.get(cacheKey);
            if (cached) {
                contentContainer.innerHTML = cached;
                // Re-execute scripts from cached content
                const scripts = contentContainer.querySelectorAll('script');
                scripts.forEach(function(oldScript) {
                    const newScript = document.createElement('script');
                    Array.from(oldScript.attributes).forEach(attr => {
                        newScript.setAttribute(attr.name, attr.value);
                    });
                    if (oldScript.src) {
                        newScript.src = oldScript.src;
                    } else {
                        newScript.textContent = oldScript.textContent || oldScript.innerHTML;
                    }
                    oldScript.remove();
                    document.body.appendChild(newScript);
                });
                return;
            }
            
            // Show loading state
            contentContainer.innerHTML = '<div class="text-center py-8"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-3 text-muted">Loading page...</p></div>';
            
            // Update active menu items
            document.querySelectorAll('#sidebar-nav a, #sidebar-nav button.nav-item').forEach(function(link) {
                link.classList.remove('bg-gradient-to-r', 'from-blue-600', 'to-purple-600', 'text-white', 'shadow-lg', 'bg-gray-700', 'text-white');
                // Also remove active class from nav-item
                link.classList.remove('active');
            });
            
            // Find and activate the clicked link
            // Try exact match first
            let clickedLink = Array.from(document.querySelectorAll('#sidebar-nav a')).find(link => {
                const href = link.getAttribute('href');
                return href === url || href === url.split('?')[0]; // Match without query params
            });
            
            // If no exact match, try to match by route pattern
            if (!clickedLink) {
                // For projects route
                if (url.includes('/admin/projects') || url.includes('projects')) {
                    clickedLink = Array.from(document.querySelectorAll('#sidebar-nav a')).find(link => {
                        const href = link.getAttribute('href');
                        return href && (href.includes('/admin/projects') || href.includes('projects') || href.includes('admin/projects'));
                    });
                    
                    // Also expand the Projects parent menu if it exists
                    const projectsMenu = document.getElementById('projects-menu');
                    const projectsToggle = document.querySelector('button[data-target="projects-menu"]');
                    if (projectsMenu && projectsToggle) {
                        projectsMenu.classList.remove('hidden');
                        projectsToggle.setAttribute('aria-expanded', 'true');
                        // Rotate chevron icon
                        const chevron = projectsToggle.querySelector('svg[data-icon="chevron"]');
                        if (chevron) {
                            chevron.classList.add('rotate-180');
                        }
                        // Activate parent button
                        projectsToggle.classList.add('bg-gradient-to-r', 'from-blue-600', 'to-purple-600', 'text-white', 'shadow-lg');
                    }
                }
            }
            
            if (clickedLink) {
                clickedLink.classList.add('bg-gray-700', 'text-white', 'active');
                // Also activate parent nav-item if it exists
                const navItem = clickedLink.closest('.nav-item') || clickedLink;
                if (navItem) {
                    navItem.classList.add('active');
                }
                
                // If it's a submenu item, expand and activate parent
                const submenu = clickedLink.closest('.submenu');
                if (submenu) {
                    const menuId = submenu.getAttribute('id');
                    if (menuId) {
                        const parentToggle = document.querySelector(`button[data-target="${menuId}"]`);
                        if (parentToggle) {
                            submenu.classList.remove('hidden');
                            parentToggle.setAttribute('aria-expanded', 'true');
                            const chevron = parentToggle.querySelector('svg[data-icon="chevron"]');
                            if (chevron) {
                                chevron.classList.add('rotate-180');
                            }
                            parentToggle.classList.add('bg-gradient-to-r', 'from-blue-600', 'to-purple-600', 'text-white', 'shadow-lg');
                        }
                    }
                }
            }
            
            // Fetch page content
            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html',
                    'X-Page-Load': 'true'  // Signal that this is a page load, not a filter request
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(html => {
                // Create a temporary container to parse the HTML
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                // Extract the content from the response
                // Look for the main content area - it should be inside main > div > #page-content
                let newContent = '';
                
                // Try multiple selectors to find the content
                const selectors = [
                    '#page-content',
                    'main .max-w-7xl',
                    'main > div',
                    'main',
                    'body > main',
                    '.content',
                    'body'
                ];
                
                for (let selector of selectors) {
                    const element = doc.querySelector(selector);
                    if (element) {
                        if (selector === '#page-content') {
                            newContent = element.innerHTML;
                        } else if (selector === 'main .max-w-7xl') {
                            const pageContent = element.querySelector('#page-content');
                            newContent = pageContent ? pageContent.innerHTML : element.innerHTML;
                        } else if (selector === 'main > div') {
                            const pageContent = element.querySelector('#page-content');
                            newContent = pageContent ? pageContent.innerHTML : element.innerHTML;
                        } else if (selector === 'main') {
                            const pageContent = element.querySelector('#page-content');
                            newContent = pageContent ? pageContent.innerHTML : element.innerHTML;
                        } else {
                            const pageContent = element.querySelector('#page-content');
                            newContent = pageContent ? pageContent.innerHTML : element.innerHTML;
                        }
                        break;
                    }
                }
                
                // If still no content, try to extract from body
                if (!newContent && doc.body) {
                    const bodyContent = doc.body.querySelector('#page-content') || doc.body.querySelector('main') || doc.body;
                    newContent = bodyContent ? bodyContent.innerHTML : '';
                }
                
                // Extract scripts from the full document (not just page-content)
                // This includes scripts from the scripts stack
                const allScripts = doc.querySelectorAll('script');
                const scriptsToExecute = [];
                allScripts.forEach(function(oldScript) {
                    // Skip if script is empty or invalid
                    if (!oldScript.src && (!oldScript.textContent || !oldScript.textContent.trim())) {
                        return;
                    }
                    
                    const newScript = document.createElement('script');
                    Array.from(oldScript.attributes).forEach(attr => {
                        newScript.setAttribute(attr.name, attr.value);
                    });
                    if (oldScript.src) {
                        newScript.src = oldScript.src;
                    } else {
                        const scriptContent = oldScript.textContent || oldScript.innerHTML || '';
                        // Validate script content is complete
                        if (scriptContent.trim()) {
                            // Count braces and parentheses to check if script is complete
                            const openBraces = (scriptContent.match(/\{/g) || []).length;
                            const closeBraces = (scriptContent.match(/\}/g) || []).length;
                            const openParens = (scriptContent.match(/\(/g) || []).length;
                            const closeParens = (scriptContent.match(/\)/g) || []).length;
                            
                            // If braces/parens don't match, skip this script (might be incomplete)
                            if (openBraces !== closeBraces || openParens !== closeParens) {
                                console.warn('Skipping potentially incomplete script from scripts stack:', {
                                    openBraces,
                                    closeBraces,
                                    openParens,
                                    closeParens,
                                    contentLength: scriptContent.length,
                                    preview: scriptContent.substring(0, 100)
                                });
                                return;
                            }
                            
                            // Try to validate syntax
                            try {
                                new Function(scriptContent);
                            } catch (syntaxError) {
                                console.error('Syntax error in script from scripts stack, skipping:', syntaxError.message);
                                console.warn('Script content preview:', scriptContent.substring(0, 200));
                                return; // Skip this script
                            }
                        }
                        newScript.textContent = scriptContent;
                    }
                    scriptsToExecute.push(newScript);
                });
                
                // Also extract modals that might be outside #page-content
                const modals = doc.querySelectorAll('[id$="Modal"], [id$="ConfirmationModal"]');
                let modalsHtml = '';
                modals.forEach(modal => {
                    // Check if modal doesn't already exist in the document
                    if (!document.getElementById(modal.id)) {
                        modalsHtml += modal.outerHTML;
                    }
                });
                
                // Update content
                contentContainer.innerHTML = newContent || '<div class="alert alert-warning">No content found</div>';
                
                // Append modals to body if they don't exist
                if (modalsHtml) {
                    const tempModalContainer = document.createElement('div');
                    tempModalContainer.innerHTML = modalsHtml;
                    while (tempModalContainer.firstChild) {
                        document.body.appendChild(tempModalContainer.firstChild);
                    }
                }
                
                // Execute scripts after content is loaded
                // Use a small delay to ensure DOM is ready
                setTimeout(function() {
                    // First, execute scripts from the full document (from scripts stack)
                    scriptsToExecute.forEach(function(newScript) {
                        // Check if script with same content already exists to avoid duplicates
                        const existingScripts = document.querySelectorAll('script');
                        let scriptExists = false;
                        existingScripts.forEach(function(existing) {
                            if (newScript.src && existing.src === newScript.src) {
                                scriptExists = true;
                            } else if (!newScript.src && existing.textContent === newScript.textContent && existing.textContent.trim() !== '') {
                                scriptExists = true;
                            }
                        });
                        
                        if (!scriptExists) {
                            try {
                                document.body.appendChild(newScript);
                            } catch (e) {
                                console.error('Error executing script:', e);
                            }
                        }
                    });
                    
                    // Also execute any scripts that might be in the loaded content itself
                    const contentScripts = contentContainer.querySelectorAll('script');
                    contentScripts.forEach(function(oldScript) {
                        // Skip if script is already processed
                        if (oldScript.dataset.processed) return;
                        
                        // Get the full script content before removing (declare once)
                        const scriptContent = oldScript.textContent || oldScript.innerHTML || '';
                        
                        // Check if this script was already executed by checking its content hash
                        if (scriptContent.trim()) {
                            const scriptHash = btoa(scriptContent).substring(0, 32);
                            const existingScript = Array.from(document.querySelectorAll('script')).find(s => {
                                const existingContent = s.textContent || s.innerHTML || '';
                                if (existingContent.trim()) {
                                    const existingHash = btoa(existingContent).substring(0, 32);
                                    return existingHash === scriptHash && !s.dataset.processed;
                                }
                                return false;
                            });
                            if (existingScript) {
                                return; // Skip this script, it's already been executed
                            }
                        }
                        
                        oldScript.dataset.processed = 'true';
                        
                        // Validate script content is complete (basic check)
                        if (!oldScript.src && scriptContent.trim()) {
                            // Count braces to check if script is complete
                            const openBraces = (scriptContent.match(/\{/g) || []).length;
                            const closeBraces = (scriptContent.match(/\}/g) || []).length;
                            const openParens = (scriptContent.match(/\(/g) || []).length;
                            const closeParens = (scriptContent.match(/\)/g) || []).length;
                            
                            // If braces/parens don't match, skip this script (might be incomplete)
                            if (openBraces !== closeBraces || openParens !== closeParens) {
                                console.warn('Skipping potentially incomplete script:', {
                                    openBraces,
                                    closeBraces,
                                    openParens,
                                    closeParens,
                                    contentLength: scriptContent.length
                                });
                                return;
                            }
                        }
                        
                        const newScript = document.createElement('script');
                        Array.from(oldScript.attributes).forEach(attr => {
                            if (attr.name !== 'data-processed') {
                                newScript.setAttribute(attr.name, attr.value);
                            }
                        });
                        if (oldScript.src) {
                            newScript.src = oldScript.src;
                            newScript.onload = function() {
                                // Script loaded, check if it's Chart.js and initialize dashboard if needed
                                if (oldScript.src.includes('chart.js') && document.getElementById('incomeExpenseChart')) {
                                    setTimeout(function() {
                                        if (typeof window.initDashboardCharts === 'function') {
                                            window.initDashboardCharts();
                                        } else if (typeof initDashboardCharts === 'function') {
                                            initDashboardCharts();
                                        }
                                    }, 100);
                                }
                            };
                            newScript.onerror = function() {
                                console.error('Error loading script:', oldScript.src);
                            };
                        } else {
                            newScript.textContent = scriptContent;
                        }
                        
                        // Remove old script and append new one to body to execute it
                        try {
                            // Validate script before executing
                            if (!oldScript.src && newScript.textContent) {
                                // Try to parse the script to check for syntax errors
                                try {
                                    // This will throw an error if the script has syntax errors
                                    new Function(newScript.textContent);
                                } catch (syntaxError) {
                                    console.error('Syntax error in script, skipping:', syntaxError.message);
                                    console.warn('Script content preview:', newScript.textContent.substring(0, 200));
                                    return; // Skip this script
                                }
                            }
                            
                            oldScript.remove();
                            document.body.appendChild(newScript);
                        } catch (e) {
                            console.error('Error executing script:', e);
                            // Don't remove the old script if there was an error
                        }
                    });
                    
                    // Check if dashboard charts need to be initialized
                    if (contentContainer.querySelector('#incomeExpenseChart')) {
                        setTimeout(function() {
                            if (typeof window.initializeDashboard === 'function') {
                                window.initializeDashboard();
                            } else if (typeof initializeDashboard === 'function') {
                                initializeDashboard();
                            }
                        }, 200);
                    }
                }, 50);
                
                // Re-initialize any components that need it
                wrapTablesForMobile();
                
                // Re-attach event listeners for dynamically loaded content
                if (typeof attachPaginationListeners === 'function') {
                    attachPaginationListeners();
                }
                
                // Re-attach submenu toggle event listeners
                if (typeof window.attachSubmenuToggleListeners === 'function') {
                    window.attachSubmenuToggleListeners();
                } else if (typeof attachSubmenuToggleListeners === 'function') {
                    attachSubmenuToggleListeners();
                }
                
                // Ensure active state is maintained after content load
                // Re-check and activate the correct menu item based on current URL
                const currentUrl = url.split('?')[0]; // Remove query params
                if (currentUrl.includes('/admin/projects')) {
                    const projectsLink = document.querySelector('#sidebar-nav a[href*="projects"]');
                    const projectsMenu = document.getElementById('projects-menu');
                    const projectsToggle = document.querySelector('button[data-target="projects-menu"]');
                    
                    if (projectsLink) {
                        projectsLink.classList.add('bg-gray-700', 'text-white');
                    }
                    if (projectsMenu && projectsToggle) {
                        projectsMenu.classList.remove('hidden');
                        projectsToggle.setAttribute('aria-expanded', 'true');
                        projectsToggle.classList.add('bg-gradient-to-r', 'from-blue-600', 'to-purple-600', 'text-white', 'shadow-lg');
                        const chevron = projectsToggle.querySelector('svg[data-icon="chevron"]');
                        if (chevron) {
                            chevron.classList.add('rotate-180');
                        }
                    }
                }
                
                // Update browser URL without page refresh (for browser history)
                if (typeof window.history !== 'undefined' && window.history.pushState) {
                    window.history.pushState({path: url}, '', url);
                }
                
                // Scroll to top
                window.scrollTo({ top: 0, behavior: 'smooth' });
            })
            .catch(error => {
                console.error('Error loading page:', error);
                contentContainer.innerHTML = '<div class="alert alert-danger"><strong>Error:</strong> Failed to load page. <a href="' + url + '" class="alert-link">Click here to reload</a></div>';
            });
        };
        
        // Handle browser back/forward buttons for AJAX navigation
        window.addEventListener('popstate', function(event) {
            if (event.state && event.state.path) {
                loadPageViaAjax(event.state.path);
            } else {
                // Fallback: reload the page if no state
                window.location.reload();
            }
        });
        
        // AJAX Navigation - Intercept all internal links within page-content (for dashboard cards, etc.)
        // Use event delegation with capture phase to catch events early
        // Set up immediately after loadPageViaAjax is defined
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a[href]');
            if (!link) return;
            
            // Only intercept links within page-content (skip sidebar links as they're handled separately)
            const pageContent = document.getElementById('page-content');
            if (!pageContent || !pageContent.contains(link)) return;
            
            // Skip if link is in sidebar (already handled by sidebar navigation)
            const sidebarNav = document.getElementById('sidebar-nav');
            if (sidebarNav && sidebarNav.contains(link)) return;
            
            // Skip if link has data-ajax="false" attribute
            if (link.getAttribute('data-ajax') === 'false') return;
            
            const href = link.getAttribute('href');
            if (!href) return;
            
            // Skip if it's an external link, mailto, tel, hash link, or download link
            if (href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:') || 
                href.startsWith('http://') || href.startsWith('https://') || link.hasAttribute('download')) {
                return;
            }
            
            // Skip if it's a logout link or form submission
            if (href.includes('logout') || link.closest('form')) {
                return;
            }
            
            // Skip if it's opening in a new tab/window
            if (link.getAttribute('target') === '_blank' || link.getAttribute('target') === '_new') {
                return;
            }
            
            // Skip if modifier keys are pressed (Ctrl, Cmd, Shift, etc.)
            if (e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) {
                return;
            }
            
            // Check if loadPageViaAjax function exists
            if (typeof window.loadPageViaAjax !== 'function') {
                console.warn('loadPageViaAjax not available yet');
                return;
            }
            
            // Prevent default navigation and use AJAX
            e.preventDefault();
            e.stopImmediatePropagation();
            window.loadPageViaAjax(href);
        }, true); // Use capture phase to catch events before other handlers
        
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
        
        // Remove text from action buttons, keep only icons
        document.addEventListener('DOMContentLoaded', function() {
            // Remove text nodes from all btn-sm buttons, keep only icons
            document.querySelectorAll('.btn-sm').forEach(function(button) {
                // Get all child nodes
                const childNodes = Array.from(button.childNodes);
                
                childNodes.forEach(function(node) {
                    // If it's a text node (not an element), remove it
                    if (node.nodeType === Node.TEXT_NODE) {
                        const text = node.textContent.trim();
                        // Only remove if it's not empty and not just whitespace
                        if (text && text.length > 0) {
                            node.remove();
                        }
                    }
                    // If it's an element but not an icon (i or .bi), remove it
                    else if (node.nodeType === Node.ELEMENT_NODE) {
                        if (!node.classList.contains('bi') && 
                            node.tagName !== 'I' && 
                            !node.querySelector('.bi') && 
                            !node.querySelector('i')) {
                            node.remove();
                        }
                    }
                });
            });
        });
        
        // Auto-wrap tables in scrollable containers on mobile
        function wrapTablesForMobile() {
            if (window.innerWidth <= 768) {
                const tables = document.querySelectorAll('table');
                tables.forEach(table => {
                    // Check if table is already wrapped in overflow-x-auto
                    const parent = table.parentElement;
                    if (parent && !parent.classList.contains('overflow-x-auto') && 
                        !parent.classList.contains('table-responsive') &&
                        !parent.style.overflowX) {
                        // Check if parent already has overflow-x-auto in its class list or inline style
                        const hasOverflow = window.getComputedStyle(parent).overflowX === 'auto' ||
                                          window.getComputedStyle(parent).overflowX === 'scroll';
                        
                        if (!hasOverflow) {
                            // Create wrapper if needed
                            const wrapper = document.createElement('div');
                            wrapper.className = 'overflow-x-auto';
                            wrapper.style.webkitOverflowScrolling = 'touch';
                            table.parentNode.insertBefore(wrapper, table);
                            wrapper.appendChild(table);
                        }
                    }
                });
            }
        }
        
        // Run on page load and resize
        document.addEventListener('DOMContentLoaded', function() {
            wrapTablesForMobile();
        });
        
        window.addEventListener('resize', function() {
            wrapTablesForMobile();
        });
        
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
    <script src="{{ asset('js/form-validation.js') }}"></script>
</body>
</html>


