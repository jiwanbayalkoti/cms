<?php

namespace App\Exports;

use App\Models\MeasurementBook;
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

class MeasurementBookExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithCustomStartCell, WithEvents
{
    protected MeasurementBook $book;

    public function __construct(MeasurementBook $book)
    {
        $this->book = $book->load(['company', 'project', 'mainItems.children']);
    }

    public function collection()
    {
        // Return items in order: main works first, then their sub-works
        $items = collect();
        foreach ($this->book->mainItems as $mainItem) {
            $items->push($mainItem);
            foreach ($mainItem->children as $subItem) {
                $items->push($subItem);
            }
        }
        return $items;
    }

    public function headings(): array
    {
        $u = $this->book->dimension_unit ?? 'ft';
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

    public function map($item): array
    {
        // If it's a sub-work, show with indentation
        $works = $item->parent_id ? '└─ ' . $item->works : $item->works;
        $sn = $item->parent_id ? '' : $item->sn;
        
        return [
            $sn,
            $works,
            $item->no !== null ? round((float) $item->no, 4) : '',
            $item->length_ft !== null ? round((float) $item->length_ft, 4) : '',
            $item->breadth_ft !== null ? round((float) $item->breadth_ft, 4) : '',
            $item->height_ft !== null ? round((float) $item->height_ft, 4) : '',
            $item->quantity !== null ? round((float) $item->quantity, 4) : '',
            $item->total_qty !== null ? round((float) $item->total_qty, 4) : '',
            $item->unit ?? '',
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
                $book = $this->book;
                $company = $book->company;
                $project = $book->project;
                // Count all items (main works + sub-works)
                $itemCount = $book->mainItems->count() + $book->mainItems->sum(fn($item) => $item->children->count());
                $lastRow = 8 + $itemCount;

                // Header block – Company, Address, Client, Project, Contract, Date, Title (same design as show)
                $ws->setCellValue('A1', 'Company Name: ' . ($company->name ?? ''));
                $ws->mergeCells('A1:I1');
                $ws->setCellValue('A2', 'Address: ' . ($company->address ?? ''));
                $ws->mergeCells('A2:I2');
                $ws->setCellValue('A3', 'Client Name: ' . ($project->client_name ?? ''));
                $ws->mergeCells('A3:I3');
                $ws->setCellValue('A4', 'Project Name: ' . ($project->name ?? ''));
                $ws->mergeCells('A4:I4');
                $ws->setCellValue('A5', 'Contract No: ' . ($book->contract_no ?? ''));
                $ws->mergeCells('A5:I5');
                $ws->setCellValue('A6', 'Measurement taken date: ' . ($book->measurement_date ? $book->measurement_date->format('Y-m-d') : ''));
                $ws->mergeCells('A6:I6');
                $ws->setCellValue('A7', 'Measurement Book');
                $ws->mergeCells('A7:I7');
                $ws->getStyle('A7')->getFont()->setBold(true)->setSize(12);

                // Table header row (row 8) – sabai columns: bold, grey fill, center
                $ws->getStyle('A8:I8')->getFont()->setBold(true);
                $ws->getStyle('A8:I8')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                $ws->getStyle('A8:I8')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE0E0E0');

                // Borders: sabai columns A8:I(lastRow) – thin border har cell
                if ($lastRow >= 8) {
                    $ws->getStyle('A8:I' . $lastRow)->getBorders()->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN);
                }

                // Data row styling (A9:I lastRow)
                if ($lastRow >= 9) {
                    // SN (A): center
                    $ws->getStyle('A9:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    // Works (B): left, wrap
                    $ws->getStyle('B9:B' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setWrapText(true);
                    // no (C): center
                    $ws->getStyle('C9:C' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    // L, B, H, Qty, Total qty (D–H): right, number 0.0000
                    $ws->getStyle('D9:H' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $ws->getStyle('D9:H' . $lastRow)->getNumberFormat()->setFormatCode('0.0000');
                    // Unit (I): center
                    $ws->getStyle('I9:I' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    
                    // Style main works with light grey background (like table-light in view)
                    $row = 9;
                    foreach ($book->mainItems as $mainItem) {
                        $ws->getStyle("A{$row}:I{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FFF8F9FA');
                        $ws->getStyle("B{$row}")->getFont()->setBold(true);
                        $row++;
                        // Sub-works follow (no special styling, just normal)
                        foreach ($mainItem->children as $subItem) {
                            $row++;
                        }
                    }
                }
            },
        ];
    }
}
