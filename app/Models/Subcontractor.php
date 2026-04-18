<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\CompanyScoped;

class Subcontractor extends Model
{
    use HasFactory, CompanyScoped;

    protected $fillable = [
        'company_id',
        'name',
        'phone',
        'email',
        'contact_person',
        'pan_number',
        'address',
        'notes',
        'work_types',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'work_types' => 'array',
    ];

    /**
     * Preset work types for multi-select (custom types can be added per record).
     *
     * @return list<string>
     */
    public static function workTypeOptions(): array
    {
        return [
            'Building',
            'Wall',
            'Electric',
            'Plumbing',
            'Painting',
            'Furniture',
            'UPVC',
            'Steel',
            'Iron',
            'Truss',
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}
