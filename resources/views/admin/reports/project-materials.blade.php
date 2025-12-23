@extends('admin.layout')

@section('title', 'Project Material Report')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Project Material Report</h1>
    <p class="mt-2 text-gray-600">Track material consumption, cost, and supplier coverage by project.</p>
</div>

<div class="bg-white shadow-lg rounded-lg p-6 mb-6">
    <form method="GET" action="{{ route('admin.reports.project-materials') }}">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">From Delivery Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">To Delivery Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Project</label>
                <select name="project_name" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All projects</option>
                    @foreach($projectOptions as $project)
                        <option value="{{ $project->name }}" {{ $projectName === $project->name ? 'selected' : '' }}>
                            {{ $project->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Material Name</label>
                <select name="material_name" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All materials</option>
                    @foreach($materialOptions as $materialOption)
                        <option value="{{ $materialOption->name }}" {{ $materialName === $materialOption->name ? 'selected' : '' }}>
                            {{ $materialOption->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
                <select name="supplier_name" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All suppliers</option>
                    @foreach($supplierOptions as $supplierOption)
                        <option value="{{ $supplierOption->name }}" {{ $supplierName === $supplierOption->name ? 'selected' : '' }}>
                            {{ $supplierOption->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="mt-4 flex flex-wrap gap-3">
            <a href="{{ route('admin.reports.project-materials') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50">Reset</a>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Apply Filters</button>
            <a href="{{ route('admin.reports.project-materials.export', request()->all()) }}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Export Excel</a>
        </div>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    <div class="bg-white shadow rounded-lg p-4">
        <p class="text-sm text-gray-500">Deliveries</p>
        <p class="text-2xl font-semibold text-gray-900 mt-1">{{ number_format($overall['deliveries']) }}</p>
    </div>
    <div class="bg-white shadow rounded-lg p-4">
        <p class="text-sm text-gray-500">Total Quantity</p>
        <p class="text-2xl font-semibold text-gray-900 mt-1">{{ number_format($overall['total_quantity'] ?? 0, 2) }}</p>
    </div>
    <div class="bg-white shadow rounded-lg p-4">
        <p class="text-sm text-gray-500">Total Cost</p>
        <p class="text-2xl font-semibold text-gray-900 mt-1">₹{{ number_format($overall['total_cost'] ?? 0, 2) }}</p>
    </div>
    <div class="bg-white shadow rounded-lg p-4">
        <p class="text-sm text-gray-500">Projects Covered</p>
        <p class="text-2xl font-semibold text-gray-900 mt-1">{{ number_format($overall['projects']) }}</p>
    </div>
    <div class="bg-white shadow rounded-lg p-4">
        <p class="text-sm text-gray-500">Suppliers Involved</p>
        <p class="text-2xl font-semibold text-gray-900 mt-1">{{ number_format($overall['suppliers']) }}</p>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <div class="xl:col-span-2 bg-white shadow rounded-lg">
        <div class="p-4 border-b">
            <h2 class="text-lg font-semibold text-gray-900">Project Consumption Summary</h2>
            <p class="text-sm text-gray-500">Aggregated delivery stats by project</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Deliveries</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Received</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Used</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Remaining</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Cost</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Suppliers</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($projectSummary as $summary)
                        <tr>
                            <td class="px-4 py-2 text-sm text-gray-900">{{ $summary->project_name ?? 'Not Assigned' }}</td>
                            <td class="px-4 py-2 text-sm text-center text-gray-700">{{ number_format($summary->deliveries) }}</td>
                            <td class="px-4 py-2 text-sm text-right text-gray-700">{{ number_format($summary->total_quantity ?? 0, 2) }}</td>
                            <td class="px-4 py-2 text-sm text-right text-gray-700">{{ number_format($summary->total_used ?? 0, 2) }}</td>
                            <td class="px-4 py-2 text-sm text-right text-gray-700">{{ number_format($summary->total_remaining ?? 0, 2) }}</td>
                            <td class="px-4 py-2 text-sm text-right text-gray-900 font-semibold">₹{{ number_format($summary->total_cost ?? 0, 2) }}</td>
                            <td class="px-4 py-2 text-sm text-center text-gray-700">{{ number_format($summary->supplier_count ?? 0) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-4 text-center text-sm text-gray-500">No data available for the selected filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white shadow rounded-lg">
            <div class="p-4 border-b">
                <h3 class="text-base font-semibold text-gray-900">Top Materials (by cost)</h3>
            </div>
            <div class="p-4">
                <ul class="space-y-3">
                    @forelse($topMaterials as $material)
                        <li class="flex justify-between text-sm">
                            <span class="text-gray-700">{{ $material->material_name ?? 'Not specified' }}</span>
                            <span class="text-gray-900 font-medium">₹{{ number_format($material->total_cost ?? 0, 2) }}</span>
                        </li>
                    @empty
                        <li class="text-sm text-gray-500">No material data.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg">
            <div class="p-4 border-b">
                <h3 class="text-base font-semibold text-gray-900">Top Suppliers (by cost)</h3>
            </div>
            <div class="p-4">
                <ul class="space-y-3">
                    @forelse($topSuppliers as $supplier)
                        <li class="flex justify-between text-sm">
                            <span class="text-gray-700">{{ $supplier->supplier_name ?? 'Not specified' }}</span>
                            <span class="text-gray-900 font-medium">₹{{ number_format($supplier->total_cost ?? 0, 2) }}</span>
                        </li>
                    @empty
                        <li class="text-sm text-gray-500">No supplier data.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg">
            <div class="p-4 border-b">
                <h3 class="text-base font-semibold text-gray-900">Recent Deliveries</h3>
            </div>
            <div class="p-4">
                <ul class="space-y-3">
                    @forelse($recentDeliveries as $delivery)
                        <li class="text-sm text-gray-700">
                            <div class="flex justify-between">
                                <span>{{ $delivery->material_name }} → {{ $delivery->project_name ?? 'Not Assigned' }}</span>
                                <span class="text-gray-500">{{ optional($delivery->delivery_date)->format('d M Y') ?? '-' }}</span>
                            </div>
                            <div class="text-xs text-gray-500">Qty: {{ number_format($delivery->quantity_received ?? 0, 2) }} {{ $delivery->unit }} · ₹{{ number_format($delivery->total_cost ?? 0, 2) }}</div>
                        </li>
                    @empty
                        <li class="text-sm text-gray-500">No recent deliveries found.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

