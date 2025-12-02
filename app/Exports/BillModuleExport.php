<?php

namespace App\Exports;

use App\Models\BillModule;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class BillModuleExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithCustomStartCell, WithEvents
{
    protected $bill;

    public function __construct(BillModule $bill)
    {
        $this->bill = $bill->load('items.billCategory', 'items.billSubcategory');
    }

    public function collection()
    {
        return $this->bill->items;
    }

    public function headings(): array
    {
        return [
            'S.N.',
            'Category',
            'Subcategory',
            'Description',
            'Unit',
            'Quantity',
            'Wastage %',
            'Effective Qty',
            'Unit Rate',
            'Amount',
            'Tax %',
            'Net Amount',
            'Remarks',
        ];
    }

    public function map($item): array
    {
        static $sn = 0;
        $sn++;

        return [
            $sn,
            $item->billCategory->name ?? $item->category ?? '—',
            $item->billSubcategory->name ?? $item->subcategory ?? '',
            $item->description,
            $item->uom,
            (float) $item->quantity,
            (float) $item->wastage_percent,
            (float) $item->effective_quantity,
            (float) $item->unit_rate,
            (float) $item->total_amount,
            (float) $item->tax_percent,
            (float) $item->net_amount,
            $item->remarks ?? '',
        ];
    }

    public function startCell(): string
    {
        return 'A2';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $dataRowCount = $this->bill->items->count();
                $totalRow = $dataRowCount + 3;

                // Header
                $sheet->setCellValue("A1", "Bill: {$this->bill->title}");
                $sheet->mergeCells("A1:M1");
                $sheet->getStyle("A1")->getFont()->setBold(true)->setSize(14);

                // Totals
                $aggregate = $this->bill->aggregate;
                if ($aggregate) {
                    $sheet->setCellValue("A{$totalRow}", "Subtotal:");
                    $sheet->setCellValue("J{$totalRow}", $aggregate->subtotal);
                    
                    $totalRow++;
                    $sheet->setCellValue("A{$totalRow}", "Tax Total:");
                    $sheet->setCellValue("J{$totalRow}", $aggregate->tax_total);
                    
                    $totalRow++;
                    $sheet->setCellValue("A{$totalRow}", "Overhead ({$aggregate->overhead_percent}%):");
                    $sheet->setCellValue("J{$totalRow}", $aggregate->overhead_amount);
                    
                    $totalRow++;
                    $sheet->setCellValue("A{$totalRow}", "Contingency ({$aggregate->contingency_percent}%):");
                    $sheet->setCellValue("J{$totalRow}", $aggregate->contingency_amount);
                    
                    $totalRow++;
                    $sheet->setCellValue("A{$totalRow}", "GRAND TOTAL:");
                    $sheet->setCellValue("J{$totalRow}", $aggregate->grand_total);
                    $sheet->getStyle("A{$totalRow}:M{$totalRow}")->getFont()->setBold(true);
                }
            },
        ];
    }
}
