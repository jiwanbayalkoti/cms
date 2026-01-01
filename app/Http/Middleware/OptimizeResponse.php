<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class OptimizeResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        // Add performance headers
        if (method_exists($response, 'header')) {
            $response->header('X-Content-Type-Options', 'nosniff');
            $response->header('X-Frame-Options', 'SAMEORIGIN');
            
            // Enable browser caching for static assets
            if ($request->is('*.css') || $request->is('*.js') || $request->is('*.jpg') || $request->is('*.png') || $request->is('*.gif')) {
                $response->header('Cache-Control', 'public, max-age=31536000');
            }
        }
        
        return $response;
    }
}

