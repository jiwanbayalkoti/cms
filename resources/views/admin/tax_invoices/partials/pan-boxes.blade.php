@php
    $pan = preg_replace('/\D/', '', (string) ($pan ?? ''));
    $pan = str_pad(substr($pan, 0, 9), 9, ' ', STR_PAD_RIGHT);
    $digits = str_split($pan);
@endphp
<span class="pan-boxes">
    @foreach($digits as $d)
        <span class="pan-box">{{ $d === ' ' ? '' : $d }}</span>
    @endforeach
</span>
