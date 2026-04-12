@extends('admin.layout')

@section('title', 'Letterhead Design')

@section('content')
@php
    $font = old('letterhead_font_family', $company->letterhead_font_family ?: 'Inter, Arial, sans-serif');
    $letterheadAddress = trim((string) ($company->address ?? ''));
    $nextChs = (string) ((int) ($company->letterhead_chs_last_no ?? 0) + 1);
    $nextPs = (string) ((int) ($company->letterhead_ps_last_no ?? 0) + 1);
    $metaChsValue = old('letterhead_meta_chs_value', $nextChs);
    $metaPsValue = old('letterhead_meta_ps_value', $nextPs);
    $metaDateValue = old('letterhead_meta_date_value', $company->letterhead_meta_date_value ?? '');
    if ($metaDateValue === '' || $metaDateValue === null) {
        $metaDateValue = now()->format('Y-m-d');
    }
    $builtInFonts = [
        'Inter, Arial, sans-serif' => 'Inter',
        'Poppins, Arial, sans-serif' => 'Poppins',
        'Roboto, Arial, sans-serif' => 'Roboto',
        'Georgia, serif' => 'Georgia',
        'Nirmala UI, Arial, sans-serif' => 'Nirmala UI',
        'Noto Sans, Arial, sans-serif' => 'Noto Sans',
    ];
    // Mirror CompanyController::letterheadExportPdf $fontMap + mPDF stacks (notosans vs freeserif).
    $letterheadPreviewMirrorFonts = [
        'Inter, Arial, sans-serif' => [
            'body' => '"Noto Sans", "Noto Sans Devanagari", sans-serif',
            'np' => '"Noto Sans Devanagari", "Noto Sans", sans-serif',
        ],
        'Poppins, Arial, sans-serif' => [
            'body' => '"Noto Sans", "Noto Sans Devanagari", sans-serif',
            'np' => '"Noto Sans Devanagari", "Noto Sans", sans-serif',
        ],
        'Roboto, Arial, sans-serif' => [
            'body' => '"Noto Sans", "Noto Sans Devanagari", sans-serif',
            'np' => '"Noto Sans Devanagari", "Noto Sans", sans-serif',
        ],
        'Noto Sans, Arial, sans-serif' => [
            'body' => '"Noto Sans", "Noto Sans Devanagari", sans-serif',
            'np' => '"Noto Sans Devanagari", "Noto Sans", sans-serif',
        ],
        'Georgia, serif' => [
            'body' => '"Noto Serif", "Noto Sans", "Noto Sans Devanagari", serif',
            'np' => '"Noto Sans Devanagari", "Noto Serif", "Noto Sans", serif',
        ],
        'Nirmala UI, Arial, sans-serif' => [
            'body' => '"Noto Serif", "Noto Sans", "Noto Sans Devanagari", serif',
            'np' => '"Noto Sans Devanagari", "Noto Serif", "Noto Sans", serif',
        ],
    ];
    $lhMirror = $letterheadPreviewMirrorFonts[$font] ?? $letterheadPreviewMirrorFonts['Inter, Arial, sans-serif'];
@endphp

{{-- Same faces as mPDF embedded fonts --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans:ital,wght@0,400;0,600;0,700;0,800;1,400&family=Noto+Sans+Devanagari:wght@400;600;700;800&family=Noto+Serif:ital,wght@0,400;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">

<div class="mb-4 d-flex flex-wrap align-items-start justify-content-between gap-2">
    <div>
        <h2 class="h4 mb-1">Letterhead Designer</h2>
        <p class="text-muted mb-0">Adjust settings in the side panel; the preview updates live.</p>
    </div>
    <a href="{{ route('admin.companies.letterhead.exports') }}" class="btn btn-outline-primary btn-keep-text">
        <i class="bi bi-folder2-open" aria-hidden="true"></i> Exported PDFs
    </a>
</div>

<div class="row g-3 g-xl-4 align-items-start align-items-lg-stretch lh-letterhead-layout">
    <aside class="col-12 col-lg-4 order-2 order-lg-1 lh-sidebar-col">
<div class="card shadow-sm lh-sidebar-card">
    <div class="card-header py-2">
        <span class="fw-semibold">Letterhead settings</span>
    </div>
    <div class="card-body">
        <form id="letterheadForm" method="POST" action="{{ route('admin.companies.letterhead.update') }}">
            @csrf
            @method('PUT')
            @php
                $lhLayoutRaw = old('letterhead_layout_json');
                if ($lhLayoutRaw === null) {
                    $lhLayoutRaw = $company->letterhead_layout_json ?? '';
                }
                $lhLayoutForJs = [];
                if (is_string($lhLayoutRaw) && $lhLayoutRaw !== '') {
                    $d = json_decode($lhLayoutRaw, true);
                    $lhLayoutForJs = is_array($d) ? $d : [];
                }
                $lhAssetUrls = [];
                foreach ($company->letterheadAssets ?? [] as $a) {
                    $u = $a->getUrl();
                    if ($u) {
                        $lhAssetUrls[$a->id] = $u;
                    }
                }
            @endphp
            <input type="hidden" name="letterhead_layout_json" id="letterhead-layout-json" value="{{ is_string($lhLayoutRaw) ? $lhLayoutRaw : '' }}">
            <div class="row g-3 lh-design-form-row">
                @php
                    $enAlign = old('letterhead_name_en_align', $company->letterhead_name_en_align ?: 'center');
                    $npAlign = old('letterhead_name_np_align', $company->letterhead_name_np_align ?: 'center');
                    $adAlign = old('letterhead_address_align', $company->letterhead_address_align ?: 'center');
                    $chsAlign = old('letterhead_meta_chs_align', $company->letterhead_meta_chs_align ?: 'left');
                    $psAlign = old('letterhead_meta_ps_align', $company->letterhead_meta_ps_align ?: 'left');
                    $dateAlign = old('letterhead_meta_date_align', $company->letterhead_meta_date_align ?: 'right');
                @endphp
                <div class="col-md-4">
                    <label class="form-label">च.स. Align</label>
                    <select class="form-select" id="letterhead-meta-chs-align" name="letterhead_meta_chs_align"><option value="left" {{ $chsAlign === 'left' ? 'selected' : '' }}>Left</option><option value="center" {{ $chsAlign === 'center' ? 'selected' : '' }}>Center</option><option value="right" {{ $chsAlign === 'right' ? 'selected' : '' }}>Right</option></select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">प.स. Align</label>
                    <select class="form-select" id="letterhead-meta-ps-align" name="letterhead_meta_ps_align"><option value="left" {{ $psAlign === 'left' ? 'selected' : '' }}>Left</option><option value="center" {{ $psAlign === 'center' ? 'selected' : '' }}>Center</option><option value="right" {{ $psAlign === 'right' ? 'selected' : '' }}>Right</option></select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">मिति Align</label>
                    <select class="form-select" id="letterhead-meta-date-align" name="letterhead_meta_date_align"><option value="left" {{ $dateAlign === 'left' ? 'selected' : '' }}>Left</option><option value="center" {{ $dateAlign === 'center' ? 'selected' : '' }}>Center</option><option value="right" {{ $dateAlign === 'right' ? 'selected' : '' }}>Right</option></select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">च.स. Value</label>
                    <input type="text" class="form-control bg-light" id="letterhead-meta-chs-value" value="{{ $metaChsValue }}" maxlength="100" readonly title="हरेक PDF export पछि अर्को नम्बर (+1)">
                    <div class="form-text small">अर्को export मा यही नम्बर PDF मा जान्छ; हरेक export पछि +1 बढ्छ।</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">प.स. Value</label>
                    <input type="text" class="form-control bg-light" id="letterhead-meta-ps-value" value="{{ $metaPsValue }}" maxlength="100" readonly title="हरेक PDF export पछि अर्को नम्बर (+1)">
                    <div class="form-text small">हरेक export पछि प.स. पनि +1 बढ्छ।</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">मिति Value</label>
                    <input type="text" class="form-control" id="letterhead-meta-date-value" name="letterhead_meta_date_value" value="{{ $metaDateValue }}" placeholder="मिति value" maxlength="100">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Font</label>
                    <select name="letterhead_font_family" id="letterhead-font-family" class="form-select">
                        @foreach($builtInFonts as $value => $label)
                            <option value="{{ $value }}" {{ $font === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Header Alignment</label>
                    @php $headerAlign = old('letterhead_header_alignment', $company->letterhead_header_alignment ?: 'left'); @endphp
                    <select name="letterhead_header_alignment" id="letterhead-header-alignment" class="form-select">
                        <option value="left" {{ $headerAlign === 'left' ? 'selected' : '' }}>Left</option>
                        <option value="center" {{ $headerAlign === 'center' ? 'selected' : '' }}>Center</option>
                        <option value="right" {{ $headerAlign === 'right' ? 'selected' : '' }}>Right</option>
                    </select>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Nepali Name</label>
                    <input type="text" class="form-control" id="letterhead-tagline" name="letterhead_tagline" value="{{ old('letterhead_tagline', $company->letterhead_tagline ?: '') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">English Size (rem)</label>
                    <input type="number" class="form-control" step="0.05" min="1" max="6" id="letterhead-name-en-size" name="letterhead_name_en_size" value="{{ old('letterhead_name_en_size', $company->letterhead_name_en_size ?? 3) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Nepali Size (rem)</label>
                    <input type="number" class="form-control" step="0.05" min="1" max="6" id="letterhead-name-np-size" name="letterhead_name_np_size" value="{{ old('letterhead_name_np_size', $company->letterhead_name_np_size ?? 3.15) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Address Size (rem)</label>
                    <input type="number" class="form-control" step="0.05" min="0.8" max="4" id="letterhead-address-size" name="letterhead_address_size" value="{{ old('letterhead_address_size', $company->letterhead_address_size ?? 1.15) }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Text Spacing</label>
                    <input type="number" class="form-control" step="0.05" min="-1" max="4" id="letterhead-name-letter-spacing" name="letterhead_name_letter_spacing" value="{{ old('letterhead_name_letter_spacing', $company->letterhead_name_letter_spacing ?? 0.2) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Name lines spacing</label>
                    <input type="number" class="form-control" step="0.01" min="0" max="1.6" id="letterhead-name-line-height" name="letterhead_name_line_height" value="{{ old('letterhead_name_line_height', $company->letterhead_name_line_height ?? 0.95) }}" aria-describedby="letterhead-name-line-height-hint">
                    <div id="letterhead-name-line-height-hint" class="form-text">Space between English name, Nepali name, and address (not the blue line or letter body).</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Primary</label>
                    <input type="color" class="form-control form-control-color w-100" id="letterhead-primary-color" name="letterhead_primary_color" value="{{ old('letterhead_primary_color', $company->letterhead_primary_color ?: '#1d4ed8') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">English Color</label>
                    <input type="color" class="form-control form-control-color w-100" id="letterhead-name-en-color" name="letterhead_name_en_color" value="{{ old('letterhead_name_en_color', $company->letterhead_name_en_color ?: '#0f2a5a') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Nepali Color</label>
                    <input type="color" class="form-control form-control-color w-100" id="letterhead-name-np-color" name="letterhead_name_np_color" value="{{ old('letterhead_name_np_color', $company->letterhead_name_np_color ?: '#a31212') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Address Color</label>
                    <input type="color" class="form-control form-control-color w-100" id="letterhead-address-color" name="letterhead_address_color" value="{{ old('letterhead_address_color', $company->letterhead_address_color ?: '#a31212') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Font Style</label>
                    @php $fontStyle = old('letterhead_name_font_style', $company->letterhead_name_font_style ?: 'normal'); @endphp
                    <select class="form-select" id="letterhead-name-font-style" name="letterhead_name_font_style">
                        <option value="normal" {{ $fontStyle === 'normal' ? 'selected' : '' }}>Normal</option>
                        <option value="italic" {{ $fontStyle === 'italic' ? 'selected' : '' }}>Italic</option>
                        <option value="oblique" {{ $fontStyle === 'oblique' ? 'selected' : '' }}>Oblique</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">EN Align</label>
                    <select class="form-select" id="letterhead-name-en-align" name="letterhead_name_en_align"><option value="left" {{ $enAlign === 'left' ? 'selected' : '' }}>Left</option><option value="center" {{ $enAlign === 'center' ? 'selected' : '' }}>Center</option><option value="right" {{ $enAlign === 'right' ? 'selected' : '' }}>Right</option></select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">NP Align</label>
                    <select class="form-select" id="letterhead-name-np-align" name="letterhead_name_np_align"><option value="left" {{ $npAlign === 'left' ? 'selected' : '' }}>Left</option><option value="center" {{ $npAlign === 'center' ? 'selected' : '' }}>Center</option><option value="right" {{ $npAlign === 'right' ? 'selected' : '' }}>Right</option></select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Address Align</label>
                    <select class="form-select" id="letterhead-address-align" name="letterhead_address_align"><option value="left" {{ $adAlign === 'left' ? 'selected' : '' }}>Left</option><option value="center" {{ $adAlign === 'center' ? 'selected' : '' }}>Center</option><option value="right" {{ $adAlign === 'right' ? 'selected' : '' }}>Right</option></select>
                </div>

                <div class="col-md-8">
                    <label class="form-label">Footer Text</label>
                    <input type="text" class="form-control" id="letterhead-footer-text" name="letterhead_footer_text" value="{{ old('letterhead_footer_text', $company->letterhead_footer_text ?: '') }}">
                </div>
                <div class="col-md-2"><label class="form-label">Watermark</label><input type="text" class="form-control" id="letterhead-watermark-text" name="letterhead_watermark_text" value="{{ old('letterhead_watermark_text', $company->letterhead_watermark_text ?: $company->name) }}"></div>
                @php $watermarkMode = old('letterhead_watermark_mode', $company->letterhead_watermark_mode ?: 'text'); @endphp
                <div class="col-md-1">
                    <label class="form-label">WM Type</label>
                    <select class="form-select" id="letterhead-watermark-mode" name="letterhead_watermark_mode">
                        <option value="text" {{ $watermarkMode === 'text' ? 'selected' : '' }}>Text</option>
                        <option value="logo" {{ $watermarkMode === 'logo' ? 'selected' : '' }}>Logo</option>
                    </select>
                </div>
                <div class="col-md-1"><label class="form-label">Opacity</label><input type="number" min="1" max="60" class="form-control" id="letterhead-watermark-opacity" name="letterhead_watermark_opacity" value="{{ old('letterhead_watermark_opacity', $company->letterhead_watermark_opacity ?: 10) }}"></div>
                <div class="col-md-1 d-flex align-items-end"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="letterhead-show-watermark" name="letterhead_show_watermark" value="1" {{ old('letterhead_show_watermark', $company->letterhead_show_watermark) ? 'checked' : '' }}><label class="form-check-label" for="letterhead-show-watermark">Show</label></div></div>
            </div>
            <div class="mt-3"><button type="submit" class="btn btn-primary w-100">Save Letterhead</button></div>
        </form>
    </div>
</div>

<div class="card shadow-sm lh-sidebar-card mt-3">
    <div class="card-header py-2">
        <span class="fw-semibold">Signatures & logos</span>
    </div>
    <div class="card-body">
        <form id="letterhead-asset-upload-form" method="POST" action="{{ route('admin.companies.letterhead.assets.store') }}" enctype="multipart/form-data" class="mb-3">
            @csrf
            <div class="mb-2">
                <label class="form-label small mb-0">Type</label>
                <select name="kind" class="form-select form-select-sm">
                    <option value="signature">Signature</option>
                    <option value="logo">Logo / stamp</option>
                </select>
            </div>
            <div class="mb-2">
                <label class="form-label small mb-0">Label (optional)</label>
                <input type="text" name="label" class="form-control form-control-sm" maxlength="120" placeholder="e.g. Director">
            </div>
            <div class="mb-2">
                <label class="form-label small mb-0">Image</label>
                <input type="file" name="file" class="form-control form-control-sm" accept="image/jpeg,image/png,image/webp,image/gif" required>
            </div>
            <button type="submit" class="btn btn-sm btn-outline-primary btn-keep-text w-100">Upload</button>
        </form>
        <p class="small text-muted mb-2">Add to the letter preview, then drag to position. Export PDF keeps the same placement.</p>
        <div class="mb-2" id="lh-asset-page-row">
            <label class="form-label small mb-0" for="lh-asset-target-page">Place new signature/logo on</label>
            <select id="lh-asset-target-page" class="form-select form-select-sm" aria-label="Target page for new overlay"></select>
        </div>
        <div id="letterhead-asset-library" class="d-flex flex-column gap-2">
            @foreach($company->letterheadAssets ?? [] as $asset)
                <div class="d-flex align-items-center gap-2 border rounded p-2 lh-asset-library-row" data-asset-id="{{ $asset->id }}">
                    <img src="{{ $asset->getUrl() }}" alt="" width="48" height="48" class="rounded border bg-light flex-shrink-0" style="object-fit:contain;">
                    <div class="flex-grow-1 small min-w-0">
                        <div class="fw-semibold text-body">{{ $asset->kind === 'logo' ? 'Logo / stamp' : 'Signature' }}</div>
                        <div class="text-muted text-truncate">{{ $asset->label ?: '—' }}</div>
                    </div>
                    <button type="button" class="btn btn-sm btn-keep-text lh-asset-add fw-semibold" data-asset-id="{{ $asset->id }}">Add to letter</button>
                    <button type="button" class="btn btn-sm btn-keep-text btn-outline-danger lh-asset-delete flex-shrink-0" title="Remove from library" data-delete-url="{{ route('admin.companies.letterhead.assets.destroy', $asset) }}" data-asset-id="{{ $asset->id }}" aria-label="Delete">&times;</button>
                </div>
            @endforeach
            @if(!($company->letterheadAssets ?? collect())->count())
                <p id="letterhead-asset-library-empty" class="small text-muted mb-0">Upload a transparent PNG signature or company stamp first.</p>
            @endif
        </div>
    </div>
</div>
    </aside>

    <div class="col-12 col-lg-8 order-1 order-lg-2 lh-preview-main-col">
        <input type="hidden" id="letterhead-address-text" value="{{ $letterheadAddress }}">
<form id="letter-export-form" method="POST" action="{{ route('admin.companies.letterhead.export.pdf.post') }}" target="_blank" class="d-none">
            @csrf
            <input type="hidden" id="export-letterhead-font-family" name="letterhead_font_family" value="{{ $font }}">
            <input type="hidden" id="export-letterhead-header-alignment" name="letterhead_header_alignment" value="{{ old('letterhead_header_alignment', $company->letterhead_header_alignment ?: 'left') }}">
            <input type="hidden" id="export-letterhead-tagline" name="letterhead_tagline" value="{{ old('letterhead_tagline', $company->letterhead_tagline ?: '') }}">
            <input type="hidden" id="export-letterhead-watermark-text" name="letterhead_watermark_text" value="{{ old('letterhead_watermark_text', $company->letterhead_watermark_text ?: $company->name) }}">
            <input type="hidden" id="export-letterhead-watermark-mode" name="letterhead_watermark_mode" value="{{ old('letterhead_watermark_mode', $company->letterhead_watermark_mode ?: 'text') }}">
            <input type="hidden" id="export-letterhead-watermark-opacity" name="letterhead_watermark_opacity" value="{{ old('letterhead_watermark_opacity', $company->letterhead_watermark_opacity ?: 10) }}">
            <input type="hidden" id="export-letterhead-show-watermark" name="letterhead_show_watermark" value="{{ old('letterhead_show_watermark', $company->letterhead_show_watermark) ? 1 : 0 }}">
            <input type="hidden" id="export-letterhead-name-en-size" name="letterhead_name_en_size">
            <input type="hidden" id="export-letterhead-name-np-size" name="letterhead_name_np_size">
            <input type="hidden" id="export-letterhead-address-size" name="letterhead_address_size">
            <input type="hidden" id="export-letterhead-name-letter-spacing" name="letterhead_name_letter_spacing">
            <input type="hidden" id="export-letterhead-name-line-height" name="letterhead_name_line_height">
            <input type="hidden" id="export-letterhead-name-en-color" name="letterhead_name_en_color">
            <input type="hidden" id="export-letterhead-name-np-color" name="letterhead_name_np_color">
            <input type="hidden" id="export-letterhead-address-color" name="letterhead_address_color">
            <input type="hidden" id="export-letterhead-name-font-style" name="letterhead_name_font_style">
            <input type="hidden" id="export-letterhead-name-en-align" name="letterhead_name_en_align">
            <input type="hidden" id="export-letterhead-name-np-align" name="letterhead_name_np_align">
            <input type="hidden" id="export-letterhead-address-align" name="letterhead_address_align">
            <input type="hidden" id="export-letterhead-meta-chs-align" name="letterhead_meta_chs_align">
            <input type="hidden" id="export-letterhead-meta-ps-align" name="letterhead_meta_ps_align">
            <input type="hidden" id="export-letterhead-meta-date-align" name="letterhead_meta_date_align">
            <input type="hidden" id="export-letterhead-meta-chs-value" name="letterhead_meta_chs_value" value="{{ $metaChsValue }}">
            <input type="hidden" id="export-letterhead-meta-ps-value" name="letterhead_meta_ps_value" value="{{ $metaPsValue }}">
            <input type="hidden" id="export-letterhead-meta-date-value" name="letterhead_meta_date_value" value="{{ $metaDateValue }}">
            <input type="hidden" id="export-letterhead-overlay-json" name="letterhead_overlay_json" value="">
            <textarea id="letter-content-hidden" name="letter_content" class="d-none">{!! old('letter_content') !!}</textarea>
            {{-- One HTML fragment per preview page; PDF inserts <pagebreak /> between (must match preview pagination). --}}
            <textarea id="export-letter-content-pages-json" name="letter_content_pages_json" class="d-none" autocomplete="off"></textarea>
</form>

<div class="card shadow-sm lh-main-preview-card">
    <div class="card-header py-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <span class="fw-semibold">Preview</span>
        <span class="text-muted small d-none d-sm-inline">Type in the letter body</span>
    </div>
    <div class="card-body lh-preview-card-body">
        <div id="letterhead-preview-pages" class="lh-preview-pages" style="--lh-font: {{ $lhMirror['body'] }}; --lh-font-np: {{ $lhMirror['np'] }};"></div>
        <div id="letterhead-preview" class="lh-preview d-none" aria-hidden="true">
            <div class="lh-header" id="lh-header">
                <div class="lh-header-main">
                    <div class="lh-logo-wrap">@if($company->getLogoUrl())<img src="{{ $company->getLogoUrl() }}" class="lh-logo" alt="">@endif</div>
                    <div class="lh-text-wrap">
                        <div class="lh-company-name">{{ $company->name }}</div>
                        <div class="lh-company-name-en" id="lh-preview-tagline">{{ $company->letterhead_tagline }}</div>
                        <div class="lh-address-line" id="lh-preview-address">{{ $letterheadAddress }}</div>
                    </div>
                </div>
                <div class="lh-meta-row">
                    <div class="lh-meta-left">
                        <div class="lh-meta-line">च.स. <span class="lh-dotted" id="lh-preview-meta-chs"></span></div>
                        <div class="lh-meta-line">प.स. <span class="lh-dotted" id="lh-preview-meta-ps"></span></div>
                    </div>
                    <div class="lh-meta-right">मिति: <span class="lh-dotted lh-dotted-date" id="lh-preview-meta-date"></span></div>
                </div>
                <div class="lh-rule"></div>
            </div>
            <div class="lh-body" id="lh-preview-content">This area represents your letter content.</div>
            <div class="lh-footer"><span id="lh-preview-footer-text">{{ $company->letterhead_footer_text }}</span> | Contact us on: Phone number: {{ $company->phone ?: '-' }} | Email: {{ $company->email ?: '-' }} | PAN No.: {{ trim($company->tax_number ?? '') ?: '-' }}</div>
            <div class="lh-watermark" id="lh-watermark">{{ $company->letterhead_watermark_text ?: $company->name }}</div>
            <img class="lh-watermark-logo" id="lh-watermark-logo" src="{{ $company->getLogoUrl() ?: '' }}" alt="">
            <div class="lh-overlay-layer" aria-hidden="true"></div>
        </div>
        <div class="mt-3 text-end">
            <button type="submit" form="letter-export-form" class="btn btn-outline-danger">Export PDF</button>
        </div>
    </div>
</div>
    </div>
</div>

<style>
.lh-letterhead-layout { min-height: min(85vh, 1200px); }
/* lg+: sidebar column stretches to match preview row height; settings scroll inside card-body.
   Do not use h-100 on the card — it blocks flex shrink and breaks overflow scrolling. */
@media (min-width: 992px) {
    .lh-letterhead-layout.row {
        align-items: stretch;
    }
    .lh-sidebar-col {
        display: flex;
        flex-direction: column;
        align-self: stretch;
        min-height: 0;
    }
    .lh-sidebar-card {
        flex: 1 1 0;
        min-height: 0;
        max-height: 100%;
        display: flex;
        flex-direction: column;
        position: static;
        overflow: hidden;
    }
    .lh-sidebar-card > .card-header {
        flex-shrink: 0;
    }
    .lh-sidebar-card > .card-body {
        flex: 1 1 0;
        min-height: 0;
        overflow-y: auto;
        overflow-x: hidden;
        -webkit-overflow-scrolling: touch;
        overscroll-behavior: contain;
    }
}
@media (max-width: 991.98px) {
    .lh-sidebar-card .card-body {
        max-height: min(70vh, 640px);
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        overscroll-behavior: contain;
    }
}
.lh-sidebar-col .lh-design-form-row > [class*="col-"] {
    flex: 0 0 100%;
    max-width: 100%;
}
.lh-preview-card-body {
    background: #f3f4f6;
    border-radius: 0 0 0.375rem 0.375rem;
    overflow-x: visible;
    overflow-y: visible;
}
.lh-preview-main-col { min-width: 0; min-height: 0; }

/* S = --lh-name-line-height; row gap px = 2 + (S - 0.8) * 28 — same as PDF (px→mm in letterhead-pdf-mpdf-header) */
.lh-preview-pages { --lh-preview-page-width: 794px; --lh-responsive-scale: 1; --lh-name-line-height: 0.95; --lh-font: "Noto Sans", "Noto Sans Devanagari", sans-serif; --lh-font-np: "Noto Sans Devanagari", "Noto Sans", sans-serif; width: min(100%, var(--lh-preview-page-width)); margin: 0 auto; }
.lh-preview { position: relative; border: 1px solid #d1d5db; background: #fff; width: 794px; max-width: 100%; min-height: 1123px; margin: 0 auto; overflow-x: visible; overflow-y: visible; box-sizing: border-box; }
.lh-preview-pages .lh-preview { width: 100%; margin-bottom: 14px; }
.lh-header { padding: 8px 11px 0 8px; font-family: var(--lh-font); }
.lh-header-main { display: flex; align-items: flex-start; gap: calc(10px * var(--lh-responsive-scale)); min-height: calc(106px * var(--lh-responsive-scale)); justify-content: var(--lh-header-justify, flex-start); }
.lh-logo-wrap { flex: 0 0 calc(76px * var(--lh-responsive-scale)); display: flex; justify-content: center; }
.lh-logo { max-height: calc(76px * var(--lh-responsive-scale)); max-width: calc(76px * var(--lh-responsive-scale)); object-fit: contain; }
.lh-text-wrap { flex: var(--lh-text-wrap-flex, 1 1 auto); min-width: 0; max-width: 100%; }
.lh-company-name { font-size: calc(var(--lh-name-en-size, 34.56px) * var(--lh-responsive-scale)); font-family: var(--lh-font); color: var(--lh-name-en-color, #0f2a5a); font-weight: 800; line-height: 1.12; letter-spacing: var(--lh-name-letter-spacing, 0.2px); text-align: var(--lh-name-en-align, left); font-style: var(--lh-name-font-style, normal); white-space: nowrap; overflow: hidden; width: 100%; }
.lh-company-name-en { font-size: calc(var(--lh-name-np-size, 33.77px) * var(--lh-responsive-scale)); font-family: var(--lh-font-np); color: var(--lh-name-np-color, #a31212); font-weight: 800; line-height: 1.12; letter-spacing: var(--lh-name-letter-spacing, 0.2px); text-align: var(--lh-name-np-align, left); font-style: var(--lh-name-font-style, normal); margin-top: calc((2px + (var(--lh-name-line-height, 0.95) - 0.8) * 28px) * var(--lh-responsive-scale, 1)); white-space: nowrap; overflow: hidden; width: 100%; }
.lh-address-line { display: block; width: 100%; font-size: calc(var(--lh-address-size, 18.4px) * var(--lh-responsive-scale)); font-family: var(--lh-font-np); color: var(--lh-address-color, #a31212); font-weight: 700; line-height: 1.12; letter-spacing: var(--lh-name-letter-spacing, 0.2px); text-align: var(--lh-address-align, center); font-style: var(--lh-name-font-style, normal); margin-top: calc((2px + (var(--lh-name-line-height, 0.95) - 0.8) * 28px) * var(--lh-responsive-scale, 1)); white-space: nowrap; overflow: hidden; }
.lh-meta-row { display: grid; grid-template-columns: 33% 34% 33%; align-items: start; margin-top: 2px; font-size: calc(12.3px * var(--lh-responsive-scale)); font-family: var(--lh-font); }
.lh-meta-left { display: flex; flex-direction: column; gap: 2px; padding-right: 8px; }
.lh-meta-line { display: block; width: 100%; line-height: 1.35; }
.lh-meta-line:first-child { text-align: var(--lh-meta-chs-align, left); }
.lh-meta-line:last-child { text-align: var(--lh-meta-ps-align, left); }
.lh-meta-right { grid-column: 3; width: 100%; text-align: var(--lh-meta-date-align, right); line-height: 1.35; padding-right: calc(26px * var(--lh-responsive-scale)); overflow-wrap: anywhere; word-wrap: break-word; }
.lh-dotted { display: inline-block; width: calc(78px * var(--lh-responsive-scale)); border-bottom: 1px dotted #6b7280; height: 1em; vertical-align: baseline; }
.lh-dotted-date { width: calc(128px * var(--lh-responsive-scale)); }
.lh-dotted.lh-filled { border-bottom-color: transparent; }
.lh-rule { border-top: 2.5px solid #2a5da8; margin-top: 1px; }
/* Match letterhead-pdf-body.blade.php (mPDF) so line breaks / vertical rhythm ≈ PDF — keeps signature/logo overlays aligned */
.lh-body {
    padding: 18px 20px 20px;
    min-height: 820px;
    font-family: var(--lh-font);
    font-size: 12pt;
    line-height: 1.55;
    font-weight: 600;
    color: #111827;
    text-align: left;
}
.lh-body p {
    margin: 0 0 3.5mm;
    line-height: 1.55;
}
.lh-body h1, .lh-body h2, .lh-body h3, .lh-body h4, .lh-body h5, .lh-body h6 {
    margin: 0 0 3.5mm;
    font-weight: 700;
    line-height: 1.35;
}
.lh-body ul, .lh-body ol {
    margin: 0 0 3.5mm;
    padding-left: 1.25em;
}
.lh-body li {
    margin: 0 0 0.35em;
    line-height: 1.55;
}
.lh-body li:last-child {
    margin-bottom: 0;
}
.lh-body blockquote {
    margin: 0 0 3.5mm;
    padding-left: 0.75em;
    border-left: 3px solid #e5e7eb;
}
.lh-body table {
    border-collapse: collapse;
    width: 100%;
    margin: 0 0 3.5mm;
    font-size: inherit;
}
.lh-body th, .lh-body td {
    border: 1px solid #d1d5db;
    padding: 2px 6px;
    vertical-align: top;
}
.lh-preview-pages .lh-body[contenteditable="true"] { outline: none; cursor: text; }
/* Pagination measurer: same typography as .lh-body but no min-height (see JS append to #letterhead-preview-pages) */
.lh-measurer-probe.lh-body {
    min-height: 0 !important;
    position: absolute;
    left: -99999px;
    top: 0;
    visibility: hidden;
    pointer-events: none;
    width: 754px;
    box-sizing: border-box;
    padding: 18px 20px 20px;
    height: auto;
}
.lh-footer { border-top: 2px solid #c41e3a; padding: 8px 10px; text-align: center; font-size: 12px; }
.lh-watermark { position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%) rotate(-25deg); font-size: 84px; color: var(--lh-primary, #1d4ed8); opacity: 0.1; pointer-events: none; }
.lh-watermark-logo { position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); width: min(280px, 62%); max-height: 260px; object-fit: contain; opacity: 0; pointer-events: none; filter: grayscale(0.15); }
.lh-overlay-layer { position: absolute; left: 0; top: 0; right: 0; bottom: 0; pointer-events: none; z-index: 25; }
.lh-overlay-item { position: absolute; pointer-events: auto; box-sizing: border-box; }
.lh-overlay-item img { width: 100%; height: auto; display: block; vertical-align: top; pointer-events: none; user-select: none; }
.lh-overlay-resize { position: absolute; right: 0; bottom: 0; width: 14px; height: 14px; cursor: nwse-resize; background: rgba(29, 78, 216, 0.45); border-radius: 2px 0 0 0; z-index: 2; }
.lh-overlay-remove { position: absolute; left: -8px; top: -8px; width: 22px; height: 22px; padding: 0; line-height: 1; font-size: 14px; border-radius: 50%; z-index: 3; }
/* Letterhead: admin layout hides .btn-sm text & forces 38×38 — restore labeled buttons */
.lh-letterhead-layout .btn-sm.btn-keep-text {
    min-width: auto !important;
    width: auto !important;
    height: auto !important;
    min-height: 2.25rem;
    padding: 0.375rem 0.65rem !important;
    font-size: 0.8125rem !important;
    line-height: 1.25 !important;
    white-space: nowrap;
}
.lh-letterhead-layout .btn-sm.btn-keep-text.w-100 {
    width: 100% !important;
    white-space: normal;
}
.lh-sidebar-card .lh-asset-add {
    background-color: #2563eb !important;
    border: 1px solid #1d4ed8 !important;
    color: #ffffff !important;
    min-width: 7.25rem;
}
.lh-preview .btn-sm.btn-keep-text.lh-overlay-remove {
    min-width: 1.5rem !important;
    width: 1.5rem !important;
    height: 1.5rem !important;
    min-height: 1.5rem !important;
    padding: 0 !important;
    font-size: 0.875rem !important;
    line-height: 1 !important;
}

@media (max-width: 991.98px) {
    .lh-preview-pages {
        --lh-preview-page-width: 100%;
    }
    .lh-preview {
        min-height: 980px;
    }
    .lh-header {
        padding: 8px 8px 0;
    }
    .lh-header-main {
        gap: 8px;
        min-height: 96px;
    }
    .lh-logo-wrap {
        flex-basis: 62px;
    }
    .lh-logo {
        max-width: 62px;
        max-height: 62px;
    }
    .lh-meta-row {
        font-size: 11.5px;
    }
    .lh-meta-right {
        padding-right: 10px;
    }
    .lh-dotted {
        width: 62px;
    }
    .lh-dotted-date {
        width: 96px;
    }
    .lh-body {
        min-height: 700px;
        padding: 14px 14px 16px;
    }
    .lh-footer {
        font-size: 10.5px;
        padding: 6px 8px;
    }
    .lh-watermark {
        font-size: 58px;
    }
    .lh-watermark-logo {
        width: min(220px, 60%);
        max-height: 200px;
    }
}

@media (max-width: 575.98px) {
    .lh-preview {
        min-height: 860px;
    }
    .lh-header-main {
        min-height: 86px;
    }
    .lh-logo-wrap {
        flex-basis: 52px;
    }
    .lh-logo {
        max-width: 52px;
        max-height: 52px;
    }
    .lh-company-name-en {
        margin-top: -2px;
    }
    .lh-meta-row {
        font-size: 10.5px;
        column-gap: 4px;
    }
    .lh-dotted {
        width: 52px;
    }
    .lh-dotted-date {
        width: 78px;
    }
    .lh-body {
        min-height: 620px;
        padding: 12px;
    }
    .lh-footer {
        font-size: 9.5px;
    }
}
</style>

@push('scripts')
<script>
(function () {
    function lhNormalizePlacements(arr) {
        if (!Array.isArray(arr)) return [];
        return arr.map(function (p, i) {
            const aid = parseInt(p.asset_id, 10) || 0;
            if (aid < 1) return null;
            return {
                uid: String(p.uid || ('uid-' + i + '-' + Date.now())),
                asset_id: aid,
                page: Math.max(0, parseInt(p.page, 10) || 0),
                left: Math.max(0, Math.min(100, parseFloat(p.left) || 0)),
                top: Math.max(0, Math.min(100, parseFloat(p.top) || 0)),
                width: Math.max(3, Math.min(100, parseFloat(p.width) || 20)),
            };
        }).filter(Boolean);
    }

    function lhSerializePlacements() {
        return JSON.stringify(lhPlacements.map(function (p) {
            return {
                uid: p.uid,
                asset_id: p.asset_id,
                page: p.page | 0,
                left: Math.round(p.left * 10000) / 10000,
                top: Math.round(p.top * 10000) / 10000,
                width: Math.round(p.width * 10000) / 10000,
            };
        }));
    }

    const LH_ASSET_URLS = @json($lhAssetUrls ?? []);
    let lhPlacements = lhNormalizePlacements(@json($lhLayoutForJs ?? []));
    let lhOverlayDrag = null;

    const form = document.getElementById('letterheadForm');
    const exportForm = document.getElementById('letter-export-form');
    const previewTemplate = document.getElementById('letterhead-preview');
    const previewPages = document.getElementById('letterhead-preview-pages');
    if (!form || !previewTemplate || !previewPages) return;

    function lhEscapeHtml(s) {
        const t = document.createElement('textarea');
        t.textContent = s == null ? '' : String(s);
        return t.innerHTML;
    }

    function lhAppendAssetLibraryRow(asset, destroyUrl) {
        const lib = document.getElementById('letterhead-asset-library');
        if (!lib) return;
        const empty = document.getElementById('letterhead-asset-library-empty');
        if (empty) empty.remove();
        LH_ASSET_URLS[String(asset.id)] = asset.url;
        const row = document.createElement('div');
        row.className = 'd-flex align-items-center gap-2 border rounded p-2 lh-asset-library-row';
        row.dataset.assetId = String(asset.id);
        const kindLabel = asset.kind === 'logo' ? 'Logo / stamp' : 'Signature';
        row.innerHTML =
            '<img src="' + lhEscapeHtml(asset.url) + '" alt="" width="48" height="48" class="rounded border bg-light flex-shrink-0" style="object-fit:contain;">' +
            '<div class="flex-grow-1 small min-w-0">' +
            '<div class="fw-semibold text-body">' + lhEscapeHtml(kindLabel) + '</div>' +
            '<div class="text-muted text-truncate">' + lhEscapeHtml(asset.label || '—') + '</div></div>' +
            '<button type="button" class="btn btn-sm btn-keep-text lh-asset-add fw-semibold" data-asset-id="' + asset.id + '">Add to letter</button>' +
            '<button type="button" class="btn btn-sm btn-keep-text btn-outline-danger lh-asset-delete flex-shrink-0" title="Remove from library" data-delete-url="' + lhEscapeHtml(destroyUrl) + '" data-asset-id="' + asset.id + '" aria-label="Delete">&times;</button>';
        lib.appendChild(row);
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    (function initLetterheadAssetUpload() {
        const uploadForm = document.getElementById('letterhead-asset-upload-form');
        if (!uploadForm) return;
        uploadForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const fd = new FormData(uploadForm);
            const btn = uploadForm.querySelector('[type="submit"]');
            if (btn) { btn.disabled = true; }
            fetch(uploadForm.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: fd,
            })
                .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
                .then(function (res) {
                    if (!res.ok || !res.j.success) {
                        const msg = (res.j && (res.j.message || res.j.error)) || 'Upload failed.';
                        window.alert(msg);
                        return;
                    }
                    lhAppendAssetLibraryRow(res.j.asset, res.j.destroy_url);
                    uploadForm.reset();
                })
                .catch(function () { window.alert('Upload failed. Check your connection and try again.'); })
                .finally(function () { if (btn) btn.disabled = false; });
        });
    })();

    document.getElementById('letterhead-asset-library')?.addEventListener('click', function (e) {
        const del = e.target.closest('.lh-asset-delete');
        if (del) {
            e.preventDefault();
            if (!confirm('Delete this image from the library?')) return;
            const url = del.getAttribute('data-delete-url');
            const aid = del.getAttribute('data-asset-id');
            if (!url) return;
            fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
            })
                .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
                .then(function (res) {
                    if (!res.ok || !res.j.success) {
                        window.alert((res.j && res.j.message) || 'Delete failed.');
                        return;
                    }
                    del.closest('.lh-asset-library-row')?.remove();
                    delete LH_ASSET_URLS[String(aid)];
                    lhPlacements = lhPlacements.filter(function (p) { return String(p.asset_id) !== String(aid); });
                    lhSyncOverlaysFromState();
                    const lib = document.getElementById('letterhead-asset-library');
                    if (lib && !lib.querySelector('.lh-asset-library-row')) {
                        const emptyP = document.createElement('p');
                        emptyP.id = 'letterhead-asset-library-empty';
                        emptyP.className = 'small text-muted mb-0';
                        emptyP.textContent = 'Upload a transparent PNG signature or company stamp first.';
                        lib.appendChild(emptyP);
                    }
                })
                .catch(function () { window.alert('Delete failed.'); });
            return;
        }
        const addBtn = e.target.closest('.lh-asset-add');
        if (!addBtn) return;
        const id = parseInt(addBtn.getAttribute('data-asset-id'), 10);
        if (!id || !LH_ASSET_URLS[String(id)]) return;
        const pageCount = previewPages.querySelectorAll('.lh-preview').length || 1;
        const sel = document.getElementById('lh-asset-target-page');
        let pageIdx = 0;
        if (sel && sel.value) {
            pageIdx = parseInt(sel.value, 10) - 1;
        }
        if (!Number.isFinite(pageIdx)) pageIdx = 0;
        pageIdx = Math.max(0, Math.min(pageIdx, pageCount - 1));
        lhPlacements.push({
            uid: (crypto.randomUUID && crypto.randomUUID()) || ('p' + Date.now() + Math.random()),
            asset_id: id,
            page: pageIdx,
            left: 58,
            top: 68,
            width: 22,
        });
        lhClampPlacementsToPages();
        lhSyncOverlaysFromState();
    });

    const letterheadPreviewMirrorFonts = @json($letterheadPreviewMirrorFonts);
    const lhDefaultFontKey = @json($font);
    function resolveFont(v) {
        const k = v || lhDefaultFontKey;
        const m = letterheadPreviewMirrorFonts[k];
        return (m && m.body) ? m.body : '"Noto Sans", "Noto Sans Devanagari", sans-serif';
    }
    function resolveNpFont(v) {
        const k = v || lhDefaultFontKey;
        const m = letterheadPreviewMirrorFonts[k];
        return (m && m.np) ? m.np : '"Noto Sans Devanagari", "Noto Sans", sans-serif';
    }
    function clamp(n, min, max) {
        return Math.max(min, Math.min(max, n));
    }
    const defaultAddressText = @json($letterheadAddress);
    const bodyMaxHeight = 820;
    let previewInputTimer = null;
    const measurer = document.createElement('div');
    measurer.className = 'lh-body lh-measurer-probe';
    /* Do not append here — renderPreviewPages must attach measurer before each measure (innerHTML clear was detaching it). */

    function splitNodesByPageHeight(sourceHtml) {
        function toElementNode(node) {
            if (node.nodeType === Node.ELEMENT_NODE) return node;
            if (node.nodeType === Node.TEXT_NODE) {
                const p = document.createElement('p');
                p.textContent = (node.textContent || '').trim();
                return p;
            }
            return null;
        }
        function splitLargeNode(node) {
            const el = toElementNode(node);
            if (!el) return [];
            const text = (el.textContent || '').replace(/\s+/g, ' ').trim();
            if (!text) return [];
            const words = text.split(' ');
            if (words.length <= 1) return [el.cloneNode(true)];

            const parts = [];
            let chunk = '';
            const tag = el.tagName ? el.tagName.toLowerCase() : 'p';
            const className = el.getAttribute('class');
            const inlineStyle = el.getAttribute('style');
            words.forEach(function (word) {
                const nextChunk = chunk ? (chunk + ' ' + word) : word;
                const probe = document.createElement(tag);
                if (className) probe.setAttribute('class', className);
                if (inlineStyle) probe.setAttribute('style', inlineStyle);
                probe.textContent = nextChunk;
                measurer.appendChild(probe);
                const tooTall = measurer.scrollHeight > bodyMaxHeight;
                measurer.removeChild(probe);
                if (tooTall && chunk) {
                    const finalized = document.createElement(tag);
                    if (className) finalized.setAttribute('class', className);
                    if (inlineStyle) finalized.setAttribute('style', inlineStyle);
                    finalized.textContent = chunk;
                    parts.push(finalized);
                    chunk = word;
                } else {
                    chunk = nextChunk;
                }
            });
            if (chunk) {
                const last = document.createElement(tag);
                if (className) last.setAttribute('class', className);
                if (inlineStyle) last.setAttribute('style', inlineStyle);
                last.textContent = chunk;
                parts.push(last);
            }
            return parts.length ? parts : [el.cloneNode(true)];
        }

        const wrapper = document.createElement('div');
        wrapper.innerHTML = sourceHtml;
        let nodes = Array.from(wrapper.childNodes).filter(function (node) {
            return !(node.nodeType === Node.TEXT_NODE && !(node.textContent || '').trim());
        });
        if (nodes.length === 0) {
            const p = document.createElement('p');
            p.textContent = 'This area represents your letter content.';
            nodes = [p];
        }

        const pages = [];
        let currentNodes = [];
        measurer.innerHTML = '';
        nodes.forEach(function (node) {
            const measureClone = node.cloneNode(true);
            measurer.appendChild(measureClone);
            if (measurer.scrollHeight <= bodyMaxHeight) {
                currentNodes.push(node.cloneNode(true));
                return;
            }

            measurer.removeChild(measureClone);
            if (currentNodes.length > 0) {
                pages.push(currentNodes);
                currentNodes = [];
                measurer.innerHTML = '';
            }

            const pieces = splitLargeNode(node);
            pieces.forEach(function (piece) {
                const pieceClone = piece.cloneNode(true);
                measurer.appendChild(pieceClone);
                if (measurer.scrollHeight > bodyMaxHeight && currentNodes.length > 0) {
                    measurer.removeChild(pieceClone);
                    pages.push(currentNodes);
                    currentNodes = [];
                    measurer.innerHTML = '';
                    measurer.appendChild(piece.cloneNode(true));
                }
                currentNodes.push(piece.cloneNode(true));
            });
            if (measurer.scrollHeight > bodyMaxHeight && currentNodes.length === 1) {
                // Failsafe for very large unbreakable elements.
                pages.push(currentNodes);
                currentNodes = [];
                measurer.innerHTML = '';
            }
        });
        if (currentNodes.length === 0) {
            const p = document.createElement('p');
            p.textContent = 'This area represents your letter content.';
            currentNodes = [p];
        }
        pages.push(currentNodes);
        return pages;
    }

    function renderPreviewPages(contentHtml, options) {
        previewPages.innerHTML = '';
        previewPages.appendChild(measurer);
        const pageContentNodes = splitNodesByPageHeight(contentHtml);
        previewPages.innerHTML = '';
        pageContentNodes.forEach(function (nodes) {
            const page = previewTemplate.cloneNode(true);
            page.classList.remove('d-none');
            page.removeAttribute('aria-hidden');
            page.removeAttribute('id');

            const taglineNode = page.querySelector('#lh-preview-tagline');
            const addressNode = page.querySelector('#lh-preview-address');
            const footerTextNode = page.querySelector('#lh-preview-footer-text');
            const bodyNode = page.querySelector('#lh-preview-content');
            const wmTextNode = page.querySelector('#lh-watermark');
            const wmLogoNode = page.querySelector('#lh-watermark-logo');
            const metaChsNode = page.querySelector('#lh-preview-meta-chs');
            const metaPsNode = page.querySelector('#lh-preview-meta-ps');
            const metaDateNode = page.querySelector('#lh-preview-meta-date');

            if (taglineNode) taglineNode.textContent = options.tagline;
            if (addressNode) addressNode.textContent = options.address;
            if (footerTextNode) footerTextNode.textContent = options.footer;
            if (bodyNode) {
                bodyNode.innerHTML = '';
                nodes.forEach(function (n) { bodyNode.appendChild(n); });
            }
            if (metaChsNode) {
                metaChsNode.textContent = options.metaChsValue;
                metaChsNode.classList.toggle('lh-filled', options.metaChsValue.trim() !== '');
            }
            if (metaPsNode) {
                metaPsNode.textContent = options.metaPsValue;
                metaPsNode.classList.toggle('lh-filled', options.metaPsValue.trim() !== '');
            }
            if (metaDateNode) {
                metaDateNode.textContent = options.metaDateValue;
                metaDateNode.classList.toggle('lh-filled', options.metaDateValue.trim() !== '');
            }
            if (wmTextNode && wmLogoNode) {
                wmTextNode.textContent = options.watermarkText;
                if (options.showWatermark && options.watermarkMode === 'logo' && wmLogoNode.getAttribute('src')) {
                    wmTextNode.style.opacity = '0';
                    wmLogoNode.style.opacity = options.watermarkOpacity;
                } else if (options.showWatermark) {
                    wmTextNode.style.opacity = options.watermarkOpacity;
                    wmLogoNode.style.opacity = '0';
                } else {
                    wmTextNode.style.opacity = '0';
                    wmLogoNode.style.opacity = '0';
                }
            }

            // Avoid duplicated ids after cloning template per page.
            page.querySelectorAll('[id]').forEach(function (el) { el.removeAttribute('id'); });
            previewPages.appendChild(page);
        });
        enablePreviewEditing();
        lhClampPlacementsToPages();
        lhSyncOverlaysFromState();
        requestAnimationFrame(function () {
            requestAnimationFrame(fitHeaderTextOneLine);
        });
        lhRefreshTargetPageSelect();
    }

    function lhRefreshTargetPageSelect() {
        const sel = document.getElementById('lh-asset-target-page');
        const row = document.getElementById('lh-asset-page-row');
        if (!sel) return;
        const n = previewPages.querySelectorAll('.lh-preview').length || 1;
        let want = parseInt(sel.value, 10);
        if (!Number.isFinite(want) || want < 1 || want > n) want = Math.min(n, 1);
        sel.innerHTML = '';
        for (let i = 1; i <= n; i++) {
            const o = document.createElement('option');
            o.value = String(i);
            o.textContent = 'Page ' + i + (n > 1 ? '' : '');
            if (i === want) o.selected = true;
            sel.appendChild(o);
        }
        if (row) row.style.display = n > 1 ? '' : 'none';
    }

    function lhFindPreviewPageUnderPoint(clientX, clientY) {
        const pages = previewPages.querySelectorAll('.lh-preview');
        for (let i = 0; i < pages.length; i++) {
            const el = pages[i];
            const rect = el.getBoundingClientRect();
            if (clientX >= rect.left && clientX <= rect.right && clientY >= rect.top && clientY <= rect.bottom) {
                return { el: el, idx: i };
            }
        }
        return null;
    }

    function lhClampPlacementsToPages() {
        const n = previewPages.querySelectorAll('.lh-preview').length || 1;
        lhPlacements.forEach(function (p) {
            if (p.page >= n) p.page = n - 1;
            if (p.page < 0) p.page = 0;
        });
    }

    function lhSyncOverlaysFromState() {
        const pages = previewPages.querySelectorAll('.lh-preview');
        pages.forEach(function (pageEl, pageIdx) {
            let layer = pageEl.querySelector('.lh-overlay-layer');
            if (!layer) {
                layer = document.createElement('div');
                layer.className = 'lh-overlay-layer';
                layer.setAttribute('aria-hidden', 'true');
                pageEl.appendChild(layer);
            }
            layer.innerHTML = '';
            lhPlacements.filter(function (p) { return (p.page | 0) === pageIdx; }).forEach(function (p) {
                const url = LH_ASSET_URLS[String(p.asset_id)] || LH_ASSET_URLS[p.asset_id];
                if (!url) return;
                const wrap = document.createElement('div');
                wrap.className = 'lh-overlay-item';
                wrap.dataset.uid = p.uid;
                wrap.style.left = p.left + '%';
                wrap.style.top = p.top + '%';
                wrap.style.width = p.width + '%';
                const img = document.createElement('img');
                img.src = url;
                img.alt = '';
                const rm = document.createElement('button');
                rm.type = 'button';
                rm.className = 'btn btn-sm btn-keep-text btn-danger lh-overlay-remove';
                rm.setAttribute('aria-label', 'Remove');
                rm.innerHTML = '&times;';
                rm.addEventListener('click', function (e) {
                    e.stopPropagation();
                    lhPlacements = lhPlacements.filter(function (x) { return x.uid !== p.uid; });
                    lhSyncOverlaysFromState();
                });
                const rz = document.createElement('div');
                rz.className = 'lh-overlay-resize';
                rz.title = 'Resize';
                wrap.appendChild(img);
                wrap.appendChild(rm);
                wrap.appendChild(rz);
                layer.appendChild(wrap);
                lhBindOverlayInteractions(wrap, p, pageEl, rz);
            });
        });
    }

    function lhBindOverlayInteractions(wrap, p, pageEl, rz) {
        wrap.addEventListener('mousedown', function (e) {
            if (e.target === rz || rz.contains(e.target)) return;
            lhOverlayDrag = {
                mode: 'move',
                p: p,
                wrap: wrap,
                pageEl: pageEl,
                sx: e.clientX,
                sy: e.clientY,
                sl: p.left,
                st: p.top,
            };
            e.preventDefault();
        });
        rz.addEventListener('mousedown', function (e) {
            lhOverlayDrag = {
                mode: 'resize',
                p: p,
                wrap: wrap,
                pageEl: pageEl,
                sx: e.clientX,
                sw: p.width,
            };
            e.preventDefault();
            e.stopPropagation();
        });
    }

    window.addEventListener('mousemove', function (e) {
        const d = lhOverlayDrag;
        if (!d) return;
        if (d.mode === 'move') {
            const hit = lhFindPreviewPageUnderPoint(e.clientX, e.clientY);
            if (hit && (d.p.page | 0) !== hit.idx) {
                d.p.page = hit.idx;
                const nr = hit.el.getBoundingClientRect();
                if (nr.width > 2 && nr.height > 2) {
                    d.p.left = Math.max(0, Math.min(100 - d.p.width, ((e.clientX - nr.left) / nr.width) * 100));
                    d.p.top = Math.max(0, Math.min(100 - 5, ((e.clientY - nr.top) / nr.height) * 100));
                }
                const newLayer = hit.el.querySelector('.lh-overlay-layer');
                if (newLayer && d.wrap.parentNode !== newLayer) {
                    newLayer.appendChild(d.wrap);
                }
                d.pageEl = hit.el;
                d.sl = d.p.left;
                d.st = d.p.top;
                d.sx = e.clientX;
                d.sy = e.clientY;
                d.wrap.style.left = d.p.left + '%';
                d.wrap.style.top = d.p.top + '%';
                return;
            }
        }
        const r = d.pageEl.getBoundingClientRect();
        if (r.width < 2 || r.height < 2) return;
        if (d.mode === 'move') {
            const dx = ((e.clientX - d.sx) / r.width) * 100;
            const dy = ((e.clientY - d.sy) / r.height) * 100;
            d.p.left = Math.max(0, Math.min(100 - d.p.width, d.sl + dx));
            d.p.top = Math.max(0, Math.min(100 - 5, d.st + dy));
            d.wrap.style.left = d.p.left + '%';
            d.wrap.style.top = d.p.top + '%';
        } else if (d.mode === 'resize') {
            const dx = ((e.clientX - d.sx) / r.width) * 100;
            d.p.width = Math.max(5, Math.min(100 - d.p.left, d.sw + dx));
            d.wrap.style.width = d.p.width + '%';
        }
    });
    window.addEventListener('mouseup', function () { lhOverlayDrag = null; });

    function fitHeaderTextOneLine() {
        const selectors = ['.lh-company-name', '.lh-company-name-en', '.lh-address-line'];
        previewPages.querySelectorAll('.lh-preview').forEach(function (page) {
            selectors.forEach(function (sel) {
                const el = page.querySelector(sel);
                if (!el) return;
                el.style.fontSize = '';
                el.style.transform = '';
                el.style.transformOrigin = '';
                el.style.whiteSpace = 'nowrap';
                const cw = el.clientWidth;
                if (cw < 4) return;
                const cs = getComputedStyle(el);
                const align = (cs.textAlign || 'left').trim();
                let fontPx = parseFloat(cs.fontSize) || 14;
                const minPx = 7;
                let guard = 0;
                while (el.scrollWidth > cw + 1 && fontPx > minPx && guard < 140) {
                    fontPx -= 0.35;
                    el.style.fontSize = fontPx + 'px';
                    guard++;
                }
                if (el.scrollWidth > cw + 1) {
                    const scale = Math.max(0.28, Math.min(1, cw / el.scrollWidth));
                    let origin = 'left center';
                    if (align === 'center' || align === 'start') origin = 'center center';
                    if (align === 'right' || align === 'end') origin = 'right center';
                    el.style.transformOrigin = origin;
                    el.style.transform = 'scaleX(' + scale + ')';
                }
            });
        });
    }

    function syncEditorFromPreviewPages() {
        const hiddenContent = document.getElementById('letter-content-hidden');
        if (!hiddenContent) return;
        const parts = [];
        previewPages.querySelectorAll('.lh-preview .lh-body').forEach(function (bodyEl) {
            const html = (bodyEl.innerHTML || '').trim();
            if (html !== '') parts.push(html);
        });
        hiddenContent.value = parts.join('');
    }

    function enablePreviewEditing() {
        previewPages.querySelectorAll('.lh-preview .lh-body').forEach(function (bodyEl) {
            bodyEl.setAttribute('contenteditable', 'true');
            bodyEl.setAttribute('spellcheck', 'false');
            if (bodyEl.dataset.previewEditBound === '1') return;
            bodyEl.dataset.previewEditBound = '1';
            function schedulePreviewRepaginate() {
                syncEditorFromPreviewPages();
                if (previewInputTimer) window.clearTimeout(previewInputTimer);
                previewInputTimer = window.setTimeout(function () {
                    updatePreview();
                }, 220);
            }
            bodyEl.addEventListener('input', function () {
                schedulePreviewRepaginate();
            });
            bodyEl.addEventListener('keyup', function () {
                schedulePreviewRepaginate();
            });
            bodyEl.addEventListener('blur', function () {
                syncEditorFromPreviewPages();
                updatePreview();
            });
        });
    }

    function updatePreview() {
        const get = (id, d='') => (document.getElementById(id)?.value ?? d);
        const fontKey = get('letterhead-font-family', lhDefaultFontKey);
        const previewFont = resolveFont(fontKey);
        const previewNpFont = resolveNpFont(fontKey);
        const enScale = clamp(parseFloat(get('letterhead-name-en-size', '3')) || 3, 1, 6);
        const npScale = clamp(parseFloat(get('letterhead-name-np-size', '3.15')) || 3.15, 1, 6);
        const adScale = clamp(parseFloat(get('letterhead-address-size', '1.15')) || 1.15, 0.8, 4);
        const letterSpacingInput = clamp(parseFloat(get('letterhead-name-letter-spacing', '0.2')) || 0.2, -1, 4);
        const previewBoxWidth = previewPages.clientWidth || 794;
        const responsiveScale = clamp(previewBoxWidth / 794, 0.38, 1);
        previewPages.style.setProperty('--lh-responsive-scale', String(responsiveScale));
        previewPages.style.setProperty('--lh-font', previewFont);
        previewPages.style.setProperty('--lh-font-np', previewNpFont);
        const headerAlign = get('letterhead-header-alignment', 'left');
        const headerJustify = headerAlign === 'center' ? 'center' : headerAlign === 'right' ? 'flex-end' : 'flex-start';
        previewPages.style.setProperty('--lh-header-justify', headerJustify);
        previewPages.style.setProperty('--lh-text-wrap-flex', headerAlign === 'left' ? '1 1 auto' : '0 1 auto');
        previewPages.style.setProperty('--lh-primary', get('letterhead-primary-color', '#1d4ed8'));
        // Mirror the same size math used in mPDF header:
        // EN => *12pt*0.72, NP => *12pt*0.67, Address => *12pt, pt->px = *1.3333
        previewPages.style.setProperty('--lh-name-en-size', (enScale * 12 * 0.72 * 1.3333).toFixed(2) + 'px');
        previewPages.style.setProperty('--lh-name-np-size', (npScale * 12 * 0.67 * 1.3333).toFixed(2) + 'px');
        previewPages.style.setProperty('--lh-address-size', (adScale * 12 * 1.3333).toFixed(2) + 'px');
        previewPages.style.setProperty('--lh-name-letter-spacing', (letterSpacingInput * 0.75 * 1.3333).toFixed(2) + 'px');
        (function () {
            const raw = get('letterhead-name-line-height', '0.95');
            const n = parseFloat(raw);
            previewPages.style.setProperty('--lh-name-line-height', String(Number.isFinite(n) ? n : 0.95));
        })();
        previewPages.style.setProperty('--lh-name-en-color', get('letterhead-name-en-color', '#0f2a5a'));
        previewPages.style.setProperty('--lh-name-np-color', get('letterhead-name-np-color', '#a31212'));
        previewPages.style.setProperty('--lh-address-color', get('letterhead-address-color', '#a31212'));
        previewPages.style.setProperty('--lh-name-font-style', get('letterhead-name-font-style', 'normal'));
        previewPages.style.setProperty('--lh-name-en-align', get('letterhead-name-en-align', 'center'));
        previewPages.style.setProperty('--lh-name-np-align', get('letterhead-name-np-align', 'center'));
        previewPages.style.setProperty('--lh-address-align', get('letterhead-address-align', 'center'));
        previewPages.style.setProperty('--lh-meta-chs-align', get('letterhead-meta-chs-align', 'left'));
        previewPages.style.setProperty('--lh-meta-ps-align', get('letterhead-meta-ps-align', 'left'));
        previewPages.style.setProperty('--lh-meta-date-align', get('letterhead-meta-date-align', 'right'));
        const hiddenContent = document.getElementById('letter-content-hidden');
        const wmMode = get('letterhead-watermark-mode', 'text');
        const showWatermark = document.getElementById('letterhead-show-watermark').checked;
        const wmOpacity = String(Math.max(1, Math.min(60, parseInt(get('letterhead-watermark-opacity','10'),10) || 10)) / 100);
        const html = hiddenContent ? String(hiddenContent.value || '').trim() : '';
        renderPreviewPages(html, {
            tagline: get('letterhead-tagline', ''),
            address: get('letterhead-address-text', defaultAddressText),
            metaChsValue: get('letterhead-meta-chs-value', ''),
            metaPsValue: get('letterhead-meta-ps-value', ''),
            metaDateValue: get('letterhead-meta-date-value', ''),
            footer: get('letterhead-footer-text', ''),
            watermarkText: get('letterhead-watermark-text', ''),
            watermarkMode: wmMode,
            showWatermark: showWatermark,
            watermarkOpacity: wmOpacity,
        });
    }
    function copyToExport() {
        syncEditorFromPreviewPages();
        const expPages = document.getElementById('export-letter-content-pages-json');
        if (expPages && previewPages) {
            const arr = [];
            previewPages.querySelectorAll('.lh-preview .lh-body').forEach(function (bodyEl) {
                arr.push(bodyEl.innerHTML || '');
            });
            expPages.value = JSON.stringify(arr);
        }
        const map = [
            ['letterhead-font-family','export-letterhead-font-family'],
            ['letterhead-header-alignment','export-letterhead-header-alignment'],
            ['letterhead-tagline','export-letterhead-tagline'],
            ['letterhead-watermark-text','export-letterhead-watermark-text'],
            ['letterhead-watermark-mode','export-letterhead-watermark-mode'],
            ['letterhead-watermark-opacity','export-letterhead-watermark-opacity'],
            ['letterhead-name-en-size','export-letterhead-name-en-size'],
            ['letterhead-name-np-size','export-letterhead-name-np-size'],
            ['letterhead-address-size','export-letterhead-address-size'],
            ['letterhead-name-letter-spacing','export-letterhead-name-letter-spacing'],
            ['letterhead-name-line-height','export-letterhead-name-line-height'],
            ['letterhead-name-en-color','export-letterhead-name-en-color'],
            ['letterhead-name-np-color','export-letterhead-name-np-color'],
            ['letterhead-address-color','export-letterhead-address-color'],
            ['letterhead-name-font-style','export-letterhead-name-font-style'],
            ['letterhead-name-en-align','export-letterhead-name-en-align'],
            ['letterhead-name-np-align','export-letterhead-name-np-align'],
            ['letterhead-address-align','export-letterhead-address-align'],
            ['letterhead-meta-chs-align','export-letterhead-meta-chs-align'],
            ['letterhead-meta-ps-align','export-letterhead-meta-ps-align'],
            ['letterhead-meta-date-align','export-letterhead-meta-date-align'],
            ['letterhead-meta-chs-value','export-letterhead-meta-chs-value'],
            ['letterhead-meta-ps-value','export-letterhead-meta-ps-value'],
            ['letterhead-meta-date-value','export-letterhead-meta-date-value'],
        ];
        map.forEach(function (x) {
            const src = document.getElementById(x[0]); const dst = document.getElementById(x[1]);
            if (src && dst) dst.value = src.value;
        });
        const wmShow = document.getElementById('letterhead-show-watermark');
        const wmShowExport = document.getElementById('export-letterhead-show-watermark');
        if (wmShow && wmShowExport) wmShowExport.value = wmShow.checked ? '1' : '0';
    }

    form.querySelectorAll('input,select,textarea').forEach(function (i) { i.addEventListener('input', updatePreview); i.addEventListener('change', updatePreview); });
    form.addEventListener('submit', function () {
        const lj = document.getElementById('letterhead-layout-json');
        if (lj) lj.value = lhSerializePlacements();
    });
    exportForm.addEventListener('submit', function () {
        copyToExport();
        const ov = document.getElementById('export-letterhead-overlay-json');
        if (ov) ov.value = lhSerializePlacements();
    });
    window.addEventListener('resize', updatePreview);

    updatePreview();
})();
</script>
@endpush
@endsection

