@php
    $lhCompany = $company ?? null;
    $lhTemplate = ($lhCompany->letterhead_template ?? 'red_wave');
    if (!in_array($lhTemplate, ['red_wave'], true)) {
        $lhTemplate = 'red_wave';
    }

    $lhPrimary = $lhCompany->letterhead_primary_color ?? '#1d4ed8';
    $lhAlign = $lhCompany->letterhead_header_alignment ?? 'left';
    $lhTagline = $lhCompany->letterhead_tagline ?: 'Building trust through quality construction';
    $lhFooter = $lhCompany->letterhead_footer_text ?: trim(($lhCompany->address ?? '') . ' ' . ($lhCompany->phone ?? '') . ' ' . ($lhCompany->email ?? ''));
    $lhWatermarkText = $lhCompany->letterhead_watermark_text ?: ($lhCompany->name ?? 'Company');
    $lhShowWatermark = (bool) ($lhCompany->letterhead_show_watermark ?? false);
    $lhWatermarkOpacity = (int) ($lhCompany->letterhead_watermark_opacity ?? 10);
    $lhFooterContactParts = array_filter([
        $lhCompany->address ?? null,
        $lhCompany->phone ?? null,
        $lhCompany->email ?? null,
    ]);
    $lhFooterContact = implode(' | ', $lhFooterContactParts);
    $lhShowBorder = (bool) ($lhCompany->letterhead_show_border ?? true);
    $lhLogoPath = null;
    if (!empty($lhCompany?->logo)) {
        $possibleLogoPath = storage_path('app/public/' . ltrim($lhCompany->logo, '/'));
        if (is_file($possibleLogoPath)) {
            $lhLogoPath = 'file://' . str_replace('\\', '/', $possibleLogoPath);
        }
    }
@endphp

<style>
    .pdf-letterhead { position: relative; margin: -4mm -4mm 8mm; padding: 0; overflow: hidden; }
    .pdf-letterhead.red-wave { border: {{ $lhShowBorder ? '1px solid #d1d5db' : '0' }}; }
    .pdf-letterhead .head { background: {{ $lhPrimary }}; padding: 4mm 4.5mm; min-height: 18mm; }
    .pdf-letterhead .header-table { width: 100%; border-collapse: collapse; }
    .pdf-letterhead .left, .pdf-letterhead .right { vertical-align: top; }
    .pdf-letterhead .left { width: 100%; }
    .pdf-letterhead .right { text-align: right; font-size: 8px; color: rgba(255,255,255,0.95); line-height: 1.5; padding-top: 1.5mm; }
    .pdf-letterhead .brand-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
    .pdf-letterhead .brand-logo { width: 13mm; vertical-align: middle; }
    .pdf-letterhead .brand-name { vertical-align: middle; padding-left: 2.2mm; width: auto; }
    .pdf-letterhead .logo { max-height: 18mm; max-width: 30mm; width: auto; height: auto; display: block; background: transparent; border-radius: 0; padding: 0; }
    .pdf-letterhead .logo-placeholder { width: 10mm; height: 10mm; border-radius: 50%; background: rgba(255,255,255,0.22); color: #fff; text-align: center; line-height: 10mm; font-size: 12px; font-weight: 700; display: inline-block; }
    .pdf-letterhead .name-wrap { display: block; width: 100%; margin-left: 0; }
    .pdf-letterhead .name { display: block; width: 100%; font-size: 12px; font-weight: 700; color: #fff; margin: 0; line-height: 1.2; }
    .pdf-letterhead .tagline { font-size: 8px; color: rgba(255,255,255,0.86); margin: 1mm 0 0; }
    .pdf-letterhead .footer-note { font-size: 8.2px; color: #fff; text-align: center; background: {{ $lhPrimary }}; padding: 1.8mm 2mm; line-height: 1.45; }
    .pdf-letterhead .footer-contact { display: block; font-size: 7.8px; opacity: 0.95; }
    .pdf-letterhead .watermark {
        position: absolute;
        left: 50%;
        top: 58%;
        transform: translate(-50%, -50%) rotate(-28deg);
        font-size: 34px;
        font-weight: 700;
        color: {{ $lhPrimary }};
        opacity: {{ max(0.01, min(0.60, $lhWatermarkOpacity / 100)) }};
        white-space: nowrap;
        z-index: 0;
    }
    .pdf-letterhead .head,
    .pdf-letterhead .footer-note { position: relative; z-index: 2; }
</style>

<div class="pdf-letterhead red-wave">
    @if($lhShowWatermark)
        <div class="watermark">{{ $lhWatermarkText }}</div>
    @endif
    <div class="head">
        <table class="header-table">
            <tr>
                <td class="left" colspan="2">
                    <table class="brand-table">
                        <tr>
                            <td class="brand-logo">
                                @if($lhLogoPath)
                                    <img class="logo" src="{{ $lhLogoPath }}" alt="Company Logo">
                                @else
                                    <span class="logo-placeholder">{{ strtoupper(substr($lhCompany->name ?? 'C', 0, 1)) }}</span>
                                @endif
                            </td>
                            <td class="brand-name">
                                <div class="name-wrap">
                                    <p class="name">{{ $lhCompany->name ?? 'Company' }}</p>
                                    <p class="tagline">{{ $lhTagline }}</p>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="right" colspan="2">
                    {{ $lhCompany->address ?: '-' }}<br>
                    {{ $lhCompany->phone ?: '-' }}<br>
                    {{ $lhCompany->email ?: '-' }}
                </td>
            </tr>
        </table>
    </div>
    @if(!empty($lhFooter))
        <p class="footer-note">
            {{ $lhFooter }}
            @if(!empty($lhFooterContact))
                <span class="footer-contact">{{ $lhFooterContact }}</span>
            @endif
        </p>
    @elseif(!empty($lhFooterContact))
        <p class="footer-note">
            <span class="footer-contact">{{ $lhFooterContact }}</span>
        </p>
    @endif
</div>

