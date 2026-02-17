<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('completed_work_record_items', function (Blueprint $table) {
            $table->decimal('length', 16, 4)->nullable()->after('boq_item_id');
            $table->decimal('breadth', 16, 4)->nullable()->after('length');
            $table->decimal('height', 16, 4)->nullable()->after('breadth');
        });
    }

    public function down(): void
    {
        Schema::table('completed_work_record_items', function (Blueprint $table) {
            $table->dropColumn(['length', 'breadth', 'height']);
        });
    }
};
