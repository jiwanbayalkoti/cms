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
        Schema::table('salary_payments', function (Blueprint $table) {
            // Add partial payment tracking fields
            $table->decimal('paid_amount', 10, 2)->default(0)->after('net_amount');
            $table->decimal('balance_amount', 10, 2)->default(0)->after('paid_amount');
        });

        // Update status enum to include 'partial'
        DB::statement("ALTER TABLE salary_payments MODIFY COLUMN status ENUM('pending', 'partial', 'paid', 'cancelled') DEFAULT 'pending'");

        // Initialize paid_amount and balance_amount for existing records
        DB::statement("UPDATE salary_payments SET paid_amount = CASE WHEN status = 'paid' THEN net_amount ELSE 0 END, balance_amount = CASE WHEN status = 'paid' THEN 0 ELSE net_amount END");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salary_payments', function (Blueprint $table) {
            $table->dropColumn(['paid_amount', 'balance_amount']);
        });

        // Revert status enum
        DB::statement("ALTER TABLE salary_payments MODIFY COLUMN status ENUM('pending', 'paid', 'cancelled') DEFAULT 'pending'");
    }
};
