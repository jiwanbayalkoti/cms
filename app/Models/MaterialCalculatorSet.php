<?php

namespace App\Models;

use App\Models\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialCalculatorSet extends Model
{
    use HasFactory, CompanyScoped;

    protected $fillable = [
        'company_id',
        'user_id',
        'name',
        'calculations',
        'summary',
    ];

    protected $casts = [
        'calculations' => 'array',
        'summary' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}


