<?php

use Illuminate\Support\Facades\Storage;

if (!function_exists('storage_url')) {
    /**
     * Get public storage URL for a file path
     * 
     * @param string|null $path The storage path (relative to storage/app/public)
     * @return string|null The full URL or null if file doesn't exist
     */
    function storage_url(?string $path): ?string
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
}

if (!function_exists('can_module')) {
    function can_module(string $module, string $action = 'view'): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->canAccessModule($module, $action);
    }
}

