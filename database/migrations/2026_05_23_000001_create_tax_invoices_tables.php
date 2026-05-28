<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('invoice_number', 50);
            $table->date('invoice_date');
            $table->string('transaction_date_bs', 20)->nullable();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('buyer_name')->nullable();
            $table->text('buyer_address')->nullable();
            $table->string('buyer_pan', 20)->nullable();
            $table->enum('payment_method', ['cash', 'cheque', 'credit', 'other'])->default('cash');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_percent', 8, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('taxable_amount', 15, 2)->default(0);
            $table->decimal('vat_percent', 5, 2)->default(13);
            $table->decimal('vat_amount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->text('amount_in_words')->nullable();
            $table->string('template', 40)->default('nepali_annex5');
            $table->enum('status', ['draft', 'issued', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->string('reference_number', 100)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'invoice_number']);
            $table->index(['company_id', 'invoice_date']);
        });

        Schema::create('tax_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_invoice_id')->constrained('tax_invoices')->cascadeOnDelete();
            $table->unsignedSmallInteger('line_number')->default(1);
            $table->string('hs_code', 30)->nullable();
            $table->text('description');
            $table->decimal('quantity', 12, 4)->default(1);
            $table->string('unit', 30)->nullable();
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('line_amount', 15, 2)->default(0);
            $table->timestamps();

            $table->index(['tax_invoice_id', 'line_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_invoice_items');
        Schema::dropIfExists('tax_invoices');
    }
};
