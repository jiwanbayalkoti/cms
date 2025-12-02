<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bill_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_module_id')->constrained('bill_modules')->cascadeOnDelete();
            $table->string('category')->index();
            $table->string('subcategory')->nullable();
            $table->text('description');
            $table->string('uom')->comment('Unit of Measurement');
            $table->decimal('quantity', 12, 3)->default(0);
            $table->decimal('unit_rate', 12, 2)->default(0);
            $table->decimal('amount', 14, 2)->default(0)->comment('quantity * unit_rate');
            $table->decimal('wastage_percent', 5, 2)->default(0);
            $table->decimal('effective_quantity', 12, 3)->default(0)->comment('quantity * (1 + wastage_percent/100)');
            $table->decimal('total_amount', 14, 2)->default(0)->comment('effective_quantity * unit_rate');
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->decimal('net_amount', 14, 2)->default(0)->comment('total_amount + tax');
            $table->json('attachments')->nullable()->comment('Array of file URLs');
            $table->json('rate_breakdown')->nullable()->comment('{material_cost, labor_cost, equipment_cost, transport_cost}');
            $table->text('remarks')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['bill_module_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_items');
    }
};
