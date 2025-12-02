<?php

namespace App\Models;

use App\Models\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillAggregate extends Model
{
    use HasFactory, CompanyScoped;

    protected $fillable = [
        'bill_module_id',
        'company_id',
        'subtotal',
        'tax_total',
        'overhead_percent',
        'overhead_amount',
        'contingency_percent',
        'contingency_amount',
        'grand_total',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'overhead_percent' => 'decimal:2',
        'overhead_amount' => 'decimal:2',
        'contingency_percent' => 'decimal:2',
        'contingency_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    public function billModule()
    {
        return $this->belongsTo(BillModule::class);
    }
}
