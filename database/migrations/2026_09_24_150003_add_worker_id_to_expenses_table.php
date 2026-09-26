<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            if (! Schema::hasColumn('expenses', 'worker_id')) {
                $table->foreignId('worker_id')->nullable()->after('staff_id')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('expenses', 'worker_payment_id')) {
                $table->foreignId('worker_payment_id')->nullable()->after('worker_id')->constrained()->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            if (Schema::hasColumn('expenses', 'worker_payment_id')) {
                $table->dropConstrainedForeignId('worker_payment_id');
            }
            if (Schema::hasColumn('expenses', 'worker_id')) {
                $table->dropConstrainedForeignId('worker_id');
            }
        });
    }
};
