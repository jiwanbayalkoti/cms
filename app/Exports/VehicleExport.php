<?php

namespace App\Exports;

use App\Models\Vehicle;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class VehicleExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(protected $query)
    {
    }

    public function collection(): Collection
    {
        return $this->query->with(['project', 'supplier', 'driverStaff'])->orderBy('vehicle_number')->get();
    }

    public function headings(): array
    {
        return [
            'Vehicle Number',
            'Type',
            'Ownership',
            'Status',
            'Project',
            'Supplier',
            'Driver',
            'Driver Contact',
            'Insurance Policy No',
            'Bluebook Expiry',
            'Insurance Expiry',
            'Fitness Expiry',
            'Route Permit Expiry',
            'Tax Token Expiry',
            'PUC Expiry',
            'Last Service',
            'Next Service',
            'Notes',
        ];
    }

    public function map($vehicle): array
    {
        /** @var Vehicle $vehicle */
        return [
            $vehicle->vehicle_number,
            $vehicle->type_label,
            $vehicle->ownership_label,
            $vehicle->status_label,
            $vehicle->project?->name,
            $vehicle->supplier?->name,
            $vehicle->driverStaff?->name ?? $vehicle->driver_name,
            $vehicle->driver_contact,
            $vehicle->insurance_policy_no,
            optional($vehicle->registration_expiry)->format('Y-m-d'),
            optional($vehicle->insurance_expiry)->format('Y-m-d'),
            optional($vehicle->fitness_expiry)->format('Y-m-d'),
            optional($vehicle->route_permit_expiry)->format('Y-m-d'),
            optional($vehicle->tax_token_expiry)->format('Y-m-d'),
            optional($vehicle->puc_expiry)->format('Y-m-d'),
            optional($vehicle->last_service_date)->format('Y-m-d'),
            optional($vehicle->next_service_date)->format('Y-m-d'),
            $vehicle->notes,
        ];
    }
}
