<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryPaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'salary_payment_id',
        'amount',
        'payment_date',
        'payment_method',
        'bank_account_id',
        'transaction_reference',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    /**
     * Get the salary payment.
     */
    public function salaryPayment()
    {
        return $this->belongsTo(SalaryPayment::class);
    }

    /**
     * Get the bank account.
     */
    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    /**
     * Get the creator.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
