@extends('admin.layout')

@section('title', 'Vehicle Documents — ' . $vehicle->vehicle_number)

@section('content')
<div class="mb-4 flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Documents &amp; Expiry History</h1>
        <p class="text-gray-600 mt-1">{{ $vehicle->vehicle_number }} · {{ $vehicle->type_label }} · {{ $vehicle->project->name ?? 'No project' }}</p>
    </div>
    <div class="flex gap-2 flex-wrap">
        <a href="{{ route('admin.vehicles.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 text-sm">Back to Vehicles</a>
        <button type="button" onclick="openUploadModal()" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm">
            <i class="bi bi-cloud-upload me-1"></i> Upload / Renew
        </button>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
    @foreach($vehicle->documentStatuses() as $doc)
        @php
            $badge = match ($doc['status']) {
                'expired' => 'bg-red-100 text-red-800 border-red-200',
                'expiring' => 'bg-amber-100 text-amber-800 border-amber-200',
                'valid' => 'bg-green-100 text-green-800 border-green-200',
                default => 'bg-gray-100 text-gray-600 border-gray-200',
            };
        @endphp
        <div class="rounded-lg border p-3 {{ $badge }}">
            <div class="text-xs font-semibold uppercase tracking-wide opacity-80">{{ $doc['label'] }}</div>
            <div class="text-lg font-bold mt-1">{{ $doc['date_label'] ?? 'Not set' }}</div>
            @if($doc['status'] === 'expired')
                <div class="text-xs mt-1 font-semibold">Expired</div>
            @elseif($doc['status'] === 'expiring')
                <div class="text-xs mt-1 font-semibold">Due in {{ $doc['days'] }} days</div>
            @endif
        </div>
    @endforeach
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <div class="xl:col-span-2">
        <div class="bg-white shadow rounded-lg p-4">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Uploaded documents</h2>
                <select id="doc-type-filter" class="border rounded-lg px-3 py-1.5 text-sm">
                    <option value="all">All types</option>
                    @foreach($types as $key => $type)
                        <option value="{{ $key }}">{{ $type['label'] }}</option>
                    @endforeach
                </select>
            </div>

            @if($documents->isEmpty())
                <div id="docs-empty" class="text-center py-10 text-gray-500">
                    <i class="bi bi-folder2-open text-4xl"></i>
                    <p class="mt-2">No documents uploaded yet. Upload insurance, bluebook, service bills, etc.</p>
                </div>
                <div id="docs-grid" class="grid grid-cols-1 sm:grid-cols-2 gap-4 hidden"></div>
            @else
                <div id="docs-empty" class="text-center py-10 text-gray-500 hidden">
                    <p>No documents for this filter.</p>
                </div>
                <div id="docs-grid" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($documents as $doc)
                        @include('admin.vehicles.partials.document-card', ['doc' => $doc, 'vehicle' => $vehicle])
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div>
        <div class="bg-white shadow rounded-lg p-4">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Renewal &amp; service history</h2>
            @if($renewals->isEmpty())
                <p class="text-sm text-gray-500">History will appear when you upload or renew documents.</p>
            @else
                <div class="space-y-4 max-h-[640px] overflow-y-auto" id="renewal-history">
                    @foreach($renewals as $renewal)
                        <div class="border-l-4 border-indigo-400 pl-3 py-1">
                            <div class="text-sm font-semibold text-gray-900">{{ $renewal->getTypeLabel() }}</div>
                            <div class="text-xs text-gray-500">{{ $renewal->renewed_at?->format('M d, Y H:i') }} · {{ $renewal->renewer?->name ?? 'System' }}</div>
                            <div class="text-sm text-gray-700 mt-1">
                                @if($renewal->previous_expiry || $renewal->new_expiry)
                                    Expiry:
                                    {{ $renewal->previous_expiry?->format('M d, Y') ?? '—' }}
                                    →
                                    <span class="font-semibold">{{ $renewal->new_expiry?->format('M d, Y') ?? '—' }}</span>
                                @endif
                                @if($renewal->service_date)
                                    <div>Service date: {{ $renewal->service_date->format('M d, Y') }}</div>
                                @endif
                                @if($renewal->new_policy_number)
                                    <div>Policy: {{ $renewal->new_policy_number }}</div>
                                @endif
                                @if($renewal->cost)
                                    <div>Cost: {{ number_format((float) $renewal->cost, 2) }}</div>
                                @endif
                            </div>
                            @if($renewal->notes)
                                <p class="text-xs text-gray-500 mt-1 italic">{{ $renewal->notes }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<div id="uploadModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-5 border-b">
            <h3 class="text-lg font-semibold">Upload / Renew Document</h3>
            <button type="button" onclick="closeUploadModal()" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>
        <form id="uploadForm" class="p-5 space-y-4" enctype="multipart/form-data">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Document type <span class="text-red-500">*</span></label>
                <select name="document_type" id="upload-document-type" required class="w-full border rounded-lg px-3 py-2" onchange="toggleServiceFields()">
                    @foreach($types as $key => $type)
                        <option value="{{ $key }}">{{ $type['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Label <span class="text-red-500">*</span></label>
                <input type="text" name="label" id="upload-label" required class="w-full border rounded-lg px-3 py-2" placeholder="e.g. Insurance renewal 2026">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">File <span class="text-red-500">*</span></label>
                <input type="file" name="file" required accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div id="expiry-field-wrap">
                <label class="block text-sm font-medium text-gray-700 mb-1" id="expiry-label">Expiry date</label>
                <input type="date" name="expiry_date" id="upload-expiry-date" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div id="service-date-wrap" class="hidden">
                <label class="block text-sm font-medium text-gray-700 mb-1">Service date (last service)</label>
                <input type="date" name="service_date" id="upload-service-date" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div id="policy-field-wrap">
                <label class="block text-sm font-medium text-gray-700 mb-1">Policy / reference no.</label>
                <input type="text" name="policy_number" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cost (optional)</label>
                <input type="number" step="0.01" min="0" name="cost" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" rows="2" class="w-full border rounded-lg px-3 py-2"></textarea>
            </div>
            <p id="upload-error" class="text-sm text-red-600 hidden"></p>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeUploadModal()" class="px-4 py-2 border rounded-lg">Cancel</button>
                <button type="submit" id="upload-submit-btn" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Save</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const vehicleId = {{ $vehicle->id }};
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

function openUploadModal() {
    document.getElementById('uploadForm').reset();
    document.getElementById('upload-error').classList.add('hidden');
    toggleServiceFields();
    document.getElementById('uploadModal').classList.remove('hidden');
}

function closeUploadModal() {
    document.getElementById('uploadModal').classList.add('hidden');
}

function toggleServiceFields() {
    const type = document.getElementById('upload-document-type').value;
    const isService = type === 'service';
    const isInsurance = type === 'insurance';
    document.getElementById('service-date-wrap').classList.toggle('hidden', !isService);
    document.getElementById('policy-field-wrap').classList.toggle('hidden', !isInsurance);
    document.getElementById('expiry-label').textContent = isService ? 'Next service date' : 'Expiry date';
}

document.getElementById('doc-type-filter')?.addEventListener('change', function() {
    const value = this.value;
    let visible = 0;
    document.querySelectorAll('.doc-card-wrap').forEach(card => {
        const show = value === 'all' || card.dataset.type === value;
        card.classList.toggle('hidden', !show);
        if (show) visible++;
    });
    document.getElementById('docs-empty').classList.toggle('hidden', visible > 0);
    document.getElementById('docs-grid').classList.toggle('hidden', visible === 0 && value !== 'all');
});

document.getElementById('uploadForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('upload-submit-btn');
    const err = document.getElementById('upload-error');
    btn.disabled = true;
    err.classList.add('hidden');

    fetch(`/admin/vehicles/${vehicleId}/documents`, {
        method: 'POST',
        body: new FormData(this),
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
    })
    .then(r => r.json().then(data => ({ ok: r.ok, data })))
    .then(({ ok, data }) => {
        if (!ok || !data.success) throw new Error(data.message || 'Upload failed');
        window.location.reload();
    })
    .catch(error => {
        err.textContent = error.message;
        err.classList.remove('hidden');
        btn.disabled = false;
    });
});

function deleteDocument(docId) {
    if (!confirm('Delete this document file?')) return;
    fetch(`/admin/vehicles/${vehicleId}/documents/${docId}`, {
        method: 'DELETE',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.querySelector(`.doc-card-wrap[data-id="${docId}"]`)?.remove();
        } else {
            alert(data.message || 'Delete failed');
        }
    });
}
</script>
@endpush
@endsection
