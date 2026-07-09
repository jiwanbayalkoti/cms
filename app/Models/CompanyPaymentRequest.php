<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyPaymentRequest extends Model
{
    protected $fillable = [
        'company_id',
        'submitted_by',
        'verified_by',
        'amount',
        'paid_for_days',
        'payment_method',
        'transaction_reference',
        'payment_date',
        'proof_path',
        'note',
        'status',
        'verified_at',
        'admin_note',
        'starts_at',
        'expires_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'verified_at' => 'datetime',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
