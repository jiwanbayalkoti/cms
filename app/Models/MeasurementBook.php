<?php

namespace App\Models;

use App\Models\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeasurementBook extends Model
{
    use HasFactory, CompanyScoped;

    protected $fillable = [
        'company_id',
        'project_id',
        'contract_no',
        'measurement_date',
        'title',
        'dimension_unit',
        'created_by',
    ];

    /** Dimension unit for L,W,H: single unit only */
    public const DIMENSION_UNITS = [
        'ft' => 'ft',
        'm'  => 'm',
        'in' => 'in',
        'cm' => 'cm',
    ];

    protected $casts = [
        'measurement_date' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(MeasurementBookItem::class)->orderBy('sort_order');
    }
    
    public function mainItems()
    {
        return $this->hasMany(MeasurementBookItem::class)->whereNull('parent_id')->orderBy('sn');
    }
}
