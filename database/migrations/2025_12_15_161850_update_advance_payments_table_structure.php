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
            // Drop old columns
            if (Schema::hasColumn('advance_payments', 'vehicle_rent_id')) {
                $table->dropForeign(['vehicle_rent_id']);
                $table->dropColumn('vehicle_rent_id');
            }
            if (Schema::hasColumn('advance_payments', 'reference_type')) {
                $table->dropColumn('reference_type');
            }
            if (Schema::hasColumn('advance_payments', 'reference_number')) {
                $table->dropColumn('reference_number');
            }
            if (Schema::hasColumn('advance_payments', 'total_amount')) {
                $table->dropColumn('total_amount');
            }
            if (Schema::hasColumn('advance_payments', 'remaining_balance')) {
                $table->dropColumn('remaining_balance');
            }
            
            // Change payment_type enum
            $table->dropColumn('payment_type');
        });
        
        Schema::table('advance_payments', function (Blueprint $table) {
            // Add new payment_type with vehicle_rent and material_payment
            $table->enum('payment_type', ['vehicle_rent', 'material_payment'])->default('vehicle_rent')->after('project_id');
            
            // Add foreign keys for related entities
            $table->foreignId('vehicle_rent_id')->nullable()->after('payment_type')->constrained('vehicle_rents')->nullOnDelete();
            $table->foreignId('construction_material_id')->nullable()->after('vehicle_rent_id')->constrained('construction_materials')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('advance_payments', function (Blueprint $table) {
            // Drop new columns
            if (Schema::hasColumn('advance_payments', 'vehicle_rent_id')) {
                $table->dropForeign(['vehicle_rent_id']);
                $table->dropColumn('vehicle_rent_id');
            }
            if (Schema::hasColumn('advance_payments', 'construction_material_id')) {
                $table->dropForeign(['construction_material_id']);
                $table->dropColumn('construction_material_id');
            }
            
            $table->dropColumn('payment_type');
        });
        
        Schema::table('advance_payments', function (Blueprint $table) {
            // Restore old structure
            $table->enum('payment_type', ['vehicle_rent', 'supplier', 'other'])->default('other');
            $table->foreignId('vehicle_rent_id')->nullable()->constrained('vehicle_rents')->nullOnDelete();
            $table->string('reference_type')->nullable();
            $table->string('reference_number')->nullable();
            $table->decimal('total_amount', 15, 2)->nullable();
            $table->decimal('remaining_balance', 15, 2)->nullable();
        });
    }
};
