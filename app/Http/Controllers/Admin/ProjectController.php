<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Project;
use App\Support\CompanyContext;
use App\Support\ProjectContext;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index()
    {
        $companyId = CompanyContext::getActiveCompanyId();
        
        $query = Project::with('company');
        
        // Filter by company if not super admin
        if ($companyId && (int) $companyId !== 1) {
            $query->where('company_id', $companyId);
        }
        
        $projects = $query->latest('updated_at')->paginate(12);

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
        
        // Get company_id and validate it exists
        $companyId = CompanyContext::getActiveCompanyId() ?? auth()->user()->company_id;
        
        // Validate company exists
        if ($companyId && !Company::find($companyId)) {
            return back()
                ->withInput()
                ->with('error', 'Invalid company selected. Please select a valid company.');
        }
        
        $validated['company_id'] = $companyId;
        $validated['created_by'] = auth()->id();

        try {
            Project::create($validated);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                // Foreign key constraint violation
                return back()
                    ->withInput()
                    ->with('error', 'Cannot create project: Invalid company selected. Please ensure you have selected a valid company.');
            }
            throw $e;
        }

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

        try {
            $project->update($validated);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                // Foreign key constraint violation
                return back()
                    ->withInput()
                    ->with('error', 'Cannot update project: Invalid company or related data. Please check your selections.');
            }
            throw $e;
        }

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

    public function switch(Request $request)
    {
        $request->validate([
            'project_id' => 'nullable|exists:projects,id',
        ]);

        $projectId = $request->input('project_id');
        
        // Verify project belongs to active company
        if ($projectId) {
            $activeCompanyId = CompanyContext::getActiveCompanyId();
            $project = Project::find($projectId);
            
            if ($project && $activeCompanyId && (int) $activeCompanyId !== 1 && $project->company_id !== $activeCompanyId) {
                return back()->with('error', 'Project does not belong to active company.');
            }
        }

        ProjectContext::setActiveProjectId($projectId ? (int) $projectId : null);
        
        return back()->with('success', $projectId ? 'Active project switched.' : 'Project filter cleared.');
    }

    protected function authorizeCompanyAccess(Project $project): void
    {
        $activeCompanyId = CompanyContext::getActiveCompanyId();
        if ($activeCompanyId && (int) $activeCompanyId !== 1 && $project->company_id !== $activeCompanyId) {
            abort(403);
        }
    }
}

