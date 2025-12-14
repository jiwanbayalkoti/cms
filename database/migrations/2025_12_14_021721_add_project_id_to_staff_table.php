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
        if (Schema::hasTable('staff') && !Schema::hasColumn('staff', 'project_id')) {
            Schema::table('staff', function (Blueprint $table) {
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
        if (Schema::hasTable('staff') && Schema::hasColumn('staff', 'project_id')) {
            Schema::table('staff', function (Blueprint $table) {
                $table->dropForeign(['project_id']);
                $table->dropColumn('project_id');
            });
        }
    }
};
