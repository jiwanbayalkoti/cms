@php
    $lhShowBorder = (bool) ($company->letterhead_show_border ?? true);
    $wrapBorder = $lhShowBorder ? 'border: 0.35mm solid #d1d5db;' : '';
    // Letter body line height (readable); not the same as header "name lines spacing".
    $pdfBodyLineHeight = 1.55;
    $pdfPages = $letterContentPages ?? null;
    if (!is_array($pdfPages) || count($pdfPages) === 0) {
        $pdfPages = [$letterContent ?? ''];
    }
@endphp
<style>
    .lh-pdf-page-wrap { font-family: {{ $pdfFontStack ?? 'freeserif, notosans, notodevanagari, serif' }}; color: #111827; }
    .lh-pdf-body { padding: 0 2mm 7mm; min-height: 40mm; font-size: 12pt; line-height: {{ $pdfBodyLineHeight }}; font-weight: 600; text-align: left; }
    .lh-pdf-body p { margin: 0 0 3.5mm; text-align: left; line-height: {{ $pdfBodyLineHeight }}; }
    .lh-pdf-body h1, .lh-pdf-body h2, .lh-pdf-body h3, .lh-pdf-body h4, .lh-pdf-body h5, .lh-pdf-body h6 { margin: 0 0 3.5mm; font-weight: 700; line-height: 1.35; }
    .lh-pdf-body ul, .lh-pdf-body ol { margin: 0 0 3.5mm; padding-left: 1.25em; }
    .lh-pdf-body li { margin: 0 0 0.35em; line-height: {{ $pdfBodyLineHeight }}; }
    .lh-pdf-body blockquote { margin: 0 0 3.5mm; padding-left: 0.75em; border-left: 0.4mm solid #e5e7eb; }
    .lh-pdf-body table { border-collapse: collapse; width: 100%; margin: 0 0 3.5mm; font-size: inherit; }
    .lh-pdf-body th, .lh-pdf-body td { border: 0.15mm solid #d1d5db; padding: 0.5mm 1.5mm; vertical-align: top; }
</style>
@foreach($pdfPages as $idx => $pageChunk)
@if($idx > 0)
<pagebreak />
@endif
<div class="lh-pdf-page-wrap" style="{{ $wrapBorder }}">
    <div class="lh-pdf-body">{!! $pageChunk !!}</div>
</div>
@endforeach

