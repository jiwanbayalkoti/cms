<?php

namespace App\Http\Middleware;

use App\Models\UserActivityLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogUserActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $user = auth()->user();
        if (!$user) {
            return $response;
        }

        $routeName = optional($request->route())->getName();
        if (!$routeName || !str_starts_with($routeName, 'admin.')) {
            return $response;
        }

        if ($this->shouldSkip($request, $routeName)) {
            return $response;
        }

        try {
            UserActivityLog::create([
                'user_id' => $user->id,
                'company_id' => $user->company_id,
                'action' => $this->buildAction($request, $routeName),
                'method' => strtoupper($request->method()),
                'route_name' => $routeName,
                'url' => $request->fullUrl(),
                'description' => $this->buildDescription($request, $routeName),
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'status_code' => $response->getStatusCode(),
            ]);
        } catch (\Throwable $e) {
            // Never break user flow due to logging.
        }

        return $response;
    }

    protected function shouldSkip(Request $request, ?string $routeName): bool
    {
        if (in_array($routeName, ['admin.user-activities.index', 'admin.logout'], true)) {
            return true;
        }

        // Do not log page views (GET); keep activity log focused on changes/actions.
        if ($request->isMethod('GET')) {
            return true;
        }

        if ($request->isMethod('OPTIONS')) {
            return true;
        }

        return false;
    }

    protected function buildAction(Request $request, string $routeName): string
    {
        if ($request->isMethod('GET')) {
            return 'Viewed ' . $this->humanizeRoute($routeName);
        }

        if ($request->isMethod('DELETE')) {
            return 'Deleted in ' . $this->humanizeRoute($routeName);
        }

        if ($request->isMethod('POST')) {
            return 'Created/Submitted in ' . $this->humanizeRoute($routeName);
        }

        if ($request->isMethod('PUT') || $request->isMethod('PATCH')) {
            return 'Updated in ' . $this->humanizeRoute($routeName);
        }

        return 'Accessed ' . $this->humanizeRoute($routeName);
    }

    protected function buildDescription(Request $request, string $routeName): string
    {
        $segments = array_values(array_filter(explode('/', trim($request->path(), '/'))));
        $resource = $segments[1] ?? 'resource';

        return ucfirst($resource) . ' - ' . strtoupper($request->method()) . ' - ' . $this->humanizeRoute($routeName);
    }

    protected function humanizeRoute(string $routeName): string
    {
        $clean = str_replace('admin.', '', $routeName);
        $clean = str_replace(['.', '-'], ' ', $clean);

        return ucwords($clean);
    }
}

