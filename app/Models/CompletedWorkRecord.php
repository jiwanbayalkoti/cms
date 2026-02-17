<?php

namespace App\Models;

use App\Models\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompletedWorkRecord extends Model
{
    use HasFactory, CompanyScoped;

    protected $fillable = ['company_id', 'project_id', 'boq_work_id', 'record_date', 'notes', 'dimension_unit'];

    protected $casts = [
        'record_date' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    public function project()
    {
        return $this->belongsTo(\App\Models\Project::class);
    }

    public function work()
    {
        return $this->belongsTo(BoqWork::class, 'boq_work_id');
    }

    public function recordItems()
    {
        return $this->hasMany(CompletedWorkRecordItem::class, 'completed_work_record_id');
    }

    public function materialUsages()
    {
        return $this->hasMany(CompletedWorkMaterialUsage::class, 'completed_work_record_id')->with('constructionMaterial');
    }
}
