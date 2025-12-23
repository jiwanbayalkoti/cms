<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class StorageHelper
{
    /**
     * Get public storage URL for a file path
     * Files are stored in public_html directory
     * 
     * @param string|null $path The storage path (relative to public_html)
     * @return string|null The full URL or null if file doesn't exist
     */
    public static function url(?string $path): ?string
    {
        if (!$path) {
            return null;
        }
        
        // Clean the path
        $path = ltrim($path, '/');
        
        // Files are stored in public_html, so generate URL directly
        // Path format: projects/photos/filename.jpg
        // URL format: /projects/photos/filename.jpg
        return url('/' . $path);
    }
    
    /**
     * Check if a file exists in public_html storage
     * 
     * @param string|null $path The storage path
     * @return bool
     */
    public static function exists(?string $path): bool
    {
        if (!$path) {
            return false;
        }
        
        return Storage::disk('public_html')->exists($path);
    }
}

