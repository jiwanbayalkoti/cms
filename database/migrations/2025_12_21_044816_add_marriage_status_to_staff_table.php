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
        Schema::table('staff', function (Blueprint $table) {
            $table->enum('marriage_status', ['single', 'married'])->default('single')->after('salary');
        });
        
        // Auto-sync assessment_type based on marriage_status
        DB::statement("UPDATE staff SET assessment_type = CASE WHEN marriage_status = 'married' THEN 'couple' ELSE 'single' END");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropColumn('marriage_status');
        });
    }
};
