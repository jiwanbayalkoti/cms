<?php

namespace App\Models;

use App\Models\AppSetting;
use App\Models\CompanyDocument;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $latestVerifiedPaymentRequestCache = false;

    protected $casts = [
        'bill_date' => 'date',
        'letterhead_show_watermark' => 'boolean',
        'letterhead_show_border' => 'boolean',
        'letterhead_watermark_opacity' => 'integer',
        'letterhead_chs_last_no' => 'integer',
        'letterhead_ps_last_no' => 'integer',
        'letterhead_name_en_size' => 'float',
        'letterhead_name_np_size' => 'float',
        'letterhead_address_size' => 'float',
        'letterhead_name_letter_spacing' => 'float',
        'letterhead_name_line_height' => 'float',
        'default_vat_percent' => 'decimal:2',
        'trial_started_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'trial_days_override' => 'integer',
        'locked_at' => 'datetime',
        'payment_required_email_sent_at' => 'datetime',
        'paid_until' => 'datetime',
    ];

    protected $fillable = [
        'name',
        'client',
        'project',
        'contract_no',
        'bill_date',
        'address',
        'email',
        'phone',
        'website',
        'tax_number',
        'vat_bill_template',
        'vat_bill_accent_color',
        'default_vat_percent',
        'vat_bill_footer_text',
        'city',
        'state',
        'country',
        'zip',
        'logo',
        'favicon',
        'letterhead_template',
        'letterhead_primary_color',
        'letterhead_font_family',
        'letterhead_header_alignment',
        'letterhead_tagline',
        'letterhead_name_en_size',
        'letterhead_name_np_size',
        'letterhead_address_size',
        'letterhead_name_letter_spacing',
        'letterhead_name_line_height',
        'letterhead_name_en_color',
        'letterhead_name_np_color',
        'letterhead_address_color',
        'letterhead_name_font_style',
        'letterhead_name_en_align',
        'letterhead_name_np_align',
        'letterhead_address_align',
        'letterhead_meta_chs_align',
        'letterhead_meta_ps_align',
        'letterhead_meta_date_align',
        'letterhead_meta_chs_value',
        'letterhead_meta_ps_value',
        'letterhead_meta_date_value',
        'letterhead_layout_json',
        'letterhead_footer_text',
        'letterhead_watermark_text',
        'letterhead_watermark_mode',
        'letterhead_show_watermark',
        'letterhead_watermark_opacity',
        'letterhead_show_border',
        'trial_started_at',
        'trial_ends_at',
        'trial_days_override',
        'subscription_status',
        'locked_at',
        'lock_reason',
        'payment_required_email_sent_at',
        'paid_until',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function adminPermissionRole()
    {
        return $this->hasOne(PermissionRole::class)
            ->withoutGlobalScopes()
            ->where('type', PermissionRole::TYPE_ADMIN_PACK);
    }

    public function incomes()
    {
        return $this->hasMany(Income::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function subcontractors()
    {
        return $this->hasMany(Subcontractor::class);
    }

    public function letterheadAssets()
    {
        return $this->hasMany(CompanyLetterheadAsset::class)->orderBy('sort_order')->orderBy('id');
    }

    public function letterheadExports()
    {
        return $this->hasMany(CompanyLetterheadExport::class)->latest();
    }

    public function paymentRequests()
    {
        return $this->hasMany(CompanyPaymentRequest::class)->latest();
    }

    public function documents()
    {
        return $this->hasMany(CompanyDocument::class)->latest();
    }

    public function startTrial(?int $days = null): void
    {
        $startedAt = now();
        $days = $days ?? $this->trial_days_override ?? AppSetting::trialDays();

        $this->forceFill([
            'trial_started_at' => $startedAt,
            'trial_ends_at' => $startedAt->copy()->addDays($days),
            'subscription_status' => 'trialing',
            'locked_at' => null,
            'lock_reason' => null,
            'payment_required_email_sent_at' => null,
        ])->save();
    }

    public function updateTrialPeriod(int $days, bool $manualOverride = true): void
    {
        $startedAt = $this->trial_started_at ?: now();
        $endsAt = $startedAt->copy()->addDays($days);
        $shouldUnlockTrial = $this->isLockedForPayment()
            && $this->lock_reason === 'trial_expired'
            && $endsAt->isFuture();

        $this->forceFill([
            'trial_started_at' => $startedAt,
            'trial_ends_at' => $endsAt,
            'trial_days_override' => $manualOverride ? $days : $this->trial_days_override,
            'subscription_status' => $shouldUnlockTrial ? 'trialing' : ($this->subscription_status ?: 'trialing'),
            'locked_at' => $shouldUnlockTrial ? null : $this->locked_at,
            'lock_reason' => $shouldUnlockTrial ? null : $this->lock_reason,
            'payment_required_email_sent_at' => $shouldUnlockTrial ? null : $this->payment_required_email_sent_at,
        ])->save();
    }

    public function isTrialExpired(): bool
    {
        return $this->subscription_status === 'trialing'
            && $this->effectiveTrialEndsAt() !== null
            && $this->effectiveTrialEndsAt()->isPast();
    }

    public function isOnTrial(): bool
    {
        return $this->subscription_status === 'trialing'
            && $this->trial_started_at !== null
            && $this->trial_ends_at !== null
            && !$this->isTrialExpired();
    }

    public function trialDaysRemaining(): int
    {
        $endsAt = $this->effectiveTrialEndsAt();

        if (!$endsAt) {
            return 0;
        }

        $secondsRemaining = now()->diffInSeconds($endsAt, false);

        return max(0, (int) ceil($secondsRemaining / 86400));
    }

    public function effectiveTrialDays(): int
    {
        return $this->trial_days_override ?: AppSetting::trialDays();
    }

    public function effectiveTrialEndsAt()
    {
        if (!$this->trial_started_at) {
            return $this->trial_ends_at;
        }

        return $this->trial_started_at->copy()->addDays($this->effectiveTrialDays());
    }

    public function trialTotalDays(): int
    {
        return $this->effectiveTrialDays();
    }

    public function trialProgressPercent(): int
    {
        $endsAt = $this->effectiveTrialEndsAt();

        if (!$this->trial_started_at || !$endsAt) {
            return 0;
        }

        $totalSeconds = max(1, $this->trial_started_at->diffInSeconds($endsAt));
        $elapsedSeconds = max(0, $this->trial_started_at->diffInSeconds(now(), false));

        return max(0, min(100, (int) round(($elapsedSeconds / $totalSeconds) * 100)));
    }

    public function isLockedForPayment(): bool
    {
        return $this->subscription_status === 'locked' || $this->locked_at !== null;
    }

    public function isPaymentExpired(): bool
    {
        return $this->subscription_status === 'active'
            && $this->paid_until !== null
            && $this->paid_until->isPast();
    }

    public function hasPaymentTimeline(): bool
    {
        return $this->paid_until !== null || $this->latestVerifiedPaymentRequest() !== null;
    }

    public function latestVerifiedPaymentRequest(): ?CompanyPaymentRequest
    {
        if ($this->latestVerifiedPaymentRequestCache !== false) {
            return $this->latestVerifiedPaymentRequestCache;
        }

        if ($this->relationLoaded('paymentRequests')) {
            $this->latestVerifiedPaymentRequestCache = $this->paymentRequests
                ->where('status', 'verified')
                ->sortByDesc(fn ($request) => $request->verified_at ?? $request->created_at)
                ->first();

            return $this->latestVerifiedPaymentRequestCache;
        }

        $this->latestVerifiedPaymentRequestCache = $this->paymentRequests()
            ->where('status', 'verified')
            ->orderByDesc('verified_at')
            ->orderByDesc('id')
            ->first();

        return $this->latestVerifiedPaymentRequestCache;
    }

    public function paymentPeriodStartedAt()
    {
        $paymentRequest = $this->earliestVerifiedPaymentRequest();

        return $paymentRequest?->starts_at
            ?? $paymentRequest?->verified_at
            ?? $paymentRequest?->created_at;
    }

    public function earliestVerifiedPaymentRequest(): ?CompanyPaymentRequest
    {
        if ($this->relationLoaded('paymentRequests')) {
            return $this->paymentRequests
                ->where('status', 'verified')
                ->sortBy(fn ($request) => $request->starts_at ?? $request->verified_at ?? $request->created_at)
                ->first();
        }

        return $this->paymentRequests()
            ->where('status', 'verified')
            ->orderBy('starts_at')
            ->orderBy('verified_at')
            ->orderBy('id')
            ->first();
    }

    public function paymentDaysRemaining(): int
    {
        if (!$this->paid_until) {
            return 0;
        }

        $secondsRemaining = now()->diffInSeconds($this->paid_until, false);

        return max(0, (int) ceil($secondsRemaining / 86400));
    }

    public function paymentTotalDays(): int
    {
        $verifiedDays = $this->totalVerifiedPaymentDays();
        if ($verifiedDays > 0) {
            return $verifiedDays;
        }

        $startedAt = $this->paymentPeriodStartedAt();
        if (!$startedAt || !$this->paid_until) {
            return 0;
        }

        return max(1, (int) ceil($startedAt->diffInSeconds($this->paid_until) / 86400));
    }

    public function totalVerifiedPaymentDays(): int
    {
        if ($this->relationLoaded('paymentRequests')) {
            return (int) $this->paymentRequests
                ->where('status', 'verified')
                ->sum('paid_for_days');
        }

        return (int) $this->paymentRequests()
            ->where('status', 'verified')
            ->sum('paid_for_days');
    }

    public function totalVerifiedPaymentAmount(): float
    {
        if ($this->relationLoaded('paymentRequests')) {
            return (float) $this->paymentRequests
                ->where('status', 'verified')
                ->sum('amount');
        }

        return (float) $this->paymentRequests()
            ->where('status', 'verified')
            ->sum('amount');
    }

    public function paymentProgressPercent(): int
    {
        $startedAt = $this->paymentPeriodStartedAt();
        if (!$startedAt || !$this->paid_until) {
            return 0;
        }

        $totalSeconds = max(1, $startedAt->diffInSeconds($this->paid_until));
        $elapsedSeconds = max(0, $startedAt->diffInSeconds(now(), false));

        return max(0, min(100, (int) round(($elapsedSeconds / $totalSeconds) * 100)));
    }

    public function lockForPayment(string $reason = 'trial_expired'): void
    {
        if ($this->isLockedForPayment() && $this->lock_reason === $reason) {
            return;
        }

        $this->forceFill([
            'subscription_status' => 'locked',
            'locked_at' => $this->locked_at ?: now(),
            'lock_reason' => $reason,
        ])->save();
    }

    public function unlockPaymentAccess(): void
    {
        $this->forceFill([
            'subscription_status' => 'active',
            'locked_at' => null,
            'lock_reason' => null,
            'payment_required_email_sent_at' => null,
        ])->save();
    }

    public function activateUntil(\DateTimeInterface $paidUntil): void
    {
        $this->forceFill([
            'subscription_status' => 'active',
            'paid_until' => $paidUntil,
            'locked_at' => null,
            'lock_reason' => null,
            'payment_required_email_sent_at' => null,
        ])->save();
    }

    /**
     * Get logo URL
     */
    public function getLogoUrl(): ?string
    {
        if (!$this->logo) {
            return null;
        }
        
        try {
        $storage = \Illuminate\Support\Facades\Storage::disk('public');
        
        if ($storage->exists($this->logo)) {
            // Use asset() for better compatibility with different server configurations
            return asset('storage/' . $this->logo);
        }
        
            // File doesn't exist - clear it from database (silently fail if update fails)
            try {
        $this->update(['logo' => null]);
            } catch (\Exception $e) {
                \Log::warning('Failed to clear logo from database: ' . $e->getMessage());
            }
            return null;
        } catch (\Exception $e) {
            \Log::error('Error getting logo URL: ' . $e->getMessage());
        return null;
        }
    }

    /**
     * Get favicon URL, generating default if needed
     */
    public function getFaviconUrl(): string
    {
        try {
        $storage = \Illuminate\Support\Facades\Storage::disk('public');
        
        // Check if favicon exists in database and on disk
        if ($this->favicon) {
            if ($storage->exists($this->favicon)) {
                // Use asset() for better compatibility with different server configurations
                return asset('storage/' . $this->favicon);
            } else {
                    // File path exists in DB but file doesn't exist - clear it (silently fail if update fails)
                    try {
                $this->update(['favicon' => null]);
                    } catch (\Exception $e) {
                        \Log::warning('Failed to clear favicon from database: ' . $e->getMessage());
                    }
            }
        }
        
        // Generate default if not exists
        if ($this->name) {
            try {
                $faviconService = app(\App\Services\FaviconGeneratorService::class);
                $faviconPath = $faviconService->generateDefaultFavicon($this->name);
                
                // Verify file was created
                if ($storage->exists($faviconPath)) {
                        try {
                    $this->update(['favicon' => $faviconPath]);
                        } catch (\Exception $e) {
                            \Log::warning('Failed to save favicon to database: ' . $e->getMessage());
                        }
                    return asset('storage/' . $faviconPath);
                }
            } catch (\Exception $e) {
                // Log error for debugging
                \Log::error('Favicon generation failed: ' . $e->getMessage());
            }
            }
        } catch (\Exception $e) {
            \Log::error('Error getting favicon URL: ' . $e->getMessage());
        }
        
        // Fallback
        return asset('favicon.ico');
    }
}


