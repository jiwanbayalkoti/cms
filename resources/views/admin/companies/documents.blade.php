@extends('admin.layout')

@section('title', 'Company Documents')

@section('content')
<style>
    .docs-page .btn-sm.btn-keep-text {
        min-width: auto !important;
        width: auto !important;
        height: auto !important;
        font-size: 0.8125rem !important;
        line-height: 1.25 !important;
        padding: 0.4rem 0.85rem !important;
        gap: 0.35rem;
    }
    .doc-action-btn {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 40px !important;
        height: 40px !important;
        min-width: 40px !important;
        padding: 0 !important;
        border-radius: 0.375rem;
        border-width: 1px;
        border-style: solid;
        background: #fff;
        line-height: 1 !important;
        font-size: 1rem !important;
        text-decoration: none !important;
    }
    .doc-action-btn svg {
        width: 20px !important;
        height: 20px !important;
        min-width: 20px !important;
        min-height: 20px !important;
        display: block !important;
        stroke: currentColor !important;
        fill: none !important;
        flex-shrink: 0 !important;
    }
    .doc-action-btn.doc-action-download {
        color: #2563eb;
        border-color: #93c5fd;
    }
    .doc-action-btn.doc-action-download:hover {
        background: #eff6ff;
        color: #1d4ed8;
    }
    .doc-action-btn.doc-action-view {
        color: #4b5563;
        border-color: #d1d5db;
    }
    .doc-action-btn.doc-action-view:hover {
        background: #f3f4f6;
        color: #111827;
    }
    .doc-action-btn.doc-action-edit {
        color: #b45309;
        border-color: #fcd34d;
    }
    .doc-action-btn.doc-action-edit:hover {
        background: #fffbeb;
        color: #92400e;
    }
    .doc-action-btn.doc-action-delete {
        color: #dc2626;
        border-color: #fca5a5;
    }
    .doc-action-btn.doc-action-delete:hover {
        background: #fef2f2;
        color: #b91c1c;
    }
    .doc-file-icon {
        width: 56px;
        height: 56px;
        display: block;
        margin: 0 auto;
    }
</style>
<div class="docs-page">
<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h2 class="h4 mb-0">Company Documents</h2>
        <p class="text-muted small mb-0">{{ $company->name }}</p>
    </div>
    <div class="d-flex gap-2 flex-wrap align-items-center">
        <select id="doc-type-filter" class="form-select" style="width: auto; min-width: 220px;">
            <option value="all">All documents</option>
            @foreach($types as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
        <select id="doc-period-filter" class="form-select" style="width: auto; min-width: 170px;">
            <option value="all">All periods</option>
            @foreach($periods as $periodKey => $periodLabel)
                <option value="{{ $periodKey }}">{{ $periodLabel }}</option>
            @endforeach
            <option value="none">No period set</option>
        </select>
        <a href="{{ route('admin.companies.profile') }}" class="btn btn-outline-secondary btn-sm btn-keep-text">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Back to Profile</span>
        </a>
        <button type="button" class="btn btn-primary btn-sm btn-keep-text" onclick="openUploadModal()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            <span>Upload Document</span>
        </button>
    </div>
</div>

{{-- Documents Grid --}}
<div id="docs-container">
    @if($documents->isEmpty())
        <div id="empty-state" class="text-center py-5">
            <i class="bi bi-file-earmark-x text-muted" style="font-size:3rem;"></i>
            <p class="text-muted mt-2">No documents uploaded yet.</p>
            <button type="button" class="btn btn-primary btn-keep-text" onclick="openUploadModal()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="me-1"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                <span>Upload First Document</span>
            </button>
        </div>
    @else
        <div id="empty-state" class="text-center py-5 d-none">
            <i class="bi bi-search text-muted" style="font-size:3rem;"></i>
            <p class="text-muted mt-2">No documents found for this category.</p>
        </div>
        <div class="row g-3" id="docs-grid">
            @foreach($documents as $doc)
                <div class="col-12 col-sm-6 col-lg-4 doc-card-wrap"
                     data-type="{{ $doc->document_type }}"
                     data-period="{{ $doc->getPeriodKey() ?? 'none' }}"
                     data-id="{{ $doc->id }}">
                    <div class="card h-100 shadow-sm border-0">
                        {{-- Preview area --}}
                        <div class="bg-light d-flex align-items-center justify-content-center" style="height:140px; border-radius:0.375rem 0.375rem 0 0; overflow:hidden;">
                            @if($doc->isImage())
                                <img src="{{ $doc->getUrl() }}" alt="{{ $doc->label }}" class="img-fluid" style="max-height:140px; object-fit:contain; cursor:pointer;"
                                     data-doc-url="{{ $doc->getUrl() }}"
                                     data-doc-label="{{ $doc->label }}"
                                     onclick="openPreview(this)">
                            @elseif($doc->isPdf())
                                <a href="{{ $doc->getUrl() }}" target="_blank" class="text-decoration-none text-center p-3">
                                    <svg class="doc-file-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#dc2626" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 3h8l5 5v13a1 1 0 01-1 1H7a1 1 0 01-1-1V4a1 1 0 011-1z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 3v5h5"/>
                                    </svg>
                                    <p class="small text-muted mt-1 mb-0">PDF</p>
                                </a>
                            @else
                                <div class="text-center p-3">
                                    <svg class="doc-file-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#6b7280" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 3h8l5 5v13a1 1 0 01-1 1H7a1 1 0 01-1-1V4a1 1 0 011-1z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 3v5h5"/>
                                    </svg>
                                    <p class="small text-muted mt-1 mb-0">{{ strtoupper(pathinfo($doc->original_name, PATHINFO_EXTENSION) ?: 'FILE') }}</p>
                                </div>
                            @endif
                        </div>
                        <div class="card-body p-3">
                            <div class="d-flex flex-wrap gap-1 mb-1">
                                <span class="badge" style="background:#e0e7ff;color:#4338ca;font-size:0.7rem;">{{ $doc->getTypeLabel() }}</span>
                                @if($doc->getPeriodLabel())
                                    <span class="badge" style="background:#dcfce7;color:#15803d;font-size:0.7rem;">{{ $doc->getPeriodLabel() }}</span>
                                @endif
                            </div>
                            <h6 class="card-title mb-1 fw-semibold" style="font-size:0.9rem;">{{ $doc->label }}</h6>
                            <p class="text-muted small mb-1">{{ $doc->original_name }}</p>
                            <p class="text-muted small mb-0">{{ $doc->getFileSizeFormatted() }} &middot; {{ $doc->created_at->format('Y-m-d') }}</p>
                            @if($doc->notes)
                                <p class="text-muted small mt-1 mb-0 fst-italic">{{ Str::limit($doc->notes, 60) }}</p>
                            @endif
                            <p class="text-muted small mb-0">Uploaded by: {{ $doc->uploader?->name ?? '—' }}</p>
                        </div>
                        <div class="card-footer bg-transparent p-2 d-flex gap-2 justify-content-end">
                            <a href="{{ route('admin.companies.documents.download', $doc) }}" class="doc-action-btn doc-action-download" title="Download">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            </a>
                            @if($doc->isImage())
                                <button type="button" class="doc-action-btn doc-action-view" title="View"
                                        data-doc-url="{{ $doc->getUrl() }}"
                                        data-doc-label="{{ $doc->label }}"
                                        onclick="openPreview(this)">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                            @elseif($doc->isPdf())
                                <a href="{{ $doc->getUrl() }}" target="_blank" class="doc-action-btn doc-action-view" title="View PDF">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                            @endif
                            <button type="button" class="doc-action-btn doc-action-edit" title="Edit"
                                    data-doc-id="{{ $doc->id }}"
                                    data-doc-type="{{ $doc->document_type }}"
                                    data-doc-label="{{ $doc->label }}"
                                    data-doc-period="{{ $doc->getPeriodKey() }}"
                                    data-doc-notes="{{ $doc->notes }}"
                                    data-doc-filename="{{ $doc->original_name }}"
                                    onclick="openEditModal(this)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <button type="button" class="doc-action-btn doc-action-delete" title="Delete"
                                    data-doc-id="{{ $doc->id }}"
                                    data-doc-label="{{ $doc->label }}"
                                    onclick="deleteDoc(this)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

{{-- Upload Modal --}}
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form id="uploadForm" enctype="multipart/form-data" onsubmit="submitUpload(event)">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadModalLabel"><i class="bi bi-upload me-2"></i>Upload Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="upload-error" class="alert alert-danger d-none"></div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Document Type <span class="text-danger">*</span></label>
                        <select name="document_type" id="doc-type" class="form-select" required>
                            <option value="">— Select type —</option>
                            @foreach($types as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Label / Name <span class="text-danger">*</span></label>
                        <input type="text" name="label" id="doc-label" class="form-control" placeholder="e.g. Company Registration 2025" required maxlength="255">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Document Period (Month / Year)
                            <span class="text-danger d-none" id="doc-period-required">*</span>
                            <span class="text-muted fw-normal" id="doc-period-optional">(optional)</span>
                        </label>
                        <input type="month" name="document_period" id="doc-period" class="form-control">
                        <div class="form-text" id="doc-period-help">Which month/year this document belongs to.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">File <span class="text-danger">*</span></label>
                        <input type="file" name="file" id="doc-file" class="form-control" required
                               accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx">
                        <div class="form-text">Accepted: PDF, Images (jpg/png/webp), Word, Excel &mdash; max 20 MB</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes <span class="text-muted fw-normal">(optional)</span></label>
                        <textarea name="notes" id="doc-notes" class="form-control" rows="2" maxlength="1000" placeholder="Any additional info..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="upload-submit-btn">
                        <i class="bi bi-upload me-1"></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form id="editForm" enctype="multipart/form-data" onsubmit="submitEdit(event)">
                @csrf
                <input type="hidden" name="document_id" id="edit-doc-id">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel"><i class="bi bi-pencil-square me-2"></i>Edit Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="edit-error" class="alert alert-danger d-none"></div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Document Type <span class="text-danger">*</span></label>
                        <select name="document_type" id="edit-doc-type" class="form-select" required>
                            @foreach($types as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Label / Name <span class="text-danger">*</span></label>
                        <input type="text" name="label" id="edit-doc-label" class="form-control" required maxlength="255">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Document Period (Month / Year)
                            <span class="text-danger d-none" id="edit-doc-period-required">*</span>
                            <span class="text-muted fw-normal" id="edit-doc-period-optional">(optional)</span>
                        </label>
                        <input type="month" name="document_period" id="edit-doc-period" class="form-control">
                        <div class="form-text">Which month/year this document belongs to.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Replace File <span class="text-muted fw-normal">(optional)</span></label>
                        <input type="file" name="file" id="edit-doc-file" class="form-control"
                               accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx">
                        <div class="form-text">Current: <span id="edit-current-file" class="fw-semibold"></span>. Leave empty to keep it.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes <span class="text-muted fw-normal">(optional)</span></label>
                        <textarea name="notes" id="edit-doc-notes" class="form-control" rows="2" maxlength="1000"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="edit-submit-btn">
                        <i class="bi bi-check2 me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Image Preview Modal --}}
<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="preview-title"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-2">
                <img id="preview-img" src="" alt="Preview" class="img-fluid" style="max-height:70vh;">
            </div>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteDocModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center pt-4 pb-2">
                <i class="bi bi-exclamation-triangle text-danger" style="font-size:2.5rem;"></i>
                <h5 class="mt-2">Delete Document?</h5>
                <p class="text-muted small" id="delete-doc-name"></p>
                <p class="text-muted small">This action cannot be undone.</p>
            </div>
            <div class="modal-footer justify-content-center gap-2">
                <button type="button" class="btn btn-secondary btn-sm btn-keep-text" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger btn-sm btn-keep-text" id="delete-doc-confirm-btn">Delete</button>
            </div>
        </div>
    </div>
</div>
</div>

@push('scripts')
<script>
window.docPeriodRequiredTypes = @json($periodRequiredTypes);

window.applyPeriodRequirement = function (type, inputId, requiredMarkId, optionalMarkId) {
    const input = document.getElementById(inputId);
    if (!input) return;

    const required = window.docPeriodRequiredTypes.indexOf(type) !== -1;
    input.required = required;

    const requiredMark = document.getElementById(requiredMarkId);
    const optionalMark = document.getElementById(optionalMarkId);
    if (requiredMark) requiredMark.classList.toggle('d-none', !required);
    if (optionalMark) optionalMark.classList.toggle('d-none', required);
};

window.submitUpload = function (e) {
    e.preventDefault();
    e.stopPropagation();

    const form = e.target;
    const btn = form.querySelector('#upload-submit-btn') || form.querySelector('[type="submit"]');
    const errEl = form.querySelector('#upload-error');

    const showError = function (msg) {
        if (errEl) {
            errEl.textContent = msg;
            errEl.classList.remove('d-none');
        } else {
            alert(msg);
        }
        if (btn) {
            btn.disabled = false;
            btn.textContent = 'Upload';
        }
    };

    if (errEl) {
        errEl.classList.add('d-none');
        errEl.textContent = '';
    }
    if (btn) {
        btn.disabled = true;
        btn.textContent = 'Uploading...';
    }

    window.csrfFetch('{{ route("admin.companies.documents.upload") }}', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: new FormData(form),
        formElement: form
    })
    .then(async function (r) {
        const data = await r.json().catch(function () { return null; });
        if (!data) {
            throw new Error('Server did not return a valid response.');
        }
        if (!r.ok || !data.success) {
            throw new Error(data.message || 'Upload failed.');
        }
        return data;
    })
    .then(function (data) {
        window.location.reload();
    })
    .catch(function (err) {
        showError(err.message || 'An error occurred. Please try again.');
        if (err.sessionExpired) {
            setTimeout(function () { window.location.reload(); }, 2500);
        }
    });

    return false;
};

window.openUploadModal = function () {
    const modalEl = document.getElementById('uploadModal');
    const form = document.getElementById('uploadForm');
    if (form) form.reset();
    const errEl = form ? form.querySelector('#upload-error') : null;
    if (errEl) {
        errEl.classList.add('d-none');
        errEl.textContent = '';
    }
    const btn = form ? form.querySelector('#upload-submit-btn') : null;
    if (btn) {
        btn.disabled = false;
        btn.textContent = 'Upload';
    }
    window.applyPeriodRequirement('', 'doc-period', 'doc-period-required', 'doc-period-optional');
    if (modalEl && window.bootstrap) {
        window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
};

window.openEditModal = function (el) {
    const doc = {
        id: el.dataset.docId,
        document_type: el.dataset.docType,
        label: el.dataset.docLabel,
        period: el.dataset.docPeriod,
        notes: el.dataset.docNotes,
        original_name: el.dataset.docFilename
    };

    const form = document.getElementById('editForm');
    if (form) form.reset();

    const errEl = document.getElementById('edit-error');
    if (errEl) {
        errEl.classList.add('d-none');
        errEl.textContent = '';
    }

    const btn = document.getElementById('edit-submit-btn');
    if (btn) {
        btn.disabled = false;
        btn.textContent = 'Save Changes';
    }

    document.getElementById('edit-doc-id').value = doc.id;
    document.getElementById('edit-doc-type').value = doc.document_type || '';
    document.getElementById('edit-doc-label').value = doc.label || '';
    document.getElementById('edit-doc-period').value = doc.period || '';
    document.getElementById('edit-doc-notes').value = doc.notes || '';
    document.getElementById('edit-current-file').textContent = doc.original_name || '—';

    window.applyPeriodRequirement(
        doc.document_type,
        'edit-doc-period',
        'edit-doc-period-required',
        'edit-doc-period-optional'
    );

    const modalEl = document.getElementById('editModal');
    if (modalEl && window.bootstrap) {
        window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
};

window.submitEdit = function (e) {
    e.preventDefault();
    e.stopPropagation();

    const form = e.target;
    const btn = document.getElementById('edit-submit-btn');
    const errEl = document.getElementById('edit-error');
    const id = document.getElementById('edit-doc-id').value;

    const showError = function (msg) {
        if (errEl) {
            errEl.textContent = msg;
            errEl.classList.remove('d-none');
        } else {
            alert(msg);
        }
        if (btn) {
            btn.disabled = false;
            btn.textContent = 'Save Changes';
        }
    };

    if (!id) {
        showError('Missing document reference.');
        return false;
    }

    if (errEl) {
        errEl.classList.add('d-none');
        errEl.textContent = '';
    }
    if (btn) {
        btn.disabled = true;
        btn.textContent = 'Saving...';
    }

    window.csrfFetch('/admin/company/documents/' + id, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: new FormData(form),
        formElement: form
    })
    .then(async function (r) {
        const data = await r.json().catch(function () { return null; });
        if (!data) {
            throw new Error('Server did not return a valid response.');
        }
        if (!r.ok || !data.success) {
            throw new Error(data.message || 'Update failed.');
        }
        return data;
    })
    .then(function () {
        window.location.reload();
    })
    .catch(function (err) {
        showError(err.message || 'An error occurred. Please try again.');
        if (err.sessionExpired) {
            setTimeout(function () { window.location.reload(); }, 2500);
        }
    });

    return false;
};

window.openPreview = function (el) {
    const img = document.getElementById('preview-img');
    const titleEl = document.getElementById('preview-title');
    const modalEl = document.getElementById('previewModal');
    if (img) img.src = el.dataset.docUrl || '';
    if (titleEl) titleEl.textContent = el.dataset.docLabel || '';
    if (modalEl && window.bootstrap) {
        window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
};

window.deleteDoc = function (el) {
    window._deleteDocId = el.dataset.docId;
    const nameEl = document.getElementById('delete-doc-name');
    if (nameEl) nameEl.textContent = el.dataset.docLabel || '';
    const modalEl = document.getElementById('deleteDocModal');
    if (modalEl && window.bootstrap) {
        window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
};

(function () {
    const typeTabs = document.getElementById('doc-type');
    if (typeTabs) {
        typeTabs.addEventListener('change', function () {
            const labelInput = document.getElementById('doc-label');
            if (labelInput && !labelInput.value.trim()) {
                const text = this.options[this.selectedIndex]?.text || '';
                labelInput.value = text !== '— Select type —' ? text : '';
            }
            window.applyPeriodRequirement(this.value, 'doc-period', 'doc-period-required', 'doc-period-optional');
        });
    }

    const editTypeSelect = document.getElementById('edit-doc-type');
    if (editTypeSelect && !editTypeSelect.dataset.bound) {
        editTypeSelect.dataset.bound = '1';
        editTypeSelect.addEventListener('change', function () {
            window.applyPeriodRequirement(this.value, 'edit-doc-period', 'edit-doc-period-required', 'edit-doc-period-optional');
        });
    }

    const confirmBtn = document.getElementById('delete-doc-confirm-btn');
    if (confirmBtn && !confirmBtn.dataset.bound) {
        confirmBtn.dataset.bound = '1';
        confirmBtn.addEventListener('click', function () {
            const id = window._deleteDocId;
            if (!id) return;
            this.disabled = true;
            this.textContent = 'Deleting...';
            window.csrfFetch('/admin/company/documents/' + id, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Delete failed.');
                    confirmBtn.disabled = false;
                    confirmBtn.textContent = 'Delete';
                }
            })
            .catch(function (err) {
                alert(err.message || 'An error occurred.');
                if (err.sessionExpired) {
                    window.location.reload();
                    return;
                }
                confirmBtn.disabled = false;
                confirmBtn.textContent = 'Delete';
            });
        });
    }

    const typeFilter = document.getElementById('doc-type-filter');
    const periodFilter = document.getElementById('doc-period-filter');

    const applyFilters = function () {
        const type = typeFilter ? typeFilter.value : 'all';
        const period = periodFilter ? periodFilter.value : 'all';
        const cards = document.querySelectorAll('.doc-card-wrap');
        let visible = 0;

        cards.forEach(function (c) {
            const typeMatch = type === 'all' || c.dataset.type === type;
            const periodMatch = period === 'all' || c.dataset.period === period;
            const show = typeMatch && periodMatch;
            c.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        const emptyState = document.getElementById('empty-state');
        if (emptyState && cards.length > 0) {
            emptyState.classList.toggle('d-none', visible !== 0);
        }
    };

    [typeFilter, periodFilter].forEach(function (select) {
        if (select && !select.dataset.bound) {
            select.dataset.bound = '1';
            select.addEventListener('change', applyFilters);
        }
    });
})();
</script>
@endpush
@endsection
