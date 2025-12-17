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
        'expense_type',
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

    /**
     * Get the category that owns the expense.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the subcategory that owns the expense.
     */
    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    /**
     * Get the staff member associated with the expense (for salary/advance).
     */
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

    /**
     * Get the project.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the construction material associated with this expense.
     */
    public function constructionMaterial()
    {
        return $this->belongsTo(ConstructionMaterial::class);
    }

    /**
     * Get the advance payment associated with this expense.
     */
    public function advancePayment()
    {
        return $this->belongsTo(AdvancePayment::class);
    }

    public function vehicleRent()
    {
        return $this->belongsTo(\App\Models\VehicleRent::class);
    }
}
