<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('companies', 'letterhead_chs_last_no')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->unsignedInteger('letterhead_chs_last_no')->default(0);
            });
        }
        if (!Schema::hasColumn('companies', 'letterhead_ps_last_no')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->unsignedInteger('letterhead_ps_last_no')->default(0);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('companies', 'letterhead_ps_last_no')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->dropColumn('letterhead_ps_last_no');
            });
        }
        if (Schema::hasColumn('companies', 'letterhead_chs_last_no')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->dropColumn('letterhead_chs_last_no');
            });
        }
    }
};
