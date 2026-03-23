<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\CompanyScoped;

class Supplier extends Model
{
    use HasFactory, CompanyScoped;

    /**
     * Advance payments recorded for this supplier (same flow as Advance Payments module).
     */
    public function advancePayments()
    {
        return $this->hasMany(AdvancePayment::class);
    }

    protected $fillable = [
        'company_id',
        'name',
        'contact',
        'email',
        'address',
        'bank_name',
        'account_holder_name',
        'account_number',
        'branch_name',
        'branch_address',
        'qr_code_image',
        'is_active',
    ];
}


