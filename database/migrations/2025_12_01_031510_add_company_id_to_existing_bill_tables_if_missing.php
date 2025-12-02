<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Fix bill_categories table using raw SQL for reliability
        if (Schema::hasTable('bill_categories')) {
            $columns = DB::select("SHOW COLUMNS FROM bill_categories");
            $columnNames = array_column($columns, 'Field');
            
            // Add company_id if missing
            if (!in_array('company_id', $columnNames)) {
                // Set a default company_id for existing rows (use company_id = 1 or get from context)
                $defaultCompanyId = DB::table('companies')->value('id') ?? 1;
                DB::statement("ALTER TABLE bill_categories ADD COLUMN company_id BIGINT UNSIGNED NULL AFTER id");
                DB::statement("UPDATE bill_categories SET company_id = {$defaultCompanyId} WHERE company_id IS NULL");
                DB::statement("ALTER TABLE bill_categories MODIFY COLUMN company_id BIGINT UNSIGNED NOT NULL");
                
                // Check if foreign key already exists
                $foreignKeys = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'bill_categories' AND COLUMN_NAME = 'company_id' AND REFERENCED_TABLE_NAME IS NOT NULL");
                if (empty($foreignKeys)) {
                    DB::statement("ALTER TABLE bill_categories ADD CONSTRAINT bill_categories_company_id_foreign FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE");
                }
                
                // Check if index already exists
                $indexes = DB::select("SHOW INDEX FROM bill_categories WHERE Key_name = 'bill_categories_company_id_index'");
                if (empty($indexes)) {
                    DB::statement("CREATE INDEX bill_categories_company_id_index ON bill_categories(company_id)");
                }
            }
            
            // Add name if missing
            if (!in_array('name', $columnNames)) {
                DB::statement("ALTER TABLE bill_categories ADD COLUMN name VARCHAR(255) NOT NULL");
            }
            
            // Add description if missing
            if (!in_array('description', $columnNames)) {
                DB::statement("ALTER TABLE bill_categories ADD COLUMN description TEXT NULL");
            }
            
            // Add is_active if missing
            if (!in_array('is_active', $columnNames)) {
                DB::statement("ALTER TABLE bill_categories ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1");
                $indexes = DB::select("SHOW INDEX FROM bill_categories WHERE Key_name = 'bill_categories_is_active_index'");
                if (empty($indexes)) {
                    DB::statement("CREATE INDEX bill_categories_is_active_index ON bill_categories(is_active)");
                }
            }
            
            // Add sort_order if missing
            if (!in_array('sort_order', $columnNames)) {
                DB::statement("ALTER TABLE bill_categories ADD COLUMN sort_order INT NOT NULL DEFAULT 0");
            }
        } else {
            // Create table if it doesn't exist
            Schema::create('bill_categories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
                
                $table->index('company_id');
                $table->index('is_active');
            });
        }

        // Fix bill_subcategories table using raw SQL
        if (Schema::hasTable('bill_subcategories')) {
            $columns = DB::select("SHOW COLUMNS FROM bill_subcategories");
            $columnNames = array_column($columns, 'Field');
            
            // Add company_id if missing
            if (!in_array('company_id', $columnNames)) {
                DB::statement("ALTER TABLE bill_subcategories ADD COLUMN company_id BIGINT UNSIGNED NOT NULL AFTER id");
                DB::statement("ALTER TABLE bill_subcategories ADD CONSTRAINT bill_subcategories_company_id_foreign FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE");
            }
            
            // Add bill_category_id if missing
            if (!in_array('bill_category_id', $columnNames)) {
                DB::statement("ALTER TABLE bill_subcategories ADD COLUMN bill_category_id BIGINT UNSIGNED NOT NULL");
                DB::statement("ALTER TABLE bill_subcategories ADD CONSTRAINT bill_subcategories_bill_category_id_foreign FOREIGN KEY (bill_category_id) REFERENCES bill_categories(id) ON DELETE CASCADE");
            }
            
            // Add name if missing
            if (!in_array('name', $columnNames)) {
                DB::statement("ALTER TABLE bill_subcategories ADD COLUMN name VARCHAR(255) NOT NULL");
            }
            
            // Add description if missing
            if (!in_array('description', $columnNames)) {
                DB::statement("ALTER TABLE bill_subcategories ADD COLUMN description TEXT NULL");
            }
            
            // Add is_active if missing
            if (!in_array('is_active', $columnNames)) {
                DB::statement("ALTER TABLE bill_subcategories ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1");
                DB::statement("CREATE INDEX bill_subcategories_is_active_index ON bill_subcategories(is_active)");
            }
            
            // Add sort_order if missing
            if (!in_array('sort_order', $columnNames)) {
                DB::statement("ALTER TABLE bill_subcategories ADD COLUMN sort_order INT NOT NULL DEFAULT 0");
            }
            
            // Add composite index if both columns exist
            $columns = DB::select("SHOW COLUMNS FROM bill_subcategories");
            $columnNames = array_column($columns, 'Field');
            if (in_array('company_id', $columnNames) && in_array('bill_category_id', $columnNames)) {
                try {
                    DB::statement("CREATE INDEX bill_subcategories_company_category_index ON bill_subcategories(company_id, bill_category_id)");
                } catch (\Exception $e) {
                    // Index might already exist
                }
            }
        } else {
            // Create table if it doesn't exist
            Schema::create('bill_subcategories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('bill_category_id')->constrained('bill_categories')->cascadeOnDelete();
                $table->string('name');
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
                
                $table->index(['company_id', 'bill_category_id']);
                $table->index('is_active');
            });
        }
    }

    public function down(): void
    {
        // This migration fixes existing tables - no rollback needed
    }
};
