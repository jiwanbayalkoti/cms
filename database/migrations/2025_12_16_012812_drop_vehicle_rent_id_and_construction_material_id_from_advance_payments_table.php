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
        Schema::table('advance_payments', function (Blueprint $table) {
            // Drop foreign keys first
            if (Schema::hasColumn('advance_payments', 'vehicle_rent_id')) {
                // Drop foreign key constraint using Laravel's convention
                $table->dropForeign(['vehicle_rent_id']);
                $table->dropColumn('vehicle_rent_id');
            }
        });
        
        Schema::table('advance_payments', function (Blueprint $table) {
            if (Schema::hasColumn('advance_payments', 'construction_material_id')) {
                // Drop foreign key constraint using Laravel's convention
                $table->dropForeign(['construction_material_id']);
                $table->dropColumn('construction_material_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('advance_payments', function (Blueprint $table) {
            // Restore the columns
            $table->foreignId('vehicle_rent_id')->nullable()->after('payment_type')->constrained('vehicle_rents')->nullOnDelete();
            $table->foreignId('construction_material_id')->nullable()->after('vehicle_rent_id')->constrained('construction_materials')->nullOnDelete();
        });
    }
};
