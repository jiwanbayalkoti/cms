<?php

namespace App\Models;

use App\Support\CompanyContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\CompanyScoped;

class Category extends Model
{
    use HasFactory, CompanyScoped;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the subcategories for the category.
     */
    public function subcategories()
    {
        return $this->hasMany(Subcategory::class);
    }

    /**
     * First active expense category in the current company scope, or a new default row.
     */
    public static function firstOrCreateDefaultExpense(): self
    {
        $existing = static::where('type', 'expense')->where('is_active', true)->orderBy('name')->first();
        if ($existing) {
            return $existing;
        }

        return static::create([
            'company_id' => CompanyContext::getActiveCompanyId(),
            'name' => 'General expenses',
            'description' => null,
            'type' => 'expense',
            'is_active' => true,
        ]);
    }
}
