<?php

namespace App\Models\Traits;

use App\Support\CompanyContext;
use Illuminate\Database\Eloquent\Builder;

trait CompanyScoped
{
    protected static function bootCompanyScoped(): void
    {
        static::addGlobalScope('company', function (Builder $builder) {
            $companyId = CompanyContext::getActiveCompanyId();
            // If active ID is null, show nothing; if 1, show all; else filter.
            if ($companyId && (int)$companyId !== 1) {
                $builder->where($builder->getModel()->getTable() . '.company_id', $companyId);
            }
        });

        static::creating(function ($model) {
            $companyId = CompanyContext::getActiveCompanyId();
            if ($companyId && empty($model->company_id)) {
                $model->company_id = $companyId;
            }
        });
    }
}


