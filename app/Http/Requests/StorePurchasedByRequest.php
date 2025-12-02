<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchasedByRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->is_admin || in_array(auth()->user()->role, ['admin', 'super_admin']));
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'contact' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'is_active' => 'sometimes|boolean',
        ];
    }
}
