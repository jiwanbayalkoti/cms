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
            $table->unsignedBigInteger('stakeholder_id')->nullable()->after('purchase_by');
            $table->foreign('stakeholder_id')->references('id')->on('stakeholders')->onDelete('set null');
            $table->index('stakeholder_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('construction_materials', function (Blueprint $table) {
            $table->dropForeign(['stakeholder_id']);
            $table->dropIndex(['stakeholder_id']);
            $table->dropColumn('stakeholder_id');
        });
    }
};
