<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\CompanyScoped;
use App\Models\Traits\ProjectScoped;

class AdvancePayment extends Model
{
    use HasFactory, CompanyScoped, ProjectScoped;

    protected $fillable = [
        'company_id',
        'project_id',
        'payment_type',
        'supplier_id',
        'amount',
        'payment_date',
        'bank_account_id',
        'payment_method',
        'transaction_reference',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
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
     * Get the supplier.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
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
     * Get payment type options.
     */
    public static function getPaymentTypes()
    {
        return [
            'vehicle_rent' => 'Vehicle Rent',
            'material_payment' => 'Material Payment',
        ];
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

    /**
     * Get the expense entry linked to this advance payment.
     */
    public function expense()
    {
        return $this->hasOne(Expense::class);
    }
}
