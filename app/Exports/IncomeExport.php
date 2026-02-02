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

class IncomeExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
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
            $this->collection = $this->query->with(['category', 'subcategory', 'project'])
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
            'Source',
            'Description',
            'Project',
            'Category',
            'Subcategory',
            'Amount',
            'Payment Method',
            'Notes',
        ];
    }

    public function map($income): array
    {
        static $sn = 0;
        $sn++;

        return [
            $sn,
            optional($income->date)->format('Y-m-d'),
            $income->source ?? '—',
            $income->description ?? '—',
            $income->project?->name ?? '—',
            $income->category?->name ?? '—',
            $income->subcategory?->name ?? '—',
            (float) $income->amount,
            $income->payment_method ?? '—',
            $income->notes ?? '—',
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
                $sheet->mergeCells("A{$totalRow}:G{$totalRow}");
                $sheet->setCellValue("H{$totalRow}", "=SUM(H2:H" . ($dataCount + 1) . ")");
                $sheet->getStyle("A{$totalRow}:J{$totalRow}")->getFont()->setBold(true);
                $sheet->getStyle("H{$totalRow}")->getNumberFormat()->setFormatCode('#,##0.00');
            },
        ];
    }
}
