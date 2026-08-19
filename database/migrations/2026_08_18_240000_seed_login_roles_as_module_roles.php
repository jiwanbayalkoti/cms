<?php

use App\Models\Company;
use App\Services\ModulePermissionService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permission_roles', function (Blueprint $table) {
            if (!Schema::hasColumn('permission_roles', 'login_role')) {
                $table->string('login_role', 30)->nullable()->after('type');
                $table->index(['company_id', 'login_role']);
            }
        });

        $service = app(ModulePermissionService::class);
        Company::query()->orderBy('id')->each(function (Company $company) use ($service) {
            $service->ensureLoginModuleRoles($company->id);
        });
    }

    public function down(): void
    {
        Schema::table('permission_roles', function (Blueprint $table) {
            if (Schema::hasColumn('permission_roles', 'login_role')) {
                $table->dropIndex(['company_id', 'login_role']);
                $table->dropColumn('login_role');
            }
        });
    }
};
