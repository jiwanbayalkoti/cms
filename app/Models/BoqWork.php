<?php

namespace App\Models;

use App\Models\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoqWork extends Model
{
    use HasFactory, CompanyScoped;

    protected $fillable = ['company_id', 'boq_type_id', 'parent_id', 'name', 'sort_order'];

    public function type()
    {
        return $this->belongsTo(BoqType::class, 'boq_type_id');
    }

    public function parent()
    {
        return $this->belongsTo(BoqWork::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(BoqWork::class, 'parent_id')->orderBy('sort_order')->orderBy('name');
    }

    public function items()
    {
        return $this->hasMany(BoqItem::class, 'boq_work_id')->orderBy('sort_order')->orderBy('id');
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }
}
