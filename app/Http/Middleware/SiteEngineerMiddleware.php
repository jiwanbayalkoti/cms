<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SiteEngineerMiddleware
{
    /**
     * Handle an incoming request.
     * Restrict site engineers to only access project galleries.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('admin.login')->with('error', 'You must be logged in to access this page.');
        }

        $user = auth()->user();
        
        // If user is site engineer, only allow access to project galleries
        if ($user->role === 'site_engineer') {
            // Get route name (may be null for unnamed routes)
            $routeName = $request->route() ? $request->route()->getName() : null;
            
            // Allow access to project index and gallery routes only (no project details)
            $allowedRoutes = [
                'admin.projects.index',
                'admin.projects.gallery',
                'admin.projects.gallery.album.add',
                'admin.projects.gallery.album.update',
                'admin.projects.gallery.album.delete',
                'admin.projects.gallery.photos.add',
                'admin.projects.gallery.photo.delete',
                'admin.projects.gallery.photo.delete.post',
                'admin.logout',
                null, // Allow routes without names (like root redirect)
            ];
            
            // Handle routes without names - allow them
            if ($routeName === null) {
                return $next($request);
            }
            
            // Check if route is in allowed list
            if (!in_array($routeName, $allowedRoutes)) {
                return redirect()->route('admin.projects.index')->with('error', 'You only have access to project galleries.');
            }
        }

        return $next($request);
    }
}

