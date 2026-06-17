<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('advance_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('advance_payments', 'staff_id')) {
                $table->foreignId('staff_id')->nullable()->after('subcontractor_id')->constrained('staff')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('advance_payments', function (Blueprint $table) {
            if (Schema::hasColumn('advance_payments', 'staff_id')) {
                $table->dropForeign(['staff_id']);
                $table->dropColumn('staff_id');
            }
        });
    }
};
