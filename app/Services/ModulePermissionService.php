<?php

namespace App\Services;

use App\Models\PermissionRole;
use App\Models\PermissionRoleModule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ModulePermissionService
{
    public const ADMIN_ALWAYS = ['users', 'dashboard'];

    private static array $ceilingCache = [];
    private static array $mapCache = [];

    public function modules(): array
    {
        return config('modules.modules', []);
    }

    public function groups(): array
    {
        return config('modules.groups', []);
    }

    public function groupedModules(?array $onlyKeys = null): array
    {
        $grouped = [];
        foreach ($this->groups() as $key => $label) {
            $grouped[$key] = [
                'label' => $label,
                'modules' => [],
            ];
        }

        foreach ($this->modules() as $moduleKey => $module) {
            if (is_array($onlyKeys) && !in_array($moduleKey, $onlyKeys, true)) {
                continue;
            }
            $group = $module['group'] ?? 'admin';
            if (!isset($grouped[$group])) {
                $grouped[$group] = ['label' => ucfirst($group), 'modules' => []];
            }
            $grouped[$group]['modules'][$moduleKey] = $module;
        }

        return array_filter($grouped, fn ($g) => !empty($g['modules']));
    }

    public function moduleForRoute(?string $routeName): ?string
    {
        if (!$routeName) {
            return null;
        }

        foreach ($this->modules() as $key => $module) {
            foreach ($module['routes'] ?? [] as $pattern) {
                if (Str::is($pattern, $routeName)) {
                    return $key;
                }
            }
        }

        return null;
    }

    public function actionFromRequest(Request $request): string
    {
        $method = strtoupper($request->method());

        return match ($method) {
            'GET', 'HEAD' => 'view',
            'DELETE' => 'delete',
            'PUT', 'PATCH' => 'update',
            'POST' => $this->actionFromPostRoute($request->route()?->getName()),
            default => 'view',
        };
    }

    private function actionFromPostRoute(?string $routeName): string
    {
        $name = strtolower((string) $routeName);

        if (Str::contains($name, ['.destroy', '.delete'])) {
            return 'delete';
        }
        if (Str::contains($name, ['.update', '.approve', '.reject', '.finalize', '.mark-paid', '.update-status', '.bulk-store'])) {
            return 'update';
        }
        if (Str::contains($name, ['.store', '.create', '.generate', '.validate'])) {
            return 'create';
        }

        return 'create';
    }

    public function userCan(User $user, string $module, string $action = 'view'): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if (in_array($module, config('modules.admin_locked', []), true)) {
            return false;
        }

        $ceiling = $this->companyCeilingMap($user->company_id);

        if ($this->isCompanyAdmin($user)) {
            if (in_array($module, self::ADMIN_ALWAYS, true)) {
                return true;
            }
            if ($ceiling === null) {
                return false;
            }

            return (bool) ($ceiling[$module][$action] ?? false);
        }

        $map = $this->permissionMapFor($user);
        $allowed = false;
        if (is_array($map)) {
            $allowed = (bool) ($map[$module][$action] ?? false);
        } else {
            $defaults = config('modules.defaults.' . $user->getRole(), []);
            $allowed = in_array($action, $defaults[$module] ?? [], true);
        }

        if (!$allowed) {
            return false;
        }

        if ($ceiling === null) {
            return false;
        }

        return (bool) ($ceiling[$module][$action] ?? false);
    }

    public function isCompanyAdmin(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Super Admin grant map for a company. Null means Super Admin has not assigned modules yet.
     */
    public function companyCeilingMap(?int $companyId): ?array
    {
        if (!$companyId) {
            return null;
        }

        if (array_key_exists($companyId, self::$ceilingCache)) {
            return self::$ceilingCache[$companyId];
        }

        $role = PermissionRole::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('type', PermissionRole::TYPE_ADMIN_PACK)
            ->where('is_active', true)
            ->with('modules')
            ->first();

        if (!$role || $role->modules->isEmpty()) {
            return self::$ceilingCache[$companyId] = $this->defaultAdminMap();
        }

        return self::$ceilingCache[$companyId] = $this->mapFromRole($role);
    }

    public function hasCompanyAdminPack(?int $companyId): bool
    {
        $map = $this->companyCeilingMap($companyId);

        return is_array($map) && !empty($map);
    }

    /**
     * Modules a user is allowed to grant to staff roles.
     */
    public function grantableModuleKeys(User $user): array
    {
        if ($user->isSuperAdmin()) {
            return array_keys($this->modules());
        }

        $ceiling = $this->companyCeilingMap($user->company_id);
        if ($ceiling === null) {
            return [];
        }

        $keys = [];
        foreach ($ceiling as $module => $flags) {
            if (in_array($module, self::ADMIN_ALWAYS, true)) {
                continue;
            }
            if (!empty($flags['view'])) {
                $keys[] = $module;
            }
        }

        return $keys;
    }

    public function grantableGroupedModules(User $user): array
    {
        return $this->groupedModules($this->grantableModuleKeys($user));
    }

    /**
     * Custom module map when an active staff permission role is assigned.
     * Returns null when the user should use login-role defaults instead.
     */
    public function permissionMapFor(User $user): ?array
    {
        $cacheKey = $user->id . ':' . ($user->permission_role_id ?? 'none');
        if (array_key_exists($cacheKey, self::$mapCache)) {
            return self::$mapCache[$cacheKey];
        }

        if (!$user->permission_role_id) {
            return self::$mapCache[$cacheKey] = null;
        }

        $role = $user->permissionRole()->with('modules')->first();
        if (!$role || !$role->is_active || $role->isAdminPack()) {
            if ($role && $role->isAdminPack() && $this->isCompanyAdmin($user)) {
                return self::$mapCache[$cacheKey] = $this->mapFromRole($role);
            }

            return self::$mapCache[$cacheKey] = null;
        }

        return self::$mapCache[$cacheKey] = $this->mapFromRole($role);
    }

    public function accessibleModuleKeys(User $user): array
    {
        $keys = [];
        foreach (array_keys($this->modules()) as $module) {
            if ($this->userCan($user, $module, 'view')) {
                $keys[] = $module;
            }
        }

        return $keys;
    }

    public function syncRoleModules(PermissionRole $role, array $incoming, ?array $allowedKeys = null): void
    {
        $validKeys = $allowedKeys ?? array_keys($this->modules());

        $role->modules()->delete();

        foreach ($incoming as $moduleKey => $flags) {
            if (!in_array($moduleKey, $validKeys, true)) {
                continue;
            }
            if (in_array($moduleKey, config('modules.admin_locked', []), true)) {
                continue;
            }

            $view = (bool) ($flags['view'] ?? false);
            $create = (bool) ($flags['create'] ?? false);
            $update = (bool) ($flags['update'] ?? false);
            $delete = (bool) ($flags['delete'] ?? false);

            if ($create || $update || $delete) {
                $view = true;
            }

            if (!$view && !$create && !$update && !$delete) {
                continue;
            }

            PermissionRoleModule::create([
                'permission_role_id' => $role->id,
                'module_key' => $moduleKey,
                'can_view' => $view,
                'can_create' => $create,
                'can_update' => $update,
                'can_delete' => $delete,
            ]);
        }

        $this->forgetCaches();
    }

    public function saveAdminPack(int $companyId, array $incoming, ?int $createdBy = null): PermissionRole
    {
        $role = PermissionRole::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('type', PermissionRole::TYPE_ADMIN_PACK)
            ->first();

        if (!$role) {
            $role = PermissionRole::withoutGlobalScopes()->create([
                'company_id' => $companyId,
                'name' => 'Company Admin',
                'slug' => 'company-admin',
                'type' => PermissionRole::TYPE_ADMIN_PACK,
                'login_role' => 'admin',
                'description' => 'Modules granted by Super Admin. Company admin can only share these with staff.',
                'is_active' => true,
                'created_by' => $createdBy,
            ]);
        } elseif (!$role->login_role) {
            $role->update(['login_role' => 'admin']);
        }

        $this->syncRoleModules($role, $incoming);
        $this->forgetCaches();

        User::where('company_id', $companyId)
            ->where('role', 'admin')
            ->update(['permission_role_id' => $role->id]);

        $this->trimStaffRolesToCeiling($companyId);
        $this->ensureLoginModuleRoles($companyId);

        return $role->fresh('modules');
    }

    /**
     * Grant the default core ERP modules to a new company's admin pack.
     */
    public function assignCoreAdminPack(int $companyId, ?int $createdBy = null): PermissionRole
    {
        return $this->saveAdminPack($companyId, $this->defaultAdminMap(), $createdBy);
    }

    /**
     * Keep already-customized packs, but fill an empty company-admin pack with defaults.
     */
    public function assignCoreAdminPackIfEmpty(int $companyId, ?int $createdBy = null): PermissionRole
    {
        $role = PermissionRole::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('type', PermissionRole::TYPE_ADMIN_PACK)
            ->with('modules')
            ->first();

        if ($role && $role->modules->isNotEmpty()) {
            return $role;
        }

        return $this->assignCoreAdminPack($companyId, $createdBy);
    }

    public function defaultAdminMap(): array
    {
        $map = [];
        foreach (config('modules.defaults.admin', []) as $moduleKey => $actions) {
            $map[$moduleKey] = [
                'view' => in_array('view', $actions, true),
                'create' => in_array('create', $actions, true),
                'update' => in_array('update', $actions, true),
                'delete' => in_array('delete', $actions, true),
            ];
        }

        return $map;
    }

    /**
     * Create User / Site Engineer / Company Admin module roles from login roles
     * and attach users who do not already have a custom staff role.
     */
    public function ensureLoginModuleRoles(int $companyId): void
    {
        // Admin login_role is handled by the admin_pack itself (saveAdminPack/assignCoreAdminPack).
        // We do NOT create a separate "Company Admin" row here to avoid duplicates.
        $defs = [
            'user' => [
                'name' => 'User',
                'slug' => 'user',
                'type' => PermissionRole::TYPE_STAFF,
                'description' => 'Login role: User. Default modules for regular users.',
                'defaults' => config('modules.defaults.user', []),
            ],
            'site_engineer' => [
                'name' => 'Site Engineer',
                'slug' => 'site-engineer',
                'type' => PermissionRole::TYPE_STAFF,
                'description' => 'Login role: Site Engineer. Default project gallery access.',
                'defaults' => config('modules.defaults.site_engineer', []),
            ],
        ];

        $builtinIds = [];

        foreach ($defs as $loginRole => $def) {
            $role = PermissionRole::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where(function ($q) use ($loginRole, $def) {
                    $q->where('login_role', $loginRole)
                        ->orWhere(function ($inner) use ($def) {
                            $inner->where('slug', $def['slug'])->whereNull('login_role');
                        });
                })
                ->first();

            if (!$role && $loginRole === 'admin') {
                $role = PermissionRole::withoutGlobalScopes()
                    ->where('company_id', $companyId)
                    ->where('type', PermissionRole::TYPE_ADMIN_PACK)
                    ->first();
            }

            if (!$role) {
                $role = PermissionRole::withoutGlobalScopes()->create([
                    'company_id' => $companyId,
                    'name' => $def['name'],
                    'slug' => $def['slug'],
                    'type' => $def['type'],
                    'login_role' => $loginRole,
                    'description' => $def['description'],
                    'is_active' => true,
                ]);
            } else {
                $role->fill([
                    'login_role' => $loginRole,
                    'type' => $def['type'],
                ]);
                if (!$role->description) {
                    $role->description = $def['description'];
                }
                $role->save();
            }

            if ($def['defaults'] !== null && $role->modules()->count() === 0) {
                $incoming = [];
                foreach ($def['defaults'] as $moduleKey => $actions) {
                    $incoming[$moduleKey] = [
                        'view' => in_array('view', $actions, true),
                        'create' => in_array('create', $actions, true),
                        'update' => in_array('update', $actions, true),
                        'delete' => in_array('delete', $actions, true),
                    ];
                }
                $this->syncRoleModules($role, $incoming);
            }

            $builtinIds[$loginRole] = $role->id;
        }

        $this->trimStaffRolesToCeiling($companyId);

        foreach ($builtinIds as $loginRole => $roleId) {
            User::where('company_id', $companyId)
                ->where('role', $loginRole)
                ->whereNull('permission_role_id')
                ->update(['permission_role_id' => $roleId]);
        }

        $this->forgetCaches();
    }

    public function builtinRoleId(int $companyId, string $loginRole): ?int
    {
        return PermissionRole::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('login_role', $loginRole)
            ->value('id');
    }

    public function adminPackMatrix(?int $companyId): array
    {
        $map = $this->companyCeilingMap($companyId);

        return is_array($map) ? $map : [];
    }

    public function denyAccess(Request $request, User $user, string $message = 'You do not have permission to access this section.'): \Symfony\Component\HttpFoundation\Response
    {
        if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
            return response()->json(['success' => false, 'message' => $message], 403);
        }

        $current = $request->route()?->getName();
        $candidates = [];
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            $candidates[] = 'admin.dashboard';
        }
        $candidates[] = 'admin.projects.index';

        foreach ($candidates as $routeName) {
            if ($routeName === $current) {
                continue;
            }
            if ($this->userCanReachRoute($user, $routeName)) {
                return redirect()->route($routeName)->with('error', $message);
            }
        }

        abort(403, $message);
    }

    public function userCanReachRoute(User $user, string $routeName): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        $module = $this->moduleForRoute($routeName);
        if (!$module) {
            return true;
        }

        return $this->userCan($user, $module, 'view');
    }

    public function forgetCaches(): void
    {
        self::$ceilingCache = [];
        self::$mapCache = [];
    }

    private function mapFromRole(PermissionRole $role): array
    {
        $map = [];
        foreach ($role->modules as $row) {
            $map[$row->module_key] = [
                'view' => (bool) $row->can_view,
                'create' => (bool) $row->can_create,
                'update' => (bool) $row->can_update,
                'delete' => (bool) $row->can_delete,
            ];
        }

        return $map;
    }

    private function trimStaffRolesToCeiling(int $companyId): void
    {
        $ceiling = $this->companyCeilingMap($companyId);
        if ($ceiling === null) {
            return;
        }

        $staffRoles = PermissionRole::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('type', PermissionRole::TYPE_STAFF)
            ->with('modules')
            ->get();

        foreach ($staffRoles as $role) {
            foreach ($role->modules as $row) {
                $cap = $ceiling[$row->module_key] ?? null;
                if (!$cap) {
                    $row->delete();
                    continue;
                }

                $view = $row->can_view && !empty($cap['view']);
                $create = $row->can_create && !empty($cap['create']);
                $update = $row->can_update && !empty($cap['update']);
                $delete = $row->can_delete && !empty($cap['delete']);

                if (!$view && !$create && !$update && !$delete) {
                    $row->delete();
                    continue;
                }

                $row->update([
                    'can_view' => $view,
                    'can_create' => $create,
                    'can_update' => $update,
                    'can_delete' => $delete,
                ]);
            }
        }
    }
}
