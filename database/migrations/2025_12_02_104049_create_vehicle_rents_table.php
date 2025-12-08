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
        Schema::create('vehicle_rents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('vehicle_type'); // e.g., Car, Truck, Bus, Motorcycle, etc.
            $table->string('vehicle_number')->nullable(); // Vehicle registration number
            $table->string('driver_name')->nullable();
            $table->string('driver_contact')->nullable();
            $table->date('rent_date');
            $table->string('start_location');
            $table->string('destination_location');
            $table->decimal('distance_km', 10, 2)->nullable(); // Distance in kilometers
            $table->decimal('rate_per_km', 10, 2)->nullable(); // Rate per kilometer
            $table->decimal('fixed_rate', 10, 2)->nullable(); // Fixed rate for the trip
            $table->enum('rate_type', ['per_km', 'fixed'])->default('fixed');
            $table->decimal('total_amount', 15, 2);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('balance_amount', 15, 2)->default(0);
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid');
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
            $table->date('payment_date')->nullable();
            $table->text('notes')->nullable();
            $table->text('purpose')->nullable(); // Purpose of the trip
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->index(['company_id', 'rent_date']);
            $table->index('project_id');
            $table->index('payment_status');
            $table->index('vehicle_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_rents');
    }
};
