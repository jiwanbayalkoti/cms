<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompletedWorkMaterialUsage extends Model
{
    protected $table = 'completed_work_material_usages';

    protected $fillable = ['completed_work_record_id', 'construction_material_id', 'quantity'];

    protected $casts = [
        'quantity' => 'decimal:4',
    ];

    public function completedWorkRecord(): BelongsTo
    {
        return $this->belongsTo(CompletedWorkRecord::class, 'completed_work_record_id');
    }

    public function constructionMaterial(): BelongsTo
    {
        return $this->belongsTo(ConstructionMaterial::class, 'construction_material_id');
    }
}
