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
        Schema::create('salary_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('staff_id')->constrained()->onDelete('restrict');
            
            // Payment period
            $table->date('payment_month'); // First day of month (YYYY-MM-01)
            $table->date('payment_date');
            
            // Salary calculation
            $table->decimal('base_salary', 10, 2); // From staff.salary
            $table->integer('working_days')->nullable(); // For partial month
            $table->integer('total_days')->nullable(); // Month total days
            $table->decimal('overtime_amount', 10, 2)->default(0);
            $table->decimal('bonus_amount', 10, 2)->default(0);
            $table->decimal('allowance_amount', 10, 2)->default(0);
            $table->decimal('deduction_amount', 10, 2)->default(0);
            $table->decimal('advance_deduction', 10, 2)->default(0); // If advance was given
            $table->decimal('gross_amount', 10, 2);
            $table->decimal('net_amount', 10, 2);
            
            // Payment details
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');
            $table->string('payment_method')->nullable();
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->onDelete('set null');
            $table->string('transaction_reference')->nullable();
            $table->text('notes')->nullable();
            
            // Integration with expenses
            $table->foreignId('expense_id')->nullable()->unique()->constrained('expenses')->onDelete('set null');
            
            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Indexes
            $table->index(['company_id', 'payment_month']);
            $table->index(['staff_id', 'payment_month']);
            $table->unique(['staff_id', 'payment_month', 'company_id']); // One salary per staff per month
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_payments');
    }
};
