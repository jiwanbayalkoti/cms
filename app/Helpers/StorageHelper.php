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
        
        // Clean the path (remove leading/trailing slashes)
        $path = trim($path, '/');
        
        // Database stores: 'projects/photos/filename.jpg' (from store() method)
        // URL should be: '/storage/projects/photos/filename.jpg' (via symlink)
        // Using asset() which respects the symlink and base path
        return asset('storage/' . $path);
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

