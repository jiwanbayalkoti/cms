<?php

namespace App\Models;

use App\Models\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillItem extends Model
{
    use HasFactory, CompanyScoped;

    protected $fillable = [
        'bill_module_id',
        'company_id',
        'bill_category_id',
        'bill_subcategory_id',
        'category',
        'subcategory',
        'description',
        'uom',
        'quantity',
        'unit_rate',
        'amount',
        'wastage_percent',
        'effective_quantity',
        'total_amount',
        'tax_percent',
        'net_amount',
        'attachments',
        'rate_breakdown',
        'remarks',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_rate' => 'decimal:2',
        'amount' => 'decimal:2',
        'wastage_percent' => 'decimal:2',
        'effective_quantity' => 'decimal:3',
        'total_amount' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'attachments' => 'array',
        'rate_breakdown' => 'array',
    ];

    public function billModule()
    {
        return $this->belongsTo(BillModule::class);
    }

    public function billCategory()
    {
        return $this->belongsTo(BillCategory::class);
    }

    public function billSubcategory()
    {
        return $this->belongsTo(BillSubcategory::class);
    }

    public function measurements()
    {
        return $this->hasMany(Measurement::class)->orderByDesc('measure_date');
    }
}
