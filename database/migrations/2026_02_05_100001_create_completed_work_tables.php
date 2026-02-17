<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('completed_work_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('boq_work_id')->constrained()->cascadeOnDelete();
            $table->date('record_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('completed_work_record_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('completed_work_record_id')->constrained()->cascadeOnDelete();
            $table->foreignId('boq_item_id')->constrained()->cascadeOnDelete();
            $table->decimal('completed_qty', 16, 4)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('completed_work_record_items');
        Schema::dropIfExists('completed_work_records');
    }
};
