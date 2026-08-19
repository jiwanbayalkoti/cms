@extends('admin.layout')

@section('title', 'Vehicle Documents')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Vehicle Documents</h1>
        <p class="text-gray-600 mt-1">Bluebook, insurance, fitness, permit, tax token, PUC and next service</p>
    </div>
    <a href="{{ route('admin.vehicles.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Back to vehicles</a>
</div>

<div class="mb-4 bg-white shadow rounded-lg p-3 flex flex-wrap gap-2">
    @php
        $filters = [
            'attention' => 'Needs attention',
            'expired' => 'Expired',
            'expiring' => 'Expiring in 30 days',
            'all' => 'All vehicles',
        ];
    @endphp
    @foreach($filters as $key => $label)
        <a href="{{ route('admin.vehicles.documents', ['filter' => $key]) }}"
           class="px-3 py-1.5 rounded-full text-sm {{ $filter === $key ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

<div class="bg-white shadow-lg rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vehicle</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    @foreach($documentDefinitions as $label)
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $label }}</th>
                    @endforeach
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($vehicles as $vehicle)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="text-sm font-semibold text-gray-900">{{ $vehicle->vehicle_number }}</div>
                            <div class="text-xs text-gray-500">{{ $vehicle->project->name ?? $vehicle->ownership_label }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $vehicle->type_label }}</td>
                        @foreach($vehicle->documentStatuses() as $doc)
                            @php
                                $class = [
                                    'expired' => 'text-red-700 bg-red-50',
                                    'expiring' => 'text-amber-800 bg-amber-50',
                                    'valid' => 'text-green-700',
                                    'missing' => 'text-gray-400',
                                ][$doc['status']] ?? 'text-gray-500';
                            @endphp
                            <td class="px-4 py-3 text-xs">
                                <span class="px-2 py-1 rounded {{ $class }}">
                                    {{ $doc['date_label'] ?? '—' }}
                                    @if($doc['status'] === 'expired')
                                        <span class="block font-semibold">Expired</span>
                                    @elseif($doc['status'] === 'expiring')
                                        <span class="block font-semibold">{{ $doc['days'] }} days</span>
                                    @endif
                                </span>
                            </td>
                        @endforeach
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.vehicles.index', ['search' => $vehicle->vehicle_number]) }}" class="text-indigo-600 text-sm hover:underline">Open</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 3 + count($documentDefinitions) }}" class="px-4 py-8 text-center text-sm text-gray-500">
                            No vehicles match this filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
