<?php

namespace App\Support;

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
            return $active ?: $user->company_id;
        }

        return $user->company_id;
    }
}


