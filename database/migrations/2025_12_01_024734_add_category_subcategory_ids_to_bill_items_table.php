<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bill_items', function (Blueprint $table) {
            $table->foreignId('bill_category_id')->nullable()->after('bill_module_id')->constrained('bill_categories')->nullOnDelete();
            $table->foreignId('bill_subcategory_id')->nullable()->after('bill_category_id')->constrained('bill_subcategories')->nullOnDelete();
            
            $table->index(['bill_category_id', 'bill_subcategory_id']);
        });
    }

    public function down(): void
    {
        Schema::table('bill_items', function (Blueprint $table) {
            $table->dropForeign(['bill_subcategory_id']);
            $table->dropForeign(['bill_category_id']);
            $table->dropIndex(['bill_category_id', 'bill_subcategory_id']);
            $table->dropColumn(['bill_category_id', 'bill_subcategory_id']);
        });
    }
};
