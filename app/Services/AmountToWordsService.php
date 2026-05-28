<?php

namespace App\Services;

class AmountToWordsService
{
    private const ONES = [
        '', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine',
        'ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen',
        'seventeen', 'eighteen', 'nineteen',
    ];

    private const TENS = [
        '', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety',
    ];

    public function rupeesPaisaToWords(float $amount): string
    {
        $rupees = (int) floor($amount);
        $paisa = (int) round(($amount - $rupees) * 100);

        if ($paisa === 100) {
            $rupees++;
            $paisa = 0;
        }

        $words = $this->numberToWords($rupees) . ' rupees';

        if ($paisa > 0) {
            $words .= ' and ' . $this->numberToWords($paisa) . ' paisa';
        }

        return ucfirst($words) . ' only.';
    }

    private function numberToWords(int $n): string
    {
        if ($n === 0) {
            return 'zero';
        }

        if ($n < 0) {
            return 'minus ' . $this->numberToWords(abs($n));
        }

        $parts = [];

        foreach ([10000000 => 'crore', 100000 => 'lakh', 1000 => 'thousand', 100 => 'hundred'] as $value => $name) {
            if ($n >= $value) {
                $count = intdiv($n, $value);
                $n %= $value;
                $parts[] = $this->numberToWords($count) . ' ' . $name;
            }
        }

        if ($n >= 20) {
            $parts[] = self::TENS[intdiv($n, 10)];
            $n %= 10;
        }

        if ($n > 0) {
            $parts[] = self::ONES[$n];
        }

        return implode(' ', array_filter($parts));
    }
}
