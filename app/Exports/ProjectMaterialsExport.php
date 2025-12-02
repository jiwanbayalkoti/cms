<?php

namespace App\Exports;

use App\Models\ConstructionMaterial;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ProjectMaterialsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithCustomStartCell, WithEvents
{
    /**
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    protected $collection;

    public function headings(): array
    {
        return [
            'ID',
            'Delivery Date',
            'Project',
            'Material',
            'Category',
            'Unit',
            'Quantity Received',
            'Quantity Used',
            'Quantity Remaining',
            'Rate per Unit',
            'Total Cost',
            'Supplier',
            'Payment Status',
            'Payment Mode',
            'Work Type',
            'Status',
        ];
    }

    public function collection(): Collection
    {
        if (!$this->collection) {
            $this->collection = $this->query->orderByDesc('delivery_date')->orderByDesc('id')->get();
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

                $sheet->setCellValue("A1", 'Project Material Export');
                $sheet->mergeCells("A1:P1");
                $sheet->getStyle("A1")->getFont()->setBold(true)->setSize(14);

                $sheet->setCellValue("A{$totalRow}", 'Totals');
                $sheet->mergeCells("A{$totalRow}:F{$totalRow}");
                $sheet->setCellValue("G{$totalRow}", $this->collection()->sum('quantity_received'));
                $sheet->setCellValue("H{$totalRow}", $this->collection()->sum('quantity_used'));
                $sheet->setCellValue("I{$totalRow}", $this->collection()->sum('quantity_remaining'));
                $sheet->setCellValue("K{$totalRow}", $this->collection()->sum('total_cost'));

                $sheet->getStyle("A{$totalRow}:P{$totalRow}")->getFont()->setBold(true);
            },
        ];
    }
}

