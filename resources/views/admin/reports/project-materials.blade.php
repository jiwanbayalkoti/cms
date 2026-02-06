@extends('admin.layout')

@section('title', 'Project Material Report')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Project Material Report</h1>
    <p class="mt-2 text-gray-600">Track material consumption, cost, and supplier coverage by project.</p>
</div>

<div class="bg-white shadow-lg rounded-lg p-6 mb-6">
    <form id="projectMaterialsFilterForm" method="GET" action="{{ route('admin.reports.project-materials') }}">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">From Delivery Date</label>
                <input type="date" name="start_date" id="pm_start_date" value="{{ $startDate }}" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" onchange="applyProjectMaterialsFilters()">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">To Delivery Date</label>
                <input type="date" name="end_date" id="pm_end_date" value="{{ $endDate }}" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" onchange="applyProjectMaterialsFilters()">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Project</label>
                <select name="project_name" id="pm_project_name" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" onchange="applyProjectMaterialsFilters()">
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
                <select name="material_name" id="pm_material_name" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" onchange="applyProjectMaterialsFilters()">
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
                <select name="supplier_name" id="pm_supplier_name" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" onchange="applyProjectMaterialsFilters()">
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
            <button type="button" onclick="resetProjectMaterialsFilters()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50">Reset</button>
            <a id="projectMaterialsExportLink" href="{{ route('admin.reports.project-materials.export', request()->all()) }}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Export Excel</a>
        </div>
    </form>
</div>

<div id="project-materials-report-content">
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6" id="pm-overall-cards">
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
                <tbody class="bg-white divide-y divide-gray-100" id="pm-project-summary-tbody">
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
                <ul class="space-y-3" id="pm-top-materials">
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
                <ul class="space-y-3" id="pm-top-suppliers">
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
                <ul class="space-y-3" id="pm-recent-deliveries">
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
</div>

<script>
(function() {
    var baseUrl = '{{ route("admin.reports.project-materials") }}';
    var exportBaseUrl = '{{ route("admin.reports.project-materials.export", []) }}';
    var defaultStart = '{{ date("Y-01-01") }}';
    var defaultEnd = '{{ date("Y-m-d") }}';

    function numFmt(n, d) {
        d = d === undefined ? 0 : d;
        if (n == null || isNaN(n)) return (0).toFixed(d);
        return Number(n).toLocaleString('en-IN', { minimumFractionDigits: d, maximumFractionDigits: d });
    }

    function dateFmt(s) {
        if (!s) return '-';
        var d = new Date(s);
        if (isNaN(d.getTime())) return '-';
        var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        return ('0' + d.getDate()).slice(-2) + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
    }

    window.applyProjectMaterialsFilters = function() {
        var form = document.getElementById('projectMaterialsFilterForm');
        var q = new URLSearchParams(new FormData(form));
        var url = baseUrl + (q.toString() ? '?' + q.toString() : '');
        document.getElementById('projectMaterialsExportLink').href = exportBaseUrl + (q.toString() ? '?' + q.toString() : '');

        var content = document.getElementById('project-materials-report-content');
        if (content) content.classList.add('opacity-60', 'pointer-events-none');

        fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                updateProjectMaterialsReport(data);
            })
            .catch(function(err) {
                console.error(err);
                if (content) content.classList.remove('opacity-60', 'pointer-events-none');
            })
            .then(function() {
                if (content) content.classList.remove('opacity-60', 'pointer-events-none');
            });
    };

    window.resetProjectMaterialsFilters = function() {
        document.getElementById('pm_start_date').value = defaultStart;
        document.getElementById('pm_end_date').value = defaultEnd;
        document.getElementById('pm_project_name').value = '';
        document.getElementById('pm_material_name').value = '';
        document.getElementById('pm_supplier_name').value = '';
        applyProjectMaterialsFilters();
    };

    function updateProjectMaterialsReport(data) {
        var o = data.overall || {};
        var cards = document.getElementById('pm-overall-cards');
        if (cards && cards.children.length >= 5) {
            cards.children[0].querySelector('.text-2xl').textContent = numFmt(o.deliveries);
            cards.children[1].querySelector('.text-2xl').textContent = numFmt(o.total_quantity || 0, 2);
            cards.children[2].querySelector('.text-2xl').textContent = '₹' + numFmt(o.total_cost || 0, 2);
            cards.children[3].querySelector('.text-2xl').textContent = numFmt(o.projects);
            cards.children[4].querySelector('.text-2xl').textContent = numFmt(o.suppliers);
        }

        var tbody = document.getElementById('pm-project-summary-tbody');
        if (tbody) {
            var rows = (data.projectSummary || []).map(function(s) {
                return '<tr><td class="px-4 py-2 text-sm text-gray-900">' + (s.project_name || 'Not Assigned') + '</td>' +
                    '<td class="px-4 py-2 text-sm text-center text-gray-700">' + numFmt(s.deliveries) + '</td>' +
                    '<td class="px-4 py-2 text-sm text-right text-gray-700">' + numFmt(s.total_quantity || 0, 2) + '</td>' +
                    '<td class="px-4 py-2 text-sm text-right text-gray-700">' + numFmt(s.total_used || 0, 2) + '</td>' +
                    '<td class="px-4 py-2 text-sm text-right text-gray-700">' + numFmt(s.total_remaining || 0, 2) + '</td>' +
                    '<td class="px-4 py-2 text-sm text-right text-gray-900 font-semibold">₹' + numFmt(s.total_cost || 0, 2) + '</td>' +
                    '<td class="px-4 py-2 text-sm text-center text-gray-700">' + numFmt(s.supplier_count || 0) + '</td></tr>';
            });
            tbody.innerHTML = rows.length ? rows.join('') : '<tr><td colspan="7" class="px-4 py-4 text-center text-sm text-gray-500">No data available for the selected filters.</td></tr>';
        }

        var topMat = document.getElementById('pm-top-materials');
        if (topMat) {
            var list = (data.topMaterials || []).map(function(m) {
                return '<li class="flex justify-between text-sm"><span class="text-gray-700">' + (m.material_name || 'Not specified') + '</span><span class="text-gray-900 font-medium">₹' + numFmt(m.total_cost || 0, 2) + '</span></li>';
            });
            topMat.innerHTML = list.length ? list.join('') : '<li class="text-sm text-gray-500">No material data.</li>';
        }

        var topSup = document.getElementById('pm-top-suppliers');
        if (topSup) {
            var list = (data.topSuppliers || []).map(function(s) {
                return '<li class="flex justify-between text-sm"><span class="text-gray-700">' + (s.supplier_name || 'Not specified') + '</span><span class="text-gray-900 font-medium">₹' + numFmt(s.total_cost || 0, 2) + '</span></li>';
            });
            topSup.innerHTML = list.length ? list.join('') : '<li class="text-sm text-gray-500">No supplier data.</li>';
        }

        var recent = document.getElementById('pm-recent-deliveries');
        if (recent) {
            var list = (data.recentDeliveries || []).map(function(d) {
                var dateStr = d.delivery_date ? dateFmt(d.delivery_date) : '-';
                return '<li class="text-sm text-gray-700"><div class="flex justify-between"><span>' + (d.material_name || '') + ' → ' + (d.project_name || 'Not Assigned') + '</span><span class="text-gray-500">' + dateStr + '</span></div><div class="text-xs text-gray-500">Qty: ' + numFmt(d.quantity_received || 0, 2) + ' ' + (d.unit || '') + ' · ₹' + numFmt(d.total_cost || 0, 2) + '</div></li>';
            });
            recent.innerHTML = list.length ? list.join('') : '<li class="text-sm text-gray-500">No recent deliveries found.</li>';
        }
    }
})();
</script>
@endsection

