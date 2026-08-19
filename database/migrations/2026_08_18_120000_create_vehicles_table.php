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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('driver_staff_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->string('vehicle_number');
            $table->string('vehicle_type');
            $table->enum('ownership', ['own', 'hired'])->default('own');
            $table->string('driver_name')->nullable();
            $table->string('driver_contact')->nullable();
            $table->enum('status', ['active', 'under_repair', 'sold', 'inactive'])->default('active');
            $table->string('photo')->nullable();
            $table->text('notes')->nullable();
            $table->string('insurance_policy_no')->nullable();
            $table->date('registration_expiry')->nullable();
            $table->date('insurance_expiry')->nullable();
            $table->date('fitness_expiry')->nullable();
            $table->date('route_permit_expiry')->nullable();
            $table->date('tax_token_expiry')->nullable();
            $table->date('puc_expiry')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'vehicle_number']);
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'vehicle_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
