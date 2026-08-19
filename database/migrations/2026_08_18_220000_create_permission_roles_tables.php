<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permission_roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->unique(['company_id', 'name']);
            $table->index('company_id');
        });

        Schema::create('permission_role_modules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('permission_role_id');
            $table->string('module_key', 80);
            $table->boolean('can_view')->default(false);
            $table->boolean('can_create')->default(false);
            $table->boolean('can_update')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->timestamps();

            $table->foreign('permission_role_id')->references('id')->on('permission_roles')->onDelete('cascade');
            $table->unique(['permission_role_id', 'module_key'], 'perm_role_module_unique');
        });

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'permission_role_id')) {
                $table->unsignedBigInteger('permission_role_id')->nullable()->after('role');
                $table->foreign('permission_role_id')->references('id')->on('permission_roles')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'permission_role_id')) {
                $table->dropForeign(['permission_role_id']);
                $table->dropColumn('permission_role_id');
            }
        });
        Schema::dropIfExists('permission_role_modules');
        Schema::dropIfExists('permission_roles');
    }
};
