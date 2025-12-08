<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\CompanyScoped;

class ChartOfAccount extends Model
{
    use HasFactory, CompanyScoped;

    protected $fillable = [
        'company_id',
        'account_code',
        'account_name',
        'account_type',
        'account_category',
        'parent_account_id',
        'level',
        'description',
        'opening_balance',
        'balance_type',
        'is_active',
        'is_system',
        'display_order',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'level' => 'integer',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * Get the parent account.
     */
    public function parentAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_account_id');
    }

    /**
     * Get child accounts.
     */
    public function childAccounts()
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_account_id')->orderBy('display_order');
    }

    /**
     * Get the company that owns the account.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get journal entry items for this account.
     */
    public function journalEntryItems()
    {
        return $this->hasMany(JournalEntryItem::class, 'account_id');
    }

    /**
     * Get bank accounts linked to this chart of account.
     */
    public function bankAccounts()
    {
        return $this->hasMany(BankAccount::class, 'chart_of_account_id');
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
     * Calculate current balance for this account.
     */
    public function getCurrentBalanceAttribute()
    {
        $debitTotal = $this->journalEntryItems()
            ->whereHas('journalEntry', function($query) {
                $query->where('is_posted', true);
            })
            ->where('entry_type', 'debit')
            ->sum('amount');

        $creditTotal = $this->journalEntryItems()
            ->whereHas('journalEntry', function($query) {
                $query->where('is_posted', true);
            })
            ->where('entry_type', 'credit')
            ->sum('amount');

        $balance = $this->opening_balance;
        
        if ($this->balance_type === 'debit') {
            $balance += $debitTotal - $creditTotal;
        } else {
            $balance += $creditTotal - $debitTotal;
        }

        return $balance;
    }
}
