<?php

namespace App\Support;

use Carbon\Carbon;
use InvalidArgumentException;

/**
 * Gregorian (AD) ↔ Bikram Sambat (BS) converter.
 * Reference: 2000-01-01 BS = 1943-04-14 AD
 */
class NepaliDate
{
    public const MONTH_NAMES_EN = [
        1 => 'Baishakh', 2 => 'Jestha', 3 => 'Ashadh', 4 => 'Shrawan',
        5 => 'Bhadra', 6 => 'Ashwin', 7 => 'Kartik', 8 => 'Mangsir',
        9 => 'Poush', 10 => 'Magh', 11 => 'Falgun', 12 => 'Chaitra',
    ];

    public const MONTH_NAMES_NP = [
        1 => 'बैशाख', 2 => 'जेष्ठ', 3 => 'आषाढ', 4 => 'श्रावण',
        5 => 'भदौ', 6 => 'असोज', 7 => 'कार्तिक', 8 => 'मंसिर',
        9 => 'पुष', 10 => 'माघ', 11 => 'फाल्गुन', 12 => 'चैत',
    ];

    /** @var array<int, array<int, int>> days in each BS month keyed by BS year */
    private static array $calendar = [
        2000 => [30, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31],
        2001 => [31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        2002 => [31, 31, 32, 32, 31, 30, 30, 29, 30, 29, 30, 30],
        2003 => [31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31],
        2004 => [30, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31],
        2005 => [31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        2006 => [31, 31, 32, 32, 31, 30, 30, 29, 30, 29, 30, 30],
        2007 => [31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31],
        2008 => [31, 31, 31, 32, 31, 31, 29, 30, 30, 29, 29, 31],
        2009 => [31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        2010 => [31, 31, 32, 32, 31, 30, 30, 29, 30, 29, 30, 30],
        2011 => [31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31],
        2012 => [31, 31, 31, 32, 31, 31, 29, 30, 30, 29, 30, 30],
        2013 => [31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        2014 => [31, 31, 32, 32, 31, 30, 30, 29, 30, 29, 30, 30],
        2015 => [31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31],
        2016 => [31, 31, 31, 32, 31, 31, 29, 30, 30, 29, 30, 30],
        2017 => [31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        2018 => [31, 32, 31, 32, 31, 30, 30, 29, 30, 29, 30, 30],
        2019 => [31, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31],
        2020 => [31, 31, 31, 32, 31, 31, 30, 29, 30, 29, 30, 30],
        2021 => [31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        2022 => [31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 30],
        2023 => [31, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31],
        2024 => [31, 31, 31, 32, 31, 31, 30, 29, 30, 29, 30, 30],
        2025 => [31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        2026 => [31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31],
        2027 => [30, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31],
        2028 => [31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        2029 => [31, 31, 32, 31, 32, 30, 30, 29, 30, 29, 30, 30],
        2030 => [31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31],
        2031 => [30, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31],
        2032 => [31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        2033 => [31, 31, 32, 32, 31, 30, 30, 29, 30, 29, 30, 30],
        2034 => [31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31],
        2035 => [30, 32, 31, 32, 31, 31, 29, 30, 30, 29, 29, 31],
        2036 => [31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        2037 => [31, 31, 32, 32, 31, 30, 30, 29, 30, 29, 30, 30],
        2038 => [31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31],
        2039 => [31, 31, 31, 32, 31, 31, 29, 30, 30, 29, 30, 30],
        2040 => [31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        2041 => [31, 31, 32, 32, 31, 30, 30, 29, 30, 29, 30, 30],
        2042 => [31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31],
        2043 => [31, 31, 31, 32, 31, 31, 29, 30, 30, 29, 30, 30],
        2044 => [31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        2045 => [31, 32, 31, 32, 31, 30, 30, 29, 30, 29, 30, 30],
        2046 => [31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31],
        2047 => [31, 31, 31, 32, 31, 31, 30, 29, 30, 29, 30, 30],
        2048 => [31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        2049 => [31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 30],
        2050 => [31, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31],
        2051 => [31, 31, 31, 32, 31, 31, 30, 29, 30, 29, 30, 30],
        2052 => [31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        2053 => [31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 30],
        2054 => [31, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31],
        2055 => [31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        2056 => [31, 31, 32, 31, 32, 30, 30, 29, 30, 29, 30, 30],
        2057 => [31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31],
        2058 => [30, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31],
        2059 => [31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        2060 => [31, 31, 32, 32, 31, 30, 30, 29, 30, 29, 30, 30],
        2061 => [31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31],
        2062 => [30, 32, 31, 32, 31, 31, 29, 30, 29, 30, 29, 31],
        2063 => [31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        2064 => [31, 31, 32, 32, 31, 30, 30, 29, 30, 29, 30, 30],
        2065 => [31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31],
        2066 => [31, 31, 31, 32, 31, 31, 29, 30, 30, 29, 29, 31],
        2067 => [31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        2068 => [31, 31, 32, 32, 31, 30, 30, 29, 30, 29, 30, 30],
        2069 => [31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31],
        2070 => [31, 31, 31, 32, 31, 31, 29, 30, 30, 29, 30, 30],
        2071 => [31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        2072 => [31, 32, 31, 32, 31, 30, 30, 29, 30, 29, 30, 30],
        2073 => [31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31],
        2074 => [31, 31, 31, 32, 31, 31, 30, 29, 30, 29, 30, 30],
        2075 => [31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        2076 => [31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 30],
        2077 => [31, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31],
        2078 => [31, 31, 31, 32, 31, 31, 30, 29, 30, 29, 30, 30],
        2079 => [31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        2080 => [31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 30],
        2081 => [31, 31, 32, 32, 31, 30, 30, 30, 29, 30, 30, 30],
        2082 => [30, 32, 31, 32, 31, 30, 30, 30, 29, 30, 30, 30],
        2083 => [31, 31, 32, 31, 31, 31, 30, 30, 29, 30, 30, 30],
        2084 => [31, 31, 32, 31, 31, 30, 30, 30, 29, 30, 30, 30],
        2085 => [31, 32, 31, 32, 30, 31, 30, 30, 29, 30, 30, 30],
        2086 => [30, 32, 31, 32, 31, 30, 30, 30, 29, 30, 30, 30],
        2087 => [31, 31, 32, 31, 31, 31, 30, 30, 29, 30, 30, 30],
        2088 => [30, 31, 32, 32, 30, 31, 30, 30, 29, 30, 30, 30],
        2089 => [30, 32, 31, 32, 31, 30, 30, 30, 29, 30, 30, 30],
        2090 => [30, 32, 31, 32, 31, 30, 30, 30, 29, 30, 30, 30],
        2091 => [31, 31, 32, 31, 31, 31, 30, 30, 29, 30, 30, 30],
        2092 => [30, 31, 32, 32, 31, 30, 30, 30, 29, 30, 30, 30],
        2093 => [30, 32, 31, 32, 31, 30, 30, 30, 29, 30, 30, 30],
        2094 => [31, 31, 32, 31, 31, 30, 30, 30, 29, 30, 30, 30],
        2095 => [31, 31, 32, 31, 31, 31, 30, 29, 30, 30, 30, 30],
        2096 => [30, 31, 32, 32, 31, 30, 30, 29, 30, 29, 30, 30],
        2097 => [31, 32, 31, 32, 31, 30, 30, 30, 29, 30, 30, 30],
        2098 => [31, 31, 32, 31, 31, 31, 29, 30, 29, 30, 30, 31],
        2099 => [31, 31, 32, 31, 31, 31, 30, 29, 29, 30, 30, 30],
        2100 => [31, 32, 31, 32, 30, 31, 30, 29, 30, 29, 30, 30],
    ];

    public static function adToBs(string|Carbon $adDate): array
    {
        $ad = $adDate instanceof Carbon ? $adDate->copy()->startOfDay() : Carbon::parse($adDate)->startOfDay();
        $ref = Carbon::create(1943, 4, 14)->startOfDay();
        $totalDays = (int) round(($ad->getTimestamp() - $ref->getTimestamp()) / 86400);

        if ($totalDays < 0) {
            throw new InvalidArgumentException('Date is before supported BS range.');
        }

        $bsYear = 2000;
        $bsMonth = 1;

        while (true) {
            if (! isset(self::$calendar[$bsYear])) {
                throw new InvalidArgumentException('Date is outside supported BS range.');
            }
            $dim = self::$calendar[$bsYear][$bsMonth - 1];
            if ($totalDays < $dim) {
                $bsDay = $totalDays + 1;
                break;
            }
            $totalDays -= $dim;
            $bsMonth++;
            if ($bsMonth > 12) {
                $bsMonth = 1;
                $bsYear++;
            }
        }

        return [
            'year' => $bsYear,
            'month' => $bsMonth,
            'day' => $bsDay,
            'month_name' => self::MONTH_NAMES_EN[$bsMonth],
            'month_name_np' => self::MONTH_NAMES_NP[$bsMonth],
            'formatted' => sprintf('%04d-%02d-%02d', $bsYear, $bsMonth, $bsDay),
            'label' => self::MONTH_NAMES_EN[$bsMonth] . ' ' . $bsDay . ', ' . $bsYear,
        ];
    }

    public static function bsToAd(int $year, int $month, int $day): Carbon
    {
        if (! isset(self::$calendar[$year]) || $month < 1 || $month > 12) {
            throw new InvalidArgumentException('Invalid BS date.');
        }
        $daysInMonth = self::$calendar[$year][$month - 1];
        if ($day < 1 || $day > $daysInMonth) {
            throw new InvalidArgumentException('Invalid BS day for month.');
        }

        $totalDays = 0;
        for ($y = 2000; $y < $year; $y++) {
            if (! isset(self::$calendar[$y])) {
                throw new InvalidArgumentException('BS year out of range.');
            }
            $totalDays += array_sum(self::$calendar[$y]);
        }
        for ($m = 0; $m < $month - 1; $m++) {
            $totalDays += self::$calendar[$year][$m];
        }
        $totalDays += $day - 1;

        return Carbon::create(1943, 4, 14)->addDays($totalDays)->startOfDay();
    }

    public static function daysInBsMonth(int $year, int $month): int
    {
        if (! isset(self::$calendar[$year]) || $month < 1 || $month > 12) {
            throw new InvalidArgumentException('Invalid BS year/month.');
        }

        return self::$calendar[$year][$month - 1];
    }

    public static function minBsYear(): int
    {
        return (int) min(array_keys(self::$calendar));
    }

    public static function maxBsYear(): int
    {
        return (int) max(array_keys(self::$calendar));
    }

    /**
     * Build day columns for a month view (AD or BS).
     * Each day includes ad_date (Y-m-d) for storage.
     *
     * @return array{year:int, month:int, month_label:string, days:list<array>}
     */
    public static function monthGrid(string $calendar, int $year, int $month): array
    {
        $days = [];

        if ($calendar === 'bs') {
            $count = self::daysInBsMonth($year, $month);
            $monthLabel = self::MONTH_NAMES_EN[$month] . ' ' . $year . ' BS';
            for ($d = 1; $d <= $count; $d++) {
                $ad = self::bsToAd($year, $month, $d);
                $days[] = [
                    'day' => $d,
                    'ad_date' => $ad->toDateString(),
                    'weekday' => $ad->format('D'),
                    'is_weekend' => $ad->isWeekend(),
                    'is_today' => $ad->isToday(),
                    'bs_label' => $d,
                    'ad_label' => $ad->format('j'),
                    'secondary' => $ad->format('M j'),
                ];
            }
        } else {
            $start = Carbon::create($year, $month, 1)->startOfDay();
            $count = $start->daysInMonth;
            $monthLabel = $start->format('F Y') . ' AD';
            for ($d = 1; $d <= $count; $d++) {
                $ad = Carbon::create($year, $month, $d)->startOfDay();
                $bs = self::adToBs($ad);
                $days[] = [
                    'day' => $d,
                    'ad_date' => $ad->toDateString(),
                    'weekday' => $ad->format('D'),
                    'is_weekend' => $ad->isWeekend(),
                    'is_today' => $ad->isToday(),
                    'bs_label' => $bs['day'],
                    'ad_label' => $d,
                    'secondary' => $bs['month_name'] . ' ' . $bs['day'],
                ];
            }
        }

        return [
            'year' => $year,
            'month' => $month,
            'month_label' => $monthLabel,
            'days' => $days,
        ];
    }

    public static function shiftMonth(string $calendar, int $year, int $month, int $delta): array
    {
        $month += $delta;
        while ($month > 12) {
            $month -= 12;
            $year++;
        }
        while ($month < 1) {
            $month += 12;
            $year--;
        }

        if ($calendar === 'bs') {
            $year = max(self::minBsYear(), min(self::maxBsYear(), $year));
        }

        return ['year' => $year, 'month' => $month];
    }

    public static function fromAdMonth(Carbon $ad): array
    {
        return [
            'calendar' => 'ad',
            'year' => (int) $ad->year,
            'month' => (int) $ad->month,
        ];
    }

    public static function fromBsOfAd(Carbon $ad): array
    {
        $bs = self::adToBs($ad);

        return [
            'calendar' => 'bs',
            'year' => $bs['year'],
            'month' => $bs['month'],
        ];
    }

    /**
     * Anchor date when switching AD ↔ BS so the current period stays selected.
     * Prefer today if it falls inside the visible grid; otherwise use the mid day.
     *
     * @param  list<array{ad_date:string}>  $days
     */
    public static function switchAnchorDate(array $days): Carbon
    {
        if ($days === []) {
            return Carbon::today();
        }

        $start = Carbon::parse($days[0]['ad_date'])->startOfDay();
        $end = Carbon::parse($days[count($days) - 1]['ad_date'])->startOfDay();
        $today = Carbon::today();

        if ($today->betweenIncluded($start, $end)) {
            return $today;
        }

        $mid = (int) floor((count($days) - 1) / 2);

        return Carbon::parse($days[$mid]['ad_date'])->startOfDay();
    }
}
