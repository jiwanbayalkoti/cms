<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('completed_work_record_items', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('completed_work_record_id')->constrained('completed_work_record_items')->cascadeOnDelete();
            $table->text('description')->nullable()->after('parent_id');
            $table->decimal('no', 12, 4)->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('completed_work_record_items', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['description', 'no']);
        });
    }
};
