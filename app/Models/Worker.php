<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\CompanyScoped;

class Worker extends Model
{
    use HasFactory, CompanyScoped;

    protected $fillable = [
        'company_id',
        'project_id',
        'name',
        'phone',
        'email',
        'address',
        'wage_type',
        'daily_wage',
        'monthly_salary',
        'join_date',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'join_date' => 'date',
        'daily_wage' => 'decimal:2',
        'monthly_salary' => 'decimal:2',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function attendances()
    {
        return $this->hasMany(WorkerAttendance::class);
    }

    public function payments()
    {
        return $this->hasMany(WorkerPayment::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function getWageRateAttribute(): float
    {
        return $this->wage_type === 'monthly'
            ? (float) ($this->monthly_salary ?? 0)
            : (float) ($this->daily_wage ?? 0);
    }

    public function getWageLabelAttribute(): string
    {
        if ($this->wage_type === 'monthly') {
            return 'Monthly — Rs. ' . number_format((float) ($this->monthly_salary ?? 0), 2);
        }

        return 'Daily — Rs. ' . number_format((float) ($this->daily_wage ?? 0), 2);
    }
}
