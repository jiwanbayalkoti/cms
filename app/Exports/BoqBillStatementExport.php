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

class BoqBillStatementExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithCustomStartCell, WithEvents
{
    protected Company $company;

    /** @var array */
    protected array $rows;

    protected float $subtotal;
    protected int $taxPercent;
    protected float $taxAmount;
    protected float $total;
    protected string $billDate;

    public function __construct(Company $company, array $rows, float $subtotal, int $taxPercent, float $taxAmount, float $total, string $billDate)
    {
        $this->company = $company;
        $this->rows = $rows;
        $this->subtotal = $subtotal;
        $this->taxPercent = $taxPercent;
        $this->taxAmount = $taxAmount;
        $this->total = $total;
        $this->billDate = $billDate;
    }

    public function collection(): Collection
    {
        return collect($this->rows);
    }

    public function headings(): array
    {
        return []; // Two-row header written in AfterSheet
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

    public function map($row): array
    {
        static $sn = 0;
        $sn++;
        $boqItem = $row['boqItem'];
        $description = (string) ($boqItem->item_description ?? '—');
        return [
            $sn,
            self::descriptionWithLineBreaks($description),
            $boqItem->unit ?? '—',
            (float) ($boqItem->qty ?? 0),
            (float) ($boqItem->rate ?? 0),
            $row['total_qty'],
            (float) ($boqItem->rate ?? 0),
            $row['total_price'],
            $row['remaining_qty'] ?? 0,
            '–',
        ];
    }

    public function startCell(): string
    {
        return 'A10'; // Row 8–9 = two-row header, data from 10
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $ws = $event->sheet->getDelegate();
                $company = $this->company;
                $dataStartRow = 10;
                $lastRow = $dataStartRow - 1 + count($this->rows);
                $summaryRow = $lastRow + 2;

                $ws->setCellValue('A1', 'Company: ' . ($company->name ?? ''));
                $ws->mergeCells('A1:J1');
                $ws->setCellValue('A2', 'Client: ' . ($company->client ?? ''));
                $ws->mergeCells('A2:J2');
                $ws->setCellValue('A3', 'Project: ' . ($company->project ?? ''));
                $ws->mergeCells('A3:J3');
                $ws->setCellValue('A4', 'Contract No: ' . ($company->contract_no ?? ''));
                $ws->mergeCells('A4:J4');
                $ws->setCellValue('A5', 'Bill Date: ' . $this->billDate);
                $ws->mergeCells('A5:J5');
                $ws->setCellValue('A6', 'Bill Statement (BoQ)');
                $ws->mergeCells('A6:J6');
                $ws->getStyle('A6')->getFont()->setBold(true)->setSize(12);

                // Two-row header (same as web): Row 8 = group headers, Row 9 = sub-headers
                $ws->setCellValue('A8', 'SN');
                $ws->setCellValue('B8', 'Description of works');
                $ws->mergeCells('C8:E8');
                $ws->setCellValue('C8', 'As per boq');
                $ws->mergeCells('F8:H8');
                $ws->setCellValue('F8', 'This bill');
                $ws->setCellValue('I8', 'remaining Qty');
                $ws->setCellValue('J8', 'Remarks');
                $ws->setCellValue('C9', 'Unit');
                $ws->setCellValue('D9', 'Quantity');
                $ws->setCellValue('E9', 'Unit price');
                $ws->setCellValue('F9', 'Quantity');
                $ws->setCellValue('G9', 'Unit price');
                $ws->setCellValue('H9', 'total price');
                $ws->getStyle('A8:J9')->getFont()->setBold(true);
                $ws->getStyle('A8:J9')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                $ws->getStyle('A8:J9')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE0E0E0');

                if ($lastRow >= $dataStartRow) {
                    $ws->getStyle('A8:J' . $lastRow)->getBorders()->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN);
                }
                if ($lastRow >= $dataStartRow) {
                    $ws->getColumnDimension('B')->setWidth(28);
                    $ws->getStyle('B' . $dataStartRow . ':B' . $lastRow)->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
                    $ws->getStyle('D' . $dataStartRow . ':J' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $ws->getStyle('D' . $dataStartRow . ':H' . $lastRow)->getNumberFormat()->setFormatCode('0.0000');
                    $ws->getStyle('I' . $dataStartRow . ':I' . $lastRow)->getNumberFormat()->setFormatCode('0.00');
                    // Row height by description length so multi-line content is visible (no single long line)
                    $charsPerLine = 48;
                    foreach ($this->rows as $i => $row) {
                        $excelRow = $dataStartRow + $i;
                        $desc = $row['boqItem']->item_description ?? '';
                        $lines = (int) max(1, ceil(mb_strlen($desc) / $charsPerLine));
                        $rowHeight = (float) min(250, max(15, $lines * 12));
                        $ws->getRowDimension($excelRow)->setRowHeight($rowHeight);
                    }
                }

                $ws->setCellValue('A' . $summaryRow, 'Subtotal:');
                $ws->setCellValue('I' . $summaryRow, $this->subtotal);
                $ws->getStyle('I' . $summaryRow)->getNumberFormat()->setFormatCode('0.00');
                $ws->setCellValue('A' . ($summaryRow + 1), 'Tax (' . $this->taxPercent . '%):');
                $ws->setCellValue('I' . ($summaryRow + 1), $this->taxAmount);
                $ws->getStyle('I' . ($summaryRow + 1))->getNumberFormat()->setFormatCode('0.00');
                $ws->setCellValue('A' . ($summaryRow + 2), 'Total:');
                $ws->setCellValue('I' . ($summaryRow + 2), $this->total);
                $ws->getStyle('A' . ($summaryRow + 2) . ':I' . ($summaryRow + 2))->getFont()->setBold(true);
                $ws->getStyle('I' . ($summaryRow + 2))->getNumberFormat()->setFormatCode('0.00');
            },
        ];
    }
}
