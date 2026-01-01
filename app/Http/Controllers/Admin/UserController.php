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
use App\Rules\ValidEmailDomain;
use App\Mail\UserAccountCreated;
use App\Mail\PasswordChanged;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    use ValidatesForms;
    
    public function __construct()
    {
        // Allow both admin and super_admin to access user management
        // Route-level middleware (admin_only) already restricts to admin/super_admin
        $this->middleware('admin');
    }
    
    /**
     * Validate user form data (AJAX endpoint)
     */
    public function validateUser(Request $request, User $user = null)
    {
        $emailRule = $user
            ? ['required', 'email', 'max:255', 'unique:users,email,' . $user->id, new ValidEmailDomain()]
            : ['required', 'email', 'max:255', 'unique:users,email', new ValidEmailDomain()];
        
        $passwordRule = $user
            ? ['nullable', 'string', 'min:8', 'confirmed', new StrongPassword()]
            : ['required', 'string', 'min:8', 'confirmed', new StrongPassword()];
        
        $rules = [
            'name' => 'required|string|max:255',
            'email' => $emailRule,
            'password' => $passwordRule,
            'password_confirmation' => $user ? 'nullable|string' : 'required|string',
            'company_id' => 'nullable|exists:companies,id',
            'role' => 'required|in:super_admin,admin,user,site_engineer',
            'is_admin' => 'nullable|boolean',
            'project_ids' => 'nullable|array',
            'project_ids.*' => 'integer|exists:projects,id',
        ];
        
        return $this->validateForm($request, $rules);
    }

    public function index()
    {
        $currentUser = auth()->user();
        $query = User::with('company')->orderBy('name');

        // Super admin can see all users
        // Regular admin can only see users from their company (excluding super_admin users)
        if (!$currentUser->isSuperAdmin()) {
            // Filter by admin's company
            if ($currentUser->company_id) {
                $query->where('company_id', $currentUser->company_id);
            } else {
                // Admin has no company, return empty
                $users = collect()->paginate(20);
                return view('admin.users.index', compact('users'));
            }
            
            // Exclude super_admin users for regular admins
            $query->where('role', '!=', 'super_admin');
        } else {
            // Super admin can filter by company context if set
            $companyId = CompanyContext::getActiveCompanyId();
            if ($companyId && (int) $companyId !== 1) {
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
        
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'companies' => $companies,
                'projects' => $projects,
            ]);
        }
        
        // Redirect to index page since popup handles everything
        return redirect()->route('admin.users.index');
    }

    public function store(Request $request)
    {
        $currentUser = auth()->user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', 'unique:users,email', new ValidEmailDomain()],
            'password' => ['required', 'string', 'min:8', 'confirmed', new StrongPassword()],
            'company_id' => 'nullable|exists:companies,id',
            'role' => 'required|in:super_admin,admin,user,site_engineer',
            'is_admin' => 'nullable|boolean',
            'project_ids' => 'nullable|array',
            'project_ids.*' => 'integer|exists:projects,id',
        ]);

        // Role-based restrictions
        // Only super admin can create super_admin users
        if ($validated['role'] === 'super_admin' && !$currentUser->isSuperAdmin()) {
            // Return JSON for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to create super admin users.',
                ], 403);
            }
            return back()->withInput()->with('error', 'You do not have permission to create super admin users.');
        }

        // Regular admin can only create users for their company
        $companyId = $validated['company_id'] ?? CompanyContext::getActiveCompanyId();
        if (!$currentUser->isSuperAdmin()) {
            $activeCompanyId = CompanyContext::getActiveCompanyId();
            if ($companyId && $companyId != $activeCompanyId) {
                // Return JSON for AJAX requests
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You can only create users for your own company.',
                    ], 403);
                }
                return back()->withInput()->with('error', 'You can only create users for your own company.');
            }
            // Force company_id to active company for regular admins
            $companyId = $activeCompanyId;
            
            // Regular admin cannot create super_admin or other admins (only regular users and site engineers)
            if (in_array($validated['role'], ['super_admin', 'admin'])) {
                // Return JSON for AJAX requests
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You do not have permission to create admin users.',
                    ], 403);
                }
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
        
        // Send email notification to the new user
        try {
            Mail::to($user->email)->send(new UserAccountCreated($user, $validated['password']));
            // Track email notification sent
            UserAccountCreated::incrementEmailCount();
        } catch (\Exception $e) {
            // Log error but don't fail user creation if email fails
            \Log::error('Failed to send user account creation email: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'email' => $user->email
            ]);
        }

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

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            $user->load('company');
            return response()->json([
                'success' => true,
                'message' => 'User created successfully.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'company_name' => $user->company ? $user->company->name : null,
                    'role' => $user->role,
                ],
            ]);
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
        
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'company_id' => $user->company_id,
                    'role' => $user->role,
                    'is_admin' => $user->is_admin,
                ],
                'companies' => $companies,
                'projects' => $projects,
                'selectedProjectIds' => $selectedProjectIds,
            ]);
        }
        
        // Redirect to index page since popup handles everything
        return redirect()->route('admin.users.index');
    }

    public function update(Request $request, User $user)
    {
        $currentUser = auth()->user();
        
        // Prevent users from modifying their own role
        if ($currentUser->id === $user->id) {
            // Users cannot change their own role or company
            $rules = [
                'name' => 'sometimes|string|max:255',
            ];
            
            // Email validation - always validate email when updating own profile
            $rules['email'] = ['required', 'email', 'max:255', 'unique:users,email,' . $user->id, new ValidEmailDomain()];
            
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
            
            // Track if password was changed
            $passwordChanged = false;
            $newPassword = null;
            
            if (!empty($validated['password'] ?? null)) {
                $passwordChanged = true;
                $newPassword = $validated['password'];
                $user->password = Hash::make($validated['password']);
            }
            
            $user->save();
            
            // Send password change notification email
            if ($passwordChanged) {
                try {
                    Mail::to($user->email)->send(new PasswordChanged($user, $newPassword, false));
                    // Track email notification sent
                    PasswordChanged::incrementEmailCount();
                } catch (\Exception $e) {
                    // Log error but don't fail update if email fails
                    \Log::error('Failed to send password change email: ' . $e->getMessage(), [
                        'user_id' => $user->id,
                        'email' => $user->email
                    ]);
                }
            }
            
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
            'role' => 'required|in:super_admin,admin,user,site_engineer',
            'is_admin' => 'nullable|boolean',
            'name' => 'sometimes|string|max:255',
            'project_ids' => 'nullable|array',
            'project_ids.*' => 'integer|exists:projects,id',
        ];
        
        // Email validation - always validate email when updating
        $rules['email'] = ['required', 'email', 'max:255', 'unique:users,email,' . $user->id, new ValidEmailDomain()];
        
        // Only validate password if it's provided (not empty)
        if ($request->filled('password')) {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed', new StrongPassword()];
            $rules['password_confirmation'] = 'required|string';
        }
        
        $validated = $request->validate($rules);

        // Role-based restrictions
        // Only super admin can assign super_admin role
        if ($validated['role'] === 'super_admin' && !$currentUser->isSuperAdmin()) {
            // Return JSON for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to assign super admin role.',
                ], 403);
            }
            return back()->withInput()->with('error', 'You do not have permission to assign super admin role.');
        }

        // Regular admin cannot change user to admin or super_admin
        // Also cannot change existing admin/super_admin users
        if (!$currentUser->isSuperAdmin()) {
            // Prevent changing to admin roles
            if (in_array($validated['role'], ['super_admin', 'admin'])) {
                // Return JSON for AJAX requests
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You do not have permission to assign admin roles.',
                    ], 403);
                }
                return back()->withInput()->with('error', 'You do not have permission to assign admin roles.');
            }
            // Prevent changing existing admin/super_admin users
            if (in_array($user->role, ['super_admin', 'admin'])) {
                // Return JSON for AJAX requests
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You do not have permission to modify admin or super admin users.',
                    ], 403);
                }
                return back()->withInput()->with('error', 'You do not have permission to modify admin or super admin users.');
            }
            
            // Regular admin can only change company to their own
            $companyId = $validated['company_id'] ?? $user->company_id;
            $activeCompanyId = CompanyContext::getActiveCompanyId();
            if ($companyId && $companyId != $activeCompanyId) {
                // Return JSON for AJAX requests
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You can only assign users to your own company.',
                    ], 403);
                }
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
        
        // Track if password was changed
        $passwordChanged = false;
        $newPassword = null;
        
        if (!empty($validated['password'] ?? null)) {
            $passwordChanged = true;
            $newPassword = $validated['password'];
            $user->password = Hash::make($validated['password']);
        }
        
        $user->company_id = $validated['company_id'] ?? $user->company_id;
        $user->role = $validated['role'];
        // Sync is_admin with role for backward compatibility
        $user->is_admin = in_array($validated['role'], ['admin', 'super_admin']);
        $user->save();
        
        // Send password change notification email (if changed by admin)
        if ($passwordChanged) {
            try {
                Mail::to($user->email)->send(new PasswordChanged($user, $newPassword, true));
                // Track email notification sent
                PasswordChanged::incrementEmailCount();
            } catch (\Exception $e) {
                // Log error but don't fail update if email fails
                \Log::error('Failed to send password change email: ' . $e->getMessage(), [
                    'user_id' => $user->id,
                    'email' => $user->email
                ]);
            }
        }

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

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            $user->load('company');
            return response()->json([
                'success' => true,
                'message' => 'User updated successfully.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'company_name' => $user->company ? $user->company->name : null,
                    'role' => $user->role,
                ],
            ]);
        }

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(Request $request, User $user)
    {
        $currentUser = auth()->user();
        
        // Prevent users from deleting themselves
        if ($currentUser->id === $user->id) {
            // Return JSON for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot delete your own account.',
                ], 403);
            }
            return back()->with('error', 'You cannot delete your own account.');
        }
        
        // Role-based restrictions
        if (!$currentUser->isSuperAdmin()) {
            // Regular admin cannot delete super_admin users
            if ($user->isSuperAdmin()) {
                // Return JSON for AJAX requests
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You do not have permission to delete super admin users.',
                    ], 403);
                }
                return back()->with('error', 'You do not have permission to delete super admin users.');
            }
            
            // Regular admin can only delete users from their company
            $companyId = CompanyContext::getActiveCompanyId();
            if ($user->company_id != $companyId) {
                // Return JSON for AJAX requests
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You can only delete users from your own company.',
                    ], 403);
                }
                abort(403, 'You can only delete users from your own company.');
            }
        }
        
        $user->delete();
        
        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully.',
            ]);
        }

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }
}


