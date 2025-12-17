<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('vehicle_rent_id')->nullable()
                ->after('project_id')
                ->constrained('vehicle_rents')
                ->nullOnDelete();
            $table->index('vehicle_rent_id');
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['vehicle_rent_id']);
            $table->dropColumn('vehicle_rent_id');
        });
    }
};

