<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\CompanyScoped;

class Staff extends Model
{
    use HasFactory, CompanyScoped;

    protected $fillable = [
        'company_id',
        'project_id',
        'name',
        'email',
        'phone',
        'position_id',
        'address',
        'salary',
        'join_date',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'join_date' => 'date',
        'salary' => 'decimal:2',
    ];

    /**
     * Get the position that owns the staff member.
     */
    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * Get the project.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the salary payments for the staff member.
     */
    public function salaryPayments()
    {
        return $this->hasMany(SalaryPayment::class);
    }

    /**
     * Get pending salary payments.
     */
    public function getPendingSalaries()
    {
        return $this->salaryPayments()
            ->where('status', 'pending')
            ->orderBy('payment_month', 'desc')
            ->get();
    }
}
