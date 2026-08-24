<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Services\CompanyPaymentLockService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyPaymentActive
{
    public function __construct(private CompanyPaymentLockService $paymentLockService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user || $user->isSuperAdmin()) {
            return $next($request);
        }

        if ($request->routeIs('admin.payment-required')
            || $request->routeIs('admin.payment-requests.store')
            || $request->routeIs('admin.payment-requests.history.pdf')
            || $request->routeIs('admin.logout')
            || $request->routeIs('admin.users.switch-back')) {
            return $next($request);
        }

        $company = $user->company_id ? Company::find($user->company_id) : null;
        if (!$company) {
            return $next($request);
        }

        if ($company->isTrialExpired()) {
            $this->paymentLockService->lockExpiredTrial($company);
            $company->refresh();
        }

        if ($company->isPaymentExpired()) {
            $company->lockForPayment('payment_expired');
            $company->refresh();
        }

        if ($company->isLockedForPayment()) {
            return redirect()->route('admin.payment-required');
        }

        return $next($request);
    }
}
