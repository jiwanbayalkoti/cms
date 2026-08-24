<?php

namespace App\Http\Middleware;

use App\Services\ModulePermissionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnlyMiddleware
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

        // Company profile/letterhead/documents are always accessible to company admins
        $companyAdminRoutes = [
            'admin.companies.profile',
            'admin.companies.profile.update',
            'admin.companies.letterhead',
            'admin.companies.letterhead.update',
            'admin.companies.letterhead.exports',
            'admin.companies.letterhead.export.pdf',
            'admin.companies.letterhead.export.pdf.post',
            'admin.companies.letterhead.assets.store',
            'admin.companies.letterhead.assets.destroy',
            'admin.companies.documents',
            'admin.companies.documents.upload',
            'admin.companies.documents.download',
            'admin.companies.documents.destroy',
        ];
        if ($user->isAdmin() && in_array($routeName, $companyAdminRoutes, true)) {
            return $next($request);
        }

        $service = app(ModulePermissionService::class);
        $module = $service->moduleForRoute($routeName);
        $action = $service->actionFromRequest($request);

        if ($module && $user->canAccessModule($module, $action)) {
            return $next($request);
        }

        if ($user->isAdmin() && !$module) {
            return $next($request);
        }

        return $service->denyAccess($request, $user);
    }
}
