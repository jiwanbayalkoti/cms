<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Support\CompanyContext;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index()
    {
        $projects = Project::with('company')
            ->latest('updated_at')
            ->paginate(12);

        return view('admin.projects.index', [
            'projects' => $projects,
        ]);
    }

    public function create()
    {
        return view('admin.projects.create', [
            'statuses' => Project::statusOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateProject($request);
        $validated['company_id'] = CompanyContext::getActiveCompanyId() ?? auth()->user()->company_id;
        $validated['created_by'] = auth()->id();

        Project::create($validated);

        return redirect()->route('admin.projects.index')
            ->with('success', 'Project created successfully.');
    }

    public function show(Project $project)
    {
        $this->authorizeCompanyAccess($project);

        return view('admin.projects.show', [
            'project' => $project->load(['company', 'creator', 'updater']),
        ]);
    }

    public function edit(Project $project)
    {
        $this->authorizeCompanyAccess($project);

        return view('admin.projects.edit', [
            'project' => $project->load('company'),
            'statuses' => Project::statusOptions(),
        ]);
    }

    public function update(Request $request, Project $project)
    {
        $this->authorizeCompanyAccess($project);
        $validated = $this->validateProject($request, $project);
        $validated['updated_by'] = auth()->id();

        $project->update($validated);

        return redirect()->route('admin.projects.index')
            ->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project)
    {
        $this->authorizeCompanyAccess($project);
        $project->delete();

        return redirect()->route('admin.projects.index')
            ->with('success', 'Project deleted successfully.');
    }

    protected function validateProject(Request $request, ?Project $project = null): array
    {
        $statusRule = implode(',', Project::statusOptions());

        return $request->validate([
            'name' => 'required|string|max:255',
            'client_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:' . $statusRule,
            'budget' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);
    }

    protected function authorizeCompanyAccess(Project $project): void
    {
        $activeCompanyId = CompanyContext::getActiveCompanyId();
        if ($activeCompanyId && (int) $activeCompanyId !== 1 && $project->company_id !== $activeCompanyId) {
            abort(403);
        }
    }
}

