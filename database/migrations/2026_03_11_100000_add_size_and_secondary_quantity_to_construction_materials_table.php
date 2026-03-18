<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds optional size (e.g. 8mm, 10mm for Rod) and secondary quantity/unit (e.g. Kg alongside Bundle).
     */
    public function up(): void
    {
        Schema::table('construction_materials', function (Blueprint $table) {
            $table->string('size', 50)->nullable()->after('material_category');
            $table->decimal('quantity_secondary', 12, 4)->nullable()->after('quantity_remaining');
            $table->string('unit_secondary', 50)->nullable()->after('quantity_secondary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('construction_materials', function (Blueprint $table) {
            $table->dropColumn(['size', 'quantity_secondary', 'unit_secondary']);
        });
    }
};
