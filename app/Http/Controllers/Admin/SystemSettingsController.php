<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\CompanyPaymentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SystemSettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'system_name' => AppSetting::getValue('system_name', config('app.name')),
            'system_logo' => AppSetting::getValue('system_logo'),
            'contact_phone' => AppSetting::getValue('contact_phone'),
            'contact_email' => AppSetting::getValue('contact_email'),
            'contact_address' => AppSetting::getValue('contact_address'),
            'trial_days' => AppSetting::trialDays(),
            'payment_instructions' => AppSetting::getValue('payment_instructions'),
            'payment_yearly_rate' => AppSetting::paymentYearlyRate(),
            'payment_discounts' => AppSetting::paymentDiscounts(),
            'payment_year_options' => AppSetting::paymentYearOptions(),
            'payment_methods' => AppSetting::paymentMethods(),
        ];

        $paymentRequests = CompanyPaymentRequest::with(['company', 'submitter'])
            ->latest()
            ->paginate(20);

        return view('admin.settings.index', compact('settings', 'paymentRequests'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'system_name' => 'required|string|max:255',
            'contact_phone' => 'nullable|string|max:100',
            'contact_email' => 'nullable|email|max:255',
            'contact_address' => 'nullable|string|max:1000',
            'trial_days' => 'required|integer|min:1|max:365',
            'payment_yearly_rate' => 'required|numeric|min:0|max:999999999',
            'payment_discounts' => 'nullable|array',
            'payment_discounts.*' => 'nullable|numeric|min:0|max:100',
            'payment_methods' => 'nullable|array',
            'payment_methods.*.enabled' => 'nullable|boolean',
            'payment_methods.*.label' => 'nullable|string|max:100',
            'payment_methods.*.details' => 'nullable|string|max:5000',
            'payment_method_qr' => 'nullable|array',
            'payment_method_qr.*' => 'nullable|image|max:2048',
            'payment_instructions' => 'nullable|string|max:5000',
            'system_logo' => 'nullable|image|max:2048',
        ]);

        AppSetting::setValue('system_name', $validated['system_name']);
        AppSetting::setValue('contact_phone', $validated['contact_phone'] ?? '');
        AppSetting::setValue('contact_email', $validated['contact_email'] ?? '');
        AppSetting::setValue('contact_address', $validated['contact_address'] ?? '');
        AppSetting::setTrialDays((int) $validated['trial_days']);
        AppSetting::setPaymentPricing((float) $validated['payment_yearly_rate'], $validated['payment_discounts'] ?? []);
        AppSetting::setValue('payment_instructions', $validated['payment_instructions'] ?? '');

        $paymentMethods = AppSetting::paymentMethods();
        foreach (AppSetting::paymentMethodDefaults() as $key => $default) {
            $submitted = $validated['payment_methods'][$key] ?? [];
            $paymentMethods[$key]['enabled'] = (bool) ($submitted['enabled'] ?? false);
            $paymentMethods[$key]['label'] = trim((string) ($submitted['label'] ?? $default['label'])) ?: $default['label'];
            $paymentMethods[$key]['details'] = (string) ($submitted['details'] ?? '');

            if ($request->hasFile("payment_method_qr.{$key}")) {
                $oldPath = $paymentMethods[$key]['qr_path'] ?? null;
                if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
                $paymentMethods[$key]['qr_path'] = $request->file("payment_method_qr.{$key}")->store('system/payment-methods', 'public');
            }
        }

        AppSetting::setPaymentMethods($paymentMethods);

        if ($request->hasFile('system_logo')) {
            $this->replaceStoredSettingFile('system_logo', $request->file('system_logo')->store('system', 'public'));
        }

        return redirect()->route('admin.settings.index')->with('success', 'Settings updated successfully.');
    }

    public function verifyPayment(Request $request, CompanyPaymentRequest $paymentRequest)
    {
        $validated = $request->validate([
            'paid_for_days' => 'required|integer|min:1|max:3650',
            'admin_note' => 'nullable|string|max:2000',
        ]);

        $company = $paymentRequest->company;
        $start = $company->paid_until && $company->paid_until->isFuture()
            ? $company->paid_until->copy()
            : now();
        $expiresAt = $start->copy()->addDays((int) $validated['paid_for_days']);
        $paymentRequest->forceFill([
            'status' => 'verified',
            'verified_by' => auth()->id(),
            'verified_at' => now(),
            'paid_for_days' => (int) $validated['paid_for_days'],
            'starts_at' => $start,
            'expires_at' => $expiresAt,
            'admin_note' => $validated['admin_note'] ?? null,
        ])->save();

        $company->activateUntil($expiresAt);

        return redirect()->route('admin.settings.index')->with('success', 'Payment verified and company activated.');
    }

    public function rejectPayment(Request $request, CompanyPaymentRequest $paymentRequest)
    {
        $validated = $request->validate([
            'admin_note' => 'nullable|string|max:2000',
        ]);

        $paymentRequest->forceFill([
            'status' => 'rejected',
            'verified_by' => auth()->id(),
            'verified_at' => now(),
            'admin_note' => $validated['admin_note'] ?? null,
        ])->save();

        return redirect()->route('admin.settings.index')->with('success', 'Payment request rejected.');
    }

    private function replaceStoredSettingFile(string $key, string $path): void
    {
        $oldPath = AppSetting::getValue($key);
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        AppSetting::setValue($key, $path);
    }
}
