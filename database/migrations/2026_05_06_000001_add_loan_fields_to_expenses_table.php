<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            if (! Schema::hasColumn('expenses', 'loan_id')) {
                $table->foreignId('loan_id')->nullable()->after('advance_payment_id')
                    ->constrained('loans')->nullOnDelete();
                $table->index('loan_id');
            }
            if (! Schema::hasColumn('expenses', 'loan_payment_id')) {
                $table->foreignId('loan_payment_id')->nullable()->after('loan_id')
                    ->constrained('loan_payments')->cascadeOnDelete();
                $table->index('loan_payment_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            if (Schema::hasColumn('expenses', 'loan_payment_id')) {
                $table->dropForeign(['loan_payment_id']);
                $table->dropColumn('loan_payment_id');
            }
            if (Schema::hasColumn('expenses', 'loan_id')) {
                $table->dropForeign(['loan_id']);
                $table->dropColumn('loan_id');
            }
        });
    }
};
