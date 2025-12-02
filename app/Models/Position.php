<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\CompanyScoped;

class Position extends Model
{
    use HasFactory, CompanyScoped;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'salary_range',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the staff members for the position.
     */
    public function staff()
    {
        return $this->hasMany(Staff::class);
    }
}
