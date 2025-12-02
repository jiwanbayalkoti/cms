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
        Schema::create('construction_materials', function (Blueprint $table) {
            $table->id(); // material_id
            $table->string('material_name');
            $table->string('material_category')->nullable();
            $table->string('unit');
            $table->decimal('quantity_received', 10, 2)->default(0);
            $table->decimal('rate_per_unit', 10, 2)->default(0);
            $table->decimal('total_cost', 10, 2)->default(0);
            $table->decimal('quantity_used', 10, 2)->default(0);
            $table->decimal('quantity_remaining', 10, 2)->default(0);
            $table->decimal('wastage_quantity', 10, 2)->default(0);
            $table->string('supplier_name')->nullable();
            $table->string('supplier_contact')->nullable();
            $table->string('bill_number')->nullable();
            $table->date('bill_date')->nullable();
            $table->enum('payment_status', ['Paid', 'Unpaid', 'Partial'])->default('Unpaid');
            $table->string('payment_mode')->nullable();
            $table->date('delivery_date')->nullable();
            $table->string('delivery_site')->nullable();
            $table->string('delivered_by')->nullable();
            $table->string('received_by')->nullable();
            $table->string('project_name')->nullable();
            $table->string('work_type')->nullable();
            $table->text('usage_purpose')->nullable();
            $table->enum('status', ['Received', 'Pending', 'Returned', 'Damaged'])->default('Received');
            $table->string('approved_by')->nullable();
            $table->date('approval_date')->nullable();
            $table->string('bill_attachment')->nullable();
            $table->string('delivery_photo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('construction_materials');
    }
};


