<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoqItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'boq_work_id',
        'item_description',
        'unit',
        'qty',
        'rate',
        'rate_in_words',
        'amount',
        'sort_order',
    ];

    protected $casts = [
        'qty' => 'decimal:4',
        'rate' => 'decimal:4',
        'amount' => 'decimal:4',
    ];

    public function work()
    {
        return $this->belongsTo(BoqWork::class, 'boq_work_id');
    }

    public static function rateToWords($number): string
    {
        $number = (float) $number;
        if ($number == 0) {
            return 'Zero';
        }
        $integer = (int) floor($number);
        $decimal = round(($number - $integer) * 100);
        $words = self::intToWords($integer);
        if ($decimal > 0) {
            $words .= ' and ' . self::intToWords($decimal) . ' Paisa';
        }
        return $words . ' Only';
    }

    protected static function intToWords($num): string
    {
        $num = (int) $num;
        if ($num === 0) {
            return '';
        }
        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
            'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        if ($num < 20) {
            return $ones[$num];
        }
        if ($num < 100) {
            return $tens[(int)($num / 10)] . ($num % 10 ? ' ' . $ones[$num % 10] : '');
        }
        if ($num < 1000) {
            return $ones[(int)($num / 100)] . ' Hundred' . ($num % 100 ? ' ' . self::intToWords($num % 100) : '');
        }
        if ($num < 100000) {
            return self::intToWords((int)($num / 1000)) . ' Thousand' . ($num % 1000 ? ' ' . self::intToWords($num % 1000) : '');
        }
        if ($num < 10000000) {
            return self::intToWords((int)($num / 100000)) . ' Lakh' . ($num % 100000 ? ' ' . self::intToWords($num % 100000) : '');
        }
        return self::intToWords((int)($num / 10000000)) . ' Crore' . ($num % 10000000 ? ' ' . self::intToWords($num % 10000000) : '');
    }
}
