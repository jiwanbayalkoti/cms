<?php

namespace App\Support;

use App\Models\Company;

class CompanyContext
{
    public static function getActiveCompanyId(): ?int
    {
        $user = auth()->user();
        if (!$user) {
            return null;
        }

        if ($user->role === 'super_admin') {
            $active = session('active_company_id');
            $companyId = $active ?: $user->company_id;
            
            // Validate company exists, if not fallback to user's company_id
            if ($companyId && !Company::find($companyId)) {
                // Clear invalid session value
                session()->forget('active_company_id');
                $companyId = $user->company_id;
            }
            
            return $companyId;
        }

        // Validate user's company exists
        if ($user->company_id && !Company::find($user->company_id)) {
            return null;
        }

        return $user->company_id;
    }
}


