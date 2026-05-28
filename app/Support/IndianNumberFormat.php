<?php

namespace App\Support;

class IndianNumberFormat
{
    public static function format(float|string $number, int $decimals = 2): string
    {
        $negative = (float) $number < 0;
        $number = abs((float) $number);
        $formatted = number_format($number, $decimals, '.', '');
        $parts = explode('.', $formatted);
        $integer = $parts[0];
        $decimal = $parts[1] ?? str_repeat('0', $decimals);

        $lastThree = substr($integer, -3);
        $rest = strlen($integer) > 3 ? substr($integer, 0, -3) : '';
        if ($rest !== '') {
            $rest = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest);
            $integer = $rest . ',' . $lastThree;
        } else {
            $integer = $lastThree;
        }

        $out = $integer;
        if ($decimals > 0) {
            $out .= '.' . $decimal;
        }

        return ($negative ? '-' : '') . $out;
    }
}
