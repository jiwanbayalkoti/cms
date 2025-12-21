<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('admin.login')->with('error', 'You must be logged in to access this page.');
        }

        $user = auth()->user();
        
        // Check if user has admin or super_admin role, or legacy is_admin flag
        $hasAccess = in_array($user->role, ['admin', 'super_admin']) || $user->is_admin;
        
        if (!$hasAccess) {
            return redirect()->route('admin.login')->with('error', 'You must be an admin or super admin to access this page.');
        }

        return $next($request);
    }
}
