<?php

namespace App\Http\Middleware;

use App\Services\ModulePermissionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ModuleAccessMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('admin.login')->with('error', 'You must be logged in to access this page.');
        }

        $user = auth()->user();
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        if (is_string($routeName) && str_starts_with($routeName, 'admin.roles.') && $user->isAdmin()) {
            return $next($request);
        }

        $alwaysAdminRoutes = [
            'admin.companies.profile',
            'admin.companies.profile.update',
            'admin.companies.letterhead',
            'admin.companies.letterhead.update',
            'admin.companies.letterhead.exports',
            'admin.companies.letterhead.export.pdf',
            'admin.companies.letterhead.export.pdf.post',
            'admin.companies.letterhead.assets.store',
            'admin.companies.letterhead.assets.destroy',
            'admin.logout',
            'admin.users.switch-back',
            'admin.projects.switch',
            'admin.payment-required',
            'admin.payment-requests.store',
            'admin.payment-requests.history.pdf',
        ];

        if ($user->isAdmin() && in_array($routeName, $alwaysAdminRoutes, true)) {
            return $next($request);
        }

        if ($routeName === 'admin.dashboard' && $user->isAdmin()) {
            return $next($request);
        }

        $galleryRoutes = [
            'admin.projects.index',
            'admin.projects.gallery',
            'admin.projects.gallery.album.add',
            'admin.projects.gallery.album.update',
            'admin.projects.gallery.album.delete',
            'admin.projects.gallery.photos.add',
            'admin.projects.gallery.photo.delete',
            'admin.projects.gallery.photo.delete.post',
            'admin.projects.gallery.photo.approve',
            'admin.projects.gallery.photo.disapprove',
            'admin.projects.gallery.photos.bulk-approve',
        ];

        if ($user->role === 'site_engineer' && in_array($routeName, $galleryRoutes, true)) {
            return $next($request);
        }

        $service = app(ModulePermissionService::class);
        $module = $service->moduleForRoute($routeName);
        if (!$module) {
            return $next($request);
        }

        $action = $service->actionFromRequest($request);
        if ($user->canAccessModule($module, $action)) {
            return $next($request);
        }

        return $service->denyAccess($request, $user);
    }
}
