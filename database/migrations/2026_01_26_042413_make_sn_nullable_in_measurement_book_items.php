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
            $table->unsignedSmallInteger('sn')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('measurement_book_items', function (Blueprint $table) {
            $table->unsignedSmallInteger('sn')->default(1)->change();
        });
    }
};
