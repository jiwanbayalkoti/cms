@php
    $compact = $compact ?? false;
    $paidUntil = $company->paid_until;
    $daysLeft = $company->paymentDaysRemaining();
    $isExpired = $paidUntil && $paidUntil->isPast();
    $isLocked = $company->isLockedForPayment();
    $tone = $isExpired || ($isLocked && $company->lock_reason === 'payment_expired') ? 'red' : 'green';
    $statusLabel = $isExpired || ($isLocked && $company->lock_reason === 'payment_expired')
        ? 'Payment Expired'
        : 'Paid Subscription';

    $yearsLeft = intdiv($daysLeft, 365);
    $remainingDaysAfterYears = $daysLeft % 365;
    $durationParts = [];
    if ($yearsLeft > 0) {
        $durationParts[] = $yearsLeft . ' yr';
    }
    if ($remainingDaysAfterYears > 0 || $yearsLeft === 0) {
        $durationParts[] = $remainingDaysAfterYears . ' day' . ($remainingDaysAfterYears === 1 ? '' : 's');
    }

    $remainingLabel = $isExpired || ($isLocked && $company->lock_reason === 'payment_expired')
        ? 'Payment period ended'
        : implode(' ', $durationParts) . ' remaining';
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
</div>
