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
     * Get logo URL
     */
    public function getLogoUrl(): ?string
    {
        if (!$this->logo) {
            return null;
        }
        
        $storage = \Illuminate\Support\Facades\Storage::disk('public');
        
        if ($storage->exists($this->logo)) {
            // Use asset() for better compatibility with different server configurations
            return asset('storage/' . $this->logo);
        }
        
        // File doesn't exist - clear it from database
        $this->update(['logo' => null]);
        return null;
    }

    /**
     * Get favicon URL, generating default if needed
     */
    public function getFaviconUrl(): string
    {
        $storage = \Illuminate\Support\Facades\Storage::disk('public');
        
        // Check if favicon exists in database and on disk
        if ($this->favicon) {
            if ($storage->exists($this->favicon)) {
                // Use asset() for better compatibility with different server configurations
                return asset('storage/' . $this->favicon);
            } else {
                // File path exists in DB but file doesn't exist - clear it
                $this->update(['favicon' => null]);
            }
        }
        
        // Generate default if not exists
        if ($this->name) {
            try {
                $faviconService = app(\App\Services\FaviconGeneratorService::class);
                $faviconPath = $faviconService->generateDefaultFavicon($this->name);
                
                // Verify file was created
                if ($storage->exists($faviconPath)) {
                    $this->update(['favicon' => $faviconPath]);
                    return asset('storage/' . $faviconPath);
                }
            } catch (\Exception $e) {
                // Log error for debugging
                \Log::error('Favicon generation failed: ' . $e->getMessage());
            }
        }
        
        // Fallback
        return asset('favicon.ico');
    }
}


