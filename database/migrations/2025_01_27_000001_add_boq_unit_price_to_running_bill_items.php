<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('running_bill_items', function (Blueprint $table) {
            $table->decimal('boq_unit_price', 16, 2)->default(0)->after('boq_qty');
        });
    }

    public function down(): void
    {
        Schema::table('running_bill_items', function (Blueprint $table) {
            $table->dropColumn('boq_unit_price');
        });
    }
};
