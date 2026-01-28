<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('measurement_books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('contract_no')->nullable();
            $table->date('measurement_date');
            $table->string('title')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('measurement_book_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('measurement_book_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sn')->default(1);
            $table->text('works');
            $table->decimal('no', 12, 4)->default(1);
            $table->decimal('length_ft', 10, 4)->nullable();
            $table->decimal('length_in', 10, 4)->nullable();
            $table->decimal('breadth_ft', 10, 4)->nullable();
            $table->decimal('breadth_in', 10, 4)->nullable();
            $table->decimal('height_ft', 10, 4)->nullable();
            $table->decimal('height_in', 10, 4)->nullable();
            $table->decimal('quantity', 16, 4)->default(0);
            $table->decimal('total_qty', 16, 4)->nullable();
            $table->string('unit', 20)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('measurement_book_items');
        Schema::dropIfExists('measurement_books');
    }
};
