<?php

namespace App\Models;

use App\Models\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillSetting extends Model
{
    use HasFactory, CompanyScoped;

    protected $fillable = [
        'company_id',
        'tax_rate_default',
        'overhead_default',
        'contingency_default',
        'currency',
        'work_categories',
    ];

    protected $casts = [
        'tax_rate_default' => 'decimal:2',
        'overhead_default' => 'decimal:2',
        'contingency_default' => 'decimal:2',
        'work_categories' => 'array',
    ];

    public static function getDefaultCategories(): array
    {
        return [
            'Earthwork',
            'RCC',
            'Masonry',
            'Plaster',
            'Flooring',
            'Doors/Windows',
            'Electrical',
            'Plumbing',
            'Finishing',
            'Structural Steel',
            'Carpentry',
            'Miscellaneous',
        ];
    }
}
