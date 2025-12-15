<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class StorageHelper
{
    /**
     * Get public storage URL for a file path
     * This is a centralized helper to ensure consistent URL generation
     * 
     * @param string|null $path The storage path (relative to storage/app/public)
     * @return string|null The full URL or null if file doesn't exist
     */
    public static function url(?string $path): ?string
    {
        if (!$path) {
            return null;
        }
        
        $storage = Storage::disk('public');
        
        if ($storage->exists($path)) {
            // Use asset() for better compatibility with different server configurations
            return asset('storage/' . $path);
        }
        
        return null;
    }
    
    /**
     * Check if a file exists in public storage
     * 
     * @param string|null $path The storage path
     * @return bool
     */
    public static function exists(?string $path): bool
    {
        if (!$path) {
            return false;
        }
        
        return Storage::disk('public')->exists($path);
    }
}

