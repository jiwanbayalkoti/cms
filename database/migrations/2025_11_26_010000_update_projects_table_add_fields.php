<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'company_id')) {
                $table->foreignId('company_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('companies')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('projects', 'client_name')) {
                $table->string('client_name')->nullable()->after('name');
            }

            if (!Schema::hasColumn('projects', 'description')) {
                $table->text('description')->nullable()->after('client_name');
            }

            if (!Schema::hasColumn('projects', 'status')) {
                $table->enum('status', ['planned', 'active', 'on_hold', 'completed', 'cancelled'])
                    ->default('planned')
                    ->after('description');
            }

            if (!Schema::hasColumn('projects', 'budget')) {
                $table->decimal('budget', 12, 2)->nullable()->after('status');
            }

            if (!Schema::hasColumn('projects', 'start_date')) {
                $table->date('start_date')->nullable()->after('budget');
            }

            if (!Schema::hasColumn('projects', 'end_date')) {
                $table->date('end_date')->nullable()->after('start_date');
            }

            if (!Schema::hasColumn('projects', 'created_by')) {
                $table->foreignId('created_by')
                    ->nullable()
                    ->after('end_date')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('projects', 'updated_by')) {
                $table->foreignId('updated_by')
                    ->nullable()
                    ->after('created_by')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $dropColumns = [
                'client_name',
                'description',
                'status',
                'budget',
                'start_date',
                'end_date',
            ];

            foreach ($dropColumns as $column) {
                if (Schema::hasColumn('projects', $column)) {
                    $table->dropColumn($column);
                }
            }

            if (Schema::hasColumn('projects', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }

            if (Schema::hasColumn('projects', 'updated_by')) {
                $table->dropConstrainedForeignId('updated_by');
            }

            if (Schema::hasColumn('projects', 'company_id')) {
                $table->dropConstrainedForeignId('company_id');
            }
        });
    }
};

