<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('categories', 'company_id')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->foreignId('company_id')->nullable()->after('id')->constrained('companies')->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('subcategories', 'company_id')) {
            Schema::table('subcategories', function (Blueprint $table) {
                $table->foreignId('company_id')->nullable()->after('id')->constrained('companies')->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('staff', 'company_id')) {
            Schema::table('staff', function (Blueprint $table) {
                $table->foreignId('company_id')->nullable()->after('id')->constrained('companies')->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('positions', 'company_id')) {
            Schema::table('positions', function (Blueprint $table) {
                $table->foreignId('company_id')->nullable()->after('id')->constrained('companies')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('categories', 'company_id')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropConstrainedForeignId('company_id');
            });
        }
        if (Schema::hasColumn('subcategories', 'company_id')) {
            Schema::table('subcategories', function (Blueprint $table) {
                $table->dropConstrainedForeignId('company_id');
            });
        }
        if (Schema::hasColumn('staff', 'company_id')) {
            Schema::table('staff', function (Blueprint $table) {
                $table->dropConstrainedForeignId('company_id');
            });
        }
        if (Schema::hasColumn('positions', 'company_id')) {
            Schema::table('positions', function (Blueprint $table) {
                $table->dropConstrainedForeignId('company_id');
            });
        }
    }
};


