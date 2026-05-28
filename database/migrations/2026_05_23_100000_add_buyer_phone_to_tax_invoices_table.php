<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tax_invoices', function (Blueprint $table) {
            if (! Schema::hasColumn('tax_invoices', 'buyer_phone')) {
                $table->string('buyer_phone', 50)->nullable()->after('buyer_pan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tax_invoices', function (Blueprint $table) {
            if (Schema::hasColumn('tax_invoices', 'buyer_phone')) {
                $table->dropColumn('buyer_phone');
            }
        });
    }
};
