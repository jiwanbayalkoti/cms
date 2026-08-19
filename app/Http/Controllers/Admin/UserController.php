<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Traits\ValidatesForms;
use App\Models\User;
use App\Models\Company;
use App\Models\Project;
use App\Models\PermissionRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Support\CompanyContext;
use App\Services\ModulePermissionService;
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

    private function emailRules(string $uniqueRule): array
    {
        $rules = ['required', 'email', 'max:255', $uniqueRule];

        if (!app()->environment('local')) {
            $rules[] = new ValidEmailDomain();
        }

        return $rules;
    }
    
    /**
     * Validate user form data (AJAX endpoint)
     */
    public function validateUser(Request $request, User $user = null)
    {
        $emailRule = $user
            ? $this->emailRules('unique:users,email,' . $user->id)
            : $this->emailRules('unique:users,email');
        
        $passwordRule = $user
            ? ['nullable', 'string', 'min:8', 'confirmed', new StrongPassword()]
            : ['required', 'string', 'min:8', 'confirmed', new StrongPassword()];
        
        $rules = [
            'name' => 'required|string|max:255',
            'email' => $emailRule,
            'password' => $passwordRule,
            'password_confirmation' => $user ? 'nullable|string' : 'required|string',
            'company_id' => 'nullable|exists:companies,id',
            'is_admin' => 'nullable|boolean',
            'project_ids' => 'nullable|array',
            'project_ids.*' => 'integer|exists:projects,id',
            'permission_role_id' => $this->permissionRoleIdRule(),
        ];
        
        return $this->validateForm($request, $rules);
    }

    public function index()
    {
        $currentUser = auth()->user();
        $query = User::with(['company', 'permissionRole'])->orderBy('name');

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
            $requestedCompanyId = request('company_id');
            if ($currentUser->isSuperAdmin() && !$requestedCompanyId) {
                $permissionRoles = PermissionRole::withoutGlobalScopes()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(['id', 'name', 'company_id', 'type', 'login_role']);
            } else {
                $formCompanyId = $requestedCompanyId
                    ?: ($currentUser->isSuperAdmin()
                        ? CompanyContext::getActiveCompanyId()
                        : ($currentUser->company_id ?: CompanyContext::getActiveCompanyId()));
                $permissionRoles = $this->permissionRolesForForm($formCompanyId);
            }

            return response()->json([
                'companies' => $companies,
                'projects' => $projects,
                'permission_roles' => $permissionRoles,
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
            'email' => $this->emailRules('unique:users,email'),
            'password' => ['required', 'string', 'min:8', 'confirmed', new StrongPassword()],
            'company_id' => 'nullable|exists:companies,id',
            'is_admin' => 'nullable|boolean',
            'project_ids' => 'nullable|array',
            'project_ids.*' => 'integer|exists:projects,id',
            'permission_role_id' => $this->permissionRoleIdRule(),
        ]);

        $companyId = $validated['company_id'] ?? CompanyContext::getActiveCompanyId();
        if (!$currentUser->isSuperAdmin()) {
            $activeCompanyId = CompanyContext::getActiveCompanyId();
            if ($companyId && $companyId != $activeCompanyId) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You can only create users for your own company.',
                    ], 403);
                }
                return back()->withInput()->with('error', 'You can only create users for your own company.');
            }
            $companyId = $activeCompanyId;
        }

        $assignment = $this->resolveRoleAssignment(
            $request->input('permission_role_id'),
            $companyId,
            $currentUser
        );
        if (!empty($assignment['error'])) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $assignment['error']], 403);
            }
            return back()->withInput()->with('error', $assignment['error']);
        }

        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = Hash::make($validated['password']);
        $user->company_id = $companyId;
        $user->role = $assignment['role'];
        $user->is_admin = in_array($assignment['role'], ['admin', 'super_admin'], true);
        $user->permission_role_id = $assignment['permission_role_id'];
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
            $user->load(['company', 'permissionRole']);
            return response()->json([
                'success' => true,
                'message' => 'User created successfully.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'company_name' => $user->company ? $user->company->name : null,
                    'role' => $user->role,
                    'role_label' => $user->roleLabel(),
                    'permission_role_id' => $user->permission_role_id,
                    'permission_role_name' => $user->permissionRole?->name,
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
            $user->load('permissionRole');

            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'company_id' => $user->company_id,
                    'role' => $user->role,
                    'is_admin' => (bool) $user->is_admin,
                    'permission_role_id' => $user->permission_role_id,
                    'permission_role_name' => $user->permissionRole?->name,
                    'permission_role_type' => $user->permissionRole?->type,
                    'permission_role_login_role' => $user->permissionRole?->login_role,
                ],
                'companies' => $companies->values()->all(),
                'projects' => $projects->values()->all(),
                'selectedProjectIds' => $selectedProjectIds,
                'permission_roles' => $this->permissionRolesForForm(
                    $user->company_id ?: CompanyContext::getActiveCompanyId()
                )->values()->all(),
            ]);
        }
        
        // Redirect to index page since popup handles everything
        return redirect()->route('admin.users.index');
    }

    public function update(Request $request, User $user)
    {
        $currentUser = auth()->user();
        $isSelf = (int) $currentUser->id === (int) $user->id;

        // Check permissions for editing other users
        if (!$isSelf && !$currentUser->isSuperAdmin()) {
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
            'is_admin' => 'nullable|boolean',
            'name' => 'sometimes|string|max:255',
            'project_ids' => 'nullable|array',
            'project_ids.*' => 'integer|exists:projects,id',
            'permission_role_id' => $this->permissionRoleIdRule(),
        ];
        
        // Email validation - always validate email when updating
        $rules['email'] = $this->emailRules('unique:users,email,' . $user->id);
        
        // Only validate password if it's provided (not empty)
        if ($request->filled('password')) {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed', new StrongPassword()];
            $rules['password_confirmation'] = 'required|string';
        }
        
        $validated = $request->validate($rules);

        if (!$currentUser->isSuperAdmin()) {
            if (!$isSelf && in_array($user->role, ['super_admin', 'admin'], true)) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You do not have permission to modify admin or super admin users.',
                    ], 403);
                }
                return back()->withInput()->with('error', 'You do not have permission to modify admin or super admin users.');
            }

            $companyId = $validated['company_id'] ?? $user->company_id;
            $activeCompanyId = CompanyContext::getActiveCompanyId();
            if ($companyId && $companyId != $activeCompanyId) {
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

        $targetCompanyId = $validated['company_id'] ?? $user->company_id;
        if ($isSelf && !$currentUser->isSuperAdmin()) {
            $targetCompanyId = $user->company_id;
        }

        $assignment = $this->resolveRoleAssignment(
            $request->input('permission_role_id'),
            $targetCompanyId,
            $currentUser,
            $user
        );
        if (!empty($assignment['error'])) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $assignment['error']], 422);
            }
            return back()->withInput()->with('error', $assignment['error']);
        }

        if ($isSelf && $currentUser->isSuperAdmin() && ($assignment['role'] ?? null) !== 'super_admin') {
            $message = 'You cannot change your own Super Admin role.';
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }
            return back()->withInput()->with('error', $message);
        }

        if (
            $user->role === 'super_admin'
            && ($assignment['role'] ?? null) !== 'super_admin'
            && !User::where('role', 'super_admin')->where('id', '!=', $user->id)->exists()
        ) {
            $message = 'Cannot remove the last Super Admin.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }
            return back()->withInput()->with('error', $message);
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
        
        $user->forceFill([
            'company_id' => (!$isSelf || $currentUser->isSuperAdmin()) ? $targetCompanyId : $user->company_id,
            'role' => $assignment['role'],
            'is_admin' => in_array($assignment['role'], ['admin', 'super_admin'], true),
            'permission_role_id' => $assignment['permission_role_id'],
        ]);
        $user->save();
        $user->refresh()->load(['company', 'permissionRole']);
        
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
        if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
            $user->unsetRelation('permissionRole');
            $user->load(['company', 'permissionRole']);
            return response()->json([
                'success' => true,
                'message' => 'User updated successfully.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'company_name' => $user->company ? $user->company->name : null,
                    'role' => $user->role,
                    'role_label' => $user->roleLabel(),
                    'permission_role_id' => $user->permission_role_id,
                    'permission_role_name' => $user->permissionRole?->name,
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

    /**
     * Switch login to another user (super admin only).
     * The super admin will see the app as that user's role; can switch back later.
     */
    public function switchToUser(User $user)
    {
        $currentUser = auth()->user();
        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Only super admin can switch to another user.');
        }
        if ($currentUser->id === $user->id) {
            return redirect()->route('admin.users.index')->with('error', 'You cannot switch to yourself.');
        }

        session()->put('impersonate_original_user_id', $currentUser->id);
        Auth::login($user);

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('admin.projects.index');
    }

    /**
     * Switch back to super admin (only when impersonating).
     */
    public function switchBack()
    {
        $originalId = session('impersonate_original_user_id');
        if (!$originalId) {
            return redirect()->route('admin.dashboard')->with('error', 'Not impersonating.');
        }

        $originalUser = User::find($originalId);
        if (!$originalUser) {
            session()->forget('impersonate_original_user_id');
            return redirect()->route('admin.login')->with('error', 'Original session expired.');
        }

        session()->forget('impersonate_original_user_id');
        Auth::login($originalUser);

        if ($originalUser->isSuperAdmin()) {
            return redirect()->route('admin.companies.index')->with('success', 'Switched back to Super Admin.');
        }

        return redirect()->route('admin.users.index')->with('success', 'Switched back to Super Admin.');
    }

    private function permissionRolesForForm($companyId = null)
    {
        $companyId = $companyId ? (int) $companyId : (int) CompanyContext::getActiveCompanyId();

        $query = PermissionRole::withoutGlobalScopes()
            ->where('is_active', true)
            ->orderByRaw("CASE WHEN login_role = 'admin' OR type = 'admin_pack' THEN 0 WHEN login_role = 'user' THEN 1 WHEN login_role = 'site_engineer' THEN 2 ELSE 3 END")
            ->orderBy('name');

        if ($companyId) {
            $query->where('company_id', $companyId);
        } elseif (!auth()->user()?->isSuperAdmin()) {
            return collect();
        }

        return $query->get(['id', 'name', 'company_id', 'type', 'login_role', 'slug'])->values();
    }

    private function permissionRoleIdRule(): array
    {
        return [
            'required',
            function ($attribute, $value, $fail) {
                if ($value === 'super_admin' || $value === 'super-admin') {
                    return;
                }
                if (!PermissionRole::withoutGlobalScopes()->where('id', (int) $value)->where('is_active', true)->exists()) {
                    $fail('The selected role is invalid.');
                }
            },
        ];
    }

    private function resolveRoleAssignment($permissionRoleId, $companyId, User $actor, ?User $target = null): array
    {
        if ($permissionRoleId === 'super_admin' || $permissionRoleId === 'super-admin') {
            if (!$actor->isSuperAdmin()) {
                return ['error' => 'You do not have permission to assign Super Admin.'];
            }

            return [
                'role' => 'super_admin',
                'permission_role_id' => null,
            ];
        }

        $modRole = PermissionRole::withoutGlobalScopes()
            ->where('id', (int) $permissionRoleId)
            ->where('is_active', true)
            ->first();

        if (!$modRole) {
            return ['error' => 'The selected role was not found.'];
        }

        if ($companyId && (int) $modRole->company_id !== (int) $companyId) {
            $mapped = PermissionRole::withoutGlobalScopes()
                ->where('company_id', (int) $companyId)
                ->where('is_active', true)
                ->where(function ($q) use ($modRole) {
                    if ($modRole->login_role) {
                        $q->where('login_role', $modRole->login_role);
                    } else {
                        $q->where('slug', $modRole->slug)->orWhere('name', $modRole->name);
                    }
                })
                ->first();

            if (!$mapped) {
                return ['error' => 'The selected role does not belong to this company.'];
            }

            $modRole = $mapped;
        }

        $loginRole = $modRole->loginRoleValue();

        $keepingOwnAdmin = $target
            && (int) $actor->id === (int) $target->id
            && in_array($target->role, ['admin', 'super_admin'], true)
            && in_array($loginRole, ['admin', 'super_admin'], true);

        if (!$actor->isSuperAdmin() && in_array($loginRole, ['admin', 'super_admin'], true) && !$keepingOwnAdmin) {
            return ['error' => 'You do not have permission to assign admin roles.'];
        }

        return [
            'role' => $loginRole,
            'permission_role_id' => $modRole->id,
        ];
    }
}


