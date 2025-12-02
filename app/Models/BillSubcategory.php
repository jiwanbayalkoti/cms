<?php

namespace App\Models;

use App\Models\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillSubcategory extends Model
{
    use HasFactory, CompanyScoped;

    protected $fillable = [
        'company_id',
        'bill_category_id',
        'name',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(BillCategory::class, 'bill_category_id');
    }
}
