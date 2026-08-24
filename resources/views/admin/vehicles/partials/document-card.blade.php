<div class="doc-card-wrap border rounded-lg overflow-hidden {{ $doc->is_current ? 'ring-2 ring-indigo-200' : '' }}" data-type="{{ $doc->document_type }}" data-id="{{ $doc->id }}">
    <div class="bg-gray-50 h-32 flex items-center justify-center overflow-hidden">
        @if($doc->isImage())
            <img src="{{ $doc->getUrl() }}" alt="{{ $doc->label }}" class="max-h-32 object-contain">
        @elseif($doc->isPdf())
            <div class="text-center text-red-600">
                <i class="bi bi-file-earmark-pdf text-4xl"></i>
                <div class="text-xs mt-1">PDF</div>
            </div>
        @else
            <i class="bi bi-file-earmark text-4xl text-gray-400"></i>
        @endif
    </div>
    <div class="p-3">
        <div class="flex items-center gap-2 mb-1">
            <span class="text-xs px-2 py-0.5 rounded bg-indigo-100 text-indigo-700">{{ $doc->getTypeLabel() }}</span>
            @if($doc->is_current)
                <span class="text-xs px-2 py-0.5 rounded bg-green-100 text-green-700">Current</span>
            @endif
        </div>
        <h3 class="font-semibold text-sm text-gray-900">{{ $doc->label }}</h3>
        <p class="text-xs text-gray-500 mt-1">{{ $doc->original_name }} · {{ $doc->getFileSizeFormatted() }}</p>
        @if($doc->expiry_date)
            <p class="text-xs mt-1">Expires: <strong>{{ $doc->expiry_date->format('M d, Y') }}</strong></p>
        @endif
        @if($doc->service_date)
            <p class="text-xs">Service: {{ $doc->service_date->format('M d, Y') }}</p>
        @endif
        @if($doc->policy_number)
            <p class="text-xs">Policy: {{ $doc->policy_number }}</p>
        @endif
        <p class="text-xs text-gray-400 mt-1">{{ $doc->created_at->format('Y-m-d') }} · {{ $doc->uploader?->name }}</p>
        <div class="flex gap-2 mt-3">
            <a href="{{ route('admin.vehicles.vehicle-documents.download', [$vehicle, $doc]) }}" class="btn btn-sm btn-outline-primary" title="Download"><i class="bi bi-download"></i></a>
            @if($doc->isPdf() || $doc->isImage())
                <a href="{{ $doc->getUrl() }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="View"><i class="bi bi-eye"></i></a>
            @endif
            <button type="button" onclick="deleteDocument({{ $doc->id }})" class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
        </div>
    </div>
</div>
