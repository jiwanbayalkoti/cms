<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeasurementBookItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'measurement_book_id',
        'parent_id',
        'sn',
        'works',
        'work_identifier',
        'no',
        'length_ft',
        'length_in',
        'breadth_ft',
        'breadth_in',
        'height_ft',
        'height_in',
        'quantity',
        'total_qty',
        'unit',
        'sort_order',
    ];

    protected $casts = [
        'no' => 'decimal:4',
        'length_ft' => 'decimal:4',
        'length_in' => 'decimal:4',
        'breadth_ft' => 'decimal:4',
        'breadth_in' => 'decimal:4',
        'height_ft' => 'decimal:4',
        'height_in' => 'decimal:4',
        'quantity' => 'decimal:4',
        'total_qty' => 'decimal:4',
    ];

    public function measurementBook()
    {
        return $this->belongsTo(MeasurementBook::class);
    }

    public function parent()
    {
        return $this->belongsTo(MeasurementBookItem::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(MeasurementBookItem::class, 'parent_id')->orderBy('sort_order');
    }

    public function isMainWork(): bool
    {
        return $this->parent_id === null;
    }
}
