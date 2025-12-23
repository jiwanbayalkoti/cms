<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\CompanyScoped;
use App\Models\Traits\ProjectScoped;
use Carbon\Carbon;

class SalaryPayment extends Model
{
    use HasFactory, CompanyScoped, ProjectScoped;

    protected $fillable = [
        'company_id',
        'project_id',
        'staff_id',
        'payment_month',
        'payment_date',
        'base_salary',
        'working_days',
        'total_days',
        'overtime_amount',
        'bonus_amount',
        'allowance_amount',
        'deduction_amount',
        'advance_deduction',
        'assessment_type',
        'taxable_income',
        'tax_amount',
        'tax_exempt_amount',
        'gross_amount',
        'net_amount',
        'paid_amount',
        'balance_amount',
        'status',
        'payment_method',
        'bank_account_id',
        'transaction_reference',
        'notes',
        'expense_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'payment_month' => 'date',
        'payment_date' => 'date',
        'base_salary' => 'decimal:2',
        'overtime_amount' => 'decimal:2',
        'bonus_amount' => 'decimal:2',
        'allowance_amount' => 'decimal:2',
        'deduction_amount' => 'decimal:2',
        'advance_deduction' => 'decimal:2',
        'taxable_income' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'tax_exempt_amount' => 'decimal:2',
        'gross_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
    ];

    /**
     * Get the staff member.
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

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
     * Get the linked expense.
     */
    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }

    /**
     * Get the bank account.
     */
    public function bankAccount()
    {
        return $this->belongsTo(\App\Models\BankAccount::class);
    }

    /**
     * Get the creator.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the updater.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Calculate gross amount.
     */
    public function calculateGrossAmount(): float
    {
        $base = $this->base_salary;
        
        // If partial month, calculate prorated salary
        if ($this->working_days && $this->total_days) {
            $base = ($this->base_salary / $this->total_days) * $this->working_days;
        }
        
        return $base + $this->overtime_amount + $this->bonus_amount + $this->allowance_amount;
    }

    /**
     * Calculate net amount (after deductions and tax).
     */
    public function calculateNetAmount(): float
    {
        return $this->gross_amount - $this->deduction_amount - $this->advance_deduction - $this->tax_amount;
    }
    
    /**
     * Calculate tax based on Nepal tax rates.
     */
    public function calculateTax(): array
    {
        $calculator = new \App\Services\NepalTaxCalculator();
        
        // Calculate annual taxable income from monthly gross
        $monthlyGross = $this->gross_amount;
        
        // If partial month, annualize it
        if ($this->working_days && $this->total_days && $this->total_days > 0) {
            $fullMonthGross = ($monthlyGross / $this->working_days) * $this->total_days;
        } else {
            $fullMonthGross = $monthlyGross;
        }
        
        $annualTaxableIncome = $fullMonthGross * 12;
        
        // Calculate tax
        $assessmentType = $this->assessment_type ?? 'single';
        $taxResult = $calculator::calculateTax($annualTaxableIncome, $assessmentType);
        
        // Monthly tax is annual tax / 12, but prorate if partial month
        $monthlyTax = $taxResult['tax_amount'] / 12;
        
        if ($this->working_days && $this->total_days && $this->total_days > 0) {
            $monthlyTax = ($monthlyTax / $this->total_days) * $this->working_days;
        }
        
        return [
            'taxable_income' => $annualTaxableIncome,
            'tax_amount' => round($monthlyTax, 2),
            'assessment_type' => $assessmentType,
            'breakdown' => $taxResult['breakdown'],
        ];
    }

    /**
     * Get payment month name.
     */
    public function getPaymentMonthNameAttribute(): string
    {
        return Carbon::parse($this->payment_month)->format('F Y');
    }

    /**
     * Check if payment is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Get payment transactions.
     */
    public function transactions()
    {
        return $this->hasMany(SalaryPaymentTransaction::class);
    }

    /**
     * Check if payment is partial.
     */
    public function isPartial(): bool
    {
        return $this->status === 'partial';
    }

    /**
     * Check if payment is paid.
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Get total paid amount from transactions.
     */
    public function getTotalPaidFromTransactions(): float
    {
        return $this->transactions()->sum('amount');
    }

    /**
     * Update payment status based on paid amount.
     */
    public function updatePaymentStatus(): void
    {
        $balance = $this->balance_amount;
        
        if ($balance <= 0.01) {
            $this->status = 'paid';
            $this->paid_amount = $this->net_amount;
            $this->balance_amount = 0;
        } elseif ($this->paid_amount > 0) {
            $this->status = 'partial';
        } else {
            $this->status = 'pending';
        }
        
        $this->save();
    }

    /**
     * Get payment method options.
     */
    public static function getPaymentMethods()
    {
        return [
            'cash' => 'Cash',
            'bank_transfer' => 'Bank Transfer',
            'cheque' => 'Cheque',
            'online_payment' => 'Online Payment',
            'other' => 'Other',
        ];
    }
}
