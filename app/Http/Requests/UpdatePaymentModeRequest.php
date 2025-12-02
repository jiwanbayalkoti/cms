<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentModeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->is_admin || in_array(auth()->user()->role, ['admin', 'super_admin']));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $paymentModeId = $this->route('payment_mode');
        if ($paymentModeId instanceof \App\Models\PaymentMode) {
            $paymentModeId = $paymentModeId->id;
        }

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('payment_modes', 'name')->ignore($paymentModeId),
            ],
        ];
    }
}
