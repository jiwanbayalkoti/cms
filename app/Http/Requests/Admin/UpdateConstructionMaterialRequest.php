<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateConstructionMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'material_name' => 'required|string|max:255',
            'material_category' => 'nullable|string|max:255',
            'unit' => 'required|string|max:50',
            'quantity_received' => 'required|numeric|min:0',
            'rate_per_unit' => 'required|numeric|min:0',
            'quantity_used' => 'nullable|numeric|min:0',
            'wastage_quantity' => 'nullable|numeric|min:0',
            'supplier_name' => 'nullable|string|max:255',
            'supplier_contact' => 'nullable|string|max:255',
            'bill_number' => 'nullable|string|max:255',
            'bill_date' => 'nullable|date',
            'payment_status' => 'required|in:Paid,Unpaid,Partial',
            'payment_mode' => 'nullable|string|max:255',
            'purchased_by_id' => 'nullable|exists:purchased_bies,id',
            'delivery_date' => 'nullable|date',
            'delivery_site' => 'nullable|string|max:255',
            'delivered_by' => 'nullable|string|max:255',
            'received_by' => 'nullable|string|max:255',
            'project_id' => 'required|exists:projects,id',
            'work_type' => 'nullable|string|max:255',
            'usage_purpose' => 'nullable|string',
            'status' => 'required|in:Received,Pending,Returned,Damaged',
            'approved_by' => 'nullable|string|max:255',
            'approval_date' => 'nullable|date',
            'bill_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'delivery_photo' => 'nullable|file|mimes:jpg,jpeg,png|max:4096',
        ];
    }
}


