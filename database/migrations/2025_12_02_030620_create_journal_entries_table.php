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
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->string('entry_number')->unique(); // Auto-generated: JE-YYYY-0001
            $table->date('entry_date');
            $table->text('description')->nullable();
            $table->text('reference')->nullable(); // Reference document number
            $table->enum('entry_type', [
                'manual',           // Manual journal entry
                'purchase',         // From purchase invoice
                'sales',            // From sales invoice
                'payment',          // From payment
                'receipt',          // From receipt
                'adjustment',       // Adjustment entry
                'closing'           // Year-end closing entry
            ])->default('manual');
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->decimal('total_debit', 15, 2)->default(0);
            $table->decimal('total_credit', 15, 2)->default(0);
            $table->boolean('is_posted')->default(false); // Posted entries cannot be edited
            $table->date('posted_at')->nullable();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->index(['company_id', 'entry_date']);
            $table->index('entry_number');
            $table->index('is_posted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
