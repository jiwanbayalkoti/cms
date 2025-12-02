<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApproveBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        $bill = $this->route('bill') ?? $this->route('bill_module');
        if (is_null($bill) || !is_object($bill)) {
            return false;
        }
        return auth()->check()
            && (
                auth()->user()->is_admin ||
                in_array(auth()->user()->role, ['admin', 'super_admin', 'approver'])
            )
            && $bill->canApprove();
    }

    public function rules(): array
    {
        return [
            'action' => 'required|in:approve,reject',
            'comment' => 'nullable|string|max:1000',
        ];
    }
}
