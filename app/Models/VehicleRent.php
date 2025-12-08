<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\CompanyScoped;

class VehicleRent extends Model
{
    use HasFactory, CompanyScoped;

    protected $fillable = [
        'company_id',
        'project_id',
        'vehicle_type',
        'vehicle_number',
        'driver_name',
        'driver_contact',
        'rent_date',
        'rent_start_date',
        'rent_end_date',
        'start_location',
        'destination_location',
        'distance_km',
        'hours',
        'minutes',
        'rate_per_km',
        'rate_per_hour',
        'fixed_rate',
        'number_of_days',
        'rate_per_day',
        'quantity_quintal',
        'rate_per_quintal',
        'rate_type',
        'total_amount',
        'paid_amount',
        'balance_amount',
        'payment_status',
        'bank_account_id',
        'payment_date',
        'notes',
        'purpose',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'rent_date' => 'date',
        'rent_start_date' => 'date',
        'rent_end_date' => 'date',
        'payment_date' => 'date',
        'distance_km' => 'decimal:2',
        'rate_per_km' => 'decimal:2',
        'rate_per_hour' => 'decimal:2',
        'fixed_rate' => 'decimal:2',
        'rate_per_day' => 'decimal:2',
        'quantity_quintal' => 'decimal:2',
        'rate_per_quintal' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
    ];

    /**
     * Get the company.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the project.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the bank account.
     */
    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    /**
     * Get the user who created this record.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who updated this record.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get vehicle type options.
     */
    public static function getVehicleTypes()
    {
        return [
            'car' => 'Car',
            'truck' => 'Truck',
            'bus' => 'Bus',
            'motorcycle' => 'Motorcycle',
            'van' => 'Van',
            'pickup' => 'Pickup',
            'tractor' => 'Tractor',
            'excavator' => 'Excavator',
            'jcv' => 'JCV',
            'tipper_6wheel' => 'Tipper 6 Wheel',
            'tipper_10wheel' => 'Tipper 10 Wheel',
            'mixture' => 'Mixture',
            'other' => 'Other',
        ];
    }
    
    /**
     * Check if vehicle type requires hourly calculation.
     */
    public function requiresHourlyCalculation()
    {
        return in_array($this->vehicle_type, ['excavator', 'jcv']);
    }

    /**
     * Check if the rent is ongoing (daywise with start date but no end date).
     */
    public function getIsOngoingAttribute()
    {
        return $this->rate_type === 'daywise' 
            && $this->rent_start_date !== null 
            && $this->rent_end_date === null;
    }

    /**
     * Calculate the number of days for daywise rent (dynamically for ongoing rents).
     */
    public function getCalculatedDaysAttribute()
    {
        if ($this->rate_type !== 'daywise') {
            return $this->number_of_days ?? 0;
        }

        if ($this->rent_start_date) {
            $startDate = $this->rent_start_date;
            $endDate = $this->is_ongoing ? now() : ($this->rent_end_date ?? now());
            return max(1, $startDate->diffInDays($endDate) + 1); // +1 to include both dates
        }

        return $this->number_of_days ?? 0;
    }

    /**
     * Get the calculated total amount (dynamically for ongoing rents).
     */
    public function getCalculatedTotalAmountAttribute()
    {
        if ($this->is_ongoing && $this->rate_type === 'daywise') {
            $calculatedDays = $this->calculated_days;
            $ratePerDay = $this->rate_per_day ?? 0;
            return $calculatedDays * $ratePerDay;
        }

        return $this->total_amount;
    }

    /**
     * Get the calculated balance amount (dynamically for ongoing rents).
     */
    public function getCalculatedBalanceAmountAttribute()
    {
        $calculatedTotal = $this->calculated_total_amount;
        $paidAmount = $this->paid_amount ?? 0;
        return $calculatedTotal - $paidAmount;
    }

    /**
     * Get the calculated payment status (dynamically for ongoing rents).
     */
    public function getCalculatedPaymentStatusAttribute()
    {
        if ($this->is_ongoing) {
            $calculatedBalance = $this->calculated_balance_amount;
            $paidAmount = $this->paid_amount ?? 0;
            
            if ($calculatedBalance <= 0.01) {
                return 'paid';
            } elseif ($paidAmount > 0) {
                return 'partial';
            } else {
                return 'unpaid';
            }
        }
        
        return $this->payment_status;
    }
}
