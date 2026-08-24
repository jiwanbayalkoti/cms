<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('document_type', 50);
            $table->string('label');
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->date('expiry_date')->nullable();
            $table->date('service_date')->nullable();
            $table->string('policy_number', 100)->nullable();
            $table->decimal('cost', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_current')->default(true);
            $table->timestamps();

            $table->index(['vehicle_id', 'document_type', 'is_current']);
            $table->index(['company_id', 'document_type']);
        });

        Schema::create('vehicle_document_renewals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_document_id')->nullable()->constrained('vehicle_documents')->nullOnDelete();
            $table->string('document_type', 50);
            $table->date('previous_expiry')->nullable();
            $table->date('new_expiry')->nullable();
            $table->date('service_date')->nullable();
            $table->string('previous_policy_number', 100)->nullable();
            $table->string('new_policy_number', 100)->nullable();
            $table->decimal('cost', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('renewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('renewed_at')->useCurrent();
            $table->timestamps();

            $table->index(['vehicle_id', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_document_renewals');
        Schema::dropIfExists('vehicle_documents');
    }
};
