@extends('admin.layout')

@section('title', 'User Activity Logs')

@section('content')
<div class="mb-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">User Activity History</h1>
            <p class="text-gray-600 mt-1">Track actions, page visits, and changes made by users.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.user-activities.export.excel', request()->query()) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">
                <i class="bi bi-file-earmark-excel"></i>
                Export Excel
            </a>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    <div class="rounded-xl p-4 bg-gradient-to-r from-indigo-500 to-blue-500 text-white shadow">
        <p class="text-sm opacity-90">Today</p>
        <p class="text-3xl font-bold mt-1">{{ number_format($stats['today']) }}</p>
    </div>
    <div class="rounded-xl p-4 bg-gradient-to-r from-emerald-500 to-green-500 text-white shadow">
        <p class="text-sm opacity-90">Last 7 days</p>
        <p class="text-3xl font-bold mt-1">{{ number_format($stats['week']) }}</p>
    </div>
    <div class="rounded-xl p-4 bg-gradient-to-r from-orange-500 to-amber-500 text-white shadow">
        <p class="text-sm opacity-90">Last 30 days</p>
        <p class="text-3xl font-bold mt-1">{{ number_format($stats['month']) }}</p>
    </div>
    <div class="rounded-xl p-4 bg-gradient-to-r from-violet-500 to-purple-500 text-white shadow">
        <p class="text-sm opacity-90">Total logs</p>
        <p class="text-3xl font-bold mt-1">{{ number_format($stats['total']) }}</p>
    </div>
</div>

<div class="bg-white rounded-xl shadow border border-gray-100 mb-5">
    <form id="activity-filter-form" method="GET" action="{{ route('admin.user-activities.index') }}" class="p-4 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-3">
        <div class="xl:col-span-2">
            <label class="block text-xs text-gray-500 mb-1">Search</label>
            <input type="text" name="search" id="activity-search-input" value="{{ request('search') }}" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Action, route, URL, user..." oninput="window.userActivityDebouncedFilter && window.userActivityDebouncedFilter()">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">User</label>
            <select name="user_id" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" onchange="window.userActivityApplyFilter && window.userActivityApplyFilter()">
                <option value="">All users</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ (string) request('user_id') === (string) $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Action</label>
            <select name="action_filter" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" onchange="window.userActivityApplyFilter && window.userActivityApplyFilter()">
                <option value="">All</option>
                <option value="Created/Submitted" {{ request('action_filter') === 'Created/Submitted' ? 'selected' : '' }}>Created/Submitted</option>
                <option value="Updated" {{ request('action_filter') === 'Updated' ? 'selected' : '' }}>Updated</option>
                <option value="Deleted" {{ request('action_filter') === 'Deleted' ? 'selected' : '' }}>Deleted</option>
                <option value="User login" {{ request('action_filter') === 'User login' ? 'selected' : '' }}>User login</option>
                <option value="User logout" {{ request('action_filter') === 'User logout' ? 'selected' : '' }}>User logout</option>
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">From</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" onchange="window.userActivityApplyFilter && window.userActivityApplyFilter()">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">To</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" onchange="window.userActivityApplyFilter && window.userActivityApplyFilter()">
        </div>
        <div class="xl:col-span-6 flex flex-wrap gap-2 pt-1">
            <a href="{{ route('admin.user-activities.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 js-clear-filter">
                <i class="bi bi-x-circle"></i>
                Clear
            </a>
        </div>
    </form>
</div>

@include('admin.user-activities.partials._list')
@endsection

@push('scripts')
<script>
(() => {
    const filterForm = document.getElementById('activity-filter-form');
    const searchInput = document.getElementById('activity-search-input');
    const clearLink = document.querySelector('.js-clear-filter');
    const exportLink = document.querySelector('a[href*="user-activities/export/excel"]');

    function buildQueryString() {
        if (!filterForm) return;
        const params = new URLSearchParams(new FormData(filterForm));
        return params.toString();
    }

    function updateExportLink(queryString) {
        if (!exportLink) return;
        const baseExportUrl = '{{ route('admin.user-activities.export.excel') }}';
        exportLink.href = baseExportUrl + (queryString ? ('?' + queryString) : '');
    }

    function replaceListHtml(html) {
        const current = document.getElementById('activity-log-list-wrap');
        if (!current || !html) return;

        const tmp = document.createElement('div');
        tmp.innerHTML = html.trim();
        const next = tmp.firstElementChild;
        if (next) {
            current.replaceWith(next);
        }
    }

    function loadFilteredList(url, pushHistory = true) {
        if (!url) return;

        const ajaxUrl = url + (url.includes('?') ? '&' : '?') + 'filter_ajax=1';
        fetch(ajaxUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        })
        .then(function (res) {
            if (!res.ok) throw new Error('Failed to load activity list');
            return res.json();
        })
        .then(function (data) {
            if (data && data.html) {
                replaceListHtml(data.html);
                const normalized = url.replace(/[?&]filter_ajax=1/g, '');
                if (pushHistory && window.history && window.history.replaceState) {
                    window.history.replaceState({ path: normalized }, '', normalized);
                }
            }
        })
        .catch(function () {
            if (typeof window.loadPageViaAjax === 'function') {
                window.loadPageViaAjax(url);
            } else {
                window.location.href = url;
            }
        });
    }

    function loadFilteredPage() {
        if (!filterForm) return;
        const baseUrl = filterForm.getAttribute('action');
        const queryString = buildQueryString();
        const url = baseUrl + (queryString ? ('?' + queryString) : '');
        updateExportLink(queryString);
        loadFilteredList(url, true);
    }

    function navigateWithoutRefresh(url) {
        if (!url) return;
        const isActivityPage = url.includes('/admin/user-activities');

        if (isActivityPage) {
            const urlObj = new URL(url, window.location.origin);
            const queryString = urlObj.searchParams.toString();
            updateExportLink(queryString);
            loadFilteredList(urlObj.pathname + (queryString ? ('?' + queryString) : ''), true);
            return;
        }

        if (typeof window.loadPageViaAjax === 'function') {
            window.loadPageViaAjax(url);
        } else {
            window.location.href = url;
        }
    }

    window.userActivityApplyFilter = function () {
        loadFilteredPage();
    };

    let searchTimer = null;
    window.userActivityDebouncedFilter = function () {
        if (searchTimer) {
            clearTimeout(searchTimer);
        }
        searchTimer = setTimeout(function () {
            loadFilteredPage();
        }, 400);
    };

    if (filterForm) {
        filterForm.addEventListener('submit', function (e) {
            e.preventDefault();
            loadFilteredPage();
        });

    }

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            window.userActivityDebouncedFilter();
        });
    }

    if (clearLink) {
        clearLink.addEventListener('click', function (e) {
            e.preventDefault();
            if (filterForm) {
                filterForm.reset();
            }
            updateExportLink('');
            navigateWithoutRefresh(clearLink.getAttribute('href'));
        });
    }

    document.addEventListener('click', function (e) {
        const link = e.target.closest('#activity-log-list-wrap nav a');
        if (!link) return;
        const href = link.getAttribute('href');
        if (!href) return;
        e.preventDefault();
        navigateWithoutRefresh(href);
    });
})();
</script>
@endpush

