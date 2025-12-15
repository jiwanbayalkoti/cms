<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'email',
        'phone',
        'website',
        'tax_number',
        'city',
        'state',
        'country',
        'zip',
        'logo',
        'favicon',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function incomes()
    {
        return $this->hasMany(Income::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Get favicon URL, generating default if needed
     */
    public function getFaviconUrl(): string
    {
        if ($this->favicon && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->favicon)) {
            return asset('storage/' . $this->favicon);
        }
        
        // Generate default if not exists
        if ($this->name) {
            try {
                $faviconService = app(\App\Services\FaviconGeneratorService::class);
                $faviconPath = $faviconService->generateDefaultFavicon($this->name);
                $this->update(['favicon' => $faviconPath]);
                return asset('storage/' . $faviconPath);
            } catch (\Exception $e) {
                // Fallback if generation fails
                return asset('favicon.ico');
            }
        }
        
        return asset('favicon.ico');
    }
}


