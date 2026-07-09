<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\CompanyPaymentRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Mpdf\Mpdf;

class CompanyPaymentRequestController extends Controller
{
    public function store(Request $request)
    {
        $user = auth()->user();
        $company = $user?->company;

        if (!$company) {
            return redirect()->route('admin.payment-required')->with('error', 'Company not found.');
        }

        if ($company->paymentRequests()->where('status', 'pending')->exists()) {
            return redirect()
                ->route('admin.payment-required')
                ->with('error', 'A payment request is already pending verification.');
        }

        $paymentOptions = AppSetting::paymentYearOptions();
        $allowedDays = array_column($paymentOptions, 'days');
        $enabledMethods = AppSetting::enabledPaymentMethods();

        $validated = $request->validate([
            'paid_for_days' => ['required', 'integer', Rule::in($allowedDays)],
            'payment_method' => ['required', 'string', Rule::in(array_keys($enabledMethods))],
            'transaction_reference' => 'nullable|string|max:255',
            'payment_date' => 'nullable|date',
            'proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf,webp|max:4096',
            'note' => 'nullable|string|max:2000',
        ]);

        $amount = AppSetting::amountForPaymentDays((int) $validated['paid_for_days']);
        $selectedMethod = $enabledMethods[$validated['payment_method']] ?? null;
        $proofPath = $request->hasFile('proof')
            ? $request->file('proof')->store("company-payment-proofs/{$company->id}", 'public')
            : null;

        CompanyPaymentRequest::create([
            'company_id' => $company->id,
            'submitted_by' => $user->id,
            'amount' => $amount,
            'paid_for_days' => (int) $validated['paid_for_days'],
            'payment_method' => $selectedMethod['label'] ?? $validated['payment_method'],
            'transaction_reference' => $validated['transaction_reference'] ?? null,
            'payment_date' => $validated['payment_date'] ?? null,
            'proof_path' => $proofPath,
            'note' => $validated['note'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()
            ->route('admin.payment-required')
            ->with('success', 'Payment details submitted. Super admin will verify and activate your company.');
    }

    public static function paymentSettings(): array
    {
        return [
            'system_name' => AppSetting::getValue('system_name', config('app.name')),
            'contact_phone' => AppSetting::getValue('contact_phone'),
            'contact_email' => AppSetting::getValue('contact_email'),
            'contact_address' => AppSetting::getValue('contact_address'),
            'payment_instructions' => AppSetting::getValue('payment_instructions'),
            'year_options' => AppSetting::paymentYearOptions(),
            'payment_methods' => AppSetting::paymentMethods(),
            'enabled_payment_methods' => AppSetting::enabledPaymentMethods(),
        ];
    }

    public function historyPdf()
    {
        $company = auth()->user()?->company;

        if (!$company) {
            return redirect()->route('admin.payment-required')->with('error', 'Company not found.');
        }

        $paymentRequests = $company->paymentRequests()
            ->with(['submitter', 'verifier'])
            ->oldest()
            ->get();

        $html = view('admin.payment-requests.history-pdf', [
            'company' => $company,
            'paymentRequests' => $paymentRequests,
            'generatedAt' => now(),
        ])->render();

        $mpdf = new Mpdf([
            'format' => 'A4',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 12,
            'margin_bottom' => 12,
        ]);
        $mpdf->WriteHTML($html);

        $fileName = 'payment-history-' . $company->id . '-' . now()->format('Ymd-His') . '.pdf';

        return response($mpdf->Output($fileName, 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
        ]);
    }
}
