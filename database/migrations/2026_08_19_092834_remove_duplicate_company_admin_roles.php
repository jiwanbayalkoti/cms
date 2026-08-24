<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * For each company, if there is both an admin_pack row AND a separate
     * login_role='admin' row (slug=company-admin), merge them:
     *   - keep the admin_pack row, update its login_role = 'admin'
     *   - re-point users from the duplicate row to the admin_pack
     *   - delete the duplicate row (and its permission_role_modules)
     */
    public function up(): void
    {
        $companies = DB::table('permission_roles')
            ->select('company_id')
            ->distinct()
            ->pluck('company_id');

        foreach ($companies as $companyId) {
            $adminPack = DB::table('permission_roles')
                ->where('company_id', $companyId)
                ->where('type', 'admin_pack')
                ->first();

            $loginRole = DB::table('permission_roles')
                ->where('company_id', $companyId)
                ->where('login_role', 'admin')
                ->where('type', '!=', 'admin_pack')
                ->first();

            if (!$adminPack || !$loginRole) {
                // Ensure admin_pack has login_role set
                if ($adminPack && !$adminPack->login_role) {
                    DB::table('permission_roles')
                        ->where('id', $adminPack->id)
                        ->update(['login_role' => 'admin']);
                }
                continue;
            }

            // Re-point users from duplicate to admin_pack
            DB::table('users')
                ->where('permission_role_id', $loginRole->id)
                ->update(['permission_role_id' => $adminPack->id]);

            // Delete duplicate's modules then the duplicate itself
            DB::table('permission_role_modules')
                ->where('permission_role_id', $loginRole->id)
                ->delete();

            DB::table('permission_roles')->where('id', $loginRole->id)->delete();

            // Ensure admin_pack has login_role = admin
            DB::table('permission_roles')
                ->where('id', $adminPack->id)
                ->update(['login_role' => 'admin']);
        }
    }

    public function down(): void
    {
        // Not reversible
    }
};
