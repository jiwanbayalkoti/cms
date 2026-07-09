<?php

namespace App\Services;

use App\Mail\CompanyLockStatusChanged;
use App\Mail\CompanyPaymentRequired;
use App\Models\Company;
use Illuminate\Support\Facades\Mail;

class CompanyPaymentLockService
{
    public function lockExpiredTrial(Company $company): bool
    {
        if (!$company->isTrialExpired()) {
            return false;
        }

        $company->lockForPayment('trial_expired');
        $this->sendPaymentRequiredEmailOnce($company->fresh() ?: $company);

        return true;
    }

    public function sendPaymentRequiredEmailOnce(Company $company): void
    {
        if ($company->payment_required_email_sent_at) {
            return;
        }

        $recipients = collect([$company->email])
            ->merge($company->users()->where('role', 'admin')->pluck('email'))
            ->filter()
            ->unique()
            ->values();

        if ($recipients->isEmpty()) {
            $company->forceFill(['payment_required_email_sent_at' => now()])->save();
            return;
        }

        try {
            Mail::to($recipients->all())->send(new CompanyPaymentRequired($company));
        } catch (\Throwable $e) {
            report($e);
            return;
        }

        $company->forceFill(['payment_required_email_sent_at' => now()])->save();
    }

    public function sendManualStatusChangedEmail(Company $company, string $status): void
    {
        if (!$company->email) {
            return;
        }

        try {
            Mail::to($company->email)->send(new CompanyLockStatusChanged($company, $status));
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
