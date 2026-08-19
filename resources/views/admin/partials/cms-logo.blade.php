@php
    $logoId = $logoId ?? ('cms' . substr(md5(uniqid('', true)), 0, 6));
    $logoWidth = $logoWidth ?? 180;
    $logoHeight = $logoHeight ?? 58;
@endphp
<svg class="flex-shrink-0" width="{{ $logoWidth }}" height="{{ $logoHeight }}" viewBox="0 0 340 100" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="CMS Construction Management System">
    <defs>
        <linearGradient id="{{ $logoId }}Blue" x1="0" y1="0" x2="340" y2="0" gradientUnits="userSpaceOnUse">
            <stop stop-color="#1176d7"/>
            <stop offset="1" stop-color="#43bafc"/>
        </linearGradient>
        <linearGradient id="{{ $logoId }}Light" x1="25" y1="75" x2="330" y2="100" gradientUnits="userSpaceOnUse">
            <stop stop-color="#63d4ff"/>
            <stop offset="1" stop-color="#3aafea"/>
        </linearGradient>
        <filter id="{{ $logoId }}Shadow" x="-10%" y="-10%" width="120%" height="120%">
            <feDropShadow dx="0" dy="2" stdDeviation="2" flood-color="#1c3045" flood-opacity=".2"/>
        </filter>
    </defs>
    <text x="0" y="73"
          font-family="'Arial Black', Impact, Arial, sans-serif"
          font-size="85" font-weight="bold"
          font-style="italic"
          fill="url(#{{ $logoId }}Blue)"
          letter-spacing="2"
          filter="url(#{{ $logoId }}Shadow)">CMS</text>
    <path d="M22 81 Q90 95 170 81 Q255 65 320 81 Q300 98 170 97 Q55 94 22 81 Z"
          fill="url(#{{ $logoId }}Light)"
          stroke="#49bdff" stroke-width="1"/>
    <path d="M24 83 Q90 95 170 80 Q255 65 318 83"
          fill="none"
          stroke="#ffffff"
          stroke-width="2"
          opacity="0.2"/>
    <text x="230" y="25" font-size="17" font-family="Arial, sans-serif" fill="#ffffff">construction</text>
    <text x="230" y="48" font-size="17" font-family="Arial, sans-serif" fill="#ffffff">management</text>
    <text x="230" y="71" font-size="17" font-family="Arial, sans-serif" fill="#ffffff">system</text>
</svg>
