<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Traits\ValidatesForms;
use App\Models\User;
use App\Models\Company;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Support\CompanyContext;
use App\Rules\StrongPassword;

class UserController extends Controller
{
    use ValidatesForms;
    
    public function __construct()
    {
        $this->middleware(['admin', 'super_admin']);
    }
    
    /**
     * Validate user form data (AJAX endpoint)
     */
    public function validateUser(Request $request, User $user = null)
    {
        $emailRule = $user
            ? 'required|email|max:255|unique:users,email,' . $user->id
            : 'required|email|max:255|unique:users,email';
        
        $passwordRule = $user
            ? ['nullable', 'string', 'min:8', 'confirmed', new StrongPassword()]
            : ['required', 'string', 'min:8', 'confirmed', new StrongPassword()];
        
        $rules = [
            'name' => 'required|string|max:255',
            'email' => $emailRule,
            'password' => $passwordRule,
            'password_confirmation' => $user ? 'nullable|string' : 'required|string',
            'company_id' => 'nullable|exists:companies,id',
            'role' => 'required|in:super_admin,admin,user',
            'is_admin' => 'nullable|boolean',
            'project_ids' => 'nullable|array',
            'project_ids.*' => 'integer|exists:projects,id',
        ];
        
        return $this->validateForm($request, $rules);
    }

    public function index()
    {
        $currentUser = auth()->user();
        $companyId = CompanyContext::getActiveCompanyId();
        $query = User::with('company')->orderBy('name');

        // Super admin can see all users
        // Regular admin can only see users from their company
        if (!$currentUser->isSuperAdmin()) {
            if ((int) $companyId !== 1) {
                $query->where(function($q) use ($companyId) {
                    $q->whereNull('company_id')->orWhere('company_id', $companyId);
                });
            }
        } else {
            // Super admin can filter by company
            if ((int) $companyId !== 1) {
                $query->where(function($q) use ($companyId) {
                    $q->whereNull('company_id')->orWhere('company_id', $companyId);
                });
            }
        }

        $users = $query->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $currentUser = auth()->user();
        $companies = Company::orderBy('name')->get();
        $projectsQuery = Project::with('company')->orderBy('name');
        
        // Regular admin can only create users for their company
        if (!$currentUser->isSuperAdmin()) {
            $companyId = CompanyContext::getActiveCompanyId();
            if ($companyId) {
                $companies = Company::where('id', $companyId)->get();
                $projectsQuery->where('company_id', $companyId);
            }
        }

        $projects = $projectsQuery->get();
        
        return view('admin.users.create', compact('companies', 'projects'));
    }

    public function store(Request $request)
    {
        $currentUser = auth()->user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => ['required', 'string', 'min:8', 'confirmed', new StrongPassword()],
            'company_id' => 'nullable|exists:companies,id',
            'role' => 'required|in:super_admin,admin,user',
            'is_admin' => 'nullable|boolean',
            'project_ids' => 'nullable|array',
            'project_ids.*' => 'integer|exists:projects,id',
        ]);

        // Role-based restrictions
        // Only super admin can create super_admin users
        if ($validated['role'] === 'super_admin' && !$currentUser->isSuperAdmin()) {
            return back()->withInput()->with('error', 'You do not have permission to create super admin users.');
        }

        // Regular admin can only create users for their company
        $companyId = $validated['company_id'] ?? CompanyContext::getActiveCompanyId();
        if (!$currentUser->isSuperAdmin()) {
            $activeCompanyId = CompanyContext::getActiveCompanyId();
            if ($companyId && $companyId != $activeCompanyId) {
                return back()->withInput()->with('error', 'You can only create users for your own company.');
            }
            // Force company_id to active company for regular admins
            $companyId = $activeCompanyId;
            
            // Regular admin cannot create super_admin or other admins (only regular users)
            if (in_array($validated['role'], ['super_admin', 'admin'])) {
                return back()->withInput()->with('error', 'You do not have permission to create admin users.');
            }
        }

        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = Hash::make($validated['password']);
        $user->company_id = $companyId;
        $user->role = $validated['role'];
        // Sync is_admin with role for backward compatibility
        $user->is_admin = in_array($validated['role'], ['admin', 'super_admin']);
        $user->save();

        // Sync project access - always update if project_ids is in request
        // Filter out empty strings and invalid values from project_ids array
        $projectIds = collect($request->input('project_ids', []))
            ->filter(function($id) {
                return !empty($id) && $id !== '' && is_numeric($id);
            })
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        // Always sync project assignments (even if empty array)
        if (!empty($projectIds)) {
            // User has selected specific projects - sync them
            $projectQuery = Project::whereIn('id', $projectIds);
            if ($companyId) {
                $projectQuery->where('company_id', $companyId);
            }
            $allowedProjectIds = $projectQuery->pluck('id')->all();
            $user->projects()->sync($allowedProjectIds);
        } else {
            // No projects selected (all checkboxes unchecked) - clear all project assignments
            // This allows user to have access to all projects in their company (per getAccessibleProjectIds logic)
            $user->projects()->sync([]);
        }

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $currentUser = auth()->user();
        
        // Check if current user can edit this user
        if (!$currentUser->isSuperAdmin()) {
            $companyId = CompanyContext::getActiveCompanyId();
            if ($user->company_id != $companyId) {
                abort(403, 'You can only edit users from your own company.');
            }
            
            // Regular admin cannot edit super_admin users
            if ($user->isSuperAdmin()) {
                abort(403, 'You do not have permission to edit super admin users.');
            }
        }
        
        $companies = Company::orderBy('name')->get();
        $projectsQuery = Project::with('company')->orderBy('name');
        
        // Regular admin can only see their company
        if (!$currentUser->isSuperAdmin()) {
            $companyId = CompanyContext::getActiveCompanyId();
            if ($companyId) {
                $companies = Company::where('id', $companyId)->get();
                $projectsQuery->where('company_id', $companyId);
            }
        }
        
        $projects = $projectsQuery->get();
        $selectedProjectIds = $user->projects()->pluck('projects.id')->all();
        
        return view('admin.users.edit', compact('user', 'companies', 'projects', 'selectedProjectIds'));
    }

    public function update(Request $request, User $user)
    {
        $currentUser = auth()->user();
        
        // Prevent users from modifying their own role
        if ($currentUser->id === $user->id) {
            // Users cannot change their own role or company
            $rules = [
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|max:255|unique:users,email,' . $user->id,
            ];
            
            // Only validate password if it's provided (not empty)
            if ($request->filled('password')) {
                $rules['password'] = ['required', 'string', 'min:8', 'confirmed', new StrongPassword()];
                $rules['password_confirmation'] = 'required|string';
            }
            
            $validated = $request->validate($rules);
            
            if (array_key_exists('name', $validated)) {
                $user->name = $validated['name'];
            }
            if (array_key_exists('email', $validated)) {
                $user->email = $validated['email'];
            }
            if (!empty($validated['password'] ?? null)) {
                $user->password = Hash::make($validated['password']);
            }
            $user->save();
            
            return redirect()->route('admin.users.index')->with('success', 'Your profile updated successfully.');
        }
        
        // Check permissions for editing other users
        if (!$currentUser->isSuperAdmin()) {
            $companyId = CompanyContext::getActiveCompanyId();
            if ($user->company_id != $companyId) {
                abort(403, 'You can only edit users from your own company.');
            }
            
            // Regular admin cannot edit super_admin users
            if ($user->isSuperAdmin()) {
                abort(403, 'You do not have permission to edit super admin users.');
            }
        }
        
        // Build validation rules - password only required if provided
        $rules = [
            'company_id' => 'nullable|exists:companies,id',
            'role' => 'required|in:super_admin,admin,user',
            'is_admin' => 'nullable|boolean',
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:users,email,' . $user->id,
            'project_ids' => 'nullable|array',
            'project_ids.*' => 'integer|exists:projects,id',
        ];
        
        // Only validate password if it's provided (not empty)
        if ($request->filled('password')) {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed', new StrongPassword()];
            $rules['password_confirmation'] = 'required|string';
        }
        
        $validated = $request->validate($rules);

        // Role-based restrictions
        // Only super admin can assign super_admin role
        if ($validated['role'] === 'super_admin' && !$currentUser->isSuperAdmin()) {
            return back()->withInput()->with('error', 'You do not have permission to assign super admin role.');
        }

        // Regular admin cannot change user to admin or super_admin
        if (!$currentUser->isSuperAdmin()) {
            if (in_array($validated['role'], ['super_admin', 'admin'])) {
                return back()->withInput()->with('error', 'You do not have permission to assign admin roles.');
            }
            
            // Regular admin can only change company to their own
            $companyId = $validated['company_id'] ?? $user->company_id;
            $activeCompanyId = CompanyContext::getActiveCompanyId();
            if ($companyId && $companyId != $activeCompanyId) {
                return back()->withInput()->with('error', 'You can only assign users to your own company.');
            }
            $validated['company_id'] = $activeCompanyId;
        }

        if (array_key_exists('name', $validated)) {
            $user->name = $validated['name'];
        }
        if (array_key_exists('email', $validated)) {
            $user->email = $validated['email'];
        }
        if (!empty($validated['password'] ?? null)) {
            $user->password = Hash::make($validated['password']);
        }
        $user->company_id = $validated['company_id'] ?? $user->company_id;
        $user->role = $validated['role'];
        // Sync is_admin with role for backward compatibility
        $user->is_admin = in_array($validated['role'], ['admin', 'super_admin']);
        $user->save();

        // Sync project access - always update if project_ids is in request
        // Filter out empty strings and invalid values from project_ids array
        $projectIds = collect($request->input('project_ids', []))
            ->filter(function($id) {
                return !empty($id) && $id !== '' && is_numeric($id);
            })
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        // Always sync project assignments (even if empty array)
        $targetCompanyId = $validated['company_id'] ?? $user->company_id;
        
        if (!empty($projectIds)) {
            // User has selected specific projects - sync them
            $projectQuery = Project::whereIn('id', $projectIds);
            if ($targetCompanyId) {
                $projectQuery->where('company_id', $targetCompanyId);
            }
            $allowedProjectIds = $projectQuery->pluck('id')->all();
            $user->projects()->sync($allowedProjectIds);
        } else {
            // No projects selected (all checkboxes unchecked) - clear all project assignments
            // This allows user to have access to all projects in their company (per getAccessibleProjectIds logic)
            $user->projects()->sync([]);
        }

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $currentUser = auth()->user();
        
        // Prevent users from deleting themselves
        if ($currentUser->id === $user->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }
        
        // Role-based restrictions
        if (!$currentUser->isSuperAdmin()) {
            // Regular admin cannot delete super_admin users
            if ($user->isSuperAdmin()) {
                return back()->with('error', 'You do not have permission to delete super admin users.');
            }
            
            // Regular admin can only delete users from their company
            $companyId = CompanyContext::getActiveCompanyId();
            if ($user->company_id != $companyId) {
                abort(403, 'You can only delete users from your own company.');
            }
        }
        
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }
}


