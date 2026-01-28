<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('measurement_books', function (Blueprint $table) {
            $table->string('dimension_unit', 20)->default('ft_in')->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('measurement_books', function (Blueprint $table) {
            $table->dropColumn('dimension_unit');
        });
    }
};
