<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('completed_works')) {
            Schema::create('completed_works', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
                $table->foreignId('bill_item_id')->nullable()->constrained('bill_items')->nullOnDelete();
                $table->foreignId('bill_category_id')->nullable()->constrained('bill_categories')->nullOnDelete();
                $table->foreignId('bill_subcategory_id')->nullable()->constrained('bill_subcategories')->nullOnDelete();
                $table->string('work_type')->comment('e.g., Soling, PCC, Masonry, etc.');
                $table->text('description');
                $table->string('uom')->comment('Unit of Measurement');
                $table->decimal('quantity', 12, 3)->default(0);
                $table->date('work_date');
                $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->enum('status', ['recorded', 'verified', 'billed'])->default('recorded');
                $table->text('remarks')->nullable();
                $table->json('attachments')->nullable()->comment('Array of photo/document URLs');
                $table->timestamps();
                
                $table->index(['company_id', 'project_id']);
                $table->index(['project_id', 'work_date']);
                $table->index('bill_item_id');
            });
        } else {
            // Table exists, alter it to add missing columns
            Schema::table('completed_works', function (Blueprint $table) {
                if (!Schema::hasColumn('completed_works', 'company_id')) {
                    $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete()->after('id');
                }
                if (!Schema::hasColumn('completed_works', 'project_id')) {
                    $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
                }
                if (!Schema::hasColumn('completed_works', 'bill_item_id')) {
                    $table->foreignId('bill_item_id')->nullable()->constrained('bill_items')->nullOnDelete();
                }
                if (!Schema::hasColumn('completed_works', 'bill_category_id')) {
                    $table->foreignId('bill_category_id')->nullable()->constrained('bill_categories')->nullOnDelete();
                }
                if (!Schema::hasColumn('completed_works', 'bill_subcategory_id')) {
                    $table->foreignId('bill_subcategory_id')->nullable()->constrained('bill_subcategories')->nullOnDelete();
                }
                if (!Schema::hasColumn('completed_works', 'work_type')) {
                    $table->string('work_type')->comment('e.g., Soling, PCC, Masonry, etc.');
                }
                if (!Schema::hasColumn('completed_works', 'description')) {
                    $table->text('description');
                }
                if (!Schema::hasColumn('completed_works', 'uom')) {
                    $table->string('uom')->comment('Unit of Measurement');
                }
                if (!Schema::hasColumn('completed_works', 'quantity')) {
                    $table->decimal('quantity', 12, 3)->default(0);
                }
                if (!Schema::hasColumn('completed_works', 'work_date')) {
                    $table->date('work_date');
                }
                if (!Schema::hasColumn('completed_works', 'recorded_by')) {
                    $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
                }
                if (!Schema::hasColumn('completed_works', 'status')) {
                    $table->enum('status', ['recorded', 'verified', 'billed'])->default('recorded');
                }
                if (!Schema::hasColumn('completed_works', 'remarks')) {
                    $table->text('remarks')->nullable();
                }
                if (!Schema::hasColumn('completed_works', 'attachments')) {
                    $table->json('attachments')->nullable()->comment('Array of photo/document URLs');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('completed_works');
    }
};
