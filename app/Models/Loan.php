<?php

namespace App\Models;

use App\Models\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory, CompanyScoped;

    protected $fillable = [
        'company_id',
        'project_id',
        'direction',
        'supplier_id',
        'staff_id',
        'party_name',
        'party_source',
        'source',
        'interest_rate',
        'reference_number',
        'amount',
        'loan_date',
        'is_closed',
        'closed_at',
        'payment_method',
        'bank_account_id',
        'notes',
        'approved_at',
        'approved_by',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'loan_date' => 'date',
        'is_closed' => 'boolean',
        'closed_at' => 'date',
        'approved_at' => 'datetime',
    ];

    public function payments()
    {
        return $this->hasMany(LoanPayment::class)->orderBy('payment_date')->orderBy('id');
    }

    /**
     * Calculate outstanding principal & interest as of a date.
     * - Interest accrues daily (simple interest) on remaining principal.
     * - Allocation: payment pays interest first, then principal.
     */
    public function outstandingAsOf(\Carbon\Carbon $asOf): array
    {
        $principal = (float) ($this->amount ?? 0);
        $rate = (float) ($this->interest_rate ?? 0);
        $interestDue = 0.0;

        $asOf = $asOf->copy()->endOfDay();
        $lastDate = $this->loan_date ? \Carbon\Carbon::parse($this->loan_date)->startOfDay() : $asOf->copy()->startOfDay();

        $payments = $this->relationLoaded('payments')
            ? $this->payments
            : $this->payments()->get();

        $totalPaid = 0.0;
        $totalInterestPaid = 0.0;
        $totalPrincipalPaid = 0.0;

        foreach ($payments as $p) {
            $payDate = $p->payment_date ? \Carbon\Carbon::parse($p->payment_date)->startOfDay() : null;
            if (!$payDate) {
                continue;
            }
            if ($payDate->gt($asOf)) {
                break;
            }

            $days = max(0, $lastDate->diffInDays($payDate));
            if ($days > 0 && $principal > 0) {
                $interestDue += $principal * $rate / 100 * ($days / 365);
            }

            $payAmount = (float) ($p->amount ?? 0);
            $totalPaid += $payAmount;

            $interestPay = min($interestDue, $payAmount);
            $interestDue -= $interestPay;
            $payAmount -= $interestPay;
            $totalInterestPaid += $interestPay;

            $principalPay = min($principal, $payAmount);
            $principal -= $principalPay;
            $payAmount -= $principalPay;
            $totalPrincipalPaid += $principalPay;

            $lastDate = $payDate;
        }

        // Accrue interest till asOf
        $daysToAsOf = max(0, $lastDate->diffInDays($asOf->copy()->startOfDay()));
        if ($daysToAsOf > 0 && $principal > 0) {
            $interestDue += $principal * $rate / 100 * ($daysToAsOf / 365);
        }

        $principal = max(0.0, $principal);
        $interestDue = max(0.0, $interestDue);
        $isClosed = $principal <= 0.01 && $interestDue <= 0.01;

        return [
            'principal_outstanding' => $principal,
            'interest_due' => $interestDue,
            'total_due' => $principal + $interestDue,
            'total_paid' => $totalPaid,
            'interest_paid' => $totalInterestPaid,
            'principal_paid' => $totalPrincipalPaid,
            'is_closed' => $isClosed,
        ];
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isApproved(): bool
    {
        return $this->approved_at !== null;
    }
}

