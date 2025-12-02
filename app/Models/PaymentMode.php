<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\CompanyScoped;

class PaymentMode extends Model
{
    use HasFactory, CompanyScoped;

    protected $fillable = [
        'company_id',
        'name',
    ];
}
