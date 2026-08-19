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
            $companyId = session('active_company_id');

            if ($companyId && !Company::find($companyId)) {
                session()->forget('active_company_id');

                return null;
            }

            return $companyId ? (int) $companyId : null;
        }

        // Validate user's company exists
        if ($user->company_id && !Company::find($user->company_id)) {
            return null;
        }

        return $user->company_id;
    }
}


