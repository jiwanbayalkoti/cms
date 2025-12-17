<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('advance_payments', function (Blueprint $table) {
            // Change payment_type from ENUM to VARCHAR to support dynamic payment types
            $table->string('payment_type', 100)->change();
        });
    }
    
    public function down(): void
    {
        Schema::table('advance_payments', function (Blueprint $table) {
            // Revert back to ENUM (if needed)
            $table->enum('payment_type', ['vehicle_rent', 'material_payment'])->default('vehicle_rent')->change();
        });
    }
};

