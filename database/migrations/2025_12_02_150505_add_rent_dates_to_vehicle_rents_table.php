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
        Schema::table('vehicle_rents', function (Blueprint $table) {
            // Add rent start and end dates for daywise rent calculation
            $table->date('rent_start_date')->nullable()->after('rent_date');
            $table->date('rent_end_date')->nullable()->after('rent_start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_rents', function (Blueprint $table) {
            $table->dropColumn(['rent_start_date', 'rent_end_date']);
        });
    }
};
