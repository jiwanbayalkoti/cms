<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            if (! Schema::hasColumn('incomes', 'loan_id')) {
                $table->foreignId('loan_id')->nullable()->after('project_id')
                    ->constrained('loans')->nullOnDelete();
                $table->index('loan_id');
            }
            if (! Schema::hasColumn('incomes', 'loan_payment_id')) {
                $table->foreignId('loan_payment_id')->nullable()->after('loan_id')
                    ->constrained('loan_payments')->nullOnDelete();
                $table->index('loan_payment_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            if (Schema::hasColumn('incomes', 'loan_payment_id')) {
                $table->dropForeign(['loan_payment_id']);
                $table->dropColumn('loan_payment_id');
            }
            if (Schema::hasColumn('incomes', 'loan_id')) {
                $table->dropForeign(['loan_id']);
                $table->dropColumn('loan_id');
            }
        });
    }
};
