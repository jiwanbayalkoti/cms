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
            // Tax calculation fields
            $table->enum('assessment_type', ['single', 'couple'])->default('single')->after('advance_deduction');
            $table->decimal('taxable_income', 10, 2)->default(0)->after('assessment_type'); // Annual taxable income
            $table->decimal('tax_amount', 10, 2)->default(0)->after('taxable_income'); // Monthly tax amount
            $table->decimal('tax_exempt_amount', 10, 2)->default(0)->after('tax_amount'); // Tax exempt allowances (if any)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salary_payments', function (Blueprint $table) {
            $table->dropColumn([
                'assessment_type',
                'taxable_income',
                'tax_amount',
                'tax_exempt_amount',
            ]);
        });
    }
};
