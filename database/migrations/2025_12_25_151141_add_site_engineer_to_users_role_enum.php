<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the enum column to include 'site_engineer'
        // MySQL requires raw SQL to modify ENUM columns
        DB::statement("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('super_admin', 'admin', 'user', 'site_engineer') DEFAULT 'user'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values (remove 'site_engineer')
        // Note: This will fail if there are any users with 'site_engineer' role
        DB::statement("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('super_admin', 'admin', 'user') DEFAULT 'user'");
    }
};
