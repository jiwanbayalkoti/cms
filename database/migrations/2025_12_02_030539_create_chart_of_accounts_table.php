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
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->string('account_code', 20); // e.g., 1001, 2001, 3001
            $table->string('account_name');
            $table->enum('account_type', [
                'asset',           // Assets (1xxx)
                'liability',       // Liabilities (2xxx)
                'equity',          // Equity (3xxx)
                'revenue',         // Revenue/Income (4xxx)
                'expense'          // Expenses (5xxx)
            ]);
            $table->enum('account_category', [
                'current_asset', 'fixed_asset', 'intangible_asset', 'other_asset',
                'current_liability', 'long_term_liability', 'other_liability',
                'capital', 'retained_earnings', 'reserves',
                'operating_revenue', 'other_revenue',
                'operating_expense', 'administrative_expense', 'financial_expense', 'other_expense'
            ])->nullable();
            $table->foreignId('parent_account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->integer('level')->default(1); // 1 = Main account, 2 = Sub-account, etc.
            $table->text('description')->nullable();
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->enum('balance_type', ['debit', 'credit'])->default('debit');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false); // System accounts cannot be deleted
            $table->integer('display_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            // Unique constraint per company
            $table->unique(['company_id', 'account_code']);
            $table->index(['company_id', 'account_type']);
            $table->index('account_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chart_of_accounts');
    }
};
