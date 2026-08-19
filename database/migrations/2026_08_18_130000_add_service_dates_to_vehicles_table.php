<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->date('last_service_date')->nullable()->after('puc_expiry');
            $table->date('next_service_date')->nullable()->after('last_service_date');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['last_service_date', 'next_service_date']);
        });
    }
};
