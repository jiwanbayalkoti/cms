<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('advance_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('advance_payments', 'beneficiary_type')) {
                $table->string('beneficiary_type', 20)->default('supplier')->after('payment_type');
            }
            if (!Schema::hasColumn('advance_payments', 'subcontractor_id')) {
                $table->foreignId('subcontractor_id')->nullable()->after('supplier_id')->constrained('subcontractors')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('advance_payments', function (Blueprint $table) {
            if (Schema::hasColumn('advance_payments', 'subcontractor_id')) {
                $table->dropForeign(['subcontractor_id']);
                $table->dropColumn('subcontractor_id');
            }
            if (Schema::hasColumn('advance_payments', 'beneficiary_type')) {
                $table->dropColumn('beneficiary_type');
            }
        });
    }
};

