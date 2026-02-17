<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompletedWorkRecordItem extends Model
{
    use HasFactory;

    protected $fillable = ['completed_work_record_id', 'parent_id', 'boq_item_id', 'description', 'no', 'length', 'breadth', 'height', 'completed_qty'];

    protected $casts = [
        'no' => 'decimal:4',
        'length' => 'decimal:4',
        'breadth' => 'decimal:4',
        'height' => 'decimal:4',
        'completed_qty' => 'decimal:4',
    ];

    public function completedWorkRecord()
    {
        return $this->belongsTo(CompletedWorkRecord::class);
    }

    public function parent()
    {
        return $this->belongsTo(CompletedWorkRecordItem::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(CompletedWorkRecordItem::class, 'parent_id');
    }

    public function boqItem()
    {
        return $this->belongsTo(BoqItem::class, 'boq_item_id');
    }

    public function isMainWork(): bool
    {
        return $this->parent_id === null;
    }
}
