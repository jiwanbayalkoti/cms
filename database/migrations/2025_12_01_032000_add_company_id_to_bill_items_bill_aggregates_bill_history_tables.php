<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add company_id to bill_items table
        if (Schema::hasTable('bill_items')) {
            $columns = DB::select("SHOW COLUMNS FROM bill_items");
            $columnNames = array_column($columns, 'Field');
            
            if (!in_array('company_id', $columnNames)) {
                // Get company_id from related bill_modules for existing rows
                DB::statement("ALTER TABLE bill_items ADD COLUMN company_id BIGINT UNSIGNED NULL AFTER bill_module_id");
                DB::statement("UPDATE bill_items bi INNER JOIN bill_modules bm ON bi.bill_module_id = bm.id SET bi.company_id = bm.company_id WHERE bi.company_id IS NULL");
                DB::statement("ALTER TABLE bill_items MODIFY COLUMN company_id BIGINT UNSIGNED NOT NULL");
                
                // Add foreign key constraint
                $foreignKeys = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'bill_items' AND COLUMN_NAME = 'company_id' AND REFERENCED_TABLE_NAME IS NOT NULL");
                if (empty($foreignKeys)) {
                    DB::statement("ALTER TABLE bill_items ADD CONSTRAINT bill_items_company_id_foreign FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE");
                }
                
                // Add index
                $indexes = DB::select("SHOW INDEX FROM bill_items WHERE Key_name = 'bill_items_company_id_index'");
                if (empty($indexes)) {
                    DB::statement("CREATE INDEX bill_items_company_id_index ON bill_items(company_id)");
                }
            }
        }

        // Add company_id to bill_aggregates table
        if (Schema::hasTable('bill_aggregates')) {
            $columns = DB::select("SHOW COLUMNS FROM bill_aggregates");
            $columnNames = array_column($columns, 'Field');
            
            if (!in_array('company_id', $columnNames)) {
                // Get company_id from related bill_modules for existing rows
                DB::statement("ALTER TABLE bill_aggregates ADD COLUMN company_id BIGINT UNSIGNED NULL AFTER bill_module_id");
                DB::statement("UPDATE bill_aggregates ba INNER JOIN bill_modules bm ON ba.bill_module_id = bm.id SET ba.company_id = bm.company_id WHERE ba.company_id IS NULL");
                DB::statement("ALTER TABLE bill_aggregates MODIFY COLUMN company_id BIGINT UNSIGNED NOT NULL");
                
                // Add foreign key constraint
                $foreignKeys = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'bill_aggregates' AND COLUMN_NAME = 'company_id' AND REFERENCED_TABLE_NAME IS NOT NULL");
                if (empty($foreignKeys)) {
                    DB::statement("ALTER TABLE bill_aggregates ADD CONSTRAINT bill_aggregates_company_id_foreign FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE");
                }
                
                // Add index
                $indexes = DB::select("SHOW INDEX FROM bill_aggregates WHERE Key_name = 'bill_aggregates_company_id_index'");
                if (empty($indexes)) {
                    DB::statement("CREATE INDEX bill_aggregates_company_id_index ON bill_aggregates(company_id)");
                }
            }
        }

        // Fix bill_history table - add missing columns if table exists but is incomplete
        if (Schema::hasTable('bill_history')) {
            $columns = DB::select("SHOW COLUMNS FROM bill_history");
            $columnNames = array_column($columns, 'Field');
            
            // Add bill_module_id if missing
            if (!in_array('bill_module_id', $columnNames)) {
                DB::statement("ALTER TABLE bill_history ADD COLUMN bill_module_id BIGINT UNSIGNED NOT NULL AFTER id");
                DB::statement("ALTER TABLE bill_history ADD CONSTRAINT bill_history_bill_module_id_foreign FOREIGN KEY (bill_module_id) REFERENCES bill_modules(id) ON DELETE CASCADE");
            }
            
            // Add company_id if missing
            if (!in_array('company_id', $columnNames)) {
                DB::statement("ALTER TABLE bill_history ADD COLUMN company_id BIGINT UNSIGNED NULL AFTER bill_module_id");
                DB::statement("UPDATE bill_history bh INNER JOIN bill_modules bm ON bh.bill_module_id = bm.id SET bh.company_id = bm.company_id WHERE bh.company_id IS NULL");
                DB::statement("ALTER TABLE bill_history MODIFY COLUMN company_id BIGINT UNSIGNED NOT NULL");
                
                // Add foreign key constraint
                $foreignKeys = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'bill_history' AND COLUMN_NAME = 'company_id' AND REFERENCED_TABLE_NAME IS NOT NULL");
                if (empty($foreignKeys)) {
                    DB::statement("ALTER TABLE bill_history ADD CONSTRAINT bill_history_company_id_foreign FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE");
                }
                
                // Add index
                $indexes = DB::select("SHOW INDEX FROM bill_history WHERE Key_name = 'bill_history_company_id_index'");
                if (empty($indexes)) {
                    DB::statement("CREATE INDEX bill_history_company_id_index ON bill_history(company_id)");
                }
            }
            
            // Add action if missing
            if (!in_array('action', $columnNames)) {
                DB::statement("ALTER TABLE bill_history ADD COLUMN action VARCHAR(255) NOT NULL COMMENT 'created, updated, submitted, approved, rejected, archived'");
                $indexes = DB::select("SHOW INDEX FROM bill_history WHERE Key_name = 'bill_history_action_index'");
                if (empty($indexes)) {
                    DB::statement("CREATE INDEX bill_history_action_index ON bill_history(action)");
                }
            }
            
            // Add user_id if missing
            if (!in_array('user_id', $columnNames)) {
                DB::statement("ALTER TABLE bill_history ADD COLUMN user_id BIGINT UNSIGNED NULL");
                DB::statement("ALTER TABLE bill_history ADD CONSTRAINT bill_history_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL");
            }
            
            // Add comment if missing
            if (!in_array('comment', $columnNames)) {
                DB::statement("ALTER TABLE bill_history ADD COLUMN comment TEXT NULL");
            }
            
            // Add changes if missing
            if (!in_array('changes', $columnNames)) {
                DB::statement("ALTER TABLE bill_history ADD COLUMN changes JSON NULL COMMENT 'JSON of changed fields'");
            }
            
            // Add composite index if both columns exist
            if (in_array('bill_module_id', $columnNames)) {
                try {
                    DB::statement("CREATE INDEX bill_history_bill_module_created_index ON bill_history(bill_module_id, created_at)");
                } catch (\Exception $e) {
                    // Index might already exist
                }
            }
        }
    }

    public function down(): void
    {
        // This migration fixes existing tables - no rollback needed
    }
};

