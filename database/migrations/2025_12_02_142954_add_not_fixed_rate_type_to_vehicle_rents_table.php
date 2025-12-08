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
        // Update the rate_type enum to include not_fixed
        DB::statement("ALTER TABLE vehicle_rents MODIFY COLUMN rate_type ENUM('per_km', 'fixed', 'per_hour', 'daywise', 'per_quintal', 'not_fixed') DEFAULT 'fixed'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert the rate_type enum
        DB::statement("ALTER TABLE vehicle_rents MODIFY COLUMN rate_type ENUM('per_km', 'fixed', 'per_hour', 'daywise', 'per_quintal') DEFAULT 'fixed'");
    }
};
