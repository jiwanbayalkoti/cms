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
        Schema::table('construction_materials', function (Blueprint $table) {
            $table->dropColumn('purchase_by');
            $table->unsignedBigInteger('purchased_by_id')->nullable()->after('payment_mode');
            $table->foreign('purchased_by_id')->references('id')->on('purchased_bies')->onDelete('set null');
            $table->index('purchased_by_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('construction_materials', function (Blueprint $table) {
            $table->dropForeign(['purchased_by_id']);
            $table->dropIndex(['purchased_by_id']);
            $table->dropColumn('purchased_by_id');
            $table->string('purchase_by')->nullable()->after('payment_mode');
        });
    }
};
