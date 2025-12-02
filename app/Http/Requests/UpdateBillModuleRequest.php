<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBillModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $bill = $this->route('bill') ?? $this->route('bill_module');
        if (is_null($bill) || !is_object($bill)) {
            return false;
        }
        return auth()->check() && $bill->canEdit();
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'version' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'mb_number' => 'nullable|string|max:100',
            'mb_date' => 'nullable|date',
            'items' => 'sometimes|required|array|min:1',
            'items.*.id' => 'nullable|exists:bill_items,id',
            'items.*.bill_category_id' => 'required|exists:bill_categories,id',
            'items.*.bill_subcategory_id' => 'nullable|exists:bill_subcategories,id',
            'items.*.description' => 'required|string',
            'items.*.uom' => 'required|string|max:50',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit_rate' => 'required|numeric|min:0',
            'items.*.wastage_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.tax_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.remarks' => 'nullable|string',
            'items.*.rate_breakdown' => 'nullable|array',
            'overhead_percent' => 'nullable|numeric|min:0|max:100',
            'contingency_percent' => 'nullable|numeric|min:0|max:100',
        ];
    }
}
