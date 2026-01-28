<?php

namespace App\Exports;

use App\Models\RunningBill;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class RunningBillExport implements FromCollection, WithMapping, ShouldAutoSize, WithCustomStartCell, WithEvents
{
    protected RunningBill $bill;

    public function __construct(RunningBill $bill)
    {
        $this->bill = $bill->load(['company', 'project', 'items']);
    }

    public function collection()
    {
        return $this->bill->items;
    }

    public function map($item): array
    {
        return [
            $item->sn,
            $item->description,
            $item->unit ?? '',
            $item->boq_qty !== null ? round((float) $item->boq_qty, 4) : '',
            $item->boq_unit_price !== null ? round((float) $item->boq_unit_price, 2) : '',
            $item->this_bill_qty !== null ? round((float) $item->this_bill_qty, 4) : '',
            $item->unit_price !== null ? round((float) $item->unit_price, 2) : '',
            $item->total_price !== null ? round((float) $item->total_price, 2) : '',
            $item->remaining_qty !== null ? round((float) $item->remaining_qty, 4) : '',
            $item->remarks ?? '',
        ];
    }

    public function startCell(): string
    {
        return 'A10';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $ws = $event->sheet->getDelegate();
                $bill = $this->bill;
                $company = $bill->company;
                $project = $bill->project;
                $items = $bill->items;
                $count = $items->count();
                $lastRow = 9 + $count;

                // Header block – Company, Address, Client, Project, Contract, Bill Date, Bill Title (same design as show)
                $ws->setCellValue('A1', 'Company Name: ' . ($company->name ?? ''));
                $ws->mergeCells('A1:J1');
                $ws->setCellValue('A2', 'Address: ' . ($company->address ?? ''));
                $ws->mergeCells('A2:J2');
                $ws->setCellValue('A3', 'Client Name: ' . ($project->client_name ?? ''));
                $ws->mergeCells('A3:J3');
                $ws->setCellValue('A4', 'Project Name: ' . ($project->name ?? ''));
                $ws->mergeCells('A4:J4');
                $ws->setCellValue('A5', 'Contract No: ' . ($bill->contract_no ?? ''));
                $ws->mergeCells('A5:J5');
                $ws->setCellValue('A6', 'Bill Date: ' . ($bill->bill_date ? $bill->bill_date->format('Y-m-d') : ''));
                $ws->mergeCells('A6:J6');
                $ws->setCellValue('A7', $bill->bill_title ?? 'Running Bill');
                $ws->mergeCells('A7:J7');
                $ws->getStyle('A7')->getFont()->setBold(true)->setSize(12);

                // Two-row table header: sabai columns – As per boq | This bill (same as show)
                $ws->setCellValue('A8', 'SN');
                $ws->mergeCells('A8:A9');
                $ws->setCellValue('B8', 'Description of works');
                $ws->mergeCells('B8:B9');
                $ws->setCellValue('C8', 'As per boq');
                $ws->mergeCells('C8:E8');
                $ws->setCellValue('F8', 'This bill');
                $ws->mergeCells('F8:H8');
                $ws->setCellValue('I8', 'remaining Qty');
                $ws->mergeCells('I8:I9');
                $ws->setCellValue('J8', 'Remarks');
                $ws->mergeCells('J8:J9');

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

                // Borders: sabai columns A8:J(lastRow) – thin border har cell (header + data)
                if ($lastRow >= 9) {
                    $ws->getStyle('A8:J' . $lastRow)->getBorders()->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN);
                }

                // Data row styling (A10:J lastRow) – sabai columns same design
                if ($lastRow >= 10) {
                    // SN (A): center
                    $ws->getStyle('A10:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    // Description (B): left, wrap
                    $ws->getStyle('B10:B' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setWrapText(true);
                    // Unit (C): center
                    $ws->getStyle('C10:C' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    // As per boq: D=Quantity, E=Unit price – right, number format
                    $ws->getStyle('D10:E' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $ws->getStyle('D10:D' . $lastRow)->getNumberFormat()->setFormatCode('0.0000');
                    $ws->getStyle('E10:E' . $lastRow)->getNumberFormat()->setFormatCode('0.00');
                    // This bill: F=Qty, G=Unit price, H=total price – right, number format
                    $ws->getStyle('F10:H' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $ws->getStyle('F10:F' . $lastRow)->getNumberFormat()->setFormatCode('0.0000');
                    $ws->getStyle('G10:H' . $lastRow)->getNumberFormat()->setFormatCode('0.00');
                    // remaining Qty (I): right, 0.0000
                    $ws->getStyle('I10:I' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $ws->getStyle('I10:I' . $lastRow)->getNumberFormat()->setFormatCode('0.0000');
                    // Remarks (J): left
                    $ws->getStyle('J10:J' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                // Subtotal, Tax, Total (same as show)
                $subtotal = $items->sum('total_price');
                $taxPercent = 13;
                $taxAmount = round($subtotal * ($taxPercent / 100), 2);
                $total = $subtotal + $taxAmount;

                $r = $lastRow + 2;
                $ws->setCellValue('A' . $r, 'Subtotal:');
                $ws->setCellValue('H' . $r, $subtotal);
                $r++;
                $ws->setCellValue('A' . $r, 'Tax (' . $taxPercent . '%):');
                $ws->setCellValue('H' . $r, $taxAmount);
                $r++;
                $ws->setCellValue('A' . $r, 'Total:');
                $ws->setCellValue('H' . $r, $total);
                $ws->getStyle('A' . $r . ':H' . $r)->getFont()->setBold(true);
            },
        ];
    }
}
