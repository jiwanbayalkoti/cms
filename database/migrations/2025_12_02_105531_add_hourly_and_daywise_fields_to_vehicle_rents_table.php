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
            // Add fields for hourly calculation (for excavator/JCV)
            $table->integer('hours')->nullable()->after('distance_km');
            $table->integer('minutes')->nullable()->after('hours');
            $table->decimal('rate_per_hour', 10, 2)->nullable()->after('rate_per_km');
            
            // Add fields for daywise calculation
            $table->integer('number_of_days')->nullable()->after('minutes');
            $table->decimal('rate_per_day', 10, 2)->nullable()->after('rate_per_hour');
        });
        
        // Update the rate_type enum to include per_hour and daywise
        DB::statement("ALTER TABLE vehicle_rents MODIFY COLUMN rate_type ENUM('per_km', 'fixed', 'per_hour', 'daywise') DEFAULT 'fixed'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_rents', function (Blueprint $table) {
            $table->dropColumn(['hours', 'minutes', 'rate_per_hour', 'number_of_days', 'rate_per_day']);
        });
        
        // Revert the rate_type enum
        DB::statement("ALTER TABLE vehicle_rents MODIFY COLUMN rate_type ENUM('per_km', 'fixed') DEFAULT 'fixed'");
    }
};
