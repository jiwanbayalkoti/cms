<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ExpenseExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
{
    protected Builder $query;
    protected ?Collection $collection = null;

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    public function collection()
    {
        if ($this->collection === null) {
            $this->collection = $this->query->with(['category', 'subcategory', 'project', 'staff', 'constructionMaterial', 'advancePayment', 'vehicleRent', 'expenseType'])
                ->orderBy('date', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();
        }
        return $this->collection;
    }

    public function headings(): array
    {
        return [
            'SN',
            'Date',
            'Type',
            'Item/Description',
            'Project',
            'Category',
            'Subcategory',
            'Staff',
            'Amount',
            'Payment Method',
            'Notes',
        ];
    }

    public function map($expense): array
    {
        static $sn = 0;
        $sn++;

        $typeName = 'N/A';
        if ($expense->constructionMaterial) {
            $typeName = 'Purchase';
        } elseif ($expense->advancePayment) {
            $typeName = 'Advance';
        } elseif ($expense->vehicleRent) {
            $typeName = 'Vehicle Rent';
        } elseif ($expense->expenseType) {
            $typeName = $expense->expenseType->name;
        }

        $itemName = $expense->item_name ?? ($expense->description ? \Illuminate\Support\Str::limit($expense->description, 100) : '—');

        return [
            $sn,
            optional($expense->date)->format('Y-m-d'),
            $typeName,
            $itemName,
            $expense->project?->name ?? '—',
            $expense->category?->name ?? '—',
            $expense->subcategory?->name ?? '—',
            $expense->staff?->name ?? '—',
            (float) $expense->amount,
            $expense->payment_method ?? '—',
            $expense->notes ?? '—',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $dataCount = $this->collection()->count();
                if ($dataCount === 0) {
                    return;
                }
                $totalRow = $dataCount + 3;
                $sheet->setCellValue("A{$totalRow}", 'Total');
                $sheet->mergeCells("A{$totalRow}:H{$totalRow}");
                $sheet->setCellValue("I{$totalRow}", "=SUM(I2:I" . ($dataCount + 1) . ")");
                $sheet->getStyle("A{$totalRow}:K{$totalRow}")->getFont()->setBold(true);
                $sheet->getStyle("I{$totalRow}")->getNumberFormat()->setFormatCode('#,##0.00');
            },
        ];
    }
}
