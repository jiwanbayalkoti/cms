<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class StorageHelper
{
    /**
     * Get public storage URL for a file path
     * Project path: public_html/repositories/cms
     * Symlink: public/storage -> storage/app/public
     * 
     * @param string|null $path The storage path (relative to storage/app/public)
     * @return string|null The full URL or null if file doesn't exist
     */
    public static function url(?string $path): ?string
    {
        if (!$path) {
            return null;
        }
        
        // Clean the path
        $path = ltrim($path, '/');
        
        // Base path for project in subdirectory: public_html/repositories/cms
        $basePath = '/repositories/cms';
        
        // Generate URL: /repositories/cms/storage/projects/photos/filename.jpg
        // This works with symlink: public/storage -> storage/app/public
        return url($basePath . '/storage/' . $path);
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

