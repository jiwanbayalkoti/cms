<?php

use App\Models\Company;
use App\Models\PermissionRole;
use App\Models\PermissionRoleModule;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permission_roles', function (Blueprint $table) {
            if (!Schema::hasColumn('permission_roles', 'type')) {
                $table->string('type', 30)->default('staff')->after('slug');
                $table->index(['company_id', 'type']);
            }
        });

        $moduleKeys = array_keys(config('modules.modules', []));

        Company::query()->each(function (Company $company) use ($moduleKeys) {
            $pack = PermissionRole::withoutGlobalScopes()
                ->where('company_id', $company->id)
                ->where('type', 'admin_pack')
                ->first();

            if (!$pack) {
                $pack = PermissionRole::withoutGlobalScopes()->create([
                    'company_id' => $company->id,
                    'name' => 'Company Admin',
                    'slug' => 'company-admin',
                    'type' => 'admin_pack',
                    'description' => 'Modules granted by Super Admin to this company admin.',
                    'is_active' => true,
                ]);

                foreach ($moduleKeys as $moduleKey) {
                    PermissionRoleModule::create([
                        'permission_role_id' => $pack->id,
                        'module_key' => $moduleKey,
                        'can_view' => true,
                        'can_create' => true,
                        'can_update' => true,
                        'can_delete' => true,
                    ]);
                }
            }

            User::where('company_id', $company->id)
                ->where('role', 'admin')
                ->update(['permission_role_id' => $pack->id]);
        });
    }

    public function down(): void
    {
        Schema::table('permission_roles', function (Blueprint $table) {
            if (Schema::hasColumn('permission_roles', 'type')) {
                $table->dropIndex(['company_id', 'type']);
                $table->dropColumn('type');
            }
        });
    }
};
