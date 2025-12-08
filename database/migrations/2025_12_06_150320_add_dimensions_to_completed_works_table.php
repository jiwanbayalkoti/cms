<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('completed_works', function (Blueprint $table) {
            $table->decimal('length', 12, 3)->nullable()->after('work_type');
            $table->decimal('width', 12, 3)->nullable()->after('length');
            $table->decimal('height', 12, 3)->nullable()->after('width');
        });
    }

    public function down(): void
    {
        Schema::table('completed_works', function (Blueprint $table) {
            $table->dropColumn(['length', 'width', 'height']);
        });
    }
};
