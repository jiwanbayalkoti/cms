<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RunningBillItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'running_bill_id',
        'sn',
        'description',
        'unit',
        'boq_qty',
        'boq_unit_price',
        'this_bill_qty',
        'unit_price',
        'total_price',
        'remaining_qty',
        'remarks',
        'sort_order',
    ];

    protected $casts = [
        'boq_qty' => 'decimal:4',
        'boq_unit_price' => 'decimal:2',
        'this_bill_qty' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'remaining_qty' => 'decimal:4',
    ];

    public function runningBill()
    {
        return $this->belongsTo(RunningBill::class);
    }
}
