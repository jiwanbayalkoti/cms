<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    public const TRIAL_DAYS_KEY = 'trial_days';
    public const DEFAULT_TRIAL_DAYS = 7;
    public const PAYMENT_YEARLY_RATE_KEY = 'payment_yearly_rate';
    public const PAYMENT_DISCOUNTS_KEY = 'payment_discounts';
    public const PAYMENT_METHODS_KEY = 'payment_methods';

    protected $fillable = [
        'key',
        'value',
    ];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        return static::query()->where('key', $key)->value('value') ?? $default;
    }

    public static function setValue(string $key, mixed $value): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value]
        );
    }

    public static function trialDays(): int
    {
        $days = (int) static::getValue(static::TRIAL_DAYS_KEY, static::DEFAULT_TRIAL_DAYS);

        return max(1, min(365, $days));
    }

    public static function setTrialDays(int $days): void
    {
        static::setValue(static::TRIAL_DAYS_KEY, max(1, min(365, $days)));
    }

    public static function paymentYearlyRate(): float
    {
        return max(0, (float) static::getValue(static::PAYMENT_YEARLY_RATE_KEY, 0));
    }

    public static function paymentDiscounts(): array
    {
        $discounts = json_decode((string) static::getValue(static::PAYMENT_DISCOUNTS_KEY, '{}'), true);
        $discounts = is_array($discounts) ? $discounts : [];
        $normalized = [];

        for ($year = 1; $year <= 5; $year++) {
            $normalized[$year] = max(0, min(100, (float) ($discounts[$year] ?? 0)));
        }

        return $normalized;
    }

    public static function setPaymentPricing(float $yearlyRate, array $discounts): void
    {
        $normalizedDiscounts = [];

        for ($year = 1; $year <= 5; $year++) {
            $normalizedDiscounts[$year] = max(0, min(100, (float) ($discounts[$year] ?? 0)));
        }

        static::setValue(static::PAYMENT_YEARLY_RATE_KEY, max(0, $yearlyRate));
        static::setValue(static::PAYMENT_DISCOUNTS_KEY, json_encode($normalizedDiscounts));
    }

    public static function paymentYearOptions(): array
    {
        $yearlyRate = static::paymentYearlyRate();
        $discounts = static::paymentDiscounts();
        $options = [];

        for ($year = 1; $year <= 5; $year++) {
            $subtotal = $yearlyRate * $year;
            $discountPercent = $discounts[$year] ?? 0;
            $discountAmount = $subtotal * $discountPercent / 100;
            $amount = max(0, $subtotal - $discountAmount);

            $options[] = [
                'years' => $year,
                'days' => $year * 365,
                'yearly_rate' => $yearlyRate,
                'subtotal' => round($subtotal, 2),
                'discount_percent' => $discountPercent,
                'discount_amount' => round($discountAmount, 2),
                'amount' => round($amount, 2),
            ];
        }

        return $options;
    }

    public static function amountForPaymentDays(int $days): ?float
    {
        foreach (static::paymentYearOptions() as $option) {
            if ((int) $option['days'] === $days) {
                return (float) $option['amount'];
            }
        }

        return null;
    }

    public static function paymentMethodDefaults(): array
    {
        return [
            'bank' => [
                'label' => 'Bank Transfer',
                'enabled' => true,
                'details' => (string) static::getValue('payment_bank_details', ''),
                'qr_path' => (string) static::getValue('payment_qr_path', ''),
            ],
            'esewa' => [
                'label' => 'eSewa',
                'enabled' => true,
                'details' => '',
                'qr_path' => '',
            ],
            'khalti' => [
                'label' => 'Khalti',
                'enabled' => true,
                'details' => '',
                'qr_path' => '',
            ],
            'ime_pay' => [
                'label' => 'IME Pay',
                'enabled' => false,
                'details' => '',
                'qr_path' => '',
            ],
            'other' => [
                'label' => 'Other',
                'enabled' => false,
                'details' => (string) static::getValue('payment_wallet_details', ''),
                'qr_path' => '',
            ],
        ];
    }

    public static function paymentMethods(): array
    {
        $saved = json_decode((string) static::getValue(static::PAYMENT_METHODS_KEY, '{}'), true);
        $saved = is_array($saved) ? $saved : [];
        $methods = static::paymentMethodDefaults();

        foreach ($methods as $key => $default) {
            $savedMethod = is_array($saved[$key] ?? null) ? $saved[$key] : [];
            $methods[$key] = [
                'label' => trim((string) ($savedMethod['label'] ?? $default['label'])) ?: $default['label'],
                'enabled' => (bool) ($savedMethod['enabled'] ?? $default['enabled']),
                'details' => (string) ($savedMethod['details'] ?? $default['details']),
                'qr_path' => (string) ($savedMethod['qr_path'] ?? $default['qr_path']),
            ];
        }

        return $methods;
    }

    public static function enabledPaymentMethods(): array
    {
        return array_filter(static::paymentMethods(), fn ($method) => (bool) ($method['enabled'] ?? false));
    }

    public static function setPaymentMethods(array $methods): void
    {
        $normalized = [];

        foreach (static::paymentMethodDefaults() as $key => $default) {
            $method = is_array($methods[$key] ?? null) ? $methods[$key] : [];
            $normalized[$key] = [
                'label' => trim((string) ($method['label'] ?? $default['label'])) ?: $default['label'],
                'enabled' => (bool) ($method['enabled'] ?? false),
                'details' => (string) ($method['details'] ?? ''),
                'qr_path' => (string) ($method['qr_path'] ?? ''),
            ];
        }

        static::setValue(static::PAYMENT_METHODS_KEY, json_encode($normalized));
    }
}
