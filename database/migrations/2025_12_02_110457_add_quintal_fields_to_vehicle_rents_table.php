<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vehicle_rents', function (Blueprint $table) {
            // Add fields for quintal calculation
            $table->decimal('quantity_quintal', 10, 2)->nullable()->after('number_of_days');
            $table->decimal('rate_per_quintal', 10, 2)->nullable()->after('rate_per_day');
        });
        
        // Update the rate_type enum to include per_quintal
        DB::statement("ALTER TABLE vehicle_rents MODIFY COLUMN rate_type ENUM('per_km', 'fixed', 'per_hour', 'daywise', 'per_quintal') DEFAULT 'fixed'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_rents', function (Blueprint $table) {
            $table->dropColumn(['quantity_quintal', 'rate_per_quintal']);
        });
        
        // Revert the rate_type enum
        DB::statement("ALTER TABLE vehicle_rents MODIFY COLUMN rate_type ENUM('per_km', 'fixed', 'per_hour', 'daywise') DEFAULT 'fixed'");
    }
};
