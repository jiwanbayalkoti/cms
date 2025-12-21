<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\CompanyScoped;
use App\Models\Traits\ProjectScoped;

class Expense extends Model
{
    use HasFactory, CompanyScoped, ProjectScoped;

    protected $fillable = [
        'company_id',
        'project_id',
        'construction_material_id',
        'vehicle_rent_id',
        'advance_payment_id',
        'category_id',
        'subcategory_id',
        'expense_type_id',
        'staff_id',
        'item_name',
        'description',
        'amount',
        'date',
        'payment_method',
        'notes',
        'images',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
        'images' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function constructionMaterial()
    {
        return $this->belongsTo(ConstructionMaterial::class);
    }

    public function advancePayment()
    {
        return $this->belongsTo(AdvancePayment::class);
    }

    public function vehicleRent()
    {
        return $this->belongsTo(\App\Models\VehicleRent::class);
    }

    public function expenseType()
    {
        return $this->belongsTo(\App\Models\ExpenseType::class);
    }

    public function salaryPayment()
    {
        return $this->hasOne(\App\Models\SalaryPayment::class, 'expense_id');
    }
}
