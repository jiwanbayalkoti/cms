<?php

namespace App\Support;

class ProjectContext
{
    public static function getActiveProjectId(): ?int
    {
        $user = auth()->user();
        if (!$user) {
            return null;
        }

        // Get project from session if set
        $activeProjectId = session('active_project_id');
        if ($activeProjectId) {
            return (int) $activeProjectId;
        }

        // For now, return null - project selection will be handled in UI
        return null;
    }

    public static function setActiveProjectId(?int $projectId): void
    {
        if ($projectId) {
            session(['active_project_id' => $projectId]);
        } else {
            session()->forget('active_project_id');
        }
    }

    public static function clearActiveProject(): void
    {
        session()->forget('active_project_id');
    }
}

