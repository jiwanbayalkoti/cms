<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bill_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('tax_rate_default', 5, 2)->default(13)->comment('Default VAT/TAX percentage');
            $table->decimal('overhead_default', 5, 2)->default(10)->comment('Default overhead percentage');
            $table->decimal('contingency_default', 5, 2)->default(5)->comment('Default contingency percentage');
            $table->string('currency', 3)->default('NPR');
            $table->json('work_categories')->nullable()->comment('Available work categories');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_settings');
    }
};
