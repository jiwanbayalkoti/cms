<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\CompanyScoped;

class BankAccount extends Model
{
    use HasFactory, CompanyScoped;

    protected $fillable = [
        'company_id',
        'account_name',
        'account_number',
        'bank_name',
        'branch_name',
        'branch_address',
        'swift_code',
        'account_type',
        'chart_of_account_id',
        'opening_balance',
        'opening_balance_date',
        'current_balance',
        'currency',
        'is_active',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'opening_balance_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function chartOfAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
