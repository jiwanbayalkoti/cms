@php
    $c = $company;
    $nameEnAlign = in_array($c->letterhead_name_en_align, ['left', 'center', 'right'], true) ? $c->letterhead_name_en_align : 'center';
    $nameNpAlign = in_array($c->letterhead_name_np_align, ['left', 'center', 'right'], true) ? $c->letterhead_name_np_align : 'center';
    $addressAlign = in_array($c->letterhead_address_align, ['left', 'center', 'right'], true) ? $c->letterhead_address_align : 'center';
    $metaChsAlign = in_array($c->letterhead_meta_chs_align, ['left', 'center', 'right'], true) ? $c->letterhead_meta_chs_align : 'left';
    $metaPsAlign = in_array($c->letterhead_meta_ps_align, ['left', 'center', 'right'], true) ? $c->letterhead_meta_ps_align : 'left';
    $metaDateAlign = in_array($c->letterhead_meta_date_align, ['left', 'center', 'right'], true) ? $c->letterhead_meta_date_align : 'right';
    $metaChsValue = trim((string) ($c->letterhead_meta_chs_value ?? ''));
    $metaPsValue = trim((string) ($c->letterhead_meta_ps_value ?? ''));
    $metaDateValue = trim((string) ($c->letterhead_meta_date_value ?? ''));
    $nameEnPt = max(1.0, min(6.0, (float) ($c->letterhead_name_en_size ?? 3.0))) * 12.0;
    $nameNpPt = max(1.0, min(6.0, (float) ($c->letterhead_name_np_size ?? 3.15))) * 12.0;
    $addressPt = max(0.8, min(4.0, (float) ($c->letterhead_address_size ?? 1.15))) * 12.0;
    $letterSpacingPt = max(-1.0, min(4.0, (float) ($c->letterhead_name_letter_spacing ?? 0.2))) * 0.75;
    // Same gap model as letterhead preview: gapPx = 2 + (S - 0.8) * 28, then CSS px → mm (96dpi).
    $nameLinesSpacing = max(0, min(1.6, (float) ($c->letterhead_name_line_height ?? 0.95)));
    $headerRowGapPx = 2.0 + ($nameLinesSpacing - 0.8) * 28.0;
    $headerRowGapMm = round($headerRowGapPx * (25.4 / 96.0), 3);
    $headerSpanLineHeight = 1.12;
    $addressLine = trim((string) ($c->address ?? ''));
    $nameEnColor = preg_match('/^#[0-9A-Fa-f]{6}$/', (string) $c->letterhead_name_en_color) ? $c->letterhead_name_en_color : '#0f2a5a';
    $nameNpColor = preg_match('/^#[0-9A-Fa-f]{6}$/', (string) $c->letterhead_name_np_color) ? $c->letterhead_name_np_color : '#a31212';
    $addressColor = preg_match('/^#[0-9A-Fa-f]{6}$/', (string) $c->letterhead_address_color) ? $c->letterhead_address_color : '#a31212';
    $fontStyle = in_array($c->letterhead_name_font_style, ['normal', 'italic', 'oblique'], true) ? $c->letterhead_name_font_style : 'normal';
    $headerAlign = in_array($c->letterhead_header_alignment ?? '', ['left', 'center', 'right'], true)
        ? $c->letterhead_header_alignment
        : 'left';
    $logoPath = null;
    if (!empty($c->logo)) {
        $p = storage_path('app/public/' . ltrim($c->logo, '/'));
        if (is_file($p)) { $logoPath = 'file://' . str_replace('\\', '/', $p); }
    }
    // Match preview: single line. mPDF often ignores CSS white-space:nowrap in tables — use NBSP between words
    // and shrink pt so the line fits the text column (~logo 27mm; remaining ~55% page).
    $nameEnDisplayPt = round($nameEnPt * 0.72, 2);
    $nameStr = (string) ($c->name ?? '');
    $nameLen = mb_strlen($nameStr);
    if ($nameLen > 1) {
        $nameEnDisplayPt = max(6.5, round($nameEnDisplayPt * min(1.0, 28 / $nameLen), 2));
    }
    $taglineDisplayPt = round($nameNpPt * 0.67, 2);
    $taglineStr = (string) ($c->letterhead_tagline ?? '');
    $tagLen = mb_strlen($taglineStr);
    if ($tagLen > 1) {
        $taglineDisplayPt = max(6.5, round($taglineDisplayPt * min(1.0, 42 / $tagLen), 2));
    }
    $nbsp = "\xc2\xa0";
    $nameForPdf = $nameStr === '' ? '' : (preg_replace('/\s+/u', $nbsp, trim($nameStr)) ?? trim($nameStr));
    $taglineForPdf = trim($taglineStr) === '' ? '' : (preg_replace('/\s+/u', $nbsp, trim($taglineStr)) ?? trim($taglineStr));
    $addressForPdf = $addressLine === '' ? '' : (preg_replace('/\s+/u', $nbsp, $addressLine) ?? $addressLine);
@endphp
<table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;table-layout:fixed;font-family:{{ $pdfFontStack ?? 'freeserif, notosans, notodevanagari, serif' }};color:#111827;">
    <tr style="height:28mm;">
        <td style="padding:0.2mm 0.8mm 0 0.3mm;vertical-align:top;text-align:{{ $headerAlign }};">
            <table cellpadding="0" cellspacing="0" style="border-collapse:collapse;{{ $headerAlign === 'left' ? 'width:100%;table-layout:fixed;' : 'display:inline-table;table-layout:auto;max-width:100%;' }}">
                <tr>
                    <td style="width:27mm;vertical-align:top;padding:0.8mm 0.8mm 0 0.8mm;">
                        @if($logoPath)
                            <img src="{{ $logoPath }}" alt="" style="max-height:24mm;max-width:24mm;display:block;">
                        @endif
                    </td>
                    <td style="vertical-align:top;padding-top:0.4mm;">
                        {{-- mPDF aligns Indic/Latin lines reliably via td align + inline span, not text-align on full-width divs --}}
                        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;"><tr>
                            <td align="{{ $nameEnAlign }}" style="padding:0;vertical-align:top;">
                                <span style="font-size:{{ $nameEnDisplayPt }}pt;font-weight:800;color:{{ $nameEnColor }};line-height:{{ $headerSpanLineHeight }};letter-spacing:{{ $letterSpacingPt }}pt;font-style:{{ $fontStyle }};white-space:nowrap;">{{ $nameForPdf }}</span>
                            </td>
                        </tr></table>
                        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;margin-top:{{ $headerRowGapMm }}mm;">
                            <tr>
                                <td align="{{ $nameNpAlign }}" style="padding:0 1.2mm 0.25mm 0;vertical-align:top;">
                                    <span style="font-size:{{ $taglineDisplayPt }}pt;font-weight:800;color:{{ $nameNpColor }};line-height:{{ $headerSpanLineHeight }};letter-spacing:{{ $letterSpacingPt }}pt;font-style:{{ $fontStyle }};font-family:notodevanagari, notosans, freeserif, serif;white-space:nowrap;">{{ $taglineForPdf }}</span>
                                </td>
                            </tr>
                        </table>
                        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;margin-top:{{ $headerRowGapMm }}mm;">
                            <tr>
                                <td align="{{ $addressAlign }}" style="padding:0;vertical-align:top;">
                                    <span style="font-size:{{ round($addressPt, 2) }}pt;font-weight:800;color:{{ $addressColor }};line-height:{{ $headerSpanLineHeight }};letter-spacing:{{ $letterSpacingPt }}pt;font-style:{{ $fontStyle }};font-family:notodevanagari, notosans, freeserif, serif;white-space:nowrap;">{{ $addressForPdf }}</span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="2" style="padding:0;">
            <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;margin-bottom:0.2mm;">
                <tr>
                    <td style="width:33%;padding:0 0.8mm 0 0;">
                        <div style="font-size:9.2pt;line-height:1.35;text-align:{{ $metaChsAlign }};margin-bottom:0.55mm;">च.स.&nbsp;<span style="display:inline-block;width:18mm;{{ $metaChsValue === '' ? 'border-bottom:0.2mm dotted #6b7280;' : '' }}">{!! $metaChsValue !== '' ? e($metaChsValue) : '&nbsp;' !!}</span></div>
                        <div style="font-size:9.2pt;line-height:1.35;text-align:{{ $metaPsAlign }};">प.स.&nbsp;<span style="display:inline-block;width:18mm;{{ $metaPsValue === '' ? 'border-bottom:0.2mm dotted #6b7280;' : '' }}">{!! $metaPsValue !== '' ? e($metaPsValue) : '&nbsp;' !!}</span></div>
                    </td>
                   <td style="width:34%;padding:0 1mm 0 1mm;text-align:center;">&nbsp;</td>
                    <td style="width:33%;padding:0 10mm 0 0.8mm;text-align:{{ $metaDateAlign }};vertical-align:top;" align="{{ $metaDateAlign }}">
                        <div style="display:block;width:100%;font-size:9.2pt;line-height:1.35;text-align:{{ $metaDateAlign }};">मिति:&nbsp;<span style="display:inline-block;width:30mm;{{ $metaDateValue === '' ? 'border-bottom:0.2mm dotted #6b7280;' : '' }}">{!! $metaDateValue !== '' ? e($metaDateValue) : '&nbsp;' !!}</span></div>
                    </td>
                </tr>
            </table>
            <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                <tr>
                    <td style="border-top:0.7mm solid #2a5da8;font-size:2pt;line-height:2pt;height:2pt;padding:0;">&nbsp;</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

