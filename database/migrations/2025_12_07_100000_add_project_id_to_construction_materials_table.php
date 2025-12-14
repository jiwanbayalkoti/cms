<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('construction_materials', function (Blueprint $table) {
            if (!Schema::hasColumn('construction_materials', 'project_id')) {
                $table->foreignId('project_id')
                    ->nullable()
                    ->after('company_id')
                    ->constrained('projects')
                    ->nullOnDelete();
                $table->index('project_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('construction_materials', function (Blueprint $table) {
            if (Schema::hasColumn('construction_materials', 'project_id')) {
                $table->dropForeign(['project_id']);
                $table->dropIndex(['project_id']);
                $table->dropColumn('project_id');
            }
        });
    }
};

