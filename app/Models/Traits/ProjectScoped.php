<?php

namespace App\Models\Traits;

use App\Support\ProjectContext;
use Illuminate\Database\Eloquent\Builder;

trait ProjectScoped
{
    protected static function bootProjectScoped(): void
    {
        static::addGlobalScope('project', function (Builder $builder) {
            $projectId = ProjectContext::getActiveProjectId();
            // Only filter if project is selected (not null)
            if ($projectId) {
                $builder->where($builder->getModel()->getTable() . '.project_id', $projectId);
            }
        });

        static::creating(function ($model) {
            $projectId = ProjectContext::getActiveProjectId();
            // Only set project_id if not already set and project is selected
            if ($projectId && empty($model->project_id)) {
                $model->project_id = $projectId;
            }
        });
    }
}

