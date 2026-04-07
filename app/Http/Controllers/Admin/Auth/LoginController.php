<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\UserActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show the admin login form.
     */
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    /**
     * Handle an admin login request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember');

        // Allow all authenticated users to login
        // Access control will be handled by middleware based on user role
        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();
            $request->session()->regenerate();

            UserActivityLog::create([
                'user_id' => $user->id,
                'company_id' => $user->company_id,
                'action' => 'User login',
                'method' => 'POST',
                'route_name' => 'admin.login',
                'url' => $request->fullUrl(),
                'description' => 'Logged in to admin panel',
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'status_code' => 200,
            ]);
            
            // Redirect based on user role
            if ($user->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'))->with('success', 'Welcome back, ' . $user->name . '!');
            } elseif ($user->role === 'site_engineer') {
                // Site engineers go directly to projects list (galleries only)
                return redirect()->intended(route('admin.projects.index'))->with('success', 'Welcome back, ' . $user->name . '!');
            } else {
                // Regular users go directly to projects list
                return redirect()->intended(route('admin.projects.index'))->with('success', 'Welcome back, ' . $user->name . '!');
            }
        }

        throw ValidationException::withMessages([
            'email' => ['The provided credentials do not match our records.'],
        ]);
    }

    /**
     * Log the admin out.
     */
    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            UserActivityLog::create([
                'user_id' => $user->id,
                'company_id' => $user->company_id,
                'action' => 'User logout',
                'method' => strtoupper($request->method()),
                'route_name' => 'admin.logout',
                'url' => $request->fullUrl(),
                'description' => 'Logged out from admin panel',
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'status_code' => 200,
            ]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('success', 'You have been logged out successfully.');
    }
}
