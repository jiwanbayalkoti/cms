<?php

namespace App\Services;

use App\Models\User;
use App\Models\Vehicle;
use App\Support\CompanyContext;
use Illuminate\Support\Collection;

class VehicleAlertService
{
    public function summary(?int $companyId = null): array
    {
        $companyId = $companyId ?: CompanyContext::getActiveCompanyId();
        $items = $this->items($companyId);

        return [
            'count' => $items->count(),
            'expired' => $items->where('status', 'expired')->count(),
            'expiring' => $items->where('status', 'expiring')->count(),
            'items' => $items,
        ];
    }

    public function items(?int $companyId = null): Collection
    {
        $companyId = $companyId ?: CompanyContext::getActiveCompanyId();
        if (!$companyId) {
            return collect();
        }

        $ids = Vehicle::where('company_id', $companyId)
            ->withExpiredDocuments()
            ->pluck('id')
            ->merge(
                Vehicle::where('company_id', $companyId)
                    ->withExpiringDocuments()
                    ->pluck('id')
            )
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        return Vehicle::whereIn('id', $ids)
            ->orderBy('vehicle_number')
            ->get()
            ->flatMap(function (Vehicle $vehicle) {
                return collect($vehicle->documentStatuses())
                    ->filter(fn ($doc) => in_array($doc['status'], ['expired', 'expiring'], true))
                    ->map(function (array $doc) use ($vehicle) {
                        $isService = $doc['field'] === 'next_service_date';
                        $message = $doc['status'] === 'expired'
                            ? ($isService ? 'Service overdue' : $doc['label'] . ' expired')
                            : ($isService ? 'Service due soon' : $doc['label'] . ' due soon');

                        if ($doc['days'] !== null) {
                            $message .= $doc['status'] === 'expired'
                                ? ' (' . abs((int) $doc['days']) . ' days)'
                                : ' (' . (int) $doc['days'] . ' days)';
                        }

                        return [
                            'vehicle_id' => $vehicle->id,
                            'vehicle_number' => $vehicle->vehicle_number,
                            'field' => $doc['field'],
                            'label' => $doc['label'],
                            'status' => $doc['status'],
                            'date_label' => $doc['date_label'],
                            'days' => $doc['days'],
                            'is_service' => $isService,
                            'message' => $message,
                        ];
                    });
            })
            ->sortBy(fn ($item) => $item['status'] === 'expired' ? 0 : 1)
            ->values();
    }

    public function companyAdmins(int $companyId): Collection
    {
        return User::where('company_id', $companyId)
            ->where(function ($query) {
                $query->whereIn('role', ['admin', 'super_admin'])
                    ->orWhere('is_admin', true);
            })
            ->whereNotNull('email')
            ->get();
    }
}
