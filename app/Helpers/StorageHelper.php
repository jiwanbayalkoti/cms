<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class StorageHelper
{
    /**
     * Get public storage URL for a file path
     * Files are stored in storage/app/public and accessed via symlink
     * Project path: public_html/repositories/cms
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
        
        // For cPanel setup: project is in public_html/repositories/cms
        // Symlink: public/storage -> storage/app/public
        // URL should be: /repositories/cms/storage/projects/photos/filename.jpg
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

