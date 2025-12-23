<?php

namespace App\Http\Controllers\Admin\Traits;

use App\Models\Project;
use App\Support\CompanyContext;

trait HasProjectAccess
{
    /**
     * Get accessible projects for the current user
     */
    protected function getAccessibleProjects()
    {
        $user = auth()->user();
        $companyId = CompanyContext::getActiveCompanyId();
        
        $query = Project::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->orderBy('name');
        
        // Apply project restrictions
        $accessibleProjectIds = $user->getAccessibleProjectIds();
        
        // If null, user has access to all projects (super admin)
        if ($accessibleProjectIds === null) {
            return $query->get();
        }
        
        // If empty array, user has no project assignments - return empty collection
        if (empty($accessibleProjectIds)) {
            return collect([]);
        }
        
        // User has specific project assignments - filter by them
        $query->whereIn('id', $accessibleProjectIds);
        
        return $query->get();
    }

    /**
     * Filter query by accessible projects
     */
    protected function filterByAccessibleProjects($query, string $projectIdColumn = 'project_id')
    {
        $user = auth()->user();
        $accessibleProjectIds = $user->getAccessibleProjectIds();
        
        // If null, user has access to all projects (super admin)
        if ($accessibleProjectIds === null) {
            return $query;
        }
        
        // If empty array, user has no project assignments - return no results
        if (empty($accessibleProjectIds)) {
            $query->whereRaw('1 = 0'); // Force no results
            return $query;
        }
        
        // User has specific project assignments - filter by them
        $query->whereIn($projectIdColumn, $accessibleProjectIds);
        
        return $query;
    }

    /**
     * Check if user can access a specific project
     */
    protected function canAccessProject(int $projectId): bool
    {
        return auth()->user()->hasProjectAccess($projectId);
    }

    /**
     * Authorize project access or abort
     */
    protected function authorizeProjectAccess(int $projectId): void
    {
        if (!$this->canAccessProject($projectId)) {
            abort(403, 'You do not have access to this project.');
        }
    }
}

