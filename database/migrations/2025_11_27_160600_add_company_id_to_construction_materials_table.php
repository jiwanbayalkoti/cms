<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('construction_materials', function (Blueprint $table) {
            $table->foreignId('company_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->cascadeOnDelete();
        });

        DB::table('construction_materials')
            ->whereNull('company_id')
            ->update(['company_id' => 1]);
    }

    public function down(): void
    {
        Schema::table('construction_materials', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });
    }
};

