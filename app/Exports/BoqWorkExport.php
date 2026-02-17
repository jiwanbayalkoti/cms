<?php

namespace App\Exports;

use App\Models\Company;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class BoqWorkExport implements FromArray, ShouldAutoSize, WithEvents
{
    protected Company $company;

    /** @var array */
    protected $works;

    /** @var array<int> 1-based Excel row numbers for section title rows */
    protected array $sectionTitleRows = [];

    public function __construct(Company $company, $works)
    {
        $this->company = $company;
        $this->works = $works;
    }

    public function array(): array
    {
        $rows = [];
        $excelRow = 1;

        foreach ($this->works as $work) {
            $rows[] = [$work->name ?? '—'];
            $this->sectionTitleRows[] = $excelRow;
            $excelRow++;

            $rows[] = ['SN', 'Item Description', 'Unit', 'Qty', 'Rate', 'Rate in Words', 'Amount'];
            $excelRow++;

            foreach ($work->items as $i => $item) {
                $desc = trim((string) ($item->item_description ?? ''));
                $rateWords = trim((string) ($item->rate_in_words ?? ''));
                $rows[] = [
                    $i + 1,
                    $desc !== '' ? self::descriptionWithLineBreaks($desc) : '–',
                    $item->unit ?: '–',
                    (float) $item->qty,
                    (float) $item->rate,
                    $rateWords !== '' ? self::descriptionWithLineBreaks($rateWords, 35) : '–',
                    (float) $item->amount,
                ];
                $excelRow++;
            }

            if ($work->items->isNotEmpty()) {
                $rows[] = ['', '', '', '', '', 'Total:', $work->items->sum('amount')];
                $excelRow++;
            }

            foreach ($work->children ?? [] as $sub) {
                $rows[] = ['└ ' . ($sub->name ?? '—')];
                $this->sectionTitleRows[] = $excelRow;
                $excelRow++;

                $rows[] = ['SN', 'Item Description', 'Unit', 'Qty', 'Rate', 'Rate in Words', 'Amount'];
                $excelRow++;

                foreach ($sub->items as $i => $item) {
                    $desc = trim((string) ($item->item_description ?? ''));
                    $rateWords = trim((string) ($item->rate_in_words ?? ''));
                    $rows[] = [
                        $i + 1,
                        $desc !== '' ? self::descriptionWithLineBreaks($desc) : '–',
                        $item->unit ?: '–',
                        (float) $item->qty,
                        (float) $item->rate,
                        $rateWords !== '' ? self::descriptionWithLineBreaks($rateWords, 35) : '–',
                        (float) $item->amount,
                    ];
                    $excelRow++;
                }

                if ($sub->items->isNotEmpty()) {
                    $rows[] = ['', '', '', '', '', 'Total:', $sub->items->sum('amount')];
                    $excelRow++;
                }
            }

            $rows[] = [];
            $excelRow++;
        }

        return $rows;
    }

    protected static function descriptionWithLineBreaks(string $text, int $maxCharsPerLine = 55): string
    {
        if ($text === '' || mb_strlen($text) <= $maxCharsPerLine) {
            return $text ?: '–';
        }
        $lines = [];
        $remaining = $text;
        while (mb_strlen($remaining) > $maxCharsPerLine) {
            $chunk = mb_substr($remaining, 0, $maxCharsPerLine);
            $lastSpace = mb_strrpos($chunk, ' ');
            if ($lastSpace !== false && $lastSpace > (int) ($maxCharsPerLine * 0.5)) {
                $lines[] = trim(mb_substr($remaining, 0, $lastSpace));
                $remaining = mb_substr($remaining, $lastSpace + 1);
            } else {
                $lines[] = trim($chunk);
                $remaining = mb_substr($remaining, $maxCharsPerLine);
            }
        }
        if ($remaining !== '') {
            $lines[] = trim($remaining);
        }
        return implode("\n", $lines);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $maxRow = $sheet->getHighestRow();
                $maxCol = 'G';

                foreach ($this->sectionTitleRows as $rowNum) {
                    $sheet->mergeCells('A' . $rowNum . ':' . $maxCol . $rowNum);
                    $sheet->getStyle('A' . $rowNum)->getFont()->setBold(true);
                }

                $sheet->getStyle('A1:' . $maxCol . $maxRow)->getAlignment()->setWrapText(true);
                $sheet->getStyle('A1:' . $maxCol . $maxRow)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
                $sheet->getColumnDimension('F')->setWidth(20);

                for ($r = 1; $r <= $maxRow; $r++) {
                    $bVal = $sheet->getCell('B' . $r)->getValue();
                    if ($bVal === 'Item Description') {
                        $sheet->getStyle('A' . $r . ':' . $maxCol . $r)->getFont()->setBold(true);
                        $sheet->getStyle('A' . $r . ':' . $maxCol . $r)->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FFE9ECEF');
                    }
                }

                $sheet->getStyle('A1:' . $maxCol . $maxRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle('D2:D' . $maxRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('E2:E' . $maxRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('G2:G' . $maxRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            },
        ];
    }
}
