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
            // Try multiple URL generation methods for compatibility
            $url = asset('storage/' . $this->logo);
            
            // For live server compatibility, also check if we need absolute URL
            if (config('app.env') === 'production') {
                // Ensure we have a full URL if APP_URL is set
                $appUrl = config('app.url');
                if ($appUrl && !str_starts_with($url, 'http')) {
                    $url = rtrim($appUrl, '/') . '/' . ltrim($url, '/');
                }
            }
            
            return $url;
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
                // Try multiple URL generation methods for compatibility
                $url = asset('storage/' . $this->favicon);
                
                // For live server compatibility, also check if we need absolute URL
                if (config('app.env') === 'production') {
                    // Ensure we have a full URL if APP_URL is set
                    $appUrl = config('app.url');
                    if ($appUrl && !str_starts_with($url, 'http')) {
                        $url = rtrim($appUrl, '/') . '/' . ltrim($url, '/');
                    }
                }
                
                return $url;
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
                    $url = asset('storage/' . $faviconPath);
                    
                    // For live server compatibility
                    if (config('app.env') === 'production') {
                        $appUrl = config('app.url');
                        if ($appUrl && !str_starts_with($url, 'http')) {
                            $url = rtrim($appUrl, '/') . '/' . ltrim($url, '/');
                        }
                    }
                    
                    return $url;
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


