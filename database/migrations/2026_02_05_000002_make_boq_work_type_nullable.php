<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('boq_works', function (Blueprint $table) {
            $table->foreignId('boq_type_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('boq_works', function (Blueprint $table) {
            $table->foreignId('boq_type_id')->nullable(false)->change();
        });
    }
};
