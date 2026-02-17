<?php

namespace App\Models;

use App\Models\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoqType extends Model
{
    use HasFactory, CompanyScoped;

    protected $fillable = ['company_id', 'name', 'sort_order'];

    public function works()
    {
        return $this->hasMany(BoqWork::class, 'boq_type_id')->orderBy('sort_order')->orderBy('name');
    }
}
