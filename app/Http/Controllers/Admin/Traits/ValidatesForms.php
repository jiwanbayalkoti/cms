<?php

namespace App\Http\Controllers\Admin\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

trait ValidatesForms
{
    /**
     * Validate form data and return JSON response
     * 
     * @param Request $request
     * @param array $rules
     * @param array $messages
     * @return \Illuminate\Http\JsonResponse
     */
    protected function validateForm(Request $request, array $rules = null, array $messages = [])
    {
        // Get validation rules (use method if exists, otherwise use provided rules)
        if ($rules === null) {
            $rules = method_exists($this, 'getValidationRules') 
                ? $this->getValidationRules($request)
                : [];
        }
        
        // If no rules provided, try to get from validation method
        if (empty($rules) && method_exists($this, 'rules')) {
            $rules = $this->rules($request);
        }
        
        $validator = Validator::make($request->all(), $rules, $messages);
        
        if ($validator->fails()) {
            return response()->json([
                'valid' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        return response()->json([
            'valid' => true,
            'message' => 'Validation passed'
        ]);
    }
    
    /**
     * Get validation rules for the current request
     * Override this method in your controller to provide custom rules
     * 
     * @param Request $request
     * @return array
     */
    protected function getValidationRules(Request $request)
    {
        // Default: try to get rules from validation method
        if (method_exists($this, 'rules')) {
            return $this->rules($request);
        }
        
        return [];
    }
}

