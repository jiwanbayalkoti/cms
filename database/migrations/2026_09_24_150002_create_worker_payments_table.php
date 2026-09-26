<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worker_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('worker_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('wage_type', ['daily', 'monthly']);
            $table->date('period_start');
            $table->date('period_end');
            $table->unsignedInteger('present_days')->default(0);
            $table->unsignedInteger('half_days')->default(0);
            $table->unsignedInteger('absent_days')->default(0);
            $table->unsignedInteger('leave_days')->default(0);
            $table->unsignedInteger('total_days')->default(0);
            $table->decimal('overtime_hours', 8, 2)->default(0);
            $table->decimal('daily_wage', 12, 2)->nullable();
            $table->decimal('monthly_salary', 12, 2)->nullable();
            $table->decimal('calculated_amount', 12, 2)->default(0);
            $table->decimal('overtime_amount', 12, 2)->default(0);
            $table->decimal('bonus', 12, 2)->default(0);
            $table->decimal('deduction', 12, 2)->default(0);
            $table->decimal('net_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->date('payment_date');
            $table->string('payment_method')->nullable();
            $table->enum('status', ['pending', 'partial', 'paid', 'cancelled'])->default('paid');
            $table->foreignId('expense_id')->nullable()->constrained('expenses')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'payment_date']);
            $table->index(['worker_id', 'period_start', 'period_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_payments');
    }
};
