@php
    $c = $company;
    $pan = trim((string) ($c->tax_number ?? ''));
@endphp
<div style="border-top:0.55mm solid #c41e3a;padding-top:1.5mm;text-align:center;font-size:8.8pt;color:#b91c1c;font-family:freeserif, notosans, notodevanagari, serif;">
    Contact us on: Phone number: {{ $c->phone ?: '-' }} | Email: {{ $c->email ?: '-' }} | PAN No.: {{ $pan !== '' ? $pan : '-' }}
</div>

