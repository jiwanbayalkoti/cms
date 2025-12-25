<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnlyMiddleware
{
    /**
     * Handle an incoming request.
     * Restrict access to admin and super_admin only (not regular users).
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('admin.login')->with('error', 'You must be logged in to access this page.');
        }

        $user = auth()->user();
        
        // Only allow admin or super_admin (not regular users)
        $hasAccess = in_array($user->role, ['admin', 'super_admin']) || $user->is_admin;
        
        if (!$hasAccess) {
            return redirect()->route('admin.dashboard')->with('error', 'You do not have permission to access this page. Only administrators can access this section.');
        }

        return $next($request);
    }
}

