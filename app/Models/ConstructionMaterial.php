<?php

namespace App\Models;

use App\Models\Traits\CompanyScoped;
use App\Models\Traits\ProjectScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConstructionMaterial extends Model
{
    use HasFactory;
    use CompanyScoped, ProjectScoped;

    protected $fillable = [
        'company_id',
        'project_id',
        'material_name',
        'material_category',
        'unit',
        'quantity_received',
        'rate_per_unit',
        'total_cost',
        'quantity_used',
        'quantity_remaining',
        'wastage_quantity',
        'supplier_name',
        'supplier_contact',
        'bill_number',
        'bill_date',
        'payment_status',
        'payment_mode',
        'purchased_by_id',
        'delivery_date',
        'delivery_site',
        'delivered_by',
        'received_by',
        'project_name',
        'work_type',
        'usage_purpose',
        'status',
        'approved_by',
        'approval_date',
        'bill_attachment',
        'delivery_photo',
    ];

    public function purchasedBy()
    {
        return $this->belongsTo(PurchasedBy::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the expense entry linked to this material purchase.
     */
    public function expense()
    {
        return $this->hasOne(Expense::class);
    }

    protected $casts = [
        'quantity_received' => 'decimal:2',
        'rate_per_unit' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'quantity_used' => 'decimal:2',
        'quantity_remaining' => 'decimal:2',
        'wastage_quantity' => 'decimal:2',
        'bill_date' => 'date',
        'delivery_date' => 'date',
        'approval_date' => 'date',
    ];
}


