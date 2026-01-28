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
        Schema::table('measurement_book_items', function (Blueprint $table) {
            if (!Schema::hasColumn('measurement_book_items', 'work_identifier')) {
                $table->string('work_identifier')->nullable()->after('works')->comment('e.g., f1-4, f2-5, c1, c2, c3');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('measurement_book_items', function (Blueprint $table) {
            if (Schema::hasColumn('measurement_book_items', 'work_identifier')) {
                $table->dropColumn('work_identifier');
            }
        });
    }
};
