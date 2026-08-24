<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add company_documents module to all existing admin_pack roles
     * so company admins can use the Documents feature without needing
     * Super Admin to manually re-grant it.
     */
    public function up(): void
    {
        $packs = DB::table('permission_roles')->where('type', 'admin_pack')->get();

        foreach ($packs as $pack) {
            $exists = DB::table('permission_role_modules')
                ->where('permission_role_id', $pack->id)
                ->where('module_key', 'company_documents')
                ->exists();

            if (!$exists) {
                DB::table('permission_role_modules')->insert([
                    'permission_role_id' => $pack->id,
                    'module_key'         => 'company_documents',
                    'can_view'           => true,
                    'can_create'         => true,
                    'can_update'         => true,
                    'can_delete'         => true,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('permission_role_modules')
            ->where('module_key', 'company_documents')
            ->delete();
    }
};
