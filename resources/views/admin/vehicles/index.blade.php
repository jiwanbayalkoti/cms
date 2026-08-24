@extends('admin.layout')

@section('title', 'Vehicle Management')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Vehicles</h1>
        <p class="text-gray-600 mt-1">Company fleet and hired vehicle registry</p>
    </div>
    <div class="flex gap-2">
        @if($vehicles->total() > 0)
            <a href="{{ route('admin.vehicles.export', request()->query()) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-200">
                <i class="bi bi-file-earmark-excel-fill me-1"></i> Export
            </a>
        @endif
        <button type="button" onclick="openCreateVehicleModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition duration-200">
            <i class="bi bi-plus-lg me-1"></i> <span class="vehicle-btn-text">Add Vehicle</span>
        </button>
    </div>
</div>

@if($expiredCount > 0 || $expiringCount > 0)
<div class="mb-4 rounded-lg border {{ $expiredCount > 0 ? 'border-red-200 bg-red-50' : 'border-amber-200 bg-amber-50' }} p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
    <div class="text-sm {{ $expiredCount > 0 ? 'text-red-800' : 'text-amber-800' }}">
        @if($expiredCount > 0)
            <strong>{{ $expiredCount }}</strong> vehicle{{ $expiredCount === 1 ? '' : 's' }} with expired documents.
        @endif
        @if($expiringCount > 0)
            <strong>{{ $expiringCount }}</strong> with documents expiring within 30 days.
        @endif
    </div>
    <a href="{{ route('admin.vehicles.documents') }}" class="text-sm font-semibold {{ $expiredCount > 0 ? 'text-red-700' : 'text-amber-700' }} hover:underline">
        View documents
    </a>
</div>
@endif

<div class="mb-4 bg-white shadow-lg rounded-lg p-4">
    <form method="GET" action="{{ route('admin.vehicles.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Number, driver..."
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Project</label>
            <select name="project_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All projects</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}" {{ (string) request('project_id') === (string) $project->id ? 'selected' : '' }}>{{ $project->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
            <select name="vehicle_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All types</option>
                @foreach($vehicleTypes as $key => $label)
                    <option value="{{ $key }}" {{ request('vehicle_type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Ownership</label>
            <select name="ownership" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All</option>
                @foreach($ownershipOptions as $key => $label)
                    <option value="{{ $key }}" {{ request('ownership') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">All</option>
                    @foreach($statusOptions as $key => $label)
                        <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="mt-6 px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900">Filter</button>
        </div>
    </form>
</div>

<div class="bg-white shadow-lg rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">SN</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vehicle</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ownership</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Project</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Driver</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Next Service</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Documents</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase text-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($vehicles as $vehicle)
                    @php $docStatus = $vehicle->worst_document_status; @endphp
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ ($vehicles->currentPage() - 1) * $vehicles->perPage() + $loop->iteration }}</td>
                        <td class="px-4 py-3">
                            <div class="text-sm font-semibold text-gray-900">{{ $vehicle->vehicle_number }}</div>
                            @if($vehicle->ownership === 'hired' && $vehicle->supplier)
                                <div class="text-xs text-gray-500">{{ $vehicle->supplier->name }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $vehicle->type_label }}</td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $vehicle->ownership === 'own' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                {{ $vehicle->ownership_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $vehicle->project->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            {{ $vehicle->driverStaff->name ?? $vehicle->driver_name ?? '—' }}
                            @if($vehicle->driver_contact)
                                <div class="text-xs text-gray-500">{{ $vehicle->driver_contact }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $statusClass = [
                                    'active' => 'bg-green-100 text-green-800',
                                    'under_repair' => 'bg-amber-100 text-amber-800',
                                    'sold' => 'bg-gray-100 text-gray-800',
                                    'inactive' => 'bg-gray-100 text-gray-600',
                                ][$vehicle->status] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $statusClass }}">{{ $vehicle->status_label }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            @if($vehicle->next_service_date)
                                @php $serviceStatus = collect($vehicle->documentStatuses())->firstWhere('field', 'next_service_date'); @endphp
                                <div class="{{ ($serviceStatus['status'] ?? '') === 'expired' ? 'text-red-700 font-semibold' : (($serviceStatus['status'] ?? '') === 'expiring' ? 'text-amber-700 font-semibold' : 'text-gray-700') }}">
                                    {{ $vehicle->next_service_date->format('M d, Y') }}
                                </div>
                                @if($vehicle->last_service_date)
                                    <div class="text-xs text-gray-500">Last: {{ $vehicle->last_service_date->format('M d, Y') }}</div>
                                @endif
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($docStatus === 'expired')
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">Expired</span>
                            @elseif($docStatus === 'expiring')
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">Due soon</span>
                            @elseif($docStatus === 'valid')
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">Valid</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">Not set</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="d-flex gap-1 text-nowrap">
                                <a href="{{ route('admin.vehicles.vehicle-documents', $vehicle) }}" class="btn btn-outline-success btn-sm" title="Documents &amp; expiry history"><i class="bi bi-folder2-open"></i></a>
                                <button type="button" onclick="openViewVehicleModal({{ $vehicle->id }})" class="btn btn-outline-primary btn-sm" title="View"><i class="bi bi-eye"></i></button>
                                <button type="button" onclick="openEditVehicleModal({{ $vehicle->id }})" class="btn btn-outline-warning btn-sm" title="Edit"><i class="bi bi-pencil"></i></button>
                                <button type="button" onclick="showDeleteVehicleConfirmation({{ $vehicle->id }}, @json($vehicle->vehicle_number))" class="btn btn-outline-danger btn-sm" title="Delete"><i class="bi bi-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-4 py-8 text-center text-sm text-gray-500">
                            No vehicles found. <button type="button" onclick="openCreateVehicleModal()" class="text-indigo-600 hover:text-indigo-900">Add one now</button>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<x-pagination :paginator="$vehicles" wrapper-class="mt-4" />

<div id="deleteVehicleConfirmationModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-2xl max-w-md w-full" onclick="event.stopPropagation()">
        <div class="p-6">
            <h3 class="text-xl font-semibold text-gray-900 text-center mb-2">Delete Vehicle</h3>
            <p class="text-gray-600 text-center mb-6">
                Delete <span class="font-semibold text-gray-900" id="delete-vehicle-number"></span>? This cannot be undone.
            </p>
            <div class="flex space-x-3">
                <button type="button" onclick="closeDeleteVehicleConfirmation()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="button" onclick="confirmDeleteVehicle()" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Delete</button>
            </div>
        </div>
    </div>
</div>

<div id="vehicleModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-6 border-b">
            <h3 class="text-xl font-semibold text-gray-900" id="vehicle-modal-title">Add Vehicle</h3>
            <button type="button" onclick="closeVehicleModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6">
            <form id="vehicleForm" onsubmit="submitVehicleForm(event)" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_method" id="vehicle-method" value="POST">

                <h4 class="text-sm font-semibold text-gray-800 mb-3">Vehicle details</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle number <span class="text-red-500">*</span></label>
                        <input type="text" name="vehicle_number" id="vehicle-number" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <div class="field-error text-red-600 text-sm mt-1" data-field="vehicle_number" style="display:none;"></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type <span class="text-red-500">*</span></label>
                        <select name="vehicle_type" id="vehicle-type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select type</option>
                            @foreach($vehicleTypes as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="field-error text-red-600 text-sm mt-1" data-field="vehicle_type" style="display:none;"></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ownership <span class="text-red-500">*</span></label>
                        <select name="ownership" id="vehicle-ownership" required onchange="toggleSupplierField()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @foreach($ownershipOptions as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="supplier-field-wrap">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Supplier <span class="text-red-500 supplier-required">*</span></label>
                        <select name="supplier_id" id="vehicle-supplier-id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                        <div class="field-error text-red-600 text-sm mt-1" data-field="supplier_id" style="display:none;"></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Project</label>
                        <select name="project_id" id="vehicle-project-id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Optional</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                        <select name="status" id="vehicle-status" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @foreach($statusOptions as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Driver (staff)</label>
                        <select name="driver_staff_id" id="vehicle-driver-staff-id" onchange="fillDriverFromStaff()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">None</option>
                            @foreach($staffMembers as $member)
                                <option value="{{ $member->id }}" data-name="{{ $member->name }}" data-phone="{{ $member->phone }}">{{ $member->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Driver name</label>
                        <input type="text" name="driver_name" id="vehicle-driver-name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Driver contact</label>
                        <input type="text" name="driver_contact" id="vehicle-driver-contact" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Photo</label>
                        <input type="file" name="photo" id="vehicle-photo" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        <div class="field-error text-red-600 text-sm mt-1" data-field="photo" style="display:none;"></div>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea name="notes" id="vehicle-notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                    </div>
                </div>

                <h4 class="text-sm font-semibold text-gray-800 mt-6 mb-3">Documents &amp; expiry</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Insurance policy no.</label>
                        <input type="text" name="insurance_policy_no" id="vehicle-insurance-policy-no" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bluebook / registration expiry</label>
                        <input type="date" name="registration_expiry" id="vehicle-registration-expiry" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Insurance expiry</label>
                        <input type="date" name="insurance_expiry" id="vehicle-insurance-expiry" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fitness expiry</label>
                        <input type="date" name="fitness_expiry" id="vehicle-fitness-expiry" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Route permit expiry</label>
                        <input type="date" name="route_permit_expiry" id="vehicle-route-permit-expiry" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tax token expiry</label>
                        <input type="date" name="tax_token_expiry" id="vehicle-tax-token-expiry" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">PUC expiry</label>
                        <input type="date" name="puc_expiry" id="vehicle-puc-expiry" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Last service date</label>
                        <input type="date" name="last_service_date" id="vehicle-last-service-date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Next service date</label>
                        <input type="date" name="next_service_date" id="vehicle-next-service-date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <div class="field-error text-red-600 text-sm mt-1" data-field="next_service_date" style="display:none;"></div>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-4 mt-6">
                    <button type="button" onclick="closeVehicleModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700" id="vehicle-submit-btn">Save Vehicle</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="viewVehicleModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-6 border-b">
            <h3 class="text-xl font-semibold text-gray-900">Vehicle details</h3>
            <button type="button" onclick="closeViewVehicleModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6" id="view-vehicle-content"></div>
    </div>
</div>

<style>
    @media (max-width: 768px) {
        .vehicle-btn-text { display: none; }
    }
</style>

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
let currentVehicleId = null;
let deleteVehicleId = null;

function clearVehicleErrors() {
    document.querySelectorAll('#vehicleForm .field-error').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
}

function toggleSupplierField() {
    const hired = document.getElementById('vehicle-ownership').value === 'hired';
    const wrap = document.getElementById('supplier-field-wrap');
    wrap.style.display = hired ? 'block' : 'none';
    document.getElementById('vehicle-supplier-id').required = hired;
}

function fillDriverFromStaff() {
    const select = document.getElementById('vehicle-driver-staff-id');
    const option = select.options[select.selectedIndex];
    if (!select.value) return;
    document.getElementById('vehicle-driver-name').value = option.dataset.name || '';
    if (option.dataset.phone) {
        document.getElementById('vehicle-driver-contact').value = option.dataset.phone;
    }
}

function openCreateVehicleModal() {
    currentVehicleId = null;
    const form = document.getElementById('vehicleForm');
    form.reset();
    document.getElementById('vehicle-modal-title').textContent = 'Add Vehicle';
    document.getElementById('vehicle-method').value = 'POST';
    document.getElementById('vehicle-submit-btn').textContent = 'Save Vehicle';
    document.getElementById('vehicle-status').value = 'active';
    document.getElementById('vehicle-ownership').value = 'own';
    clearVehicleErrors();
    toggleSupplierField();
    document.getElementById('vehicleModal').classList.remove('hidden');
}

function openEditVehicleModal(id) {
    currentVehicleId = id;
    clearVehicleErrors();
    document.getElementById('vehicle-modal-title').textContent = 'Edit Vehicle';
    document.getElementById('vehicle-method').value = 'PUT';
    document.getElementById('vehicle-submit-btn').textContent = 'Update Vehicle';
    document.getElementById('vehicleModal').classList.remove('hidden');

    fetch(`/admin/vehicles/${id}/edit`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        const v = data.vehicle;
        document.getElementById('vehicle-number').value = v.vehicle_number || '';
        document.getElementById('vehicle-type').value = v.vehicle_type || '';
        document.getElementById('vehicle-ownership').value = v.ownership || 'own';
        document.getElementById('vehicle-supplier-id').value = v.supplier_id || '';
        document.getElementById('vehicle-project-id').value = v.project_id || '';
        document.getElementById('vehicle-status').value = v.status || 'active';
        document.getElementById('vehicle-driver-staff-id').value = v.driver_staff_id || '';
        document.getElementById('vehicle-driver-name').value = v.driver_name_raw || '';
        document.getElementById('vehicle-driver-contact').value = v.driver_contact || '';
        document.getElementById('vehicle-notes').value = v.notes || '';
        document.getElementById('vehicle-insurance-policy-no').value = v.insurance_policy_no || '';
        document.getElementById('vehicle-registration-expiry').value = v.registration_expiry || '';
        document.getElementById('vehicle-insurance-expiry').value = v.insurance_expiry || '';
        document.getElementById('vehicle-fitness-expiry').value = v.fitness_expiry || '';
        document.getElementById('vehicle-route-permit-expiry').value = v.route_permit_expiry || '';
        document.getElementById('vehicle-tax-token-expiry').value = v.tax_token_expiry || '';
        document.getElementById('vehicle-puc-expiry').value = v.puc_expiry || '';
        document.getElementById('vehicle-last-service-date').value = v.last_service_date || '';
        document.getElementById('vehicle-next-service-date').value = v.next_service_date || '';
        toggleSupplierField();
    })
    .catch(() => showVehicleNotification('Failed to load vehicle', 'error'));
}

function submitVehicleForm(e) {
    e.preventDefault();
    const submitBtn = document.getElementById('vehicle-submit-btn');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';
    clearVehicleErrors();

    const formData = new FormData(e.target);
    const url = currentVehicleId ? `/admin/vehicles/${currentVehicleId}` : '/admin/vehicles';
    if (currentVehicleId) formData.append('_method', 'PUT');

    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(async response => {
        const data = await response.json();
        if (data.success) {
            showVehicleNotification(data.message, 'success');
            closeVehicleModal();
            window.location.reload();
            return;
        }
        if (data.errors) {
            Object.keys(data.errors).forEach(field => {
                const errorEl = document.querySelector(`#vehicleForm .field-error[data-field="${field}"]`);
                if (errorEl) {
                    errorEl.textContent = data.errors[field][0];
                    errorEl.style.display = 'block';
                }
            });
        }
        showVehicleNotification(data.message || 'Validation failed', 'error');
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    })
    .catch(() => {
        showVehicleNotification('An error occurred while saving', 'error');
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
}

function closeVehicleModal() {
    document.getElementById('vehicleModal').classList.add('hidden');
    currentVehicleId = null;
}

function openViewVehicleModal(id) {
    const modal = document.getElementById('viewVehicleModal');
    const content = document.getElementById('view-vehicle-content');
    modal.classList.remove('hidden');
    content.innerHTML = '<p class="text-sm text-gray-500">Loading...</p>';

    fetch(`/admin/vehicles/${id}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        const v = data.vehicle;
        const docs = (v.documents || []).map(d => {
            const color = d.status === 'expired' ? 'text-red-700' : (d.status === 'expiring' ? 'text-amber-700' : (d.status === 'valid' ? 'text-green-700' : 'text-gray-500'));
            return `<div class="flex justify-between text-sm"><span>${d.label}</span><span class="${color}">${d.date_label || 'Not set'}</span></div>`;
        }).join('');
        content.innerHTML = `
            ${v.photo_url ? `<img src="${v.photo_url}" alt="" class="w-full h-40 object-cover rounded-lg mb-4">` : ''}
            <dl class="grid grid-cols-2 gap-3 text-sm mb-4">
                <div><dt class="text-gray-500">Number</dt><dd class="font-medium">${v.vehicle_number || ''}</dd></div>
                <div><dt class="text-gray-500">Type</dt><dd>${v.type_label || ''}</dd></div>
                <div><dt class="text-gray-500">Ownership</dt><dd>${v.ownership_label || ''}</dd></div>
                <div><dt class="text-gray-500">Status</dt><dd>${v.status_label || ''}</dd></div>
                <div><dt class="text-gray-500">Project</dt><dd>${v.project_name || '—'}</dd></div>
                <div><dt class="text-gray-500">Supplier</dt><dd>${v.supplier_name || '—'}</dd></div>
                <div><dt class="text-gray-500">Driver</dt><dd>${v.driver_name || '—'}</dd></div>
                <div><dt class="text-gray-500">Contact</dt><dd>${v.driver_contact || '—'}</dd></div>
                <div class="col-span-2"><dt class="text-gray-500">Insurance policy</dt><dd>${v.insurance_policy_no || '—'}</dd></div>
                <div class="col-span-2"><dt class="text-gray-500">Notes</dt><dd>${v.notes || '—'}</dd></div>
            </dl>
            <h4 class="text-sm font-semibold mb-2">Documents</h4>
            <div class="space-y-1 mb-4">${docs}</div>
            <a href="/admin/vehicles/${v.id}/documents/manage" class="inline-flex items-center px-3 py-2 text-sm font-semibold rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">Manage documents &amp; history</a>
        `;
    })
    .catch(() => { content.innerHTML = '<p class="text-red-600 text-sm">Failed to load vehicle.</p>'; });
}

function closeViewVehicleModal() {
    document.getElementById('viewVehicleModal').classList.add('hidden');
}

function showDeleteVehicleConfirmation(id, number) {
    deleteVehicleId = id;
    document.getElementById('delete-vehicle-number').textContent = number;
    document.getElementById('deleteVehicleConfirmationModal').classList.remove('hidden');
}

function closeDeleteVehicleConfirmation() {
    deleteVehicleId = null;
    document.getElementById('deleteVehicleConfirmationModal').classList.add('hidden');
}

function confirmDeleteVehicle() {
    if (!deleteVehicleId) return;
    fetch(`/admin/vehicles/${deleteVehicleId}`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showVehicleNotification(data.message, 'success');
            closeDeleteVehicleConfirmation();
            window.location.reload();
        } else {
            showVehicleNotification(data.message || 'Delete failed', 'error');
        }
    })
    .catch(() => showVehicleNotification('Delete failed', 'error'));
}

function showVehicleNotification(message, type = 'success') {
    const el = document.createElement('div');
    el.className = `fixed top-4 right-4 px-6 py-4 rounded-lg shadow-2xl z-50 text-white ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
    el.textContent = message;
    document.body.appendChild(el);
    setTimeout(() => el.remove(), 3000);
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeVehicleModal();
        closeViewVehicleModal();
        closeDeleteVehicleConfirmation();
    }
});

toggleSupplierField();
</script>
@endpush
@endsection
