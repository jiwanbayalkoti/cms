<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('incomes') && !Schema::hasColumn('incomes', 'project_id')) {
            Schema::table('incomes', function (Blueprint $table) {
                $table->foreignId('project_id')->nullable()->after('company_id')->constrained('projects')->nullOnDelete();
                $table->index('project_id');
            });
        }
        
        if (Schema::hasTable('expenses') && !Schema::hasColumn('expenses', 'project_id')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->foreignId('project_id')->nullable()->after('company_id')->constrained('projects')->nullOnDelete();
                $table->index('project_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('incomes') && Schema::hasColumn('incomes', 'project_id')) {
            Schema::table('incomes', function (Blueprint $table) {
                $table->dropForeign(['project_id']);
                $table->dropColumn('project_id');
            });
        }
        
        if (Schema::hasTable('expenses') && Schema::hasColumn('expenses', 'project_id')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->dropForeign(['project_id']);
                $table->dropColumn('project_id');
            });
        }
    }
};
