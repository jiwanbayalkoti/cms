<?php

namespace App\Providers;

use App\Services\VehicleAlertService;
use App\Support\CompanyContext;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        /**
         * Ensure helper functions are always loaded (covers shared hosting where
         * composer dump-autoload may not be run after deploy).
         */
        $helpers = app_path('Helpers/functions.php');
        if (file_exists($helpers)) {
            require_once $helpers;
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        View::composer('admin.layout', function ($view) {
            $user = auth()->user();
            if (!$user || !$user->isAdmin()) {
                $view->with('vehicleAlertCount', 0);
                $view->with('vehicleAlertItems', collect());
                return;
            }

            $summary = app(VehicleAlertService::class)->summary(CompanyContext::getActiveCompanyId());
            $view->with('vehicleAlertCount', $summary['count']);
            $view->with('vehicleAlertItems', $summary['items']->take(8));
        });
    }
}
