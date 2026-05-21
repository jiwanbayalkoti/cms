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
        'subcontractor_id',
        'advance_payment_id',
        'loan_id',
        'loan_payment_id',
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

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function loanPayment()
    {
        return $this->belongsTo(LoanPayment::class);
    }

    public function vehicleRent()
    {
        return $this->belongsTo(\App\Models\VehicleRent::class);
    }

    public function subcontractor()
    {
        return $this->belongsTo(Subcontractor::class);
    }

    public function expenseType()
    {
        return $this->belongsTo(\App\Models\ExpenseType::class);
    }

    public function salaryPayment()
    {
        return $this->hasOne(\App\Models\SalaryPayment::class, 'expense_id');
    }

    public function isLoanGivenExpense(): bool
    {
        if (! $this->loan_id || $this->loan_payment_id) {
            return false;
        }

        $loan = $this->relationLoaded('loan') ? $this->loan : $this->loan()->first(['id', 'direction']);

        return $loan && $loan->direction === 'repaid';
    }

    /**
     * Display type label and Tailwind badge classes for expense lists.
     *
     * @return array{name: string, class: string}
     */
    public function listTypeBadge(): array
    {
        if ($this->construction_material_id) {
            return ['name' => 'Purchase', 'class' => 'bg-blue-100 text-blue-800'];
        }
        if ($this->advance_payment_id) {
            return ['name' => 'Advance', 'class' => 'bg-yellow-100 text-yellow-800'];
        }
        if ($this->vehicle_rent_id) {
            return ['name' => 'Vehicle rent', 'class' => 'bg-purple-100 text-purple-800'];
        }
        if ($this->loan_id) {
            if ($this->isLoanGivenExpense()) {
                return ['name' => 'Loan Given', 'class' => 'bg-red-100 text-red-800'];
            }

            return ['name' => 'Loan repayment', 'class' => 'bg-orange-100 text-orange-800'];
        }
        if ($this->subcontractor_id) {
            return ['name' => 'Sub-contractor', 'class' => 'bg-teal-100 text-teal-800'];
        }
        if ($this->expenseType) {
            return ['name' => $this->expenseType->name, 'class' => 'bg-gray-100 text-gray-800'];
        }

        return ['name' => 'N/A', 'class' => 'bg-gray-100 text-gray-800'];
    }
}
