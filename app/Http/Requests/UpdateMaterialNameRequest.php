<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMaterialNameRequest extends FormRequest
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
        $materialNameId = $this->route('material_name');
        if ($materialNameId instanceof \App\Models\MaterialName) {
            $materialNameId = $materialNameId->id;
        }

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('material_names', 'name')->ignore($materialNameId),
            ],
        ];
    }
}
