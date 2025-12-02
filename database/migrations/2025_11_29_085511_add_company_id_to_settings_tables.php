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
        foreach (['material_names', 'material_categories', 'material_units', 'payment_modes', 'suppliers'] as $tbl) {
            Schema::table($tbl, function (Blueprint $table) use ($tbl) {
                $table->foreignId('company_id')->nullable()->after('id')->constrained('companies')->onDelete('cascade');
                $table->index('company_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['material_names', 'material_categories', 'material_units', 'payment_modes', 'suppliers'] as $tbl) {
            Schema::table($tbl, function (Blueprint $table) {
                $table->dropConstrainedForeignId('company_id');
                $table->dropIndex(['company_id']);
            });
        }
    }
};
