<?php

namespace App\Models;

use App\Models\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompletedWork extends Model
{
    use HasFactory, CompanyScoped;

    protected $fillable = [
        'company_id',
        'project_id',
        'bill_item_id',
        'bill_category_id',
        'bill_subcategory_id',
        'work_type',
        'length',
        'width',
        'height',
        'description',
        'uom',
        'quantity',
        'work_date',
        'recorded_by',
        'status',
        'remarks',
        'attachments',
    ];

    protected $casts = [
        'work_date' => 'date',
        'length' => 'decimal:3',
        'width' => 'decimal:3',
        'height' => 'decimal:3',
        'quantity' => 'decimal:3',
        'attachments' => 'array',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function billItem()
    {
        return $this->belongsTo(BillItem::class);
    }

    public function billCategory()
    {
        return $this->belongsTo(BillCategory::class);
    }

    public function billSubcategory()
    {
        return $this->belongsTo(BillSubcategory::class);
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function canBill(): bool
    {
        return $this->status !== 'billed' && $this->bill_item_id !== null;
    }
}
