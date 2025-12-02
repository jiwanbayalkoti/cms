<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use App\Models\ConstructionMaterial;
use App\Models\PurchasedBy;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ConstructionMaterialsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithCustomStartCell, WithEvents
{
    protected $query;
    protected $collection;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function headings(): array
    {
        return [
            'ID', 'Delivery Date', 'Project', 'Material', 'Category', 'Unit', 'Qty Received', 'Qty Used', 'Qty Remaining',
            'Rate per Unit', 'Total Cost', 'Supplier', 'Purchased By', 'Payment Status', 'Payment Mode', 'Work Type', 'Status',
        ];
    }

    public function collection()
    {
        if (!$this->collection) {
            $this->collection = $this->query->with('purchasedBy')->orderByDesc('delivery_date')->orderByDesc('id')->get();
        }
        return $this->collection;
    }

    public function map($material): array
    {
        return [
            $material->id,
            optional($material->delivery_date)->format('Y-m-d'),
            $material->project_name,
            $material->material_name,
            $material->material_category,
            $material->unit,
            (float) $material->quantity_received,
            (float) $material->quantity_used,
            (float) $material->quantity_remaining,
            (float) $material->rate_per_unit,
            (float) $material->total_cost,
            $material->supplier_name,
            optional($material->purchasedBy)->name,
            $material->payment_status,
            $material->payment_mode,
            $material->work_type,
            $material->status,
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
                $dataRowCount = $this->collection()->count();
                $totalRow = $dataRowCount + 3;

                $sheet->setCellValue("A1", 'Construction Materials Export');
                $sheet->mergeCells("A1:R1");
                $sheet->getStyle("A1")->getFont()->setBold(true)->setSize(14);

                $sheet->setCellValue("A{$totalRow}", 'Total');
                $sheet->mergeCells("A{$totalRow}:J{$totalRow}");
                $sheet->setCellValue("K{$totalRow}", $this->collection()->sum('total_cost'));
                $sheet->getStyle("A{$totalRow}:R{$totalRow}")->getFont()->setBold(true);
            },
        ];
    }
}
