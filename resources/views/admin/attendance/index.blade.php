@extends('admin.layout')

@section('title', 'Worker Attendance')

@section('content')
<div class="mb-4 sm:mb-5 flex flex-wrap justify-between items-start gap-2 sm:gap-3">
    <div>
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Attendance</h1>
        <p class="text-xs sm:text-sm text-gray-500 mt-1">Monthly sheet · AD / BS · filters load without refresh</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a id="att-download" href="{{ route('admin.attendance.export', request()->only(['calendar', 'year', 'month', 'project_id'])) }}"
           class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 sm:px-4 py-2 rounded-lg text-sm inline-flex items-center gap-1.5">
            <i class="bi bi-download"></i> Download
        </a>
        <a href="{{ route('admin.attendance.history') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-3 sm:px-4 py-2 rounded-lg text-sm">History</a>
    </div>
</div>

<div id="att-flash" class="hidden mb-4 px-4 py-3 rounded-lg text-sm"></div>

<div id="att-root" class="relative">
    <div id="att-loading" class="hidden absolute inset-0 z-40 bg-white/60 rounded-xl flex items-start justify-center pt-24">
        <div class="bg-white border border-gray-200 shadow-lg rounded-lg px-4 py-2 text-sm text-gray-700">Loading…</div>
    </div>
    @include('admin.attendance._panel')
</div>

<script>
(function () {
    const INDEX_URL = @json(route('admin.attendance.index'));
    const EXPORT_URL = @json(route('admin.attendance.export'));
    const CELL_URL = @json(route('admin.attendance.cell'));
    const MARK_URL = @json(route('admin.attendance.mark-column'));
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
    const root = document.getElementById('att-root');
    const loadingEl = document.getElementById('att-loading');
    const flashEl = document.getElementById('att-flash');
    const statusTitles = { present: 'Present', half_day: 'Half Day', leave: 'Leave', absent: 'Absent', '': 'Unmarked' };
    const cycle = ['', 'present', 'half_day', 'leave', 'absent'];
    const meta = {
        '': { short: '·', title: 'Unmarked', bg: 'bg-white hover:bg-gray-50 text-gray-300 border-gray-200' },
        present: { short: 'P', title: 'Present', bg: 'bg-emerald-500 text-white border-emerald-600' },
        half_day: { short: 'H', title: 'Half Day', bg: 'bg-amber-400 text-amber-950 border-amber-500' },
        leave: { short: 'L', title: 'Leave', bg: 'bg-sky-500 text-white border-sky-600' },
        absent: { short: 'A', title: 'Absent', bg: 'bg-rose-500 text-white border-rose-600' },
    };
    const allClasses = Object.values(meta).map(m => m.bg).join(' ').split(/\s+/);
    let loadSeq = 0;

    function panel() {
        return document.getElementById('att-panel');
    }

    function currentParams(overrides = {}) {
        const p = panel();
        const params = {
            calendar: p?.dataset.calendar || 'bs',
            year: p?.dataset.year || '',
            month: p?.dataset.month || '',
            project_id: p?.dataset.project || '',
        };
        Object.assign(params, overrides);
        if (!params.project_id) delete params.project_id;
        return params;
    }

    function showFlash(message, isError) {
        if (!flashEl || !message) return;
        flashEl.textContent = message;
        flashEl.classList.remove('hidden', 'bg-emerald-50', 'border-emerald-200', 'text-emerald-800', 'bg-rose-50', 'border-rose-200', 'text-rose-800', 'border');
        flashEl.classList.add('border', isError ? 'bg-rose-50' : 'bg-emerald-50', isError ? 'border-rose-200' : 'border-emerald-200', isError ? 'text-rose-800' : 'text-emerald-800');
        clearTimeout(showFlash._t);
        showFlash._t = setTimeout(() => flashEl.classList.add('hidden'), 2500);
    }

    function setStatusMsg(el, text, isError) {
        if (!el) return;
        el.textContent = text || '';
        el.classList.toggle('text-rose-600', !!isError);
        el.classList.toggle('text-indigo-600', !isError);
    }

    async function saveAttendance(workerId, date, status) {
        const body = new FormData();
        body.append('_token', CSRF);
        body.append('worker_id', workerId);
        body.append('date', date);
        if (status) body.append('status', status);
        const projectId = panel()?.dataset.project;
        if (projectId) body.append('project_id', projectId);

        const res = await fetch(CELL_URL, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok || !data.success) throw new Error(data.message || 'Not allowed');
        return data;
    }

    async function loadPanel(overrides = {}, push = true) {
        const params = currentParams(overrides);
        const qs = new URLSearchParams({ ...params, partial: '1' });
        const seq = ++loadSeq;
        loadingEl?.classList.remove('hidden');

        try {
            const res = await fetch(INDEX_URL + '?' + qs.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html',
                },
            });
            if (!res.ok) throw new Error('Failed to load');
            const html = await res.text();
            if (seq !== loadSeq) return;

            // Keep loading overlay; replace panel only
            const tmp = document.createElement('div');
            tmp.innerHTML = html.trim();
            const newPanel = tmp.querySelector('#att-panel') || tmp.firstElementChild;
            const oldPanel = panel();
            if (oldPanel && newPanel) {
                oldPanel.replaceWith(newPanel);
            } else if (newPanel) {
                root.appendChild(newPanel);
            }

            if (push) {
                const urlQs = new URLSearchParams(params);
                history.pushState(params, '', INDEX_URL + '?' + urlQs.toString());
            }
            initInteractions();
        } catch (e) {
            showFlash(e.message || 'Could not load attendance', true);
        } finally {
            if (seq === loadSeq) loadingEl?.classList.add('hidden');
        }
    }

    function applyVisual(btn, status) {
        const m = meta[status] || meta[''];
        allClasses.forEach(c => btn.classList.remove(c));
        m.bg.split(/\s+/).forEach(c => btn.classList.add(c));
        btn.textContent = m.short;
        btn.dataset.status = status;
        const base = (btn.dataset.baseTitle || btn.title.split(' · ').slice(0, 2).join(' · '));
        btn.dataset.baseTitle = base;
        btn.title = base + ' · ' + m.title + ' (click to cycle)';
    }

    function initDesktopCells() {
        const statusEl = document.getElementById('att-save-status');
        document.querySelectorAll('.att-cell').forEach(btn => {
            btn.addEventListener('click', async function () {
                const cur = this.dataset.status || '';
                const next = cycle[(cycle.indexOf(cur) + 1) % cycle.length];
                applyVisual(this, next);
                setStatusMsg(statusEl, 'Saving…');
                try {
                    await saveAttendance(this.dataset.worker, this.dataset.date, next);
                    setStatusMsg(statusEl, 'Saved');
                    setTimeout(() => { if (statusEl?.textContent === 'Saved') setStatusMsg(statusEl, ''); }, 1200);
                } catch (e) {
                    applyVisual(this, cur);
                    setStatusMsg(statusEl, e.message || 'Error', true);
                }
            });
        });
    }

    function initMobile() {
        const mobileRoot = document.getElementById('att-mobile');
        if (!mobileRoot) return;

        const chips = [...mobileRoot.querySelectorAll('.att-day-chip')];
        const workers = [...mobileRoot.querySelectorAll('.att-mobile-worker')];
        const dayLabel = document.getElementById('att-mobile-day-label');
        const markDateInput = document.getElementById('att-mobile-mark-date');
        const mobileStatusEl = document.getElementById('att-save-status-mobile');
        const defaultDay = panel()?.dataset.defaultDay;
        let selectedDate = defaultDay;

        function paintWorkerCard(card, date) {
            const join = card.dataset.join || '';
            const mapEl = card.querySelector('.att-mobile-map');
            let map = {};
            try { map = JSON.parse(mapEl.textContent || '{}'); } catch (e) {}
            const locked = (join && date < join);
            const actions = card.querySelector('.att-mobile-actions');
            const lockedEl = card.querySelector('.att-mobile-locked');
            const label = card.querySelector('.att-mobile-status-label');
            const status = (map[date] && map[date].status) ? map[date].status : '';

            lockedEl.classList.toggle('hidden', !locked);
            actions.classList.toggle('hidden', locked);
            label.textContent = locked ? 'Locked' : (statusTitles[status] || 'Unmarked');

            card.querySelectorAll('.att-mobile-btn').forEach(btn => {
                const active = !locked && btn.dataset.status === status;
                btn.classList.toggle('opacity-40', !active);
                btn.classList.toggle('opacity-100', active);
                btn.classList.toggle('ring-2', active);
                btn.classList.toggle('ring-offset-1', active);
                btn.classList.toggle('ring-slate-800', active);
            });
        }

        function selectDay(date) {
            selectedDate = date;
            chips.forEach(chip => {
                const on = chip.dataset.date === date;
                chip.classList.toggle('border-indigo-600', on);
                chip.classList.toggle('bg-indigo-600', on);
                chip.classList.toggle('text-white', on);
                chip.classList.toggle('border-gray-200', !on && chip.dataset.future !== '1');
                chip.classList.toggle('bg-white', !on && chip.dataset.future !== '1');
                chip.classList.toggle('text-gray-800', !on && chip.dataset.future !== '1');
                if (on && dayLabel) dayLabel.textContent = chip.dataset.label || date;
            });
            if (markDateInput) markDateInput.value = date;
            workers.forEach(card => paintWorkerCard(card, date));
            const activeChip = chips.find(c => c.dataset.date === date);
            if (activeChip) activeChip.scrollIntoView({ inline: 'center', block: 'nearest', behavior: 'smooth' });
        }

        chips.forEach(chip => {
            chip.addEventListener('click', () => {
                if (chip.dataset.future === '1') return;
                selectDay(chip.dataset.date);
            });
        });

        workers.forEach(card => {
            card.querySelectorAll('.att-mobile-btn').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const join = card.dataset.join || '';
                    if (join && selectedDate < join) return;
                    const mapEl = card.querySelector('.att-mobile-map');
                    let map = {};
                    try { map = JSON.parse(mapEl.textContent || '{}'); } catch (e) {}
                    const current = (map[selectedDate] && map[selectedDate].status) ? map[selectedDate].status : '';
                    const next = (btn.dataset.status === current) ? '' : btn.dataset.status;
                    if (next) map[selectedDate] = { status: next };
                    else delete map[selectedDate];
                    mapEl.textContent = JSON.stringify(map);
                    paintWorkerCard(card, selectedDate);
                    setStatusMsg(mobileStatusEl, 'Saving…');
                    try {
                        await saveAttendance(card.dataset.worker, selectedDate, next);
                        setStatusMsg(mobileStatusEl, 'Saved');
                        setTimeout(() => { if (mobileStatusEl?.textContent === 'Saved') setStatusMsg(mobileStatusEl, ''); }, 1200);
                    } catch (e) {
                        if (current) map[selectedDate] = { status: current };
                        else delete map[selectedDate];
                        mapEl.textContent = JSON.stringify(map);
                        paintWorkerCard(card, selectedDate);
                        setStatusMsg(mobileStatusEl, e.message || 'Error', true);
                    }
                });
            });
        });

        if (defaultDay) selectDay(defaultDay);
    }

    function initFilters() {
        document.querySelectorAll('.att-nav').forEach(btn => {
            btn.addEventListener('click', () => {
                loadPanel({
                    calendar: btn.dataset.calendar,
                    year: btn.dataset.year,
                    month: btn.dataset.month,
                    project_id: panel()?.dataset.project || '',
                });
            });
        });

        document.getElementById('att-year')?.addEventListener('change', (e) => {
            loadPanel({ year: e.target.value });
        });
        document.getElementById('att-month')?.addEventListener('change', (e) => {
            loadPanel({ month: e.target.value });
        });
        document.getElementById('att-project')?.addEventListener('change', (e) => {
            loadPanel({ project_id: e.target.value });
        });
    }

    function initMarkColumn() {
        document.querySelectorAll('form.att-mark-column').forEach(form => {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                if (!confirm('Mark all eligible workers Present for this day?')) return;
                const body = new FormData(form);
                try {
                    const res = await fetch(MARK_URL, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        body
                    });
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok || !data.success) throw new Error(data.message || 'Failed');
                    showFlash(data.message || 'Marked');
                    await loadPanel({}, false);
                } catch (err) {
                    showFlash(err.message || 'Could not mark column', true);
                }
            });
        });
    }

    function syncDownload() {
        const link = document.getElementById('att-download');
        if (!link) return;
        const qs = new URLSearchParams(currentParams());
        link.href = EXPORT_URL + '?' + qs.toString();
    }

    function initInteractions() {
        initFilters();
        initDesktopCells();
        initMobile();
        initMarkColumn();
        syncDownload();
    }

    window.addEventListener('popstate', (e) => {
        const state = e.state || Object.fromEntries(new URL(location.href).searchParams.entries());
        loadPanel(state, false);
    });

    initInteractions();
})();
</script>
@endsection
