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
        
        // Clean the path
        $path = ltrim($path, '/');
        
        // Check if file exists (optional - can be removed for performance)
        // if (!$storage->exists($path)) {
        //     return null;
        // }
        
        // Production server has Laravel in repositories/cms subdirectory
        // Generate URL with correct base path: /repositories/cms/storage/app/public/
        $basePath = '/repositories/cms/storage/app/public/';
        return url($basePath . $path);
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

