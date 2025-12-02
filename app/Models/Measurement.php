<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Measurement extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_item_id',
        'measured_by',
        'measure_date',
        'measured_quantity',
        'note',
        'photo_urls',
        'mb_reference',
    ];

    protected $casts = [
        'measure_date' => 'date',
        'measured_quantity' => 'decimal:3',
        'photo_urls' => 'array',
    ];

    public function billItem()
    {
        return $this->belongsTo(BillItem::class);
    }

    public function measurer()
    {
        return $this->belongsTo(User::class, 'measured_by');
    }
}
