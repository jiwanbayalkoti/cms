<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('running_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('contract_no')->nullable();
            $table->date('bill_date');
            $table->string('bill_title'); // e.g. "1ST Running Bill"
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('running_bill_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('running_bill_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sn')->default(1);
            $table->text('description');
            $table->string('unit', 20)->nullable();
            $table->decimal('boq_qty', 16, 4)->default(0);   // As per BOQ
            $table->decimal('this_bill_qty', 16, 4)->default(0);
            $table->decimal('unit_price', 16, 2)->default(0);
            $table->decimal('total_price', 16, 2)->default(0); // this_bill_qty * unit_price
            $table->decimal('remaining_qty', 16, 4)->nullable(); // boq_qty - this_bill_qty (or computed)
            $table->string('remarks')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('running_bill_items');
        Schema::dropIfExists('running_bills');
    }
};
