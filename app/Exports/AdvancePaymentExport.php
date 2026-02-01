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

class AdvancePaymentExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
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
            $this->collection = $this->query->with(['project', 'supplier', 'bankAccount'])
                ->orderBy('payment_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();
        }
        return $this->collection;
    }

    public function headings(): array
    {
        return [
            'SN',
            'Payment Date',
            'Payment Type',
            'Project',
            'Supplier',
            'Amount',
            'Payment Method',
            'Bank Account',
            'Transaction Reference',
            'Notes',
        ];
    }

    public function map($payment): array
    {
        static $sn = 0;
        $sn++;

        $paymentTypes = [
            'vehicle_rent' => 'Vehicle Rent',
            'material_payment' => 'Material Payment',
        ];

        return [
            $sn,
            optional($payment->payment_date)->format('Y-m-d'),
            $paymentTypes[$payment->payment_type] ?? ucfirst(str_replace('_', ' ', $payment->payment_type)),
            $payment->project?->name ?? '—',
            $payment->supplier?->name ?? '—',
            (float) $payment->amount,
            ucfirst(str_replace('_', ' ', $payment->payment_method ?? '—')),
            $payment->bankAccount?->account_name ?? '—',
            $payment->transaction_reference ?? '—',
            $payment->notes ?? '—',
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
                $sheet->mergeCells("A{$totalRow}:E{$totalRow}");
                $sheet->setCellValue("F{$totalRow}", "=SUM(F2:F" . ($dataCount + 1) . ")");
                $sheet->getStyle("A{$totalRow}:J{$totalRow}")->getFont()->setBold(true);
                $sheet->getStyle("F{$totalRow}")->getNumberFormat()->setFormatCode('#,##0.00');
            },
        ];
    }
}
