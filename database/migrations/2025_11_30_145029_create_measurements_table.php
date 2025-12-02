<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_item_id')->constrained('bill_items')->cascadeOnDelete();
            $table->foreignId('measured_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('measure_date');
            $table->decimal('measured_quantity', 12, 3);
            $table->text('note')->nullable();
            $table->json('photo_urls')->nullable();
            $table->string('mb_reference')->nullable()->comment('Measurement Book Reference');
            $table->timestamps();
            
            $table->index(['bill_item_id', 'measure_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('measurements');
    }
};
