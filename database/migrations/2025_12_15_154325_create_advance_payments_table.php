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
        Schema::create('advance_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            
            // Payment type: vehicle_rent, supplier, other
            $table->enum('payment_type', ['vehicle_rent', 'supplier', 'other'])->default('other');
            
            // Related entity IDs (nullable, only one should be set based on payment_type)
            $table->foreignId('vehicle_rent_id')->nullable()->constrained('vehicle_rents')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            
            // For 'other' type, store reference manually
            $table->string('reference_type')->nullable(); // e.g., 'construction_material', 'staff_payment', etc.
            $table->string('reference_number')->nullable(); // Reference number or ID
            
            // Payment details
            $table->decimal('amount', 15, 2);
            $table->decimal('total_amount', 15, 2)->nullable(); // Total amount for the related entity
            $table->decimal('remaining_balance', 15, 2)->nullable(); // Calculated: total_amount - sum of all advance payments
            $table->date('payment_date');
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
            $table->string('payment_method')->nullable(); // cash, bank_transfer, cheque, etc.
            $table->string('transaction_reference')->nullable(); // Transaction ID, cheque number, etc.
            $table->text('notes')->nullable();
            
            // Metadata
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            // Indexes
            $table->index(['company_id', 'project_id']);
            $table->index('payment_type');
            $table->index('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advance_payments');
    }
};
