<div id="activity-log-list-wrap" class="bg-white rounded-xl shadow border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-gray-700">
                <tr>
                    <th class="px-4 py-3 text-left">SN</th>
                    <th class="px-4 py-3 text-left">Time</th>
                    <th class="px-4 py-3 text-left">User</th>
                    <th class="px-4 py-3 text-left">Company</th>
                    <th class="px-4 py-3 text-left">Action</th>
                    <th class="px-4 py-3 text-left">Details</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($logs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-700 font-medium whitespace-nowrap">
                            {{ ($logs->currentPage() - 1) * $logs->perPage() + $loop->iteration }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                            <div class="font-medium text-gray-900">{{ $log->created_at?->format('Y-m-d H:i:s') }}</div>
                            <div class="text-xs text-gray-500">{{ $log->created_at?->diffForHumans() }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900">{{ optional($log->user)->name ?: 'Unknown user' }}</div>
                            <div class="text-xs text-gray-500">{{ optional($log->user)->email ?: '—' }}</div>
                        </td>
                        <td class="px-4 py-3 text-gray-700">{{ optional($log->company)->name ?: '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            <div class="max-w-md truncate" title="{{ $log->url }}">{{ $log->description ?: $log->url }}</div>
                            <div class="text-xs text-gray-500">IP: {{ $log->ip_address ?: '—' }} | HTTP {{ $log->status_code ?: '—' }}</div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-gray-500">
                            No activity logs found for the selected filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pagination :paginator="$logs" wrapper-class="p-4" />
</div>

