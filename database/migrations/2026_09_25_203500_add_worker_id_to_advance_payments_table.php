<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('advance_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('advance_payments', 'worker_id')) {
                $table->foreignId('worker_id')->nullable()->after('staff_id')->constrained('workers')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('advance_payments', function (Blueprint $table) {
            if (Schema::hasColumn('advance_payments', 'worker_id')) {
                $table->dropForeign(['worker_id']);
                $table->dropColumn('worker_id');
            }
        });
    }
};
