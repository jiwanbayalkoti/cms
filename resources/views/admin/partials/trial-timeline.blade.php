@php
    $compact = $compact ?? false;
    $daysLeft = $company->trialDaysRemaining();
    $totalDays = $company->trialTotalDays();
    $trialEndsAt = $company->effectiveTrialEndsAt();
    $progress = $company->trialProgressPercent();
    $isLocked = $company->isLockedForPayment();
    $isExpired = $company->isTrialExpired() || ($isLocked && $company->lock_reason === 'trial_expired');
    $tone = $isLocked || $isExpired ? 'red' : 'amber';
    $statusLabel = $isLocked
        ? 'Locked'
        : ($isExpired ? 'Trial Expired' : 'Trial Period');
    $remainingLabel = $isLocked || $isExpired
        ? 'Trial ended'
        : $daysLeft . ' day' . ($daysLeft === 1 ? '' : 's') . ' left of ' . $totalDays . ' days';
@endphp

<div class="{{ $compact ? 'mt-3' : 'mb-4 rounded-lg border border-' . $tone . '-200 bg-' . $tone . '-50 p-4' }}">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 mb-2">
        <div class="font-semibold {{ $compact ? 'text-sm text-' . $tone . '-900' : 'text-' . $tone . '-900' }}">
            {{ $statusLabel }}
        </div>
        <div class="{{ $compact ? 'text-xs' : 'text-sm' }} text-{{ $tone }}-800">
            {{ $remainingLabel }}
        </div>
    </div>

    <div class="h-2 w-full overflow-hidden rounded-full bg-{{ $tone }}-100">
        <div class="h-full rounded-full bg-{{ $tone }}-500 transition-all duration-300" style="width: {{ $isLocked || $isExpired ? 100 : $progress }}%"></div>
    </div>

    <div class="mt-2 flex items-center justify-between {{ $compact ? 'text-[11px]' : 'text-xs' }} text-{{ $tone }}-700">
        <span>Start: {{ optional($company->trial_started_at)->format('Y-m-d') }}</span>
        <span>{{ $isLocked || $isExpired ? 100 : $progress }}% used</span>
        <span>End: {{ optional($trialEndsAt)->format('Y-m-d') }}</span>
    </div>
    @if($isLocked && $company->locked_at)
        <div class="mt-2 {{ $compact ? 'text-[11px]' : 'text-xs' }} text-red-700">
            Locked at: {{ $company->locked_at->format('Y-m-d H:i') }}
        </div>
    @endif
</div>
