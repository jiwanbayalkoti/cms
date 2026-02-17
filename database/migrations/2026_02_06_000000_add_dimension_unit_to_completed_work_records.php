<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('completed_work_records', function (Blueprint $table) {
            $table->string('dimension_unit', 10)->default('m')->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('completed_work_records', function (Blueprint $table) {
            $table->dropColumn('dimension_unit');
        });
    }
};
