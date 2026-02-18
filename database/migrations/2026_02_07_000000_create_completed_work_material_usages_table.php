<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('completed_work_material_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('completed_work_record_id')->constrained('completed_work_records')->cascadeOnDelete();
            $table->foreignId('construction_material_id')->constrained('construction_materials')->cascadeOnDelete();
            $table->decimal('quantity', 12, 4)->default(0);
            $table->timestamps();
            $table->unique(['completed_work_record_id', 'construction_material_id'], 'cwmu_record_material_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('completed_work_material_usages');
    }
};
