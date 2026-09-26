@php
    $statusMeta = [
        '' => ['short' => '·', 'title' => 'Unmarked', 'bg' => 'bg-white hover:bg-gray-50 text-gray-300 border-gray-200'],
        'present' => ['short' => 'P', 'title' => 'Present', 'bg' => 'bg-emerald-500 text-white border-emerald-600'],
        'half_day' => ['short' => 'H', 'title' => 'Half Day', 'bg' => 'bg-amber-400 text-amber-950 border-amber-500'],
        'leave' => ['short' => 'L', 'title' => 'Leave', 'bg' => 'bg-sky-500 text-white border-sky-600'],
        'absent' => ['short' => 'A', 'title' => 'Absent', 'bg' => 'bg-rose-500 text-white border-rose-600'],
    ];
    $todayStr = $today->toDateString();
    $defaultMobileDay = collect($grid['days'])->last(fn ($d) => $d['ad_date'] <= $todayStr)['ad_date']
        ?? ($grid['days'][0]['ad_date'] ?? $todayStr);
    $anchor = \App\Support\NepaliDate::switchAnchorDate($grid['days']);
    $switchToAd = \App\Support\NepaliDate::fromAdMonth($anchor);
    $switchToBs = \App\Support\NepaliDate::fromBsOfAd($anchor);
@endphp

<div id="att-panel"
     data-calendar="{{ $calendar }}"
     data-year="{{ $year }}"
     data-month="{{ $month }}"
     data-project="{{ $projectId }}"
     data-default-day="{{ $defaultMobileDay }}"
     data-prev-year="{{ $prev['year'] }}"
     data-prev-month="{{ $prev['month'] }}"
     data-next-year="{{ $next['year'] }}"
     data-next-month="{{ $next['month'] }}">

{{-- Toolbar --}}
<div class="bg-white border border-gray-200 rounded-xl shadow-sm p-3 sm:p-4 mb-4">
    <div class="flex flex-col gap-3">
        <div class="flex flex-wrap items-center gap-2">
            <div class="inline-flex rounded-lg border border-gray-300 overflow-hidden text-sm font-medium">
                <button type="button" class="att-nav px-3 py-2 sm:py-1.5 {{ $calendar === 'ad' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}"
                        data-calendar="ad" data-year="{{ $switchToAd['year'] }}" data-month="{{ $switchToAd['month'] }}">AD</button>
                <button type="button" class="att-nav px-3 py-2 sm:py-1.5 border-l border-gray-300 {{ $calendar === 'bs' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}"
                        data-calendar="bs" data-year="{{ $switchToBs['year'] }}" data-month="{{ $switchToBs['month'] }}">BS</button>
            </div>

            <div class="flex items-center gap-1.5 flex-1 min-w-0">
                <button type="button" class="att-nav inline-flex items-center justify-center w-10 h-10 sm:w-9 sm:h-9 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 shrink-0 text-lg"
                        data-calendar="{{ $calendar }}" data-year="{{ $prev['year'] }}" data-month="{{ $prev['month'] }}" title="Previous month">‹</button>

                <div class="flex items-center gap-1.5 flex-1 min-w-0">
                    <select id="att-year" class="flex-1 min-w-0 px-2 py-2 sm:py-1.5 border border-gray-300 rounded-lg text-sm">
                        @if($calendar === 'bs')
                            @for($y = \App\Support\NepaliDate::minBsYear(); $y <= \App\Support\NepaliDate::maxBsYear(); $y++)
                                <option value="{{ $y }}" {{ $y === $year ? 'selected' : '' }}>{{ $y }} BS</option>
                            @endfor
                        @else
                            @for($y = now()->year - 5; $y <= now()->year + 2; $y++)
                                <option value="{{ $y }}" {{ $y === $year ? 'selected' : '' }}>{{ $y }} AD</option>
                            @endfor
                        @endif
                    </select>
                    <select id="att-month" class="flex-[1.4] min-w-0 px-2 py-2 sm:py-1.5 border border-gray-300 rounded-lg text-sm">
                        @if($calendar === 'bs')
                            @foreach($bsMonths as $m => $name)
                                <option value="{{ $m }}" {{ $m === $month ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        @else
                            @foreach($adMonths as $m => $name)
                                <option value="{{ $m }}" {{ $m === $month ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <button type="button" class="att-nav inline-flex items-center justify-center w-10 h-10 sm:w-9 sm:h-9 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 shrink-0 text-lg"
                        data-calendar="{{ $calendar }}" data-year="{{ $next['year'] }}" data-month="{{ $next['month'] }}" title="Next month">›</button>
            </div>
        </div>

        <select id="att-project" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            <option value="">All projects</option>
            @foreach($projects as $project)
                <option value="{{ $project->id }}" {{ (string)$projectId === (string)$project->id ? 'selected' : '' }}>{{ $project->name }}</option>
            @endforeach
        </select>

        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2 sm:gap-3 pt-1 border-t border-gray-100">
            <div>
                <div class="text-base sm:text-lg font-semibold text-gray-900">{{ $grid['month_label'] }}</div>
                <div class="text-xs text-gray-500">{{ $dualLabel }}</div>
            </div>
            <div class="grid grid-cols-2 sm:flex sm:flex-wrap gap-2 sm:gap-3 text-xs">
                <span class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-2 py-1"><span class="w-5 h-5 rounded bg-emerald-500 text-white text-[10px] font-bold inline-flex items-center justify-center">P</span> Present <strong>{{ $summary['present'] }}</strong></span>
                <span class="inline-flex items-center gap-1.5 rounded-lg bg-amber-50 px-2 py-1"><span class="w-5 h-5 rounded bg-amber-400 text-amber-950 text-[10px] font-bold inline-flex items-center justify-center">H</span> Half <strong>{{ $summary['half_day'] }}</strong></span>
                <span class="inline-flex items-center gap-1.5 rounded-lg bg-sky-50 px-2 py-1"><span class="w-5 h-5 rounded bg-sky-500 text-white text-[10px] font-bold inline-flex items-center justify-center">L</span> Leave <strong>{{ $summary['leave'] }}</strong></span>
                <span class="inline-flex items-center gap-1.5 rounded-lg bg-rose-50 px-2 py-1"><span class="w-5 h-5 rounded bg-rose-500 text-white text-[10px] font-bold inline-flex items-center justify-center">A</span> Absent <strong>{{ $summary['absent'] }}</strong></span>
            </div>
        </div>
    </div>
</div>

@if($workers->isEmpty())
<div class="bg-white border border-gray-200 rounded-xl p-8 sm:p-10 text-center text-gray-500">
    No active workers. <a href="{{ route('admin.workers.create') }}" class="text-indigo-600 hover:underline">Add a worker</a> first.
</div>
@else

{{-- Mobile --}}
<div class="md:hidden space-y-3" id="att-mobile">
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="px-3 py-2 border-b border-gray-100 flex items-center justify-between gap-2">
            <span class="text-sm font-semibold text-gray-800">Pick a day</span>
            <span class="text-xs text-gray-500" id="att-mobile-day-label"></span>
        </div>
        <div class="flex gap-2 overflow-x-auto px-3 py-3 scroll-smooth" id="att-day-strip" style="-webkit-overflow-scrolling: touch;">
            @foreach($grid['days'] as $day)
                @php $isFutureDay = $day['ad_date'] > $todayStr; @endphp
                <button type="button"
                    class="att-day-chip shrink-0 w-12 rounded-xl border px-1 py-2 text-center transition
                        {{ $isFutureDay ? 'opacity-40 border-gray-200 bg-gray-50 text-gray-400 cursor-not-allowed' : 'border-gray-200 bg-white text-gray-800' }}
                        {{ $day['is_today'] ? 'ring-2 ring-indigo-400' : '' }}
                        {{ $day['is_weekend'] && !$isFutureDay ? 'bg-slate-50' : '' }}"
                    data-date="{{ $day['ad_date'] }}"
                    data-future="{{ $isFutureDay ? '1' : '0' }}"
                    data-label="{{ $calendar === 'bs' ? $day['bs_label'].' ('.$day['secondary'].')' : $day['ad_label'].' · '.$day['secondary'] }}"
                    @if($isFutureDay) disabled @endif>
                    <div class="text-[9px] uppercase text-gray-400 leading-none">{{ substr($day['weekday'], 0, 2) }}</div>
                    <div class="text-base font-bold leading-tight mt-0.5">{{ $calendar === 'bs' ? $day['bs_label'] : $day['ad_label'] }}</div>
                    <div class="text-[9px] text-gray-400 leading-none mt-0.5">{{ $calendar === 'bs' ? $day['ad_label'] : $day['bs_label'] }}</div>
                </button>
            @endforeach
        </div>
        <div class="px-3 pb-3">
            <form class="att-mark-column" data-ajax="1">
                @csrf
                <input type="hidden" name="date" id="att-mobile-mark-date" value="{{ $defaultMobileDay }}">
                <input type="hidden" name="status" value="present">
                <input type="hidden" name="calendar" value="{{ $calendar }}">
                <input type="hidden" name="year" value="{{ $year }}">
                <input type="hidden" name="month" value="{{ $month }}">
                @if($projectId)<input type="hidden" name="project_id" value="{{ $projectId }}">@endif
                <button type="submit" class="w-full py-2.5 rounded-lg bg-emerald-600 text-white text-sm font-medium hover:bg-emerald-700">
                    Mark all Present for this day
                </button>
            </form>
        </div>
    </div>

    <div class="space-y-2" id="att-mobile-workers">
        @foreach($workers as $worker)
            @php $joinDate = $worker->join_date ? $worker->join_date->toDateString() : null; @endphp
            <div class="att-mobile-worker bg-white border border-gray-200 rounded-xl shadow-sm p-3"
                 data-worker="{{ $worker->id }}"
                 data-join="{{ $joinDate }}">
                <div class="flex items-start justify-between gap-2 mb-2">
                    <div class="min-w-0">
                        <div class="font-semibold text-gray-900 truncate">{{ $worker->name }}</div>
                        @if($worker->project)
                            <div class="text-xs text-gray-400 truncate">{{ $worker->project->name }}</div>
                        @endif
                    </div>
                    <span class="att-mobile-status-label text-xs font-medium text-gray-500 shrink-0"></span>
                </div>
                <div class="att-mobile-locked hidden text-xs text-amber-700 bg-amber-50 border border-amber-100 rounded-lg px-3 py-2">
                    Locked for this date
                </div>
                <div class="att-mobile-actions grid grid-cols-4 gap-2">
                    @foreach(['present' => ['P','Present','bg-emerald-500'], 'half_day' => ['H','Half','bg-amber-400 text-amber-950'], 'leave' => ['L','Leave','bg-sky-500'], 'absent' => ['A','Absent','bg-rose-500']] as $st => $info)
                        <button type="button"
                            class="att-mobile-btn h-12 rounded-xl border-2 border-transparent text-white font-bold text-sm {{ $info[2] }} opacity-40"
                            data-status="{{ $st }}"
                            data-title="{{ $info[1] }}">
                            {{ $info[0] }}
                        </button>
                    @endforeach
                </div>
                <script type="application/json" class="att-mobile-map">@json($attendanceMap[$worker->id] ?? new \stdClass())</script>
            </div>
        @endforeach
    </div>

    <p class="text-xs text-center text-gray-400 px-2 pb-2">
        Tap P / H / L / A to mark · tap again to clear · <span id="att-save-status-mobile" class="text-indigo-600 font-medium"></span>
    </p>
</div>

{{-- Desktop --}}
<div class="hidden md:block bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-auto max-h-[70vh]">
        <table class="attendance-grid border-collapse text-sm" style="min-width: max-content;">
            <thead class="sticky top-0 z-20">
                <tr class="bg-slate-800 text-white">
                    <th class="sticky left-0 z-30 bg-slate-800 px-3 py-2 text-left font-semibold border-b border-r border-slate-700 min-w-[11rem]">Worker</th>
                    @foreach($grid['days'] as $day)
                        @php $isFutureDay = $day['ad_date'] > $todayStr; @endphp
                        <th class="px-0 py-1.5 text-center border-b border-r border-slate-700 font-normal min-w-[2.4rem] {{ $isFutureDay ? 'bg-slate-600 opacity-60' : ($day['is_today'] ? 'bg-indigo-700' : ($day['is_weekend'] ? 'bg-slate-700' : '')) }}"
                            title="{{ $day['ad_date'] }} · {{ $day['secondary'] }}{{ $isFutureDay ? ' · Future (locked)' : '' }}">
                            <div class="text-[10px] uppercase opacity-70 leading-none">{{ $day['weekday'] }}</div>
                            <div class="text-sm font-bold leading-tight">{{ $calendar === 'bs' ? $day['bs_label'] : $day['ad_label'] }}</div>
                            <div class="text-[9px] opacity-60 leading-none">{{ $calendar === 'bs' ? $day['ad_label'].' AD' : $day['bs_label'].' BS' }}</div>
                            @unless($isFutureDay)
                            <div class="mt-0.5 flex justify-center">
                                <form class="att-mark-column inline" data-ajax="1">
                                    @csrf
                                    <input type="hidden" name="date" value="{{ $day['ad_date'] }}">
                                    <input type="hidden" name="status" value="present">
                                    <input type="hidden" name="calendar" value="{{ $calendar }}">
                                    <input type="hidden" name="year" value="{{ $year }}">
                                    <input type="hidden" name="month" value="{{ $month }}">
                                    @if($projectId)<input type="hidden" name="project_id" value="{{ $projectId }}">@endif
                                    <button type="submit" class="text-[9px] px-1 rounded bg-white/10 hover:bg-emerald-500/80" title="Mark all Present">✓</button>
                                </form>
                            </div>
                            @endunless
                        </th>
                    @endforeach
                    <th class="px-2 py-2 text-center border-b border-slate-700 font-semibold min-w-[5.5rem] bg-slate-800 sticky right-0 z-30 leading-tight">
                        <span class="block text-[10px] uppercase tracking-wide opacity-80">Total</span>
                        <span class="block text-xs">Attendance</span>
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach($workers as $worker)
                    @php $rowPresent = 0; $rowHalf = 0; $rowLeave = 0; $rowAbsent = 0; @endphp
                    <tr class="hover:bg-slate-50/80 group">
                        <td class="sticky left-0 z-10 bg-white group-hover:bg-slate-50 px-3 py-2 border-b border-r border-gray-200 min-w-[11rem]">
                            <div class="font-medium text-gray-900 truncate max-w-[10rem]" title="{{ $worker->name }}">{{ $worker->name }}</div>
                            @if($worker->project)
                                <div class="text-[10px] text-gray-400 truncate">{{ $worker->project->name }}</div>
                            @endif
                        </td>
                        @foreach($grid['days'] as $day)
                            @php
                                $cell = $attendanceMap[$worker->id][$day['ad_date']] ?? null;
                                $status = $cell['status'] ?? '';
                                $meta = $statusMeta[$status] ?? $statusMeta[''];
                                if ($status === 'present') $rowPresent++;
                                elseif ($status === 'half_day') $rowHalf++;
                                elseif ($status === 'leave') $rowLeave++;
                                elseif ($status === 'absent') $rowAbsent++;
                                $isFuture = $day['ad_date'] > $todayStr;
                                $joinDate = $worker->join_date ? $worker->join_date->toDateString() : null;
                                $beforeJoin = $joinDate && $day['ad_date'] < $joinDate;
                                $locked = $isFuture || $beforeJoin;
                                $lockReason = $isFuture ? 'Future date — locked' : ($beforeJoin ? 'Before join date ('.$joinDate.') — locked' : '');
                            @endphp
                            <td class="p-0.5 border-b border-r border-gray-100 text-center {{ $day['is_weekend'] ? 'bg-slate-50' : '' }} {{ $day['is_today'] ? 'bg-indigo-50/60' : '' }} {{ $locked ? 'bg-gray-100' : '' }}">
                                @if($locked)
                                    <span class="inline-flex w-8 h-8 mx-auto items-center justify-center rounded border border-dashed border-gray-300 text-xs text-gray-300 bg-gray-50 cursor-not-allowed"
                                          title="{{ $worker->name }} · {{ $day['ad_date'] }} · {{ $lockReason }}">—</span>
                                @else
                                    <button type="button"
                                        class="att-cell w-8 h-8 mx-auto rounded border text-xs font-bold transition {{ $meta['bg'] }}"
                                        data-worker="{{ $worker->id }}"
                                        data-date="{{ $day['ad_date'] }}"
                                        data-status="{{ $status }}"
                                        title="{{ $worker->name }} · {{ $day['ad_date'] }} · {{ $meta['title'] }} (click to cycle)">
                                        {{ $meta['short'] }}
                                    </button>
                                @endif
                            </td>
                        @endforeach
                        <td class="px-1 py-1 border-b border-gray-200 text-center text-[10px] text-gray-600 sticky right-0 bg-white group-hover:bg-slate-50 z-10">
                            <span class="text-emerald-600 font-semibold">{{ $rowPresent }}</span>/<span class="text-amber-600">{{ $rowHalf }}</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3 border-t border-gray-100 bg-gray-50 text-xs text-gray-500 flex flex-wrap gap-4 justify-between">
        <span>Click cell: Unmarked → Present → Half → Leave → Absent → clear. Future &amp; pre-join dates are locked.</span>
        <span id="att-save-status" class="text-indigo-600 font-medium"></span>
    </div>
</div>
@endif
</div>
