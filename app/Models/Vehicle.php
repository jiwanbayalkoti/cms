<?php

namespace App\Models;

use App\Models\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class Vehicle extends Model
{
    use HasFactory, CompanyScoped;

    public const EXPIRY_WARNING_DAYS = 30;

    protected $fillable = [
        'company_id',
        'project_id',
        'supplier_id',
        'driver_staff_id',
        'vehicle_number',
        'vehicle_type',
        'ownership',
        'driver_name',
        'driver_contact',
        'status',
        'photo',
        'notes',
        'insurance_policy_no',
        'registration_expiry',
        'insurance_expiry',
        'fitness_expiry',
        'route_permit_expiry',
        'tax_token_expiry',
        'puc_expiry',
        'last_service_date',
        'next_service_date',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'registration_expiry' => 'date',
        'insurance_expiry' => 'date',
        'fitness_expiry' => 'date',
        'route_permit_expiry' => 'date',
        'tax_token_expiry' => 'date',
        'puc_expiry' => 'date',
        'last_service_date' => 'date',
        'next_service_date' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function driverStaff()
    {
        return $this->belongsTo(Staff::class, 'driver_staff_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function getVehicleTypes(): array
    {
        return VehicleRent::getVehicleTypes();
    }

    public static function getOwnershipOptions(): array
    {
        return [
            'own' => 'Own',
            'hired' => 'Hired',
        ];
    }

    public static function getStatusOptions(): array
    {
        return [
            'active' => 'Active',
            'under_repair' => 'Under Repair',
            'sold' => 'Sold',
            'inactive' => 'Inactive',
        ];
    }

    public static function documentDefinitions(): array
    {
        return [
            'registration_expiry' => 'Bluebook / Registration',
            'insurance_expiry' => 'Insurance',
            'fitness_expiry' => 'Fitness',
            'route_permit_expiry' => 'Route Permit',
            'tax_token_expiry' => 'Tax Token',
            'puc_expiry' => 'PUC',
            'next_service_date' => 'Next Service',
        ];
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? Storage::url($this->photo) : null;
    }

    public function getTypeLabelAttribute(): string
    {
        return self::getVehicleTypes()[$this->vehicle_type] ?? ucfirst(str_replace('_', ' ', (string) $this->vehicle_type));
    }

    public function getOwnershipLabelAttribute(): string
    {
        return self::getOwnershipOptions()[$this->ownership] ?? ucfirst((string) $this->ownership);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::getStatusOptions()[$this->status] ?? ucfirst(str_replace('_', ' ', (string) $this->status));
    }

    public function documentStatuses(): array
    {
        $today = now()->startOfDay();
        $soonUntil = now()->addDays(self::EXPIRY_WARNING_DAYS)->endOfDay();
        $items = [];

        foreach (self::documentDefinitions() as $field => $label) {
            /** @var Carbon|null $date */
            $date = $this->{$field};
            $status = 'missing';
            $days = null;

            if ($date) {
                $days = (int) $today->diffInDays($date, false);
                if ($date->lt($today)) {
                    $status = 'expired';
                } elseif ($date->lte($soonUntil)) {
                    $status = 'expiring';
                } else {
                    $status = 'valid';
                }
            }

            $items[] = [
                'field' => $field,
                'label' => $label,
                'date' => $date,
                'date_label' => $date ? $date->format('M d, Y') : null,
                'status' => $status,
                'days' => $days,
            ];
        }

        return $items;
    }

    public function getWorstDocumentStatusAttribute(): string
    {
        $statuses = collect($this->documentStatuses())->pluck('status');

        if ($statuses->contains('expired')) {
            return 'expired';
        }
        if ($statuses->contains('expiring')) {
            return 'expiring';
        }
        if ($statuses->contains('valid')) {
            return 'valid';
        }

        return 'missing';
    }

    public function scopeWithExpiredDocuments(Builder $query): Builder
    {
        $today = now()->toDateString();

        return $query->where(function (Builder $inner) use ($today) {
            foreach (array_keys(self::documentDefinitions()) as $field) {
                $inner->orWhereDate($field, '<', $today);
            }
        });
    }

    public function scopeWithExpiringDocuments(Builder $query): Builder
    {
        $today = now()->toDateString();
        $soon = now()->addDays(self::EXPIRY_WARNING_DAYS)->toDateString();

        return $query->where(function (Builder $outer) use ($today, $soon) {
            foreach (array_keys(self::documentDefinitions()) as $field) {
                $outer->orWhere(function (Builder $inner) use ($field, $today, $soon) {
                    $inner->whereDate($field, '>=', $today)
                        ->whereDate($field, '<=', $soon);
                });
            }
        });
    }
}
