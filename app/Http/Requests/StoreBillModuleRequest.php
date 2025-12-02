<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Support\CompanyContext;

class StoreBillModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'version' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'mb_number' => 'nullable|string|max:100',
            'mb_date' => 'nullable|date',
            'items' => 'required|array|min:1',
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
