<?php

namespace App\Exports;

use App\Models\Company;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class BoqMeasurementBookExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithCustomStartCell, WithEvents
{
    protected Company $company;

    /** @var array<int, array{work: \App\Models\BoqWork, rows: array}> */
    protected array $workAggregated;

    /** @var array<int, array{work_name: string, row: array}> */
    protected array $flatRows = [];

    protected string $dimensionUnit = 'm';

    public function __construct(Company $company, array $workAggregated)
    {
        $this->company = $company;
        $this->workAggregated = $workAggregated;
        if (!empty($workAggregated) && isset($workAggregated[0]['dimension_unit'])) {
            $this->dimensionUnit = $workAggregated[0]['dimension_unit'];
        }
        foreach ($workAggregated as $block) {
            $workName = $block['work']->name ?? '—';
            foreach ($block['rows'] as $row) {
                $this->flatRows[] = ['work_name' => $workName, 'row' => $row];
            }
        }
    }

    public function collection(): Collection
    {
        return collect($this->flatRows);
    }

    public function headings(): array
    {
        $u = $this->dimensionUnit;
        return [
            'SN',
            'Works',
            'no',
            "Length ({$u})",
            "Breadth ({$u})",
            "Height ({$u})",
            'Quantity',
            'Total qty',
            'Unit',
        ];
    }

    protected static function descriptionWithLineBreaks(string $text, int $maxCharsPerLine = 55): string
    {
        $text = trim($text);
        if ($text === '' || mb_strlen($text) <= $maxCharsPerLine) {
            return $text ?: '—';
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

    public function map($item): array
    {
        $row = $item['row'];
        $workName = $item['work_name'];
        static $snByWork = [];
        if (!isset($snByWork[$workName])) {
            $snByWork[$workName] = 0;
        }
        if ($row['type'] === 'main') {
            $snByWork[$workName]++;
        }
        $sn = $row['type'] === 'main' ? $snByWork[$workName] : '';

        $rawDescription = (string) ($row['type'] === 'sub'
            ? ('└ ' . ($row['description'] ?? '—'))
            : ($row['boqItem']->item_description ?? '—'));
        $description = self::descriptionWithLineBreaks($rawDescription);
        $no = isset($row['no']) && $row['no'] !== null && $row['no'] !== '' ? (float) $row['no'] : '';
        $length = isset($row['length']) && $row['length'] !== null && $row['length'] !== '' ? (float) $row['length'] : '';
        $breadth = isset($row['breadth']) && $row['breadth'] !== null && $row['breadth'] !== '' ? (float) $row['breadth'] : '';
        $height = isset($row['height']) && $row['height'] !== null && $row['height'] !== '' ? (float) $row['height'] : '';
        $quantity = $row['quantity'] ?? $row['total_qty'];
        $totalQty = $row['total_qty'];
        $unit = $row['type'] === 'main' ? ($row['boqItem']->unit ?? '–') : ($row['unit'] ?? '–');

        return [
            $sn,
            $description,
            $no,
            $length,
            $breadth,
            $height,
            $quantity,
            $totalQty,
            $unit,
        ];
    }

    public function startCell(): string
    {
        return 'A8';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $ws = $event->sheet->getDelegate();
                $company = $this->company;
                $lastRow = 7 + count($this->flatRows);

                $ws->setCellValue('A1', 'Company: ' . ($company->name ?? ''));
                $ws->mergeCells('A1:I1');
                $ws->setCellValue('A2', 'Client: ' . ($company->client ?? ''));
                $ws->mergeCells('A2:I2');
                $ws->setCellValue('A3', 'Project: ' . ($company->project ?? ''));
                $ws->mergeCells('A3:I3');
                $ws->setCellValue('A4', 'Contract No: ' . ($company->contract_no ?? ''));
                $ws->mergeCells('A4:I4');
                $ws->setCellValue('A5', 'Bill Date: ' . ($company->bill_date ? $company->bill_date->format('Y-m-d') : ''));
                $ws->mergeCells('A5:I5');
                $ws->setCellValue('A6', 'Measurement Book (BoQ) – All completed work');
                $ws->mergeCells('A6:I6');
                $ws->getStyle('A6')->getFont()->setBold(true)->setSize(12);

                $ws->getStyle('A8:I8')->getFont()->setBold(true);
                $ws->getStyle('A8:I8')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                $ws->getStyle('A8:I8')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE0E0E0');

                if ($lastRow >= 8) {
                    $ws->getStyle('A8:I' . $lastRow)->getBorders()->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN);
                }
                if ($lastRow >= 9) {
                    $ws->getColumnDimension('B')->setWidth(28);
                    $ws->getStyle('B9:B' . $lastRow)->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
                    $ws->getStyle('C9:I' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $ws->getStyle('G9:H' . $lastRow)->getNumberFormat()->setFormatCode('0.0000');
                    $charsPerLine = 48;
                    foreach ($this->flatRows as $i => $item) {
                        $excelRow = 9 + $i;
                        $desc = $item['row']['type'] === 'sub'
                            ? ($item['row']['description'] ?? '')
                            : ($item['row']['boqItem']->item_description ?? '');
                        $lines = (int) max(1, ceil(mb_strlen($desc) / $charsPerLine));
                        $rowHeight = (float) min(250, max(15, $lines * 12));
                        $ws->getRowDimension($excelRow)->setRowHeight($rowHeight);
                    }
                }
            },
        ];
    }
}
