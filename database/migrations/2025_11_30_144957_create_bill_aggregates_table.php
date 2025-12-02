<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bill_aggregates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_module_id')->unique()->constrained('bill_modules')->cascadeOnDelete();
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('tax_total', 14, 2)->default(0);
            $table->decimal('overhead_percent', 5, 2)->default(0);
            $table->decimal('overhead_amount', 14, 2)->default(0);
            $table->decimal('contingency_percent', 5, 2)->default(0);
            $table->decimal('contingency_amount', 14, 2)->default(0);
            $table->decimal('grand_total', 14, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_aggregates');
    }
};
