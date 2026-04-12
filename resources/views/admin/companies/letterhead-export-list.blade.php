@extends('admin.layout')

@section('title', 'Letterhead exported PDFs')

@section('content')
<div class="mb-4 d-flex flex-wrap align-items-center justify-content-between gap-2">
    <div>
        <h2 class="h4 mb-1">Exported PDFs</h2>
        <p class="text-muted mb-0 small">{{ $company->name ?? 'Company' }} — saved letterhead PDFs from export.</p>
    </div>
    <a href="{{ route('admin.companies.letterhead') }}" class="btn btn-outline-secondary btn-keep-text">
        <i class="bi bi-pencil-square" aria-hidden="true"></i> Letterhead designer
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        @if($letterheadExports->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Saved at</th>
                            <th scope="col">File</th>
                            <th scope="col" class="text-end">Open</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($letterheadExports as $ex)
                            <tr>
                                <td class="text-nowrap small">{{ $ex->created_at?->timezone(config('app.timezone'))->format('Y-m-d H:i') }}</td>
                                <td class="small text-break">{{ $ex->file_name }}</td>
                                <td class="text-end">
                                    @if($ex->getPublicUrl())
                                        <button type="button"
                                            class="btn btn-sm btn-outline-primary btn-keep-text"
                                            data-bs-toggle="modal"
                                            data-bs-target="#lhExportPdfModal"
                                            data-pdf-url="{{ e($ex->getPublicUrl()) }}"
                                            data-file-name="{{ e($ex->file_name) }}">
                                            <i class="bi bi-eye" aria-hidden="true"></i> View PDF
                                        </button>
                                    @else
                                        <span class="text-muted small">Missing file</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $letterheadExports->links() }}
            </div>
        @else
            <p class="text-muted mb-0">No exports yet. Open <a href="{{ route('admin.companies.letterhead') }}">Letterhead designer</a> and use Export PDF — each export is stored here.</p>
        @endif
    </div>
</div>

<div class="modal fade" id="lhExportPdfModal" tabindex="-1" aria-labelledby="lhExportPdfModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title text-truncate me-2" id="lhExportPdfModalLabel">PDF</h5>
                <a id="lhExportPdfOpenTab" href="#" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary btn-keep-text me-2 d-none">Open in new tab</a>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 bg-secondary bg-opacity-10" style="min-height: 75vh;">
                <iframe id="lhExportPdfFrame" title="PDF preview" class="w-100 border-0 d-block bg-white" style="min-height: 75vh; height: calc(100vh - 12rem);"></iframe>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var modalEl = document.getElementById('lhExportPdfModal');
    var frame = document.getElementById('lhExportPdfFrame');
    var titleEl = document.getElementById('lhExportPdfModalLabel');
    var openTab = document.getElementById('lhExportPdfOpenTab');
    if (!modalEl || !frame) return;

    modalEl.addEventListener('show.bs.modal', function (event) {
        var btn = event.relatedTarget;
        if (!btn) return;
        var url = btn.getAttribute('data-pdf-url');
        var name = btn.getAttribute('data-file-name') || 'PDF';
        frame.src = url || 'about:blank';
        if (titleEl) titleEl.textContent = name;
        if (openTab) {
            if (url) {
                openTab.href = url;
                openTab.classList.remove('d-none');
            } else {
                openTab.href = '#';
                openTab.classList.add('d-none');
            }
        }
    });
    modalEl.addEventListener('hidden.bs.modal', function () {
        frame.src = 'about:blank';
        if (openTab) openTab.href = '#';
    });
})();
</script>
@endpush
