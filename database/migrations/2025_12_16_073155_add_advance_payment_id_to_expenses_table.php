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
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('advance_payment_id')->nullable()->after('construction_material_id')
                ->constrained('advance_payments')->onDelete('set null');
            $table->index('advance_payment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['advance_payment_id']);
            $table->dropColumn('advance_payment_id');
        });
    }
};
