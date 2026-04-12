<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('companies', 'letterhead_meta_chs_value')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->string('letterhead_meta_chs_value', 100)->nullable();
            });
        }
        if (!Schema::hasColumn('companies', 'letterhead_meta_ps_value')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->string('letterhead_meta_ps_value', 100)->nullable();
            });
        }
        if (!Schema::hasColumn('companies', 'letterhead_meta_date_value')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->string('letterhead_meta_date_value', 100)->nullable();
            });
        }

        if (!Schema::hasTable('company_letterhead_exports')) {
            Schema::create('company_letterhead_exports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->string('path', 512);
                $table->string('file_name', 255);
                $table->timestamps();
                $table->index(['company_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('company_letterhead_exports');

        if (Schema::hasColumn('companies', 'letterhead_meta_date_value')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->dropColumn('letterhead_meta_date_value');
            });
        }
        if (Schema::hasColumn('companies', 'letterhead_meta_ps_value')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->dropColumn('letterhead_meta_ps_value');
            });
        }
        if (Schema::hasColumn('companies', 'letterhead_meta_chs_value')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->dropColumn('letterhead_meta_chs_value');
            });
        }
    }
};
