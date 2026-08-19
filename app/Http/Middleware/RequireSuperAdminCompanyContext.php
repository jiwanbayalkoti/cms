<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireSuperAdminCompanyContext
{
    /**
     * Super admins without an active company may only use platform-level routes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user || !$user->isSuperAdmin()) {
            return $next($request);
        }

        if (session('active_company_id')) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();
        if ($routeName && $this->isAllowedWithoutCompany($routeName)) {
            return $next($request);
        }

        $message = 'Please select a company from the Super Admin dashboard to continue.';

        if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $message], 403);
        }

        return redirect()
            ->route('admin.companies.index')
            ->with('error', $message);
    }

    private function isAllowedWithoutCompany(string $routeName): bool
    {
        $prefixes = [
            'admin.super-admin.',
            'admin.companies.',
            'admin.settings.',
            'admin.payment-requests.',
            'admin.users.',
            'admin.user-activities.',
            'admin.roles.',
        ];

        foreach ($prefixes as $prefix) {
            if (str_starts_with($routeName, $prefix)) {
                return true;
            }
        }

        return in_array($routeName, [
            'admin.logout',
            'admin.users.switch-back',
        ], true);
    }
}
