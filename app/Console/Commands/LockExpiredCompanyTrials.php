<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\CompanyPaymentLockService;
use Illuminate\Console\Command;

class LockExpiredCompanyTrials extends Command
{
    protected $signature = 'companies:lock-expired-trials';

    protected $description = 'Lock companies whose trial or paid subscription has expired and send payment required emails.';

    public function handle(CompanyPaymentLockService $paymentLockService): int
    {
        $lockedCount = 0;

        Company::where('subscription_status', 'trialing')
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<=', now())
            ->orderBy('id')
            ->chunkById(100, function ($companies) use ($paymentLockService, &$lockedCount) {
                foreach ($companies as $company) {
                    if ($paymentLockService->lockExpiredTrial($company)) {
                        $lockedCount++;
                    }
                }
            });

        Company::where('subscription_status', 'active')
            ->whereNotNull('paid_until')
            ->where('paid_until', '<=', now())
            ->orderBy('id')
            ->chunkById(100, function ($companies) use (&$lockedCount) {
                foreach ($companies as $company) {
                    if (!$company->isLockedForPayment()) {
                        $company->lockForPayment('payment_expired');
                        $lockedCount++;
                    }
                }
            });

        $this->info("Locked {$lockedCount} expired trial compan" . ($lockedCount === 1 ? 'y.' : 'ies.'));

        return self::SUCCESS;
    }
}
