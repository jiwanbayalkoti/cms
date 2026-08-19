<?php

namespace App\Http\Middleware;

use App\Services\ModulePermissionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SiteEngineerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('admin.login')->with('error', 'You must be logged in to access this page.');
        }

        $user = auth()->user();

        if ($user->role !== 'site_engineer') {
            return $next($request);
        }

        $routeName = $request->route() ? $request->route()->getName() : null;

        $allowedRoutes = [
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
            'admin.logout',
            'admin.users.switch-back',
            null,
        ];

        if ($routeName === null || in_array($routeName, $allowedRoutes, true)) {
            return $next($request);
        }

        $service = app(ModulePermissionService::class);
        $module = $service->moduleForRoute($routeName);
        $action = $service->actionFromRequest($request);

        if ($module && $user->canAccessModule($module, $action)) {
            return $next($request);
        }

        return $service->denyAccess(
            $request,
            $user,
            'You only have access to project galleries, or modules assigned to your role.'
        );
    }
}
