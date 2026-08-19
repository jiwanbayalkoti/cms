<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        $values = ['company_id' => null];
        if (Schema::hasColumn('users', 'permission_role_id')) {
            $values['permission_role_id'] = null;
        }

        DB::table('users')
            ->where('role', 'super_admin')
            ->update($values);
    }

    public function down(): void
    {
        // Cannot safely restore previous company links.
    }
};
