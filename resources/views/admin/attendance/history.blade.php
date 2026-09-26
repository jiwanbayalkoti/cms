@extends('admin.layout')

@section('title', 'Attendance History')

@section('content')
<div class="mb-6 flex flex-wrap justify-between items-center gap-3">
    <h1 class="text-3xl font-bold text-gray-900">Attendance History</h1>
    <a href="{{ route('admin.attendance.index') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">Mark Attendance</a>
</div>

<div class="mb-4 bg-white shadow-lg rounded-lg p-4">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Start</label>
            <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">End</label>
            <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Worker</label>
            <select name="worker_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                <option value="">All</option>
                @foreach($workers as $w)
                    <option value="{{ $w->id }}" {{ (string)$workerId === (string)$w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Project</label>
            <select name="project_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                <option value="">All</option>
                @foreach($projects as $p)
                    <option value="{{ $p->id }}" {{ (string)$projectId === (string)$p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">Filter</button>
    </form>
</div>

<div class="bg-white shadow-lg rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Worker</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Project</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">OT</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($records as $row)
                <tr>
                    <td class="px-6 py-3 text-sm">{{ $row->date->format('M d, Y') }}</td>
                    <td class="px-6 py-3 text-sm font-medium">{{ $row->worker?->name }}</td>
                    <td class="px-6 py-3 text-sm">{{ $row->project?->name ?? '—' }}</td>
                    <td class="px-6 py-3 text-sm">
                        @php
                            $cls = match($row->status) {
                                'present' => 'bg-green-100 text-green-800',
                                'half_day' => 'bg-yellow-100 text-yellow-800',
                                'leave' => 'bg-blue-100 text-blue-800',
                                default => 'bg-red-100 text-red-800',
                            };
                        @endphp
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $cls }}">{{ $row->status_label }}</span>
                    </td>
                    <td class="px-6 py-3 text-sm">{{ $row->overtime_hours }}</td>
                    <td class="px-6 py-3 text-sm text-gray-500">{{ $row->notes ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">No attendance records.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<x-pagination :paginator="$records" wrapper-class="mt-4" />
@endsection
