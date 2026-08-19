<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PermissionRole;
use App\Models\User;
use App\Services\ModulePermissionService;
use App\Support\CompanyContext;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RolePermissionController extends Controller
{
    public function __construct(private ModulePermissionService $permissions)
    {
        $this->middleware('admin');
    }

    public function index(Request $request)
    {
        $this->ensureAdmin();
        $companyId = $this->requireCompanyId();

        $this->permissions->ensureLoginModuleRoles($companyId);
        $this->permissions->assignCoreAdminPackIfEmpty($companyId, auth()->id());

        $roleQuery = PermissionRole::withoutGlobalScopes()->where('company_id', $companyId)
            ->withCount('users')
            ->with('modules')
            ->orderByRaw("CASE WHEN type = 'admin_pack' THEN 0 ELSE 1 END")
            ->orderBy('name');

        if (!auth()->user()->isSuperAdmin()) {
            $roleQuery->where('type', PermissionRole::TYPE_STAFF);
        }

        $roles = $roleQuery->get();

        $selected = null;
        if ($request->filled('role')) {
            $selected = $roles->firstWhere('id', (int) $request->role);
        }
        if (!$selected) {
            $selected = $roles->first();
        }

        $actor = auth()->user();
        $groupedModules = $actor->isSuperAdmin()
            ? $this->permissions->groupedModules()
            : $this->permissions->grantableGroupedModules($actor);
        $matrix = $this->matrixFor($selected);
        $assignableUsers = User::where('company_id', $companyId)
            ->whereNotIn('role', ['super_admin', 'admin'])
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role', 'permission_role_id']);
        $canEditSelected = $selected && ($actor->isSuperAdmin() || !$selected->isAdminPack());
        $hasAdminPack = $this->permissions->hasCompanyAdminPack($companyId);

        return view('admin.roles.index', compact(
            'roles',
            'selected',
            'groupedModules',
            'matrix',
            'assignableUsers',
            'canEditSelected',
            'hasAdminPack'
        ));
    }

    public function store(Request $request)
    {
        $this->ensureAdmin();
        $companyId = $this->requireCompanyId();

        if (!auth()->user()->isSuperAdmin() && !$this->permissions->hasCompanyAdminPack($companyId)) {
            return redirect()->route('admin.roles.index')->with('error', 'Super Admin has not granted this company any modules yet.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:permission_roles,name,NULL,id,company_id,' . $companyId,
            'description' => 'nullable|string|max:500',
        ]);

        $role = PermissionRole::create([
            'company_id' => $companyId,
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'type' => PermissionRole::TYPE_STAFF,
            'description' => $validated['description'] ?? null,
            'is_active' => true,
            'created_by' => auth()->id(),
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Role created.', 'role' => $role]);
        }

        return redirect()->route('admin.roles.index', ['role' => $role->id])->with('success', 'Role created.');
    }

    public function update(Request $request, PermissionRole $permissionRole)
    {
        $this->ensureAdmin();
        $this->authorizeRole($permissionRole);
        $this->ensureCanMutate($permissionRole);

        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:permission_roles,name,' . $permissionRole->id . ',id,company_id,' . $permissionRole->company_id,
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $permissionRole->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Role updated.']);
        }

        return redirect()->route('admin.roles.index', ['role' => $permissionRole->id])->with('success', 'Role updated.');
    }

    public function destroy(Request $request, PermissionRole $permissionRole)
    {
        $this->ensureAdmin();
        $this->authorizeRole($permissionRole);
        $this->ensureCanMutate($permissionRole);

        if ($permissionRole->isAdminPack() || $permissionRole->isLoginRole()) {
            abort(403, 'Login roles cannot be deleted. Edit their module ticks instead.');
        }

        User::where('permission_role_id', $permissionRole->id)->update(['permission_role_id' => null]);
        $permissionRole->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Role deleted.']);
        }

        return redirect()->route('admin.roles.index')->with('success', 'Role deleted.');
    }

    public function syncPermissions(Request $request, PermissionRole $permissionRole)
    {
        $this->ensureAdmin();
        $this->authorizeRole($permissionRole);

        $actor = auth()->user();
        $allowedKeys = $actor->isSuperAdmin()
            ? array_keys($this->permissions->modules())
            : $this->permissions->grantableModuleKeys($actor);

        if ($permissionRole->isAdminPack() && !$actor->isSuperAdmin()) {
            abort(403, 'Only Super Admin can change company admin modules.');
        }

        $validated = $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'array',
            'permissions.*.view' => 'sometimes|boolean',
            'permissions.*.create' => 'sometimes|boolean',
            'permissions.*.update' => 'sometimes|boolean',
            'permissions.*.delete' => 'sometimes|boolean',
        ]);

        $incoming = $validated['permissions'] ?? [];

        if ($permissionRole->isAdminPack()) {
            $this->permissions->saveAdminPack($permissionRole->company_id, $incoming, $actor->id);
        } else {
            $this->permissions->syncRoleModules($permissionRole, $incoming, $allowedKeys);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Permissions saved.']);
        }

        return redirect()->route('admin.roles.index', ['role' => $permissionRole->id])->with('success', 'Module permissions saved.');
    }

    public function assignUsers(Request $request, PermissionRole $permissionRole)
    {
        $this->ensureAdmin();
        $this->authorizeRole($permissionRole);
        $this->ensureCanMutate($permissionRole);

        if ($permissionRole->isAdminPack()) {
            abort(403, 'Company admin pack cannot be assigned to staff.');
        }

        $companyId = CompanyContext::getActiveCompanyId();

        $validated = $request->validate([
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'integer|exists:users,id',
        ]);

        $ids = collect($validated['user_ids'] ?? [])->map(fn ($id) => (int) $id)->unique()->all();

        $allowedIds = User::where('company_id', $companyId)
            ->whereNotIn('role', ['super_admin', 'admin'])
            ->whereIn('id', $ids ?: [0])
            ->pluck('id')
            ->all();

        User::where('permission_role_id', $permissionRole->id)
            ->where('company_id', $companyId)
            ->whereNotIn('id', $allowedIds)
            ->update([
                'permission_role_id' => app(\App\Services\ModulePermissionService::class)->builtinRoleId((int) $companyId, 'user'),
                'role' => 'user',
                'is_admin' => false,
            ]);

        if (!empty($allowedIds)) {
            User::where('company_id', $companyId)
                ->whereIn('id', $allowedIds)
                ->update([
                    'permission_role_id' => $permissionRole->id,
                    'role' => $permissionRole->loginRoleValue(),
                    'is_admin' => false,
                ]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Users assigned to role.']);
        }

        return redirect()->route('admin.roles.index', ['role' => $permissionRole->id])->with('success', 'Users assigned to this role.');
    }

    private function matrixFor(?PermissionRole $role): array
    {
        $matrix = [];
        if (!$role) {
            return $matrix;
        }

        foreach ($role->modules as $row) {
            $matrix[$row->module_key] = [
                'view' => $row->can_view,
                'create' => $row->can_create,
                'update' => $row->can_update,
                'delete' => $row->can_delete,
            ];
        }

        return $matrix;
    }

    private function requireCompanyId(): int
    {
        $companyId = CompanyContext::getActiveCompanyId();
        if ($companyId) {
            return (int) $companyId;
        }

        if (auth()->user()?->isSuperAdmin()) {
            abort(redirect()
                ->route('admin.companies.index')
                ->with('error', 'Select a company first to manage its roles and permissions.'));
        }

        abort(403, 'Select a company before managing roles.');
    }

    private function ensureAdmin(): void
    {
        if (!auth()->user()?->isAdmin()) {
            abort(403, 'Only administrators can manage roles and permissions.');
        }
    }

    private function ensureCanMutate(PermissionRole $role): void
    {
        if ($role->isAdminPack() && !auth()->user()?->isSuperAdmin()) {
            abort(403, 'Only Super Admin can change company admin modules.');
        }
    }

    private function authorizeRole(PermissionRole $role): void
    {
        if ((int) $role->company_id !== (int) CompanyContext::getActiveCompanyId()) {
            abort(403, 'This role does not belong to the active company.');
        }
    }
}
