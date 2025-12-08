<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use App\Models\VehicleRent;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class VehicleRentExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithCustomStartCell, WithEvents
{
    protected $query;
    protected $collection;
    protected $vehicleTypes;

    public function __construct($query)
    {
        $this->query = $query;
        $this->vehicleTypes = VehicleRent::getVehicleTypes();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Date',
            'Vehicle Type',
            'Vehicle Number',
            'From Location',
            'To Location',
            'Route',
            'Rate Type',
            'Project',
            'Total Amount',
            'Paid Amount',
            'Balance Amount',
            'Payment Status',
            'Rent Start Date',
            'Rent End Date',
            'Days',
            'Status',
        ];
    }

    public function collection()
    {
        if (!$this->collection) {
            $this->collection = $this->query->with(['project'])->orderBy('rent_date', 'desc')->orderBy('created_at', 'desc')->get();
        }
        return $this->collection;
    }

    public function map($rent): array
    {
        $rateTypeLabels = [
            'fixed' => 'Fixed Rate',
            'per_km' => 'Per KM',
            'per_hour' => 'Per Hour',
            'daywise' => 'Daywise',
            'per_quintal' => 'Per Quintal',
            'not_fixed' => 'Not Fixed',
        ];
        $rateTypeLabel = $rateTypeLabels[$rent->rate_type] ?? ucfirst(str_replace('_', ' ', $rent->rate_type));

        // Use calculated amounts for ongoing daywise rents
        $totalAmount = $rent->is_ongoing ? $rent->calculated_total_amount : $rent->total_amount;
        $balanceAmount = $rent->is_ongoing ? $rent->calculated_balance_amount : $rent->balance_amount;
        $paymentStatus = $rent->is_ongoing ? $rent->calculated_payment_status : $rent->payment_status;
        $days = $rent->rate_type === 'daywise' ? ($rent->is_ongoing ? $rent->calculated_days : $rent->number_of_days) : null;

        return [
            $rent->id,
            optional($rent->rent_date)->format('Y-m-d'),
            $this->vehicleTypes[$rent->vehicle_type] ?? $rent->vehicle_type,
            $rent->vehicle_number ?? '—',
            $rent->start_location,
            $rent->destination_location,
            $rent->start_location . ' → ' . $rent->destination_location,
            $rateTypeLabel,
            $rent->project->name ?? '—',
            (float) $totalAmount,
            (float) $rent->paid_amount,
            (float) $balanceAmount,
            ucfirst($paymentStatus),
            optional($rent->rent_start_date)->format('Y-m-d'),
            $rent->is_ongoing ? 'Ongoing' : (optional($rent->rent_end_date)->format('Y-m-d') ?? '—'),
            $days ?? '—',
            $rent->is_ongoing ? 'Ongoing' : 'Completed',
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

                // Header
                $sheet->setCellValue("A1", 'Vehicle Rents Export');
                $sheet->mergeCells("A1:Q1");
                $sheet->getStyle("A1")->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle("A1")->getAlignment()->setHorizontal('center');

                // Calculate totals
                $totalAmount = 0;
                $totalPaid = 0;
                $totalBalance = 0;

                foreach ($this->collection() as $rent) {
                    $totalAmount += $rent->is_ongoing ? $rent->calculated_total_amount : $rent->total_amount;
                    $totalPaid += $rent->paid_amount;
                    $totalBalance += $rent->is_ongoing ? $rent->calculated_balance_amount : $rent->balance_amount;
                }

                // Totals row
                $sheet->setCellValue("A{$totalRow}", 'Totals');
                $sheet->mergeCells("A{$totalRow}:I{$totalRow}");
                $sheet->setCellValue("J{$totalRow}", $totalAmount);
                $sheet->setCellValue("K{$totalRow}", $totalPaid);
                $sheet->setCellValue("L{$totalRow}", $totalBalance);
                $sheet->getStyle("A{$totalRow}:Q{$totalRow}")->getFont()->setBold(true);
                $sheet->getStyle("J{$totalRow}:L{$totalRow}")->getNumberFormat()->setFormatCode('#,##0.00');
            },
        ];
    }
}

