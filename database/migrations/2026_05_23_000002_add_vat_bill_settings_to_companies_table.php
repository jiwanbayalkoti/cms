<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (! Schema::hasColumn('companies', 'vat_bill_template')) {
                $table->string('vat_bill_template', 40)->default('english_standard')->after('tax_number');
            }
            if (! Schema::hasColumn('companies', 'vat_bill_accent_color')) {
                $table->string('vat_bill_accent_color', 20)->default('#f8d7da')->after('vat_bill_template');
            }
            if (! Schema::hasColumn('companies', 'default_vat_percent')) {
                $table->decimal('default_vat_percent', 5, 2)->default(13)->after('vat_bill_accent_color');
            }
            if (! Schema::hasColumn('companies', 'vat_bill_footer_text')) {
                $table->string('vat_bill_footer_text', 255)->nullable()->after('default_vat_percent');
            }
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            foreach (['vat_bill_template', 'vat_bill_accent_color', 'default_vat_percent', 'vat_bill_footer_text'] as $col) {
                if (Schema::hasColumn('companies', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
