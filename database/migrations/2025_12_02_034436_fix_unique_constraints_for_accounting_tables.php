<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix journal_entries entry_number to be unique per company
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropUnique(['entry_number']);
            $table->unique(['company_id', 'entry_number']);
        });
        
        // Fix purchase_invoices invoice_number to be unique per company
        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->dropUnique(['invoice_number']);
            $table->unique(['company_id', 'invoice_number']);
        });
        
        // Fix sales_invoices invoice_number to be unique per company
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->dropUnique(['invoice_number']);
            $table->unique(['company_id', 'invoice_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'entry_number']);
            $table->unique('entry_number');
        });
        
        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'invoice_number']);
            $table->unique('invoice_number');
        });
        
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'invoice_number']);
            $table->unique('invoice_number');
        });
    }
};
